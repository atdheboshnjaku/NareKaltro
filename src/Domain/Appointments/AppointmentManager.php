<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

use DateTimeImmutable;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Clients\ClientRepository;
use Fin\Narekaltro\Domain\Locations\LocationRepository;
use Fin\Narekaltro\Domain\Services\ServiceRepository;
use Fin\Narekaltro\Domain\Staff\StaffMember;
use Fin\Narekaltro\Domain\Staff\StaffRepository;
use Fin\Narekaltro\Http\NotFoundException;

final class AppointmentManager
{
	public function __construct(
		private AppointmentCalendarRepository $calendar,
		private AppointmentWriteRepository $writes,
		private ClientRepository $clients,
		private LocationRepository $locations,
		private ServiceRepository $services,
		private StaffRepository $staff,
		private AppointmentAccessControl $access
	) {
	}

	public function formOptions(AuthenticatedUser $actor): array
	{
		$scope = $this->access->scopeFor($actor);

		return [
			'locations' => array_values(array_filter(
				$this->locations->activeForAccount($actor->accountId),
				static fn (mixed $location): bool => $scope->includesLocation((int) $location->id)
			)),
			'employees' => array_values(array_filter(
				$this->staff->activeForAccount($actor->accountId),
				static fn (StaffMember $member): bool => $scope->includesLocation($member->locationId)
					&& $scope->includesEmployee($member->id)
			)),
			'services' => $this->services->activeForAccount($actor->accountId),
		];
	}

	public function create(AuthenticatedUser $actor, AppointmentFormData $input): int
	{
		[$data] = $this->validated($actor, $input);

		return $this->writes->create($actor->accountId, $data);
	}

	public function update(AuthenticatedUser $actor, int $appointmentId, AppointmentFormData $input): void
	{
		$appointment = $this->find($actor, $appointmentId);
		[$data, $editableCostServiceIds] = $this->validated($actor, $input, $appointment);
		$this->writes->update($appointmentId, $actor->accountId, $data, $editableCostServiceIds);
	}

	public function reschedule(
		AuthenticatedUser $actor,
		int $appointmentId,
		string $startDate,
		?string $endDate
	): void {
		$appointment = $this->find($actor, $appointmentId);
		$errors = [];
		$start = $this->date($startDate, 'start_date', true, $errors);
		$end = $this->date($endDate, 'end_date', false, $errors);

		if ($errors !== []) {
			throw new AppointmentValidationFailed($errors);
		}

		$mayUpdateEndDate = $this->canUseEndTime(
			$actor,
			$appointment->locationId,
			array_map(static fn (AppointmentService $service): int => $service->id, $appointment->services),
			$appointment->id
		);

		$this->writes->reschedule(
			$appointmentId,
			$actor->accountId,
			(string) $start,
			$end,
			$mayUpdateEndDate
		);
	}

	public function cancel(AuthenticatedUser $actor, int $appointmentId): void
	{
		$this->find($actor, $appointmentId);
		$this->writes->cancel($appointmentId, $actor->accountId);
	}

	/** @return array{canUseEndTime: bool, serviceCosts: array<int, array{canView: bool, canUpdate: bool, cost: ?string}>} */
	public function capabilities(
		AuthenticatedUser $actor,
		int $locationId,
		array $serviceIds,
		?int $appointmentId = null
	): array {
		$existing = $appointmentId === null ? null : $this->find($actor, $appointmentId);
		$errors = [];
		$this->assertTargets($actor, $locationId, $serviceIds, $existing, $errors, false);
		if ($errors !== []) {
			throw new AppointmentValidationFailed($errors);
		}
		$currentCosts = [];
		foreach ($existing?->services ?? [] as $service) {
			$currentCosts[$service->id] = $service->cost;
		}
		$serviceCosts = [];

		foreach ($serviceIds as $serviceId) {
			$context = new AppointmentPolicyContext($appointmentId, $locationId, $serviceId);
			$canView = $this->access->can($actor, AppointmentCapability::CostView, $context);
			$serviceCosts[$serviceId] = [
				'canView' => $canView,
				'canUpdate' => $this->access->canUpdateCosts($actor, $context),
				'cost' => $canView ? ($currentCosts[$serviceId] ?? null) : null,
			];
		}

		return [
			'canUseEndTime' => $this->canUseEndTime($actor, $locationId, $serviceIds, $appointmentId),
			'serviceCosts' => $serviceCosts,
		];
	}

	/** @return array{AppointmentFormData, list<int>} */
	private function validated(
		AuthenticatedUser $actor,
		AppointmentFormData $input,
		?ScheduledAppointment $existing = null
	): array {
		$errors = [];
		$start = $this->date($input->startDate, 'start_date', true, $errors);
		$end = $this->date($input->endDate, 'end_date', false, $errors);

		if ($existing === null && $this->clients->findActiveForAccount($input->clientId, $actor->accountId) === null) {
			$errors['client_id'] = 'Please select an active client.';
		}

		$this->assertTargets($actor, $input->locationId, $input->serviceIds, $existing, $errors);
		$employeeId = $this->employeeId($actor, $input, $existing, $errors);
		if ($errors !== []) {
			throw new AppointmentValidationFailed($errors);
		}

		$appointmentId = $existing?->id;
		$editableCostServiceIds = [];
		$acceptedCosts = [];
		foreach ($input->serviceIds as $serviceId) {
			$context = new AppointmentPolicyContext($appointmentId, $input->locationId, $serviceId);
			if (!$this->access->canUpdateCosts($actor, $context)) {
				continue;
			}

			$editableCostServiceIds[] = $serviceId;
			$value = $input->costs[$serviceId] ?? '';
			if ($value === '') {
				continue;
			}

			if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $value)) {
				$errors['service_cost_' . $serviceId] = 'Enter a valid service cost with up to two decimal places.';
				continue;
			}

			$acceptedCosts[$serviceId] = $value;
		}

		$mayUseEndTime = $this->canUseEndTime(
			$actor,
			$input->locationId,
			$input->serviceIds,
			$appointmentId
		);
		if (!$mayUseEndTime) {
			$end = $existing?->endDate;
		}

		if ($errors !== []) {
			throw new AppointmentValidationFailed($errors);
		}

		return [
			new AppointmentFormData(
				locationId: $input->locationId,
				employeeId: $employeeId,
				clientId: $existing?->clientId ?? $input->clientId,
				serviceIds: $input->serviceIds,
				startDate: (string) $start,
				endDate: $end,
				notes: $input->notes,
				costs: $acceptedCosts
			),
			$editableCostServiceIds,
		];
	}

	private function employeeId(
		AuthenticatedUser $actor,
		AppointmentFormData $input,
		?ScheduledAppointment $existing,
		array &$errors
	): int {
		if ($input->employeeId > 0) {
			$employeeId = $input->employeeId;
		} elseif ($existing?->employeeId !== null) {
			$employeeId = $existing->employeeId;
		} elseif ($existing === null) {
			$employeeId = $actor->id;
		} else {
			$errors['employee_id'] = 'Please select an active staff member.';

			return 0;
		}

		$employee = $this->staff->findActiveForAccount($employeeId, $actor->accountId);

		if ($employee === null) {
			$errors['employee_id'] = 'Please select an active staff member.';

			return 0;
		}

		if (!$this->access->scopeFor($actor)->includesEmployee($employeeId)) {
			$errors['employee_id'] = 'You do not have access to assign this employee.';
		}

		if (!$this->access->canAccessLocation($actor, $employee->locationId)) {
			$errors['employee_id'] = 'You do not have access to this employee.';
		}

		return $employeeId;
	}

	private function assertTargets(
		AuthenticatedUser $actor,
		int $locationId,
		array $serviceIds,
		?ScheduledAppointment $existing = null,
		array &$errors = [],
		bool $requireServices = true
	): void {
		if ($locationId < 1) {
			$errors['location_id'] = 'Please select a location.';
		} elseif (!$this->access->canAccessLocation($actor, $locationId)) {
			$errors['location_id'] = 'You do not have access to appointments in this location.';
		} elseif (
			$existing?->locationId !== $locationId
			&& $this->locations->findActiveForAccount($locationId, $actor->accountId) === null
		) {
			$errors['location_id'] = 'Please select an active location.';
		}

		if ($serviceIds === []) {
			if ($requireServices) {
				$errors['service_ids'] = 'Please select at least one service.';
			}
			return;
		}

		$currentIds = [];
		foreach ($existing?->services ?? [] as $service) {
			$currentIds[$service->id] = true;
		}

		foreach ($serviceIds as $serviceId) {
			$service = $this->services->findForAccount($serviceId, $actor->accountId);
			if ($service === null || (!$service->active && !isset($currentIds[$serviceId]))) {
				$errors['service_ids'] = 'A selected service is not available.';
			}
		}
	}

	private function find(AuthenticatedUser $actor, int $appointmentId): ScheduledAppointment
	{
		return $this->calendar->findActiveForAccount($appointmentId, $this->access->scopeFor($actor))
			?? throw new NotFoundException('Appointment not found.');
	}

	private function canUseEndTime(
		AuthenticatedUser $actor,
		int $locationId,
		array $serviceIds,
		?int $appointmentId
	): bool {
		if ($serviceIds === []) {
			return $this->access->can(
				$actor,
				AppointmentCapability::EndTimeUse,
				new AppointmentPolicyContext($appointmentId, $locationId)
			);
		}

		foreach ($serviceIds as $serviceId) {
			if (!$this->access->can(
				$actor,
				AppointmentCapability::EndTimeUse,
				new AppointmentPolicyContext($appointmentId, $locationId, (int) $serviceId)
			)) {
				return false;
			}
		}

		return true;
	}

	private function date(?string $value, string $field, bool $required, array &$errors): ?string
	{
		$value = trim((string) $value);
		if ($value === '') {
			if ($required) {
				$errors[$field] = 'Please select an appointment date and time.';
			}

			return null;
		}

		foreach (['Y-m-d\TH:i:s', 'Y-m-d\TH:i', 'Y-m-d H:i:s'] as $format) {
			$date = DateTimeImmutable::createFromFormat('!' . $format, $value);
			$check = DateTimeImmutable::getLastErrors();
			if ($date !== false && ($check === false || ($check['warning_count'] === 0 && $check['error_count'] === 0))) {
				return $date->format('Y-m-d H:i:s');
			}
		}

		$errors[$field] = 'Please select a valid appointment date and time.';

		return null;
	}
}

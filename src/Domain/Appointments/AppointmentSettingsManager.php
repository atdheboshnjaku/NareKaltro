<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Appointments;

use Fin\Narekaltro\Domain\Auth\AccessPolicyRepository;
use Fin\Narekaltro\Domain\Auth\AccountAccessPolicy;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Locations\LocationRepository;
use Fin\Narekaltro\Domain\Services\ServiceRepository;
use Fin\Narekaltro\Domain\Shared\PageRequest;
use Fin\Narekaltro\Domain\Shared\PageResult;
use Fin\Narekaltro\Domain\Staff\StaffRepository;
use Fin\Narekaltro\Http\NotFoundException;

final class AppointmentSettingsManager
{
	public function __construct(
		private AccessPolicyRepository $policies,
		private StaffRepository $staff,
		private LocationRepository $locations,
		private ServiceRepository $services,
		private AppointmentReferenceRepository $appointments
	) {
	}

	public function settings(string $accountId): array
	{
		$policy = $this->policy($accountId);
		$appointmentPolicy = AppointmentAccessPolicy::fromDocument($policy->appointmentPolicyDocument());

		return [
			'endTimeEnabled' => $appointmentPolicy->endTimeEnabled(),
			'rules' => array_map(
				fn (array $rule): array => $this->summarizeRule($accountId, $policy, $rule),
				$policy->appointmentRules()
			),
		];
	}

	public function settingsPage(string $accountId, PageRequest $page): array
	{
		$settings = $this->settings($accountId);

		return [
			'endTimeEnabled' => $settings['endTimeEnabled'],
			'rules' => PageResult::fromItems($settings['rules'], $page),
		];
	}

	public function editor(string $accountId, ?string $ruleId = null): array
	{
		$policy = $this->policy($accountId);
		$rule = $ruleId === null ? null : $policy->findAppointmentRule($ruleId);

		if ($ruleId !== null && $rule === null) {
			throw new NotFoundException('Appointment rule not found.');
		}

		$data = $rule === null
			? new AppointmentRuleFormData('', true, [], [], [], [], [])
			: AppointmentRuleFormData::fromDocument($rule);

		return [
			'ruleId' => $ruleId,
			'data' => $data,
			'roles' => $policy->rolesForAssignment(),
			'staff' => $this->selectableStaff($accountId, $data->userIds),
			'locations' => $this->selectableLocations($accountId, $data->locationIds),
			'services' => $this->selectableServices($accountId, $data->serviceIds),
		];
	}

	public function updateDefaults(AuthenticatedUser $actor, bool $endTimeEnabled): void
	{
		$policy = $this->policy($actor->accountId);
		$this->policies->save($policy->withAppointmentEndTimeDefault($endTimeEnabled), $actor->id);
	}

	public function create(AuthenticatedUser $actor, AppointmentRuleFormData $data): string
	{
		$this->assertValid($actor->accountId, $data);
		$ruleId = 'rule_' . bin2hex(random_bytes(8));
		$policy = $this->policy($actor->accountId);
		$this->policies->save($policy->withAppointmentRule($data->toDocument($ruleId)), $actor->id);

		return $ruleId;
	}

	public function update(AuthenticatedUser $actor, string $ruleId, AppointmentRuleFormData $data): void
	{
		$policy = $this->policy($actor->accountId);
		if ($policy->findAppointmentRule($ruleId) === null) {
			throw new NotFoundException('Appointment rule not found.');
		}

		$this->assertValid($actor->accountId, $data);
		$this->policies->save($policy->withAppointmentRule($data->toDocument($ruleId)), $actor->id);
	}

	public function deactivate(AuthenticatedUser $actor, string $ruleId): void
	{
		$policy = $this->policy($actor->accountId);
		$rule = $policy->findAppointmentRule($ruleId)
			?? throw new NotFoundException('Appointment rule not found.');
		$data = AppointmentRuleFormData::fromDocument($rule);
		$inactive = new AppointmentRuleFormData(
			name: $data->name,
			active: false,
			effects: $data->effects,
			roleIds: $data->roleIds,
			userIds: $data->userIds,
			locationIds: $data->locationIds,
			serviceIds: $data->serviceIds,
			appointmentIds: $data->appointmentIds
		);

		$this->policies->save($policy->withAppointmentRule($inactive->toDocument($ruleId)), $actor->id);
	}

	private function assertValid(string $accountId, AppointmentRuleFormData $data): void
	{
		$errors = $data->validate();
		$policy = $this->policy($accountId);

		foreach ($data->roleIds as $roleId) {
			if ($policy->findRole($roleId) === null) {
				$errors['roles'] = 'One of the selected roles is not available.';
				break;
			}
		}

		foreach ($data->userIds as $userId) {
			if ($this->staff->findForAccount($userId, $accountId) === null) {
				$errors['users'] = 'One of the selected users is not available.';
				break;
			}
		}

		foreach ($data->locationIds as $locationId) {
			if ($this->locations->findForAccount($locationId, $accountId) === null) {
				$errors['locations'] = 'One of the selected locations is not available.';
				break;
			}
		}

		foreach ($data->serviceIds as $serviceId) {
			if ($this->services->findForAccount($serviceId, $accountId) === null) {
				$errors['services'] = 'One of the selected services is not available.';
				break;
			}
		}

		foreach ($data->appointmentIds as $appointmentId) {
			if (!$this->appointments->existsForAccount($appointmentId, $accountId)) {
				$errors['appointments'] = 'The selected appointment is not available.';
				break;
			}
		}

		if ($errors !== []) {
			throw new AppointmentSettingsValidationFailed($errors);
		}
	}

	private function policy(string $accountId): AccountAccessPolicy
	{
		return $this->policies->find($accountId)
			?? throw new NotFoundException('Access policy not found for this account.');
	}

	private function summarizeRule(string $accountId, AccountAccessPolicy $policy, array $rule): array
	{
		$data = AppointmentRuleFormData::fromDocument($rule);
		$targets = [];

		foreach ($policy->roleNames($data->roleIds) as $role) {
			$targets[] = ['label' => $role, 'type' => 'Role'];
		}

		foreach ($data->userIds as $id) {
			$member = $this->staff->findForAccount($id, $accountId);
			$targets[] = ['label' => $member?->name ?? "User #{$id}", 'type' => 'User'];
		}

		foreach ($data->locationIds as $id) {
			$location = $this->locations->findForAccount($id, $accountId);
			$targets[] = ['label' => $location?->name ?? "Location #{$id}", 'type' => 'Location'];
		}

		foreach ($data->serviceIds as $id) {
			$service = $this->services->findForAccount($id, $accountId);
			$targets[] = ['label' => $service?->name ?? "Service #{$id}", 'type' => 'Service'];
		}

		foreach ($data->appointmentIds as $id) {
			$targets[] = ['label' => "Appointment #{$id}", 'type' => 'Appointment'];
		}

		$effects = [];
		foreach ($data->effects as $capability => $effect) {
			$type = AppointmentCapability::tryFrom($capability);
			$effects[] = [
				'label' => $type?->label() ?? $capability,
				'effect' => $effect,
			];
		}

		return [
			'id' => (string) ($rule['id'] ?? ''),
			'name' => $data->name,
			'active' => $data->active,
			'targets' => $targets,
			'effects' => $effects,
		];
	}

	private function selectableStaff(string $accountId, array $selectedIds): array
	{
		$items = [];
		foreach ($this->staff->activeForAccount($accountId) as $member) {
			$items[$member->id] = $member;
		}
		foreach ($selectedIds as $id) {
			$member = $this->staff->findForAccount($id, $accountId);
			if ($member !== null) {
				$items[$member->id] = $member;
			}
		}

		return array_values($items);
	}

	private function selectableLocations(string $accountId, array $selectedIds): array
	{
		$items = [];
		foreach ($this->locations->activeForAccount($accountId) as $location) {
			$items[$location->id] = $location;
		}
		foreach ($selectedIds as $id) {
			$location = $this->locations->findForAccount($id, $accountId);
			if ($location !== null) {
				$items[$location->id] = $location;
			}
		}

		return array_values($items);
	}

	private function selectableServices(string $accountId, array $selectedIds): array
	{
		$items = [];
		foreach ($this->services->activeForAccount($accountId) as $service) {
			$items[$service->id] = $service;
		}
		foreach ($selectedIds as $id) {
			$service = $this->services->findForAccount($id, $accountId);
			if ($service !== null) {
				$items[$service->id] = $service;
			}
		}

		return array_values($items);
	}
}

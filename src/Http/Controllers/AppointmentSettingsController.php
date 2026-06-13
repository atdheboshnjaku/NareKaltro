<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Appointments\AppointmentRuleFormData;
use Fin\Narekaltro\Domain\Appointments\AppointmentSettingsManager;
use Fin\Narekaltro\Domain\Appointments\AppointmentSettingsValidationFailed;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\Authorization;
use Fin\Narekaltro\Domain\Auth\Permission;

final class AppointmentSettingsController extends Controller
{
	public function __construct(
		View $view,
		private Authorization $auth,
		private AppointmentSettingsManager $settings
	) {
		parent::__construct($view);
	}

	public function index(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_SETTINGS_MANAGE);
		$page = $this->pagination($request, 25, 100);
		$settings = $this->settings->settingsPage($user->accountId, $page);

		return $this->render('appointments.settings.index', [
			'title' => 'Appointment Settings',
			'endTimeEnabled' => $settings['endTimeEnabled'],
			'rules' => $settings['rules']->items,
			'total' => $settings['rules']->total,
			'pagination' => $settings['rules'],
			'currentUser' => $user,
			'activeNav' => 'appointments',
			'navigationAccess' => $this->navigationAccess($this->auth),
		]);
	}

	public function updateDefaults(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_SETTINGS_MANAGE);
		$this->settings->updateDefaults(
			$user,
			(string) $request->input('end_time_enabled', '') === '1'
		);

		return $this->redirect('/appointments/settings');
	}

	public function create(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_SETTINGS_MANAGE);

		return $this->renderRule('appointments.settings.create', $user);
	}

	public function store(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_SETTINGS_MANAGE);
		$data = AppointmentRuleFormData::fromArray($request->all());

		try {
			$ruleId = $this->settings->create($user, $data);
		} catch (AppointmentSettingsValidationFailed $exception) {
			return $this->renderRule('appointments.settings.create', $user, null, $data, $exception->errors(), 422);
		}

		return $this->redirect('/appointments/settings/rules/edit?id=' . rawurlencode($ruleId));
	}

	public function edit(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_SETTINGS_MANAGE);
		$ruleId = (string) $request->query('id');

		return $this->renderRule('appointments.settings.edit', $user, $ruleId);
	}

	public function update(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_SETTINGS_MANAGE);
		$ruleId = (string) $request->input('id');
		$data = AppointmentRuleFormData::fromArray($request->all());

		try {
			$this->settings->update($user, $ruleId, $data);
		} catch (AppointmentSettingsValidationFailed $exception) {
			return $this->renderRule('appointments.settings.edit', $user, $ruleId, $data, $exception->errors(), 422);
		}

		return $this->redirect('/appointments/settings/rules/edit?id=' . rawurlencode($ruleId));
	}

	public function deactivate(Request $request): Response
	{
		$user = $this->auth->require(Permission::APPOINTMENTS_SETTINGS_MANAGE);
		$this->settings->deactivate($user, (string) $request->input('id'));

		return $this->redirect('/appointments/settings');
	}

	private function renderRule(
		string $template,
		AuthenticatedUser $user,
		?string $ruleId = null,
		?AppointmentRuleFormData $old = null,
		array $errors = [],
		int $status = 200
	): Response {
		$editor = $this->settings->editor($user->accountId, $ruleId);

		return $this->render($template, [
			'title' => $ruleId === null ? 'New Appointment Rule' : 'Edit Appointment Rule',
			'ruleId' => $ruleId,
			'old' => $old ?? $editor['data'],
			'roles' => $editor['roles'],
			'staff' => $editor['staff'],
			'locations' => $editor['locations'],
			'services' => $editor['services'],
			'errors' => $errors,
			'currentUser' => $user,
			'activeNav' => 'appointments',
			'navigationAccess' => $this->navigationAccess($this->auth),
		], $status);
	}
}

<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final class Permission
{
	public const DASHBOARD_VIEW = 'dashboard.view';

	public const APPOINTMENTS_VIEW = 'appointments.view';
	public const APPOINTMENTS_CREATE = 'appointments.create';
	public const APPOINTMENTS_UPDATE = 'appointments.update';
	public const APPOINTMENTS_DELETE = 'appointments.delete';
	public const APPOINTMENTS_COST_VIEW = 'appointments.cost.view';
	public const APPOINTMENTS_COST_UPDATE = 'appointments.cost.update';
	public const APPOINTMENTS_SETTINGS_MANAGE = 'appointments.settings.manage';
	public const APPOINTMENTS_SCOPE_ACCOUNT = 'appointments.scope.account';
	public const APPOINTMENTS_SCOPE_ASSIGNED_LOCATION = 'appointments.scope.assigned_location';
	public const APPOINTMENTS_SCOPE_ASSIGNED_EMPLOYEE = 'appointments.scope.assigned_employee';

	public const CLIENTS_VIEW = 'clients.view';
	public const CLIENTS_CREATE = 'clients.create';
	public const CLIENTS_UPDATE = 'clients.update';
	public const CLIENTS_DELETE = 'clients.delete';

	public const USERS_VIEW = 'users.view';
	public const USERS_CREATE = 'users.create';
	public const USERS_UPDATE = 'users.update';
	public const USERS_DELETE = 'users.delete';
	public const USERS_ACCESS_MANAGE = 'users.access.manage';

	public const ROLES_VIEW = 'roles.view';
	public const ROLES_MANAGE = 'roles.manage';

	public const SERVICES_VIEW = 'services.view';
	public const SERVICES_MANAGE = 'services.manage';

	public const LOCATIONS_VIEW = 'locations.view';
	public const LOCATIONS_MANAGE = 'locations.manage';

	public const REPORTS_VIEW = 'reports.view';
	public const REPORTS_SCOPE_ACCOUNT = 'reports.scope.account';
	public const REPORTS_SCOPE_ASSIGNED_LOCATION = 'reports.scope.assigned_location';
	public const REPORTS_VALUES_VIEW = 'reports.values.view';

	public static function catalog(): array
	{
		return [
			self::DASHBOARD_VIEW => [
				'group' => 'dashboard',
				'label' => 'View dashboard',
				'description' => 'Access the dashboard.',
			],
			self::APPOINTMENTS_VIEW => [
				'group' => 'appointments',
				'label' => 'View appointments',
				'description' => 'See appointments in calendar and lists.',
			],
			self::APPOINTMENTS_CREATE => [
				'group' => 'appointments',
				'label' => 'Create appointments',
				'description' => 'Create appointments.',
			],
			self::APPOINTMENTS_UPDATE => [
				'group' => 'appointments',
				'label' => 'Update appointments',
				'description' => 'Edit appointment details and scheduling.',
			],
			self::APPOINTMENTS_DELETE => [
				'group' => 'appointments',
				'label' => 'Delete appointments',
				'description' => 'Cancel or remove appointments.',
			],
			self::APPOINTMENTS_COST_VIEW => [
				'group' => 'appointments',
				'label' => 'View appointment costs',
				'description' => 'See service cost fields and totals on appointments.',
			],
			self::APPOINTMENTS_COST_UPDATE => [
				'group' => 'appointments',
				'label' => 'Update appointment costs',
				'description' => 'Create or edit appointment service costs.',
			],
			self::APPOINTMENTS_SETTINGS_MANAGE => [
				'group' => 'appointments',
				'label' => 'Manage appointment settings',
				'description' => 'Configure appointment visibility and behavior rules.',
			],
			self::APPOINTMENTS_SCOPE_ACCOUNT => [
				'group' => 'appointments',
				'label' => 'View account-wide appointments',
				'description' => 'See appointment calendar data across every location in this account.',
			],
			self::APPOINTMENTS_SCOPE_ASSIGNED_LOCATION => [
				'group' => 'appointments',
				'label' => 'View assigned-location appointments',
				'description' => 'See appointment calendar data only for the user assigned location.',
			],
			self::APPOINTMENTS_SCOPE_ASSIGNED_EMPLOYEE => [
				'group' => 'appointments',
				'label' => 'View assigned employee appointments',
				'description' => 'See appointments where this user is the selected employee.',
			],
			self::CLIENTS_VIEW => [
				'group' => 'clients',
				'label' => 'View clients',
				'description' => 'See client records.',
			],
			self::CLIENTS_CREATE => [
				'group' => 'clients',
				'label' => 'Create clients',
				'description' => 'Create client records.',
			],
			self::CLIENTS_UPDATE => [
				'group' => 'clients',
				'label' => 'Update clients',
				'description' => 'Edit client records.',
			],
			self::CLIENTS_DELETE => [
				'group' => 'clients',
				'label' => 'Delete clients',
				'description' => 'Deactivate client records.',
			],
			self::USERS_VIEW => [
				'group' => 'users',
				'label' => 'View staff users',
				'description' => 'See staff user records.',
			],
			self::USERS_CREATE => [
				'group' => 'users',
				'label' => 'Create staff users',
				'description' => 'Create staff user records.',
			],
			self::USERS_UPDATE => [
				'group' => 'users',
				'label' => 'Update staff users',
				'description' => 'Edit staff user records.',
			],
			self::USERS_DELETE => [
				'group' => 'users',
				'label' => 'Delete staff users',
				'description' => 'Deactivate staff user records.',
			],
			self::USERS_ACCESS_MANAGE => [
				'group' => 'users',
				'label' => 'Manage staff access',
				'description' => 'Assign roles and user-specific permission rules.',
			],
			self::ROLES_VIEW => [
				'group' => 'roles',
				'label' => 'View roles',
				'description' => 'See role and permission configuration.',
			],
			self::ROLES_MANAGE => [
				'group' => 'roles',
				'label' => 'Manage roles and permissions',
				'description' => 'Create roles and edit permission assignments.',
			],
			self::SERVICES_VIEW => [
				'group' => 'services',
				'label' => 'View services',
				'description' => 'See services.',
			],
			self::SERVICES_MANAGE => [
				'group' => 'services',
				'label' => 'Manage services',
				'description' => 'Create, edit, and deactivate services.',
			],
			self::LOCATIONS_VIEW => [
				'group' => 'locations',
				'label' => 'View locations',
				'description' => 'See business locations.',
			],
			self::LOCATIONS_MANAGE => [
				'group' => 'locations',
				'label' => 'Manage locations',
				'description' => 'Create, edit, and deactivate business locations.',
			],
			self::REPORTS_VIEW => [
				'group' => 'reports',
				'label' => 'View reports',
				'description' => 'See reports and analytics.',
			],
			self::REPORTS_SCOPE_ACCOUNT => [
				'group' => 'reports',
				'label' => 'View account-wide reports',
				'description' => 'See report data across every location in this account.',
			],
			self::REPORTS_SCOPE_ASSIGNED_LOCATION => [
				'group' => 'reports',
				'label' => 'View assigned-location reports',
				'description' => 'See report data only for the user assigned location.',
			],
			self::REPORTS_VALUES_VIEW => [
				'group' => 'reports',
				'label' => 'View report values',
				'description' => 'See money and booked value figures in reports and dashboard analytics.',
			],
		];
	}
}

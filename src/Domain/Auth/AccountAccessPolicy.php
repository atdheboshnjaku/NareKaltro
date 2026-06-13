<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

use Fin\Narekaltro\Domain\Appointments\AppointmentAccessPolicy;
use Fin\Narekaltro\Domain\Appointments\AppointmentCapability;
use Fin\Narekaltro\Domain\Appointments\AppointmentPolicyContext;
use Fin\Narekaltro\Domain\Appointments\AppointmentPolicyDecision;
use JsonException;
use RuntimeException;

final class AccountAccessPolicy
{
	private const PERMISSION_RENAMES = [
		'appointments.scope.assigned_performer' => Permission::APPOINTMENTS_SCOPE_ASSIGNED_EMPLOYEE,
	];

	private function __construct(
		public readonly string $accountId,
		private array $document,
		public readonly int $revision = 1
	) {
	}

	public static function defaults(string $accountId): self
	{
		$permissions = [];

		foreach (Permission::catalog() as $key => $definition) {
			$permissions[$key] = [
				'group' => $definition['group'],
				'label' => $definition['label'],
				'description' => $definition['description'] ?? '',
				'active' => true,
			];
		}

		return new self($accountId, [
			'permissions' => $permissions,
			'roles' => self::defaultRoles(),
			'role_id_map' => [
				'1' => 'role_client',
				'2' => 'role_employee',
				'3' => 'role_manager',
				'4' => 'role_admin',
			],
			'users' => [],
			'appointment_policy' => AppointmentAccessPolicy::defaults()->defaultDocument(),
		]);
	}

	public static function fromJson(string $accountId, string $json, int $revision): self
	{
		try {
			$document = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
		} catch (JsonException $exception) {
			throw new RuntimeException("Access policy for account [{$accountId}] contains invalid JSON.", previous: $exception);
		}

		if (!is_array($document)) {
			throw new RuntimeException("Access policy for account [{$accountId}] is not a JSON object.");
		}

		$policy = new self($accountId, $document, $revision);
		$policy->normalizePermissionKeys();
		$policy->appendNewCatalogPermissions();
		$policy->appendAppointmentPolicyDefaults();

		return $policy;
	}

	public function toJson(): string
	{
		return json_encode($this->document, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
	}

	public function rolesWithPermissions(): array
	{
		$roles = [];

		foreach ($this->roles() as $id => $role) {
			$role['id'] = $id;
			$role['status'] = (bool) ($role['active'] ?? false);
			$role['permissions'] = $this->permissionRows($role['permissions'] ?? []);
			$roles[] = $role;
		}

		usort($roles, static fn (array $left, array $right): int => strcasecmp(
			(string) ($left['name'] ?? ''),
			(string) ($right['name'] ?? '')
		));

		return $roles;
	}

	public function findRole(string $roleId): ?array
	{
		$role = $this->roles()[$roleId] ?? null;

		if (!is_array($role)) {
			return null;
		}

		return [
			'id' => $roleId,
			'name' => (string) ($role['name'] ?? ''),
			'description' => $role['description'] ?? null,
			'status' => (bool) ($role['active'] ?? false),
			'permissions' => array_values(array_filter($role['permissions'] ?? [], 'is_string')),
		];
	}

	public function permissions(): array
	{
		$permissions = [];

		foreach ($this->permissionDefinitions() as $key => $definition) {
			$permissions[] = [
				'slug' => $key,
				'permission_group' => (string) ($definition['group'] ?? ''),
				'label' => (string) ($definition['label'] ?? $key),
				'description' => (string) ($definition['description'] ?? ''),
				'active' => (bool) ($definition['active'] ?? false),
			];
		}

		usort($permissions, static function (array $left, array $right): int {
			return [$left['permission_group'], $left['label']]
				<=> [$right['permission_group'], $right['label']];
		});

		return $permissions;
	}

	public function allows(AuthenticatedUser $user, string $permission): bool
	{
		if ($user->accountId !== $this->accountId || !$this->permissionIsActive($permission)) {
			return false;
		}

		$userConfig = $this->userConfig($user->id, $user->roleId);
		$allowed = [];

		foreach ($userConfig['roles'] as $roleId) {
			$role = $this->roles()[$roleId] ?? null;

			if (!is_array($role) || !($role['active'] ?? false)) {
				continue;
			}

			foreach ($role['permissions'] ?? [] as $key) {
				if (is_string($key) && $this->permissionIsActive($key)) {
					$allowed[$key] = true;
				}
			}
		}

		foreach ($userConfig['allow'] as $key) {
			if ($this->permissionIsActive($key)) {
				$allowed[$key] = true;
			}
		}

		foreach ($userConfig['deny'] as $key) {
			unset($allowed[$key]);
		}

		return isset($allowed[$permission]);
	}

	public function hasRole(AuthenticatedUser $user, string $roleId): bool
	{
		if ($user->accountId !== $this->accountId) {
			return false;
		}

		$role = $this->roles()[$roleId] ?? null;

		return is_array($role)
			&& (bool) ($role['active'] ?? false)
			&& in_array($roleId, $this->userConfig($user->id, $user->roleId)['roles'], true);
	}

	public function appointmentDecision(
		AuthenticatedUser $user,
		AppointmentCapability $capability,
		AppointmentPolicyContext $context
	): AppointmentPolicyDecision {
		if ($user->accountId !== $this->accountId) {
			return new AppointmentPolicyDecision(false, 'foreign_account');
		}

		$permission = match ($capability) {
			AppointmentCapability::CostView => Permission::APPOINTMENTS_COST_VIEW,
			AppointmentCapability::CostUpdate => Permission::APPOINTMENTS_COST_UPDATE,
			AppointmentCapability::EndTimeUse => null,
		};

		if ($permission !== null && !$this->permissionIsActive($permission)) {
			return new AppointmentPolicyDecision(false, 'inactive_permission');
		}

		$policy = AppointmentAccessPolicy::fromDocument($this->document['appointment_policy'] ?? null);
		$roles = $this->userConfig($user->id, $user->roleId)['roles'];

		return $policy->decide(
			capability: $capability,
			context: $context,
			userId: $user->id,
			roleIds: $roles,
			permissionBaseline: $permission !== null && $this->allows($user, $permission)
		);
	}

	public function appointmentPolicyDocument(): array
	{
		$document = $this->document['appointment_policy'] ?? [];

		return is_array($document) ? $document : [];
	}

	#[\NoDiscard]
	public function withAppointmentEndTimeDefault(bool $enabled): self
	{
		$policy = clone $this;
		$document = $policy->appointmentPolicyDocument();
		$defaults = is_array($document['defaults'] ?? null) ? $document['defaults'] : [];
		$defaults[AppointmentCapability::EndTimeUse->value] = $enabled;
		$document['defaults'] = $defaults;
		$document['rules'] = is_array($document['rules'] ?? null) ? $document['rules'] : [];
		$policy->document['appointment_policy'] = $document;

		return $policy;
	}

	public function appointmentRules(): array
	{
		$rules = $this->appointmentPolicyDocument()['rules'] ?? [];

		return is_array($rules) ? array_values(array_filter($rules, 'is_array')) : [];
	}

	public function findAppointmentRule(string $ruleId): ?array
	{
		foreach ($this->appointmentRules() as $rule) {
			if (($rule['id'] ?? null) === $ruleId) {
				return $rule;
			}
		}

		return null;
	}

	#[\NoDiscard]
	public function withAppointmentRule(array $rule): self
	{
		$policy = clone $this;
		$document = $policy->appointmentPolicyDocument();
		$rules = $policy->appointmentRules();
		$ruleId = (string) ($rule['id'] ?? '');
		$updated = false;

		foreach ($rules as $index => $existing) {
			if (($existing['id'] ?? null) === $ruleId) {
				$rules[$index] = $rule;
				$updated = true;
				break;
			}
		}

		if (!$updated) {
			$rules[] = $rule;
		}

		$document['rules'] = $rules;
		$policy->document['appointment_policy'] = $document;

		return $policy;
	}

	public function addRole(RoleFormData $data): string
	{
		$roleId = 'role_' . bin2hex(random_bytes(8));
		$this->document['roles'][$roleId] = $this->roleDocument($data);

		return $roleId;
	}

	public function updateRole(string $roleId, RoleFormData $data): void
	{
		if (!isset($this->roles()[$roleId])) {
			throw new RuntimeException("Role [{$roleId}] does not exist in this account policy.");
		}

		$this->document['roles'][$roleId] = $this->roleDocument($data);
	}

	#[\NoDiscard]
	public function withUpdatedRole(string $roleId, RoleFormData $data): self
	{
		$policy = clone $this;
		$policy->updateRole($roleId, $data);

		return $policy;
	}

	public function rolesForAssignment(): array
	{
		$roles = [];

		foreach ($this->roles() as $id => $role) {
			if (!is_array($role)) {
				continue;
			}

			$roles[] = [
				'id' => $id,
				'name' => (string) ($role['name'] ?? ''),
				'active' => (bool) ($role['active'] ?? false),
			];
		}

		usort($roles, static fn (array $left, array $right): int => strcasecmp($left['name'], $right['name']));

		return $roles;
	}

	public function userAccess(int $userId, int $roleId): array
	{
		$customized = is_array($this->document['users'][(string) $userId] ?? null);
		$config = $this->userConfig($userId, $roleId);

		return [
			'customized' => $customized,
			'roles' => $config['roles'],
			'allow' => $config['allow'],
			'deny' => $config['deny'],
		];
	}

	public function roleNames(array $roleIds): array
	{
		$names = [];

		foreach ($this->strings($roleIds) as $roleId) {
			$role = $this->roles()[$roleId] ?? null;

			if (is_array($role)) {
				$names[] = (string) ($role['name'] ?? $roleId);
			}
		}

		return $names;
	}

	public function validateUserAccess(UserAccessFormData $data): array
	{
		$errors = $data->validate();
		$roles = $this->roles();
		$permissions = $this->permissionDefinitions();

		foreach ($data->roles as $roleId) {
			if (!isset($roles[$roleId])) {
				$errors['roles'] = 'One of the selected roles is not available.';
				break;
			}
		}

		foreach (array_merge($data->allow, $data->deny) as $permission) {
			if (!isset($permissions[$permission])) {
				$errors['permissions'] = 'One of the selected permissions is not available.';
				break;
			}
		}

		return $errors;
	}

	#[\NoDiscard]
	public function withUserAccess(int $userId, UserAccessFormData $data): self
	{
		$policy = clone $this;
		$policy->document['users'][(string) $userId] = [
			'roles' => $data->roles,
			'allow' => $data->allow,
			'deny' => $data->deny,
		];

		return $policy;
	}

	#[\NoDiscard]
	public function withoutUserAccess(int $userId): self
	{
		$policy = clone $this;
		unset($policy->document['users'][(string) $userId]);

		return $policy;
	}

	private function userConfig(int $userId, int $roleId): array
	{
		$config = $this->document['users'][(string) $userId] ?? null;

		if (is_array($config)) {
			return [
				'roles' => $this->strings($config['roles'] ?? []),
				'allow' => $this->strings($config['allow'] ?? []),
				'deny' => $this->strings($config['deny'] ?? []),
			];
		}

		$mappedRole = $this->document['role_id_map'][(string) $roleId] ?? null;

		return [
			'roles' => is_string($mappedRole) ? [$mappedRole] : [],
			'allow' => [],
			'deny' => [],
		];
	}

	private function permissionRows(array $keys): array
	{
		$rows = [];

		foreach ($this->strings($keys) as $key) {
			$definition = $this->permissionDefinitions()[$key] ?? null;

			if (!is_array($definition)) {
				continue;
			}

			$rows[] = [
				'slug' => $key,
				'label' => (string) ($definition['label'] ?? $key),
				'permission_group' => (string) ($definition['group'] ?? ''),
			];
		}

		return $rows;
	}

	private function permissionIsActive(string $key): bool
	{
		return (bool) ($this->permissionDefinitions()[$key]['active'] ?? false);
	}

	private function permissionDefinitions(): array
	{
		$definitions = $this->document['permissions'] ?? [];

		return is_array($definitions) ? $definitions : [];
	}

	private function roles(): array
	{
		$roles = $this->document['roles'] ?? [];

		return is_array($roles) ? $roles : [];
	}

	private function roleDocument(RoleFormData $data): array
	{
		$available = $this->permissionDefinitions();

		return [
			'name' => $data->name,
			'description' => $data->description,
			'active' => $data->active,
			'permissions' => array_values(array_filter(
				$data->permissions,
				static fn (string $key): bool => isset($available[$key])
			)),
		];
	}

	private function strings(mixed $items): array
	{
		if (!is_array($items)) {
			return [];
		}

		return array_values(array_filter($items, 'is_string'));
	}

	private function appendNewCatalogPermissions(): void
	{
		$added = [];
		$this->document['permissions'] = $this->permissionDefinitions();

		foreach (Permission::catalog() as $key => $definition) {
			if (isset($this->document['permissions'][$key])) {
				$active = (bool) ($this->document['permissions'][$key]['active'] ?? true);
				$this->document['permissions'][$key] = [
					'group' => $definition['group'],
					'label' => $definition['label'],
					'description' => $definition['description'] ?? '',
					'active' => $active,
				];

				continue;
			}

			$this->document['permissions'][$key] = [
				'group' => $definition['group'],
				'label' => $definition['label'],
				'description' => $definition['description'] ?? '',
				'active' => true,
			];
			$added[] = $key;
		}

		if ($added === []) {
			return;
		}

		$this->appendReportPermissionDefaults($added);
		$this->appendAppointmentScopePermissionDefaults($added);

		if (($this->document['role_id_map']['4'] ?? null) !== 'role_admin') {
			return;
		}

		$admin = $this->document['roles']['role_admin'] ?? null;
		if (!is_array($admin)) {
			return;
		}

		$assigned = $this->strings($admin['permissions'] ?? []);
		foreach ($added as $key) {
			if (!in_array($key, $assigned, true)) {
				$assigned[] = $key;
			}
		}

		$this->document['roles']['role_admin']['permissions'] = $assigned;
	}

	private function normalizePermissionKeys(): void
	{
		$this->document['permissions'] = $this->renamedPermissionDefinitions($this->permissionDefinitions());

		foreach ($this->roles() as $roleId => $role) {
			if (!is_array($role)) {
				continue;
			}

			$this->document['roles'][$roleId]['permissions'] = $this->renamedPermissionList($role['permissions'] ?? []);
		}

		$users = $this->document['users'] ?? [];
		if (!is_array($users)) {
			$this->document['users'] = [];

			return;
		}

		foreach ($users as $userId => $config) {
			if (!is_array($config)) {
				continue;
			}

			$this->document['users'][$userId]['allow'] = $this->renamedPermissionList($config['allow'] ?? []);
			$this->document['users'][$userId]['deny'] = $this->renamedPermissionList($config['deny'] ?? []);
		}
	}

	private function renamedPermissionDefinitions(array $definitions): array
	{
		foreach (self::PERMISSION_RENAMES as $old => $new) {
			if (!isset($definitions[$old])) {
				continue;
			}

			$definitions[$new] ??= $definitions[$old];
			unset($definitions[$old]);
		}

		return $definitions;
	}

	/** @return list<string> */
	private function renamedPermissionList(mixed $items): array
	{
		$renamed = [];

		foreach ($this->strings($items) as $key) {
			$key = self::PERMISSION_RENAMES[$key] ?? $key;
			$renamed[$key] = $key;
		}

		return array_values($renamed);
	}

	/** @param list<string> $added */
	private function appendAppointmentScopePermissionDefaults(array $added): void
	{
		if (!in_array(Permission::APPOINTMENTS_SCOPE_ACCOUNT, $added, true)) {
			return;
		}

		foreach ($this->roles() as $roleId => $role) {
			if (!is_array($role)) {
				continue;
			}

			$assigned = $this->strings($role['permissions'] ?? []);
			if (
				!in_array(Permission::APPOINTMENTS_VIEW, $assigned, true)
				|| in_array(Permission::APPOINTMENTS_SCOPE_ACCOUNT, $assigned, true)
			) {
				continue;
			}

			$assigned[] = Permission::APPOINTMENTS_SCOPE_ACCOUNT;
			$this->document['roles'][$roleId]['permissions'] = $assigned;
		}
	}

	/** @param list<string> $added */
	private function appendReportPermissionDefaults(array $added): void
	{
		$addsAccountScope = in_array(Permission::REPORTS_SCOPE_ACCOUNT, $added, true);
		$addsValues = in_array(Permission::REPORTS_VALUES_VIEW, $added, true);

		if (!$addsAccountScope && !$addsValues) {
			return;
		}

		foreach ($this->roles() as $roleId => $role) {
			if (!is_array($role)) {
				continue;
			}

			$assigned = $this->strings($role['permissions'] ?? []);
			if (!in_array(Permission::REPORTS_VIEW, $assigned, true)) {
				continue;
			}

			if ($addsAccountScope && !in_array(Permission::REPORTS_SCOPE_ACCOUNT, $assigned, true)) {
				$assigned[] = Permission::REPORTS_SCOPE_ACCOUNT;
			}

			if (
				$addsValues
				&& in_array(Permission::APPOINTMENTS_COST_VIEW, $assigned, true)
				&& !in_array(Permission::REPORTS_VALUES_VIEW, $assigned, true)
			) {
				$assigned[] = Permission::REPORTS_VALUES_VIEW;
			}

			$this->document['roles'][$roleId]['permissions'] = $assigned;
		}
	}

	private function appendAppointmentPolicyDefaults(): void
	{
		$document = $this->document['appointment_policy'] ?? null;
		if (!is_array($document)) {
			$this->document['appointment_policy'] = AppointmentAccessPolicy::defaults()->defaultDocument();

			return;
		}

		$defaults = is_array($document['defaults'] ?? null) ? $document['defaults'] : [];
		$key = AppointmentCapability::EndTimeUse->value;
		if (!array_key_exists($key, $defaults)) {
			$defaults[$key] = true;
		}

		$document['defaults'] = $defaults;
		if (!is_array($document['rules'] ?? null)) {
			$document['rules'] = [];
		}

		$this->document['appointment_policy'] = $document;
	}

	private static function defaultRoles(): array
	{
		return [
			'role_client' => [
				'name' => 'Client',
				'description' => 'External client compatibility role.',
				'active' => true,
				'permissions' => [Permission::DASHBOARD_VIEW],
			],
			'role_employee' => [
				'name' => 'Employee',
				'description' => 'Staff member with day-to-day appointment and client permissions.',
				'active' => true,
				'permissions' => [
					Permission::DASHBOARD_VIEW,
					Permission::APPOINTMENTS_VIEW,
					Permission::APPOINTMENTS_SCOPE_ACCOUNT,
					Permission::APPOINTMENTS_CREATE,
					Permission::APPOINTMENTS_UPDATE,
					Permission::CLIENTS_VIEW,
					Permission::CLIENTS_CREATE,
					Permission::CLIENTS_UPDATE,
					Permission::SERVICES_VIEW,
					Permission::LOCATIONS_VIEW,
				],
			],
			'role_manager' => [
				'name' => 'Manager',
				'description' => 'Staff manager with operational admin permissions.',
				'active' => true,
				'permissions' => [
					Permission::DASHBOARD_VIEW,
					Permission::APPOINTMENTS_VIEW,
					Permission::APPOINTMENTS_SCOPE_ACCOUNT,
					Permission::APPOINTMENTS_CREATE,
					Permission::APPOINTMENTS_UPDATE,
					Permission::APPOINTMENTS_DELETE,
					Permission::APPOINTMENTS_COST_VIEW,
					Permission::APPOINTMENTS_COST_UPDATE,
					Permission::CLIENTS_VIEW,
					Permission::CLIENTS_CREATE,
					Permission::CLIENTS_UPDATE,
					Permission::CLIENTS_DELETE,
					Permission::USERS_VIEW,
					Permission::USERS_CREATE,
					Permission::USERS_UPDATE,
					Permission::SERVICES_VIEW,
					Permission::SERVICES_MANAGE,
					Permission::LOCATIONS_VIEW,
					Permission::LOCATIONS_MANAGE,
					Permission::REPORTS_VIEW,
					Permission::REPORTS_SCOPE_ACCOUNT,
					Permission::REPORTS_VALUES_VIEW,
				],
			],
			'role_admin' => [
				'name' => 'Admin',
				'description' => 'Account administrator with all permissions.',
				'active' => true,
				'permissions' => array_keys(Permission::catalog()),
			],
		];
	}
}

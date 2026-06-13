<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

final class Authorization
{
	public function __construct(
		private CurrentUserProvider $users,
		private AccessControl $access
	) {
	}

	public function user(): AuthenticatedUser
	{
		return $this->users->user() ?? throw new AuthenticationRequired('Login required.');
	}

	public function can(string $permission): bool
	{
		$user = $this->users->user();

		return $this->access->can($user, $permission);
	}

	public function require(string $permission): AuthenticatedUser
	{
		$user = $this->user();

		if (!$this->access->can($user, $permission)) {
			throw new AuthorizationDenied("Missing permission [{$permission}].");
		}

		return $user;
	}

	public function requireRole(string $roleId): AuthenticatedUser
	{
		$user = $this->user();

		if (!$this->access->hasRole($user, $roleId)) {
			throw new AuthorizationDenied("Missing role [{$roleId}].");
		}

		return $user;
	}
}

<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Infrastructure\Auth;

use Fin\Narekaltro\Domain\Auth\AuthenticationService;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\CurrentUserProvider;
use Fin\Narekaltro\Core\Session;

final class SessionCurrentUserProvider implements CurrentUserProvider
{
	private ?AuthenticatedUser $user = null;

	private bool $resolved = false;

	public function __construct(private AuthenticationService $authentication)
	{
		$this->startSession();
	}

	#[\Override]
	public function user(): ?AuthenticatedUser
	{
		if ($this->resolved) {
			return $this->user;
		}

		$this->resolved = true;
		$userId = $_SESSION['userId'] ?? null;

		if ($userId !== null) {
			$this->user = $this->authentication->activeUserById((int) $userId);

			if ($this->user === null) {
				unset($_SESSION['userId'], $_SESSION['username']);

				return null;
			}

			$_SESSION['username'] = $this->user->name;

			return $this->user;
		}

		$rememberCookie = $_COOKIE['remember_me'] ?? null;
		$rememberCookie = is_string($rememberCookie)
			? filter_var($rememberCookie, FILTER_SANITIZE_FULL_SPECIAL_CHARS)
			: null;

		$this->user = $this->authentication->userFromRememberCookie($rememberCookie ?: null);

		if ($this->user === null) {
			return null;
		}

		session_regenerate_id(true);
		$_SESSION['userId'] = $this->user->id;
		$_SESSION['username'] = $this->user->name;

		return $this->user;
	}

	private function startSession(): void
	{
		Session::start();
	}
}

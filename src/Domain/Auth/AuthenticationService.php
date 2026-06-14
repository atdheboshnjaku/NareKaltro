<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

use Fin\Narekaltro\Domain\Shared\TransactionManager;

final class AuthenticationService
{
	public function __construct(
		private AuthenticationRepository $users,
		private AccountPolicyProvisioner $policies,
		private RegistrationMailer $mailer,
		private PasswordResetMailer $passwordResetMailer,
		private PasswordResetTokenService $passwordResetTokens,
		private TransactionManager $transactions
	) {
	}

	public function activeUserById(int $id): ?AuthenticatedUser
	{
		return $this->users->findActiveUserById($id);
	}

	public function attempt(string $email, string $password): ?AuthenticatedUser
	{
		$loginUser = $this->users->findActiveUserByEmail($email);

		if ($loginUser === null || !password_verify($password, $loginUser->passwordHash)) {
			return null;
		}

		return $loginUser->user;
	}

	public function remember(AuthenticatedUser $user): RememberMeToken
	{
		$token = RememberMeToken::generate();
		$this->users->replaceRememberToken($user->id, $token);

		return $token;
	}

	public function forget(int $userId): void
	{
		$this->users->deleteRememberTokens($userId);
	}

	public function userFromRememberCookie(?string $cookie): ?AuthenticatedUser
	{
		if ($cookie === null || $cookie === '') {
			return null;
		}

		$parts = explode(':', $cookie);
		if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
			return null;
		}

		$remembered = $this->users->findRememberedUser($parts[0]);
		if ($remembered === null || !password_verify($parts[1], $remembered->hashedValidator)) {
			return null;
		}

		return $remembered->user;
	}

	public function emailExists(string $email): bool
	{
		return $this->users->emailExists($email);
	}

	public function activeEmailExists(string $email): bool
	{
		return $this->users->activeEmailExists($email);
	}

	public function pendingVerificationForEmail(string $email): ?VerificationUser
	{
		return $this->users->findPendingVerificationByEmail($email);
	}

	public function registerAccount(string $email): bool
	{
		$pending = $this->pendingVerificationForEmail($email);
		if ($pending !== null) {
			return $this->mailer->sendVerification($pending->email, $pending->hash);
		}

		$accountId = uniqid('', true);
		$hash = $this->verificationHash();
		$this->transactions->transactional(function () use ($accountId, $email, $hash): void {
			$administratorId = $this->users->createPendingAccount($accountId, $email, $hash);
			$this->policies->provision($accountId, $administratorId);
		});

		return $this->mailer->sendVerification($email, $hash);
	}

	public function verificationUser(string $hash): ?VerificationUser
	{
		$hash = trim($hash);
		if ($hash === '') {
			return null;
		}

		return $this->users->findVerificationUser($hash);
	}

	public function completeVerification(string $hash, string $name, string $password): ?AuthenticatedUser
	{
		$verification = $this->verificationUser($hash);

		if ($verification === null || $verification->active) {
			return null;
		}

		$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
		if (!$this->users->completeVerification($verification->id, $hash, $name, $passwordHash)) {
			return null;
		}

		return $this->users->findActiveUserById($verification->id);
	}

	public function sendPasswordResetLink(string $email): ?string
	{
		$loginUser = $this->users->findActiveUserByEmail($email);
		if ($loginUser === null) {
			return null;
		}

		$token = $this->passwordResetTokens->issue($loginUser);
		if (!$this->passwordResetMailer->sendResetLink($loginUser->user->email ?? $email, $token)) {
			error_log('Password reset mail delivery failed for ' . $email);
		}

		return $token;
	}

	public function passwordResetUser(string $token): ?AuthenticatedUser
	{
		$userId = $this->passwordResetTokens->userId(trim($token));
		if ($userId === null) {
			return null;
		}

		$loginUser = $this->users->findActiveLoginUserById($userId);
		if ($loginUser === null || !$this->passwordResetTokens->isValidFor($token, $loginUser)) {
			return null;
		}

		return $loginUser->user;
	}

	public function resetPassword(string $token, string $password): ?AuthenticatedUser
	{
		$userId = $this->passwordResetTokens->userId(trim($token));
		if ($userId === null) {
			return null;
		}

		$loginUser = $this->users->findActiveLoginUserById($userId);
		if ($loginUser === null || !$this->passwordResetTokens->isValidFor($token, $loginUser)) {
			return null;
		}

		$passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
		if (!$this->users->updatePassword($loginUser->user->id, $passwordHash)) {
			return null;
		}

		$this->users->deleteRememberTokens($loginUser->user->id);

		return $this->users->findActiveUserById($loginUser->user->id);
	}

	private function verificationHash(): string
	{
		return bin2hex(random_bytes(16));
	}
}

<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Domain\Auth;

interface AuthenticationRepository
{
	public function findActiveUserById(int $id): ?AuthenticatedUser;

	public function findActiveUserByEmail(string $email): ?LoginUser;

	public function findActiveLoginUserById(int $id): ?LoginUser;

	public function findRememberedUser(string $selector): ?RememberedUser;

	public function replaceRememberToken(int $userId, RememberMeToken $token): void;

	public function deleteRememberTokens(int $userId): void;

	public function emailExists(string $email): bool;

	public function activeEmailExists(string $email): bool;

	public function createPendingAccount(string $accountId, string $email, string $hash): int;

	public function findVerificationUser(string $hash): ?VerificationUser;

	public function findPendingVerificationByEmail(string $email): ?VerificationUser;

	public function completeVerification(int $userId, string $hash, string $name, string $passwordHash): bool;

	public function updatePassword(int $userId, string $passwordHash): bool;
}

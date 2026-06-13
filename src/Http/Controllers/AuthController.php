<?php

declare(strict_types=1);

namespace Fin\Narekaltro\Http\Controllers;

use Fin\Narekaltro\Core\Request;
use Fin\Narekaltro\Core\Response;
use Fin\Narekaltro\Core\Session;
use Fin\Narekaltro\Core\View;
use Fin\Narekaltro\Domain\Auth\AuthenticationService;
use Fin\Narekaltro\Domain\Auth\AuthenticatedUser;
use Fin\Narekaltro\Domain\Auth\CurrentUserProvider;
use Fin\Narekaltro\Domain\Auth\PasswordResetThrottle;
use Fin\Narekaltro\Domain\Auth\RegistrationThrottle;

final class AuthController extends Controller
{
	private const COOKIE_NAME = 'remember_me';

	public function __construct(
		View $view,
		private AuthenticationService $authentication,
		private CurrentUserProvider $users,
		private RegistrationThrottle $registrationThrottle,
		private PasswordResetThrottle $passwordResetThrottle
	) {
		parent::__construct($view);
	}

	public function login(Request $request): Response
	{
		if ($this->users->user() instanceof AuthenticatedUser) {
			return $this->redirect('/');
		}

		return $this->renderAuth('auth.login', [
			'title' => 'Login',
			'errors' => [],
			'old' => ['email' => '', 'remember' => false],
		]);
	}

	public function authenticate(Request $request): Response
	{
		$email = trim((string) $request->input('email', ''));
		$password = (string) $request->input('password', '');
		$remember = $request->input('remember_me') !== null;
		$errors = [];

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = 'Please enter a valid email address';
		}

		if ($password === '') {
			$errors['password'] = 'Please enter your password';
		}

		$user = $errors === [] ? $this->authentication->attempt($email, $password) : null;
		if ($errors === [] && $user === null) {
			$errors['login'] = 'Email/Password combination is not correct!';
		}

		if ($errors !== []) {
			return $this->renderAuth('auth.login', [
				'title' => 'Login',
				'errors' => $errors,
				'old' => ['email' => $email, 'remember' => $remember],
			], 422);
		}

		$this->loginUser($user);
		if ($remember) {
			$this->rememberUser($request, $user);
		} else {
			$this->authentication->forget($user->id);
			$this->clearRememberCookie($request);
		}

		return $this->redirect('/');
	}

	public function register(Request $request): Response
	{
		if ($this->users->user() instanceof AuthenticatedUser) {
			return $this->redirect('/');
		}

		return $this->renderAuth('auth.register', [
			'title' => 'Register',
			'errors' => [],
			'old' => ['email' => ''],
			'registered' => false,
		]);
	}

	public function storeRegistration(Request $request): Response
	{
		$email = strtolower(trim((string) $request->input('email', '')));
		$errors = [];

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = 'Please enter a valid email address';
		}

		if ($errors !== []) {
			return $this->renderAuth('auth.register', [
				'title' => 'Register',
				'errors' => $errors,
				'old' => ['email' => $email],
				'registered' => false,
			], 422);
		}

		if (trim((string) $request->input('company_website', '')) !== '') {
			return $this->registrationSubmitted($request);
		}

		$throttle = $this->registrationThrottle->attempt($email, $this->requestIp($request));
		if (!$throttle->allowed) {
			return $this->registrationSubmitted($request);
		}

		if ($this->authentication->activeEmailExists($email)) {
			return $this->registrationSubmitted($request);
		}

		$pending = $this->authentication->pendingVerificationForEmail($email);
		if ($pending === null && !$this->authentication->emailExists($email)) {
			$this->sendRegistrationVerification($email);
			$pending = $this->authentication->pendingVerificationForEmail($email);
		} elseif ($pending !== null) {
			$this->sendRegistrationVerification($email);
		}

		return $this->registrationSubmitted($request);
	}

	public function forgotPassword(Request $request): Response
	{
		if ($this->users->user() instanceof AuthenticatedUser) {
			return $this->redirect('/');
		}

		return $this->renderAuth('auth.forgot-password', [
			'title' => 'Forgot Password',
			'errors' => [],
			'old' => ['email' => ''],
			'submitted' => false,
		]);
	}

	public function sendPasswordReset(Request $request): Response
	{
		$email = strtolower(trim((string) $request->input('email', '')));
		$errors = [];

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors['email'] = 'Please enter a valid email address';
		}

		if ($errors !== []) {
			return $this->renderAuth('auth.forgot-password', [
				'title' => 'Forgot Password',
				'errors' => $errors,
				'old' => ['email' => $email],
				'submitted' => false,
			], 422);
		}

		if (trim((string) $request->input('company_website', '')) !== '') {
			return $this->passwordResetSubmitted($request);
		}

		$throttle = $this->passwordResetThrottle->attempt($email, $this->requestIp($request));
		if (!$throttle->allowed) {
			return $this->passwordResetSubmitted($request);
		}

		$this->authentication->sendPasswordResetLink($email);

		return $this->passwordResetSubmitted($request);
	}

	public function resetPassword(Request $request): Response
	{
		if ($this->users->user() instanceof AuthenticatedUser) {
			return $this->redirect('/');
		}

		$token = trim((string) $request->query('token', ''));
		if ($this->authentication->passwordResetUser($token) === null) {
			return $this->redirect('/forgot-password');
		}

		return $this->renderPasswordReset($token);
	}

	public function completePasswordReset(Request $request): Response
	{
		if ($this->users->user() instanceof AuthenticatedUser) {
			return $this->redirect('/');
		}

		$token = trim((string) $request->input('token', ''));
		if ($this->authentication->passwordResetUser($token) === null) {
			return $this->redirect('/forgot-password');
		}

		$password = (string) $request->input('password', '');
		$passwordConfirm = (string) $request->input('password_confirm', '');
		$errors = [];

		if ($password === '') {
			$errors['password'] = 'Please enter your password';
		} elseif (strlen($password) < 8) {
			$errors['password'] = 'Password must be at least 8 characters';
		}

		if ($password !== $passwordConfirm) {
			$errors['password_confirm'] = 'Passwords do not match';
		}

		if ($errors !== []) {
			return $this->renderPasswordReset($token, $errors, 422);
		}

		$user = $this->authentication->resetPassword($token, $password);
		if ($user === null) {
			return $this->renderPasswordReset($token, ['reset' => 'Password reset failed, please request a new link'], 422);
		}

		$this->clearRememberCookie($request);
		$this->loginUser($user);

		return $this->redirect('/');
	}

	public function verify(Request $request): Response
	{
		if ($this->users->user() instanceof AuthenticatedUser) {
			return $this->redirect('/');
		}

		$hash = trim((string) $request->query('hash', ''));
		$verification = $this->authentication->verificationUser($hash);

		if ($verification === null) {
			return $this->redirect('/register');
		}

		if ($verification->active) {
			return $this->redirect('/login');
		}

		return $this->renderVerify($hash);
	}

	public function completeVerification(Request $request): Response
	{
		if ($this->users->user() instanceof AuthenticatedUser) {
			return $this->redirect('/');
		}

		$hash = trim((string) $request->input('hash', ''));
		$verification = $this->authentication->verificationUser($hash);

		if ($verification === null) {
			return $this->redirect('/register');
		}

		if ($verification->active) {
			return $this->redirect('/login');
		}

		$name = trim((string) $request->input('name', ''));
		$password = (string) $request->input('password', '');
		$errors = [];

		if ($name === '') {
			$errors['name'] = 'Please enter the users full name';
		}

		if ($password === '') {
			$errors['password'] = 'Please enter your password';
		}

		if ($errors !== []) {
			return $this->renderVerify($hash, $errors, ['name' => $name], 422);
		}

		$user = $this->authentication->completeVerification($hash, $name, $password);
		if ($user === null) {
			return $this->renderVerify($hash, ['login' => 'Email/Password combination is not correct!'], [
				'name' => $name,
			], 422);
		}

		$this->loginUser($user);

		return $this->redirect('/');
	}

	public function logout(Request $request): Response
	{
		$user = $this->users->user();
		if ($user instanceof AuthenticatedUser) {
			$this->authentication->forget($user->id);
		}

		$this->clearRememberCookie($request);
		$_SESSION = [];

		if (session_status() === PHP_SESSION_ACTIVE) {
			session_destroy();
		}

		return $this->redirect('/login');
	}

	public function index(Request $request): Response
	{
		return $this->redirect('/');
	}

	private function renderAuth(string $template, array $data = [], int $status = 200): Response
	{
		return Response::html($this->view->render($template, $data, 'layouts/auth'), $status);
	}

	private function renderVerify(
		string $hash,
		array $errors = [],
		array $old = ['name' => ''],
		int $status = 200
	): Response {
		return $this->renderAuth('auth.verify', [
			'title' => 'Verify',
			'hash' => $hash,
			'errors' => $errors,
			'old' => $old,
		], $status);
	}

	private function renderPasswordReset(
		string $token,
		array $errors = [],
		int $status = 200
	): Response {
		return $this->renderAuth('auth.reset-password', [
			'title' => 'Reset Password',
			'token' => $token,
			'errors' => $errors,
		], $status);
	}

	private function registrationSubmitted(Request $request): Response
	{
		return $this->renderAuth('auth.register', [
			'title' => 'Register',
			'errors' => [],
			'old' => ['email' => ''],
			'registered' => true,
		]);
	}

	private function passwordResetSubmitted(Request $request): Response
	{
		return $this->renderAuth('auth.forgot-password', [
			'title' => 'Forgot Password',
			'errors' => [],
			'old' => ['email' => ''],
			'submitted' => true,
		]);
	}

	private function sendRegistrationVerification(string $email): void
	{
		if (!$this->authentication->registerAccount($email)) {
			error_log('Registration verification mail delivery failed for ' . $email);
		}
	}

	private function loginUser(AuthenticatedUser $user): void
	{
		Session::start();
		session_regenerate_id(true);
		$_SESSION['userId'] = $user->id;
		$_SESSION['username'] = $user->name;
	}

	private function rememberUser(Request $request, AuthenticatedUser $user): void
	{
		$token = $this->authentication->remember($user);
		setcookie(self::COOKIE_NAME, $token->plainText, [
			'expires' => $token->expiresTimestamp(),
			'path' => '/',
			'secure' => $this->isSecure($request),
			'httponly' => true,
			'samesite' => 'Lax',
		]);
	}

	private function clearRememberCookie(Request $request): void
	{
		setcookie(self::COOKIE_NAME, '', [
			'expires' => time() - 3600,
			'path' => '/',
			'secure' => $this->isSecure($request),
			'httponly' => true,
			'samesite' => 'Lax',
		]);
	}

	private function isSecure(Request $request): bool
	{
		return strtolower((string) $request->server('HTTPS', '')) === 'on'
			|| (string) $request->server('SERVER_PORT', '') === '443'
			|| strtolower((string) $request->server('HTTP_X_FORWARDED_PROTO', '')) === 'https';
	}

	private function requestIp(Request $request): string
	{
		$forwardedFor = (string) $request->server('HTTP_X_FORWARDED_FOR', '');
		if ($forwardedFor !== '') {
			$ip = trim(explode(',', $forwardedFor)[0]);
			if (filter_var($ip, FILTER_VALIDATE_IP)) {
				return $ip;
			}
		}

		$remoteAddress = (string) $request->server('REMOTE_ADDR', '');

		return filter_var($remoteAddress, FILTER_VALIDATE_IP) ? $remoteAddress : 'unknown';
	}

}

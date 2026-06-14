<?php

$errors = $errors ?? [];
?>
<div class="login-ctn">
	<div class="login-intro-img">
		<img src="/assets/img/1.svg" alt="">
	</div>
	<div class="login-form-ctn">
		<div class="form-ctn">
			<h1>Set New Password</h1>
			<form action="/reset-password?token=<?php echo e($token); ?>" method="post" class="login-form">
				<?php echo csrf_field(); ?>
				<input type="hidden" name="token" value="<?php echo e($token); ?>">
				<?php foreach (['reset', 'password', 'password_confirm'] as $field): ?>
					<?php if (isset($errors[$field])): ?>
						<span class="warn"><?php echo e($errors[$field]); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
				<input
					type="password"
					name="password"
					placeholder="New Password"
					required
				>
				<?php require __DIR__ . '/../partials/password_strength.php'; ?>
				<input
					type="password"
					name="password_confirm"
					placeholder="Confirm Password"
					required
				>
				<input type="submit" name="submit" value="Reset Password">
			</form>
			Back to <a href="/login">Login</a>
		</div>
	</div>
</div>

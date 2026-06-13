<?php

$errors = $errors ?? [];
$old = $old ?? ['email' => '', 'remember' => false];
?>
<div class="login-ctn">
	<div class="login-intro-img">
		<img src="/assets/img/1.svg" alt="">
	</div>
	<div class="login-form-ctn">
		<div class="form-ctn">
			<h1>Welcome back</h1>
			<form action="/login" method="post" class="login-form">
				<?php echo csrf_field(); ?>
				<?php foreach (['login', 'email', 'password'] as $field): ?>
					<?php if (isset($errors[$field])): ?>
						<span class="warn"><?php echo e($errors[$field]); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
				<input
					type="email"
					name="email"
					placeholder="Email"
					value="<?php echo e($old['email'] ?? ''); ?>"
					required
				>
				<input type="password" name="password" placeholder="Password" required>
				<label class="auth-checkbox-row">
					<input
						type="checkbox"
						name="remember_me"
						id="remember_me"
						value="checked"
						<?php echo ($old['remember'] ?? false) ? 'checked' : ''; ?>
					>
					Remember Me
				</label>
				<input type="submit" name="submit">
			</form>
			<a href="/forgot-password">Forgot password?</a><br>
			Don't have an account? <a href="/register">Sign-up</a>
		</div>
	</div>
</div>

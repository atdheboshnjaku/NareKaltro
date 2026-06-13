<?php

$errors = $errors ?? [];
$old = $old ?? ['email' => ''];
$submitted = (bool) ($submitted ?? false);
?>
<div class="login-ctn">
	<div class="login-intro-img">
		<img src="/assets/img/1.svg" alt="">
	</div>
	<div class="login-form-ctn">
		<div class="form-ctn">
			<h1>Reset Password</h1>
			<form action="/forgot-password" method="post" class="login-form">
				<?php echo csrf_field(); ?>
				<?php if (isset($errors['email'])): ?>
					<span class="warn"><?php echo e($errors['email']); ?></span>
				<?php endif; ?>
				<div class="auth-hp-field" aria-hidden="true">
					<label for="company_website">Company Website</label>
					<input
						type="text"
						id="company_website"
						name="company_website"
						tabindex="-1"
						autocomplete="off"
					>
				</div>
				<input
					type="email"
					name="email"
					placeholder="Email"
					value="<?php echo e($old['email'] ?? ''); ?>"
					required
				>
				<input type="submit" name="submit" value="Send Reset Link">
			</form>
			Remembered your password? <a href="/login">Login</a>
		</div>
	</div>
</div>

<?php if ($submitted): ?>
	<script>
		$(document).ready(function () {
			swal({
				title: 'Check your email',
				text: 'If an active account exists for that email address, we have sent a password reset link.',
				icon: 'success',
				showConfirmButton: true
			});
		});
	</script>
<?php endif; ?>

<?php

$errors = $errors ?? [];
$old = $old ?? ['email' => ''];
$registered = (bool) ($registered ?? false);
?>
<div class="login-ctn">
	<div class="login-intro-img">
		<img src="/assets/img/1.svg" alt="">
	</div>
	<div class="login-form-ctn">
		<div class="form-ctn">
			<h1>Create Free Account</h1>
			<form action="/register" method="post" class="login-form">
				<?php echo csrf_field(); ?>
				<?php foreach (['email'] as $field): ?>
					<?php if (isset($errors[$field])): ?>
						<span class="warn"><?php echo e($errors[$field]); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
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
					placeholder="Enter Your Email Address"
					value="<?php echo e($old['email'] ?? ''); ?>"
					required
				>
				<input type="submit" name="submit" value="Sign Up">
			</form>
			Already have an account? <a href="/login">Login</a>
		</div>
	</div>
</div>

<?php if ($registered): ?>
	<script>
		$(document).ready(function () {
			swal({
				title: 'Thank you for joining us!',
				text: 'We have sent a verification email to the email address you provided, please also check your spam/junk box and click on Verify',
				icon: 'success',
				showConfirmButton: true
			});
		});
	</script>
<?php endif; ?>

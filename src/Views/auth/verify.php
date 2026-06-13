<?php

$errors = $errors ?? [];
$old = $old ?? ['name' => ''];
?>
<div class="login-ctn">
	<div class="login-intro-img">
		<img src="/assets/img/1.svg" alt="">
	</div>
	<div class="login-form-ctn">
		<div class="form-ctn">
			<h1>Verify</h1>
			<form action="/verify?hash=<?php echo e($hash); ?>" method="post" class="login-form">
				<?php echo csrf_field(); ?>
				<input type="hidden" name="hash" value="<?php echo e($hash); ?>">
				<?php foreach (['login', 'name', 'password'] as $field): ?>
					<?php if (isset($errors[$field])): ?>
						<span class="warn"><?php echo e($errors[$field]); ?></span>
					<?php endif; ?>
				<?php endforeach; ?>
				<input
					autocomplete="off"
					type="text"
					name="name"
					placeholder="Enter Your Fullname"
					value="<?php echo e($old['name'] ?? ''); ?>"
					required
				>
				<input
					autocomplete="off"
					type="password"
					name="password"
					placeholder="Strong Password"
					required
				>
				<input type="submit" name="submit" value="Continue">
			</form>
		</div>
	</div>
</div>

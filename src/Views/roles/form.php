<form method="post" action="<?php echo e($action); ?>">
	<?php echo csrf_field(); ?>
	<?php if (isset($role)): ?>
		<input type="hidden" name="role_id" value="<?php echo e($role['id']); ?>">
	<?php endif; ?>

	<div class="add-form">
		<?php if (isset($errors['policy'])): ?>
			<span class="warn"><?php echo e($errors['policy']); ?></span>
		<?php endif; ?>
		<?php if (isset($errors['name'])): ?>
			<span class="warn"><?php echo e($errors['name']); ?></span>
		<?php endif; ?>
		<p>
			<input type="text" name="name" value="<?php echo e($old['name']); ?>" placeholder="Role name" required>
		</p>
		<p>
			<input
				type="text"
				name="description"
				value="<?php echo e($old['description']); ?>"
				placeholder="Role description"
			>
		</p>
		<p>
			<select name="status">
				<option value="1" <?php echo $old['status'] ? 'selected' : ''; ?>>Active</option>
				<option value="0" <?php echo !$old['status'] ? 'selected' : ''; ?>>Inactive</option>
			</select>
		</p>
	</div>

	<?php
	$permissionMode = 'role';
	$selectedAllow = $selectedPermissions;
	$selectedDeny = [];
	require __DIR__ . '/../partials/access_permissions.php';
	?>

	<p class="mt-4">
		<input type="submit" name="submit" class="blue-btn alab" value="<?php echo e($submitLabel); ?>">
	</p>
</form>

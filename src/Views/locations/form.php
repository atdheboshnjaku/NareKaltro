<form class="add-form" method="post" action="<?php echo e($action); ?>">
	<?php echo csrf_field(); ?>
	<?php if (isset($location)): ?>
		<input type="hidden" name="id" value="<?php echo e($location->id); ?>">
	<?php endif; ?>

	<?php if (isset($errors['name'])): ?>
		<span class="warn"><?php echo e($errors['name']); ?></span>
	<?php endif; ?>
	<p>
		<input type="text" name="name" value="<?php echo e($old['name']); ?>" placeholder="Location name" required>
	</p>
	<p>
		<input type="submit" name="submit" class="blue-btn alab" value="<?php echo e($submitLabel); ?>">
	</p>
</form>

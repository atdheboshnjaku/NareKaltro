<form class="add-form" method="post" action="<?php echo e($action); ?>" data-service-form>
	<?php echo csrf_field(); ?>
	<?php if (isset($service)): ?>
		<input type="hidden" name="id" value="<?php echo e($service->id); ?>">
	<?php endif; ?>

	<?php if (isset($errors['name'])): ?>
		<span class="warn"><?php echo e($errors['name']); ?></span>
	<?php endif; ?>
	<p>
		<span>Service name</span>
		<input type="text" name="name" value="<?php echo e($old['name']); ?>" placeholder="Service name" required>
	</p>
	<div class="service-preview-field">
		<span>Preview</span>
		<span
			class="badge service-preview-badge"
			data-service-preview
			style="background-color: <?php echo e($old['background']); ?>; color: <?php echo e($old['color']); ?>;"
		>
			<?php echo e($old['name'] !== '' ? $old['name'] : 'Preview'); ?>
		</span>
	</div>
	<div class="service-color-row">
		<p class="service-color-field">
			<span>Service background color</span>
			<input type="color" name="background" value="<?php echo e($old['background']); ?>">
			<?php if (isset($errors['background'])): ?>
				<span class="warn"><?php echo e($errors['background']); ?></span>
			<?php endif; ?>
		</p>
		<p class="service-color-field">
			<span>Service text color</span>
			<input type="color" name="color" value="<?php echo e($old['color']); ?>">
			<?php if (isset($errors['color'])): ?>
				<span class="warn"><?php echo e($errors['color']); ?></span>
			<?php endif; ?>
		</p>
	</div>
	<p>
		<input type="submit" name="submit" class="blue-btn alab" value="<?php echo e($submitLabel); ?>">
	</p>
</form>

<script>
	(function () {
		const form = document.querySelector('[data-service-form]');
		const preview = form?.querySelector('[data-service-preview]');
		const name = form?.querySelector('input[name="name"]');
		const background = form?.querySelector('input[name="background"]');
		const color = form?.querySelector('input[name="color"]');

		if (!form || !preview || !name || !background || !color) {
			return;
		}

		const updatePreview = function () {
			preview.textContent = name.value.trim() || 'Preview';
			preview.style.backgroundColor = background.value;
			preview.style.color = color.value;
		};

		name.addEventListener('input', updatePreview);
		background.addEventListener('input', updatePreview);
		color.addEventListener('input', updatePreview);
	})();
</script>

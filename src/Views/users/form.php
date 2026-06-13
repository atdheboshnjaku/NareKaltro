<?php
$knownLocation = false;
foreach ($locations as $locationOption) {
	if ($locationOption->id === $old->locationId) {
		$knownLocation = true;
		break;
	}
}
?>
<form method="post" action="<?php echo e($action); ?>">
	<?php echo csrf_field(); ?>
	<?php if ($member !== null): ?>
		<input type="hidden" name="id" value="<?php echo e($member->id); ?>">
	<?php endif; ?>

	<div class="add-form">
		<?php if (isset($errors['policy'])): ?>
			<span class="warn"><?php echo e($errors['policy']); ?></span>
		<?php endif; ?>
		<?php if (isset($errors['location_id'])): ?>
			<span class="warn"><?php echo e($errors['location_id']); ?></span>
		<?php endif; ?>
		<p>
			<select name="location_id">
				<option value="0">Choose location</option>
				<?php if ($member !== null && $old->locationId > 0 && !$knownLocation): ?>
					<option value="<?php echo e($old->locationId); ?>" selected>
						Current assignment: <?php echo e($member->locationName ?? 'Unavailable location'); ?>
					</option>
				<?php endif; ?>
				<optgroup label="User location">
					<?php foreach ($locations as $location): ?>
						<option value="<?php echo e($location->id); ?>" <?php echo $old->locationId === $location->id ? 'selected' : ''; ?>>
							<?php echo e($location->name); ?>
						</option>
					<?php endforeach; ?>
				</optgroup>
			</select>
		</p>
		<?php if (isset($errors['name'])): ?>
			<span class="warn"><?php echo e($errors['name']); ?></span>
		<?php endif; ?>
		<p>
			<input type="text" name="name" value="<?php echo e($old->name); ?>" placeholder="Users full name" required>
		</p>
		<?php if (isset($errors['email'])): ?>
			<span class="warn"><?php echo e($errors['email']); ?></span>
		<?php endif; ?>
		<p>
			<input type="email" name="email" value="<?php echo e($old->email); ?>" placeholder="User email" required>
		</p>
		<?php if (isset($errors['password'])): ?>
			<span class="warn"><?php echo e($errors['password']); ?></span>
		<?php endif; ?>
		<p>
			<input
				type="password"
				name="password"
				placeholder="<?php echo $member === null ? 'Password' : 'New password'; ?>"
				<?php echo $member === null ? 'required' : ''; ?>
			>
		</p>
	</div>

	<?php if (isset($errors['roles'])): ?>
		<span class="warn"><?php echo e($errors['roles']); ?></span>
	<?php endif; ?>
	<table class="action-table align-middle">
		<thead>
			<tr>
				<th>Role</th>
				<th>Status</th>
				<th>Assign</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($roles as $role): ?>
				<?php $selected = in_array($role['id'], $old->roles, true) || !$role['editable']; ?>
				<tr>
					<td><?php echo e($role['name']); ?></td>
					<td>
						<p class="badge <?php echo $role['active'] ? 'badge-green' : 'badge-red'; ?>">
							<?php echo $role['active'] ? 'Active' : 'Inactive'; ?>
						</p>
					</td>
					<td>
						<input
							type="checkbox"
							name="roles[]"
							value="<?php echo e($role['id']); ?>"
							<?php echo $selected ? 'checked' : ''; ?>
							<?php echo !$role['editable'] ? 'disabled' : ''; ?>
							aria-label="Assign <?php echo e($role['name']); ?>"
						>
						<?php if ($selected && !$role['editable']): ?>
							<input type="hidden" name="roles[]" value="<?php echo e($role['id']); ?>">
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p class="mt-4">
		<input type="submit" name="submit" class="blue-btn alab" value="<?php echo e($submitLabel); ?>">
	</p>
</form>

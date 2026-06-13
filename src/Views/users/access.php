<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>User</h2>
			<p>Access for <?php echo e($member->name); ?></p>
		</div>
		<div class="box-rt-ctn">
			<a href="/users">
				<button class="action-btn align-middle">
					<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back
				</button>
			</a>
		</div>
	</div>

	<?php if (isset($errors['policy'])): ?>
		<span class="warn"><?php echo e($errors['policy']); ?></span>
	<?php endif; ?>
	<?php if (isset($errors['roles'])): ?>
		<span class="warn"><?php echo e($errors['roles']); ?></span>
	<?php endif; ?>
	<?php if (isset($errors['permissions'])): ?>
		<span class="warn"><?php echo e($errors['permissions']); ?></span>
	<?php endif; ?>

	<form method="post" action="/users/access/update">
		<?php echo csrf_field(); ?>
		<input type="hidden" name="id" value="<?php echo e($member->id); ?>">

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
								<?php echo in_array($role['id'], $selected['roles'], true) ? 'checked' : ''; ?>
								aria-label="Assign <?php echo e($role['name']); ?>"
							>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		$permissionMode = 'user';
		$selectedAllow = $selected['allow'];
		$selectedDeny = $selected['deny'];
		require __DIR__ . '/../partials/access_permissions.php';
		?>

		<p class="mt-4">
			<input type="submit" name="submit" class="blue-btn alab" value="Update access">
		</p>
	</form>

	<?php if ($selected['customized']): ?>
		<form method="post" action="/users/access/reset" class="reset-access-form">
			<?php echo csrf_field(); ?>
			<input type="hidden" name="id" value="<?php echo e($member->id); ?>">
			<p class="mt-4">
				<input type="submit" class="sm-red-btn alab" value="Reset access">
			</p>
		</form>
		<script>
			$(document).ready(function () {
				$('.reset-access-form').submit(function (event) {
					event.preventDefault();
					const form = this;

					swal({
						title: 'Reset User Access?',
						text: 'This user will return to their existing role assignment.',
						icon: 'warning',
						buttons: true,
						dangerMode: true
					}).then(function (confirmed) {
						if (confirmed) {
							form.submit();
						}
					});
				});
			});
		</script>
	<?php endif; ?>
</div>

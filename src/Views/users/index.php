<?php $hasActions = $canUpdate || $canDelete || $canManageAccess; ?>
<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Users</h2>
			<p><?php echo e($total); ?> users in total</p>
		</div>
		<?php if ($canCreate): ?>
			<div class="box-rt-ctn">
				<a href="/users/create">
					<button class="action-btn align-middle">
						<i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New User
					</button>
				</a>
			</div>
		<?php endif; ?>
	</div>

	<?php if ($removeError !== null): ?>
		<span class="warn"><?php echo e($removeError); ?></span>
	<?php endif; ?>

	<table class="action-table align-middle">
		<thead>
			<tr>
				<th>Name</th>
				<th>Email</th>
				<th>Level</th>
				<th>Access</th>
				<?php if ($hasActions): ?>
					<th>Actions</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($staff as $row): ?>
				<tr>
					<td>
						<?php echo e($row['member']->name); ?>
						<p><?php echo e($row['member']->locationName ?? ''); ?></p>
					</td>
					<td><?php echo e($row['member']->email ?? ''); ?></td>
					<td>
						<?php foreach ($row['roles'] as $role): ?>
							<p class="badge badge-blue"><?php echo e($role); ?></p>
						<?php endforeach; ?>
					</td>
					<td>
						<p class="badge <?php echo $row['customized'] ? 'badge-blue' : 'badge-green'; ?>">
							<?php echo $row['customized'] ? 'Configured' : 'Existing role'; ?>
						</p>
					</td>
					<?php if ($hasActions): ?>
						<td>
							<?php if ($canManageAccess): ?>
								<a
									href="/users/access?id=<?php echo e($row['member']->id); ?>"
									title="Edit access"
									aria-label="Edit access"
								>
									<div class="btn btn-icon">
										<i class="fa fa-key" aria-hidden="true"></i>
									</div>
								</a>
							<?php endif; ?>
							<?php if ($canUpdate): ?>
								<a
									href="/users/edit?id=<?php echo e($row['member']->id); ?>"
									title="Edit user"
									aria-label="Edit user"
								>
									<div class="btn btn-icon">
										<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
									</div>
								</a>
							<?php endif; ?>
							<?php if ($canDelete): ?>
								<form class="d-inline user-remove-form" method="post" action="/users/deactivate">
									<?php echo csrf_field(); ?>
									<input type="hidden" name="id" value="<?php echo e($row['member']->id); ?>">
									<a href="#" class="delete-confirmation" title="Remove user" aria-label="Remove user">
										<div class="btn btn-icon">
											<i class="fa fa-trash-o" aria-hidden="true"></i>
										</div>
									</a>
								</form>
							<?php endif; ?>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	$paginationRoute = '/users';
	$paginationQuery = [];
	require __DIR__ . '/../partials/pagination.php';
	?>
</div>

<?php if ($canDelete): ?>
	<script>
		$(document).ready(function () {
			$('.delete-confirmation').click(function (event) {
				event.preventDefault();
				const form = $(this).closest('form')[0];

				swal({
					title: 'Are you sure?',
					text: 'Once deleted, you will not be able to recover this user!',
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

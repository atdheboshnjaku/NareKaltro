<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Roles</h2>
			<p><?php echo e($total); ?> roles in total</p>
		</div>
		<?php if ($canManage): ?>
			<div class="box-rt-ctn">
				<a href="/roles/create">
					<button class="action-btn align-middle">
						<i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Role
					</button>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<table class="action-table align-middle">
		<thead>
			<tr>
				<th>Role</th>
				<th>Permissions</th>
				<?php if ($canManage): ?>
					<th>Actions</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($roles as $role): ?>
				<tr>
					<td><?php echo e($role['name']); ?></td>
					<td>
						<?php foreach ($role['permissions'] as $permission): ?>
							<p class="badge badge-blue"><?php echo e($permission['label']); ?></p>
						<?php endforeach; ?>
					</td>
					<?php if ($canManage): ?>
						<td>
							<a
								href="/roles/edit?id=<?php echo e($role['id']); ?>"
								title="Edit role"
								aria-label="Edit role"
							>
								<div class="btn btn-icon">
									<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								</div>
							</a>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	$paginationRoute = '/roles';
	$paginationQuery = [];
	require __DIR__ . '/../partials/pagination.php';
	?>
</div>

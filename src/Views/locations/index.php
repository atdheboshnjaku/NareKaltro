<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Locations</h2>
			<p><?php echo e($total); ?> locations in total</p>
		</div>
		<?php if ($canManage): ?>
			<div class="box-rt-ctn">
				<a href="/locations/create">
					<button class="action-btn align-middle">
						<i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Location
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
				<th>Location</th>
				<th># Employees</th>
				<th># Clients</th>
				<?php if ($canManage): ?>
					<th>Actions</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($locations as $location): ?>
				<tr>
					<td>
						<?php echo e($location->name); ?>
						<p></p>
					</td>
					<td>
						<p class="badge badge-blue">
							<?php echo e($location->employeeCount); ?>
							<?php echo $location->employeeCount === 1 ? 'Employee' : 'Employees'; ?>
						</p>
					</td>
					<td>
						<p class="badge badge-green">
							<?php echo e($location->clientCount); ?>
							<?php echo $location->clientCount === 1 ? 'Client' : 'Clients'; ?>
						</p>
					</td>
					<?php if ($canManage): ?>
						<td>
							<a
								href="/locations/edit?id=<?php echo e($location->id); ?>"
								title="Edit location"
								aria-label="Edit location"
							>
								<div class="btn btn-icon">
									<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								</div>
							</a>
							<?php if ($location->hasAssignedUsers()): ?>
								<a href="#" class="delete-denied" title="Remove location" aria-label="Remove location">
									<div class="btn btn-icon">
										<i class="fa fa-trash-o" aria-hidden="true"></i>
									</div>
								</a>
							<?php else: ?>
								<form class="d-inline location-remove-form" method="post" action="/locations/deactivate">
									<?php echo csrf_field(); ?>
									<input type="hidden" name="id" value="<?php echo e($location->id); ?>">
									<a href="#" class="delete-confirmation" title="Remove location" aria-label="Remove location">
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
	$paginationRoute = '/locations';
	$paginationQuery = [];
	require __DIR__ . '/../partials/pagination.php';
	?>
</div>

<?php if ($canManage): ?>
	<script>
		$(document).ready(function () {
			$('.delete-denied').click(function (event) {
				event.preventDefault();

				swal({
					title: 'Unable to Delete!',
					text: 'You cannot delete a location that has users assigned to it',
					icon: 'error',
					timer: 5000
				});
			});

			$('.delete-confirmation').click(function (event) {
				event.preventDefault();
				const form = $(this).closest('form')[0];

				swal({
					title: 'Are you sure?',
					text: 'Once deleted, you will not be able to recover this location!',
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

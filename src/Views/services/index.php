<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Services</h2>
			<p><?php echo e($total); ?> services in total</p>
		</div>
		<?php if ($canManage): ?>
			<div class="box-rt-ctn">
				<a href="/services/create">
					<button class="action-btn align-middle">
						<i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Service
					</button>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<table class="action-table align-middle">
		<thead>
			<tr>
				<th>Service</th>
				<th>Style</th>
				<th>Value handling</th>
				<th></th>
				<?php if ($canManage): ?>
					<th>Actions</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($services as $service): ?>
				<tr>
					<td>
						<?php echo e($service->name); ?>
						<p></p>
					</td>
					<td>
						<p
							class="badge"
							style="background-color: <?php echo e($service->background); ?>; color: <?php echo e($service->color); ?>;"
						>
							<?php echo e($service->name); ?>
						</p>
					</td>
					<td>
						<p class="badge <?php echo $service->quoteOnly ? 'badge-vacation' : 'badge-blue'; ?>">
							<?php echo $service->quoteOnly ? 'Quote only' : 'Revenue'; ?>
						</p>
					</td>
					<td><p></p></td>
					<?php if ($canManage): ?>
						<td>
							<a
								href="/services/edit?id=<?php echo e($service->id); ?>"
								title="Edit service"
								aria-label="Edit service"
							>
								<div class="btn btn-icon">
									<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								</div>
							</a>
							<form class="d-inline service-remove-form" method="post" action="/services/deactivate">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="id" value="<?php echo e($service->id); ?>">
								<a href="#" class="delete-confirmation" title="Remove service" aria-label="Remove service">
									<div class="btn btn-icon">
										<i class="fa fa-trash-o" aria-hidden="true"></i>
									</div>
								</a>
							</form>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	$paginationRoute = '/services';
	$paginationQuery = [];
	require __DIR__ . '/../partials/pagination.php';
	?>
</div>

<?php if ($canManage): ?>
	<script>
		$(document).ready(function () {
			$('.delete-confirmation').click(function (event) {
				event.preventDefault();
				const form = $(this).closest('form')[0];

				swal({
					title: 'Are you sure?',
					text: 'Once deleted, you will not be able to recover this service!',
					icon: 'warning',
					buttons: true,
					dangerMode: true
				}).then(function (willDelete) {
					if (willDelete) {
						form.submit();
					}
				});
			});
		});
	</script>
<?php endif; ?>

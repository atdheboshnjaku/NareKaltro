<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Clients</h2>
			<p><?php echo e($total); ?> clients in total</p>
		</div>
		<div class="box-rt-ctn"></div>
	</div>
	<div class="box-header">
		<div class="box-lf-ctn">
			<form class="search-form_wrapper" method="get" action="/clients">
				<button class="button-search" type="submit" title="Search clients" aria-label="Search clients">
					<i class="fa fa-search" aria-hidden="true"></i>
				</button>
				<input
					type="search"
					name="search"
					value="<?php echo e($search); ?>"
					placeholder="Search customers"
					autocomplete="off"
				>
			</form>
		</div>
		<?php if ($canCreate): ?>
			<div class="box-rt-ctn">
				<a href="/clients/create">
					<button class="action-btn align-middle">
						<i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Client
					</button>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<table class="action-table center-title align-middle">
		<thead>
			<tr>
				<th><i class="fa fa-user-circle-o fa-lg" aria-hidden="true"></i></th>
				<th>Client</th>
				<th>Email</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($clients as $client): ?>
				<tr>
					<td>
						<div class="client-pic-ctn"><?php echo e($client->initials()); ?></div>
					</td>
					<td>
						<?php echo e($client->name); ?><br>
						<p class="badge badge-green"><?php echo e($client->locationName ?? 'Unavailable location'); ?></p>
					</td>
					<td><?php echo e($client->email); ?></td>
					<td>
						<a
							href="/clients/history?id=<?php echo e($client->id); ?>"
							title="View client history"
							aria-label="View client history"
						>
							<div class="btn btn-icon">
								<i class="fa fa-history" aria-hidden="true"></i>
							</div>
						</a>
						<?php if ($canUpdate): ?>
							<a
								href="/clients/edit?id=<?php echo e($client->id); ?>"
								title="Edit client"
								aria-label="Edit client"
							>
								<div class="btn btn-icon">
									<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
								</div>
							</a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<form class="d-inline client-remove-form" method="post" action="/clients/deactivate">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="id" value="<?php echo e($client->id); ?>">
								<a href="#" class="delete-confirmation" title="Remove client" aria-label="Remove client">
									<div class="btn btn-icon">
										<i class="fa fa-trash-o" aria-hidden="true"></i>
									</div>
								</a>
							</form>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	$paginationRoute = '/clients';
	$paginationQuery = ['search' => $search];
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
					text: 'Once deleted, you will not be able to recover this client!',
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

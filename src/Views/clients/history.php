<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>History: <?php echo e($client->name); ?></h2>
			<p><?php echo e($total); ?> appointments in total</p>
		</div>
		<div class="box-rt-ctn">
			<a href="/clients">
				<button class="action-btn align-middle">
					<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back
				</button>
			</a>
		</div>
	</div>
	<table class="action-table align-middle history-table">
		<thead>
			<tr>
				<th>Location</th>
				<th>Service</th>
				<th>Start</th>
				<th>End</th>
				<th class="align-left">Notes</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($entries as $entry): ?>
				<tr>
					<td>
						<p><?php echo e($entry->locationName ?? 'Unavailable location'); ?></p>
						<?php if (!$entry->active): ?>
							<p class="badge badge-red">Cancelled/Deleted</p>
						<?php endif; ?>
					</td>
					<td>
						<?php foreach ($entry->services as $service): ?>
							<p
								class="badge"
								style="background: <?php echo e($service->background); ?>; color: <?php echo e($service->color); ?>;"
							>
								<?php echo e($service->name); ?><?php echo $service->cost !== null ? e(': EUR ' . $service->cost) : ''; ?>
							</p>
						<?php endforeach; ?>
					</td>
					<td><?php echo e($entry->startDate); ?></td>
					<td><?php echo e($entry->endDate ?? ''); ?></td>
					<td class="align-left"><?php echo e($entry->notes); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
	$paginationRoute = '/clients/history';
	$paginationQuery = ['id' => $client->id];
	require __DIR__ . '/../partials/pagination.php';
	?>
</div>

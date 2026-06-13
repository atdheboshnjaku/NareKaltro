<?php

$formatValue = static function (float|int $value, string $format): string {
	return $format === 'currency'
		? 'EUR ' . number_format((float) $value, 2)
		: number_format((float) $value);
};
$services = array_slice($dashboard['insights']['services'], 0, 5);
$locations = array_slice($dashboard['insights']['locations'], 0, 5);
$employees = array_slice($dashboard['insights']['employees'], 0, 5);
$showValues = (bool) ($dashboard['insights']['canViewValues'] ?? false);
?>
<div class="box dashboard-header-box">
	<div class="box-header report-title-row">
		<div class="box-lf-ctn">
			<h2>Dashboard</h2>
			<p><?php echo e($dashboard['dateLabel']); ?> &middot; <?php echo e(number_format($dashboard['appointments'])); ?> bookings in <?php echo e($dashboard['year']); ?></p>
		</div>
	</div>
</div>

<section class="report-summary" aria-label="Operational summary">
	<?php foreach ($dashboard['stats'] as $stat): ?>
		<div class="report-stat">
			<div class="report-stat-icon">
				<i class="fa <?php echo e($stat['icon']); ?>" aria-hidden="true"></i>
			</div>
			<p><?php echo e($stat['label']); ?></p>
			<strong><?php echo e($formatValue($stat['value'], $stat['format'])); ?></strong>
			<span><?php echo e($stat['detail']); ?></span>
		</div>
	<?php endforeach; ?>
</section>

<div class="dashboard-grid">
	<div class="box dashboard-panel">
		<div class="report-panel-header">
			<div>
				<h2>Most booked services</h2>
				<p><?php echo e($dashboard['year']); ?> active appointments</p>
			</div>
		</div>
		<table class="action-table dashboard-table align-middle">
			<thead>
				<tr>
					<th>Service</th>
					<th>Bookings</th>
					<?php if ($showValues): ?>
						<th>Visible value</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php if ($services === []): ?>
					<tr><td colspan="<?php echo $showValues ? 3 : 2; ?>" class="dashboard-empty">No booked services this year.</td></tr>
				<?php else: ?>
					<?php foreach ($services as $service): ?>
						<tr>
							<td>
								<p
									class="badge"
									style="background-color: <?php echo e($service['background']); ?>; color: <?php echo e($service['color']); ?>;"
								><?php echo e($service['name']); ?></p>
							</td>
							<td><?php echo e(number_format($service['appointments'])); ?></td>
							<?php if ($showValues): ?>
								<td><?php echo e($formatValue($service['visibleValue'], 'currency')); ?></td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<div class="box dashboard-panel">
		<div class="report-panel-header">
			<div>
				<h2>Location performance</h2>
				<p><?php echo e($dashboard['year']); ?> active appointments</p>
			</div>
		</div>
		<table class="action-table dashboard-table align-middle">
			<thead>
				<tr>
					<th>Location</th>
					<th>Bookings</th>
					<?php if ($showValues): ?>
						<th>Visible value</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php if ($locations === []): ?>
					<tr><td colspan="<?php echo $showValues ? 3 : 2; ?>" class="dashboard-empty">No booked locations this year.</td></tr>
				<?php else: ?>
					<?php foreach ($locations as $location): ?>
						<tr>
							<td><?php echo e($location['name']); ?></td>
							<td><p class="badge badge-blue"><?php echo e(number_format($location['appointments'])); ?></p></td>
							<?php if ($showValues): ?>
								<td><?php echo e($formatValue($location['visibleValue'], 'currency')); ?></td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<div class="box dashboard-panel">
		<div class="report-panel-header">
			<div>
				<h2>Employee performance</h2>
				<p><?php echo e($dashboard['year']); ?> active appointments</p>
			</div>
		</div>
		<table class="action-table dashboard-table align-middle">
			<thead>
				<tr>
					<th>Employee</th>
					<th>Bookings</th>
					<?php if ($showValues): ?>
						<th>Visible value</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php if ($employees === []): ?>
					<tr><td colspan="<?php echo $showValues ? 3 : 2; ?>" class="dashboard-empty">No assigned employees this year.</td></tr>
				<?php else: ?>
					<?php foreach ($employees as $employee): ?>
						<tr>
							<td><?php echo e($employee['name']); ?></td>
							<td><p class="badge badge-blue"><?php echo e(number_format($employee['appointments'])); ?></p></td>
							<?php if ($showValues): ?>
								<td><?php echo e($formatValue($employee['visibleValue'], 'currency')); ?></td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

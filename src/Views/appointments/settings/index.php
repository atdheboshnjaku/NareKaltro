<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Appointment Settings</h2>
			<p><?php echo e($total); ?> rules in total</p>
		</div>
		<div class="box-rt-ctn">
			<a href="/appointments/settings/rules/create">
				<button class="action-btn align-middle">
					<i class="fa fa-plus-square-o" aria-hidden="true"></i>&nbsp; New Rule
				</button>
			</a>
		</div>
	</div>

	<form class="add-form" method="post" action="/appointments/settings/defaults">
		<?php echo csrf_field(); ?>
		<p>
			<span>Appointment ending time</span>
			<select name="end_time_enabled">
				<option value="1" <?php echo $endTimeEnabled ? 'selected' : ''; ?>>Enabled</option>
				<option value="0" <?php echo !$endTimeEnabled ? 'selected' : ''; ?>>Disabled</option>
			</select>
		</p>
		<p>
			<input type="submit" name="submit" class="blue-btn alab" value="Save setting">
		</p>
	</form>

	<table class="action-table align-middle">
		<thead>
			<tr>
				<th>Rule</th>
				<th>Applies To</th>
				<th>Effects</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($rules as $rule): ?>
				<tr>
					<td><?php echo e($rule['name']); ?></td>
					<td>
						<?php if ($rule['targets'] === []): ?>
							<p class="badge badge-blue">All appointments</p>
						<?php else: ?>
							<?php foreach ($rule['targets'] as $target): ?>
								<p class="badge badge-blue"><?php echo e($target['type'] . ': ' . $target['label']); ?></p>
							<?php endforeach; ?>
						<?php endif; ?>
					</td>
					<td>
						<?php foreach ($rule['effects'] as $effect): ?>
							<p class="badge <?php echo $effect['effect'] === 'deny' ? 'badge-red' : 'badge-green'; ?>">
								<?php echo e(ucfirst($effect['effect']) . ': ' . $effect['label']); ?>
							</p>
						<?php endforeach; ?>
					</td>
					<td>
						<p class="badge <?php echo $rule['active'] ? 'badge-green' : 'badge-red'; ?>">
							<?php echo $rule['active'] ? 'Active' : 'Inactive'; ?>
						</p>
					</td>
					<td>
						<a
							href="/appointments/settings/rules/edit?id=<?php echo e($rule['id']); ?>"
							title="Edit rule"
							aria-label="Edit rule"
						>
							<div class="btn btn-icon">
								<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
							</div>
						</a>
						<?php if ($rule['active']): ?>
							<form class="d-inline rule-remove-form" method="post" action="/appointments/settings/rules/deactivate">
								<?php echo csrf_field(); ?>
								<input type="hidden" name="id" value="<?php echo e($rule['id']); ?>">
								<a href="#" class="delete-confirmation" title="Deactivate rule" aria-label="Deactivate rule">
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
	$paginationRoute = '/appointments/settings';
	$paginationQuery = [];
	require __DIR__ . '/../../partials/pagination.php';
	?>
</div>

<script>
	$(document).ready(function () {
		$('.delete-confirmation').click(function (event) {
			event.preventDefault();
			const form = $(this).closest('form')[0];

			swal({
				title: 'Deactivate Rule?',
				text: 'This appointment rule will stop applying.',
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

<form class="add-form" method="post" action="<?php echo e($action); ?>">
	<?php echo csrf_field(); ?>
	<?php if ($ruleId !== null): ?>
		<input type="hidden" name="id" value="<?php echo e($ruleId); ?>" style="display: none;">
	<?php endif; ?>
	<?php foreach ($old->appointmentIds as $appointmentId): ?>
		<input type="hidden" name="appointments[]" value="<?php echo e($appointmentId); ?>" style="display: none;">
	<?php endforeach; ?>

	<?php foreach ($errors as $error): ?>
		<span class="warn"><?php echo e($error); ?></span>
	<?php endforeach; ?>

	<p>
		<span>Rule name</span>
		<input type="text" name="name" value="<?php echo e($old->name); ?>" required>
	</p>
	<p>
		<span>Status</span>
		<select name="status">
			<option value="1" <?php echo $old->active ? 'selected' : ''; ?>>Active</option>
			<option value="0" <?php echo !$old->active ? 'selected' : ''; ?>>Inactive</option>
		</select>
	</p>

	<table class="action-table align-middle">
		<thead>
			<tr>
				<th>Action</th>
				<th>Rule</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ([
				'appointments.cost.view' => 'View appointment costs',
				'appointments.cost.update' => 'Update appointment costs',
				'appointments.end_time.use' => 'Use appointment ending time',
			] as $capability => $label): ?>
				<tr>
					<td><?php echo e($label); ?></td>
					<td>
						<select name="effects[<?php echo e($capability); ?>]">
							<option value="">No rule</option>
							<option value="allow" <?php echo ($old->effects[$capability] ?? '') === 'allow' ? 'selected' : ''; ?>>Allow</option>
							<option value="deny" <?php echo ($old->effects[$capability] ?? '') === 'deny' ? 'selected' : ''; ?>>Deny</option>
						</select>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<p>
		<span>Roles</span>
		<select class="appointment-rule-select" multiple name="roles[]">
			<?php foreach ($roles as $role): ?>
				<option value="<?php echo e($role['id']); ?>" <?php echo in_array($role['id'], $old->roleIds, true) ? 'selected' : ''; ?>>
					<?php echo e($role['name']); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<span>Users</span>
		<select class="appointment-rule-select" multiple name="users[]">
			<?php foreach ($staff as $member): ?>
				<option value="<?php echo e($member->id); ?>" <?php echo in_array($member->id, $old->userIds, true) ? 'selected' : ''; ?>>
					<?php echo e($member->name); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<span>Locations</span>
		<select class="appointment-rule-select" multiple name="locations[]">
			<?php foreach ($locations as $location): ?>
				<option value="<?php echo e($location->id); ?>" <?php echo in_array($location->id, $old->locationIds, true) ? 'selected' : ''; ?>>
					<?php echo e($location->name); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<span>Services</span>
		<select class="appointment-rule-select" multiple name="services[]">
			<?php foreach ($services as $service): ?>
				<option value="<?php echo e($service->id); ?>" <?php echo in_array($service->id, $old->serviceIds, true) ? 'selected' : ''; ?>>
					<?php echo e($service->name); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>

	<?php if ($old->appointmentIds !== []): ?>
		<p>
			<span>Appointments</span><br>
			<?php foreach ($old->appointmentIds as $appointmentId): ?>
				<span class="badge badge-blue">Appointment #<?php echo e($appointmentId); ?></span>
			<?php endforeach; ?>
		</p>
	<?php endif; ?>

	<p class="mt-4">
		<input type="submit" name="submit" class="blue-btn alab" value="<?php echo e($submitLabel); ?>">
	</p>
</form>

<script>
	$(document).ready(function () {
		$('.appointment-rule-select').select2({
			placeholder: 'All',
			allowClear: true
		});
	});
</script>

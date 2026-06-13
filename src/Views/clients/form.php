<?php
$knownLocation = false;
foreach ($locations as $locationOption) {
	if ($locationOption->id === $old->locationId) {
		$knownLocation = true;
		break;
	}
}
?>
<form action="<?php echo e($action); ?>" method="post" class="add-form" data-client-form>
	<?php echo csrf_field(); ?>
	<?php if ($client !== null): ?>
		<input type="hidden" name="id" value="<?php echo e($client->id); ?>">
	<?php endif; ?>

	<div class="profile-card">
		<div class="client-profile-pic" data-client-initials>
			<?php echo $client !== null ? e($client->initials()) : ''; ?>
		</div>
		<p>
			<span>Phone</span>
			<input type="tel" name="number" value="<?php echo e($old->phone); ?>" placeholder="Client number" autocomplete="off">
		</p>
		<?php if (isset($errors['country'])): ?>
			<span class="warn"><?php echo e($errors['country']); ?></span>
		<?php endif; ?>
		<p>
			<span>Country</span>
			<select class="csc-select" name="country" id="country">
				<option value="0">Select Country</option>
				<?php foreach ($countries as $country): ?>
					<option value="<?php echo e($country->id); ?>" <?php echo $old->countryId === $country->id ? 'selected' : ''; ?>>
						<?php echo e($country->name); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php if (isset($errors['state'])): ?>
			<span class="warn"><?php echo e($errors['state']); ?></span>
		<?php endif; ?>
		<p>
			<span>State</span>
			<select class="csc-select" name="state" id="state">
				<option value="0">Select State</option>
				<?php foreach ($states as $state): ?>
					<option value="<?php echo e($state->id); ?>" <?php echo $old->stateId === $state->id ? 'selected' : ''; ?>>
						<?php echo e($state->name); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php if (isset($errors['city'])): ?>
			<span class="warn"><?php echo e($errors['city']); ?></span>
		<?php endif; ?>
		<p>
			<span>City</span>
			<select class="csc-select" name="city" id="city">
				<option value="0">Select City</option>
				<?php foreach ($cities as $city): ?>
					<option value="<?php echo e($city->id); ?>" <?php echo $old->cityId === $city->id ? 'selected' : ''; ?>>
						<?php echo e($city->name); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>

	<div class="profile-info">
		<?php if (isset($errors['location_id'])): ?>
			<span class="warn"><?php echo e($errors['location_id']); ?></span>
		<?php endif; ?>
		<p>
			<span>Location</span>
			<select name="location_id">
				<option value="0">Choose location</option>
				<?php if ($client !== null && $old->locationId > 0 && !$knownLocation): ?>
					<option value="<?php echo e($old->locationId); ?>" selected>
						Current assignment: <?php echo e($client->locationName ?? 'Unavailable location'); ?>
					</option>
				<?php endif; ?>
				<optgroup label="User location">
					<?php foreach ($locations as $location): ?>
						<option value="<?php echo e($location->id); ?>" <?php echo $old->locationId === $location->id ? 'selected' : ''; ?>>
							<?php echo e($location->name); ?>
						</option>
					<?php endforeach; ?>
				</optgroup>
			</select>
		</p>
		<?php if (isset($errors['email'])): ?>
			<span class="warn"><?php echo e($errors['email']); ?></span>
		<?php endif; ?>
		<p>
			<span>Email</span>
			<input type="email" name="email" value="<?php echo e($old->email); ?>" autocomplete="off">
		</p>
		<?php if (isset($errors['name'])): ?>
			<span class="warn"><?php echo e($errors['name']); ?></span>
		<?php endif; ?>
		<p>
			<span>Client name</span>
			<input type="text" name="name" value="<?php echo e($old->name); ?>" required>
		</p>
		<p>
			<input type="submit" name="submit" class="blue-btn alab" value="<?php echo e($submitLabel); ?>">
		</p>
	</div>
</form>

<script>
	$(document).ready(function () {
		const form = $('[data-client-form]');
		const initials = form.find('[data-client-initials]');
		const name = form.find('input[name="name"]');
		const country = form.find('#country');
		const state = form.find('#state');
		const city = form.find('#city');

		$('.csc-select').select2();

		name.on('input', function () {
			const words = $(this).val().trim().split(/\s+/).filter(Boolean).slice(0, 2);
			initials.text(words.map(function (word) {
				return word.charAt(0).toUpperCase();
			}).join(''));
		});

		country.on('change', function () {
			const countryId = $(this).val();
			state.empty().append('<option value="0">Select State</option>').trigger('change.select2');
			city.empty().append('<option value="0">Select City</option>').trigger('change.select2');

			if (countryId === '0') {
				return;
			}

			$.getJSON('/clients/geography/states', {country_id: countryId}, function (options) {
				options.forEach(function (option) {
					state.append(new Option(option.name, option.id));
				});
				state.trigger('change.select2');
			});
		});

		state.on('change', function () {
			const stateId = $(this).val();
			const countryId = country.val();
			city.empty().append('<option value="0">Select City</option>').trigger('change.select2');

			if (stateId === '0') {
				return;
			}

			$.getJSON('/clients/geography/cities', {
				country_id: countryId,
				state_id: stateId
			}, function (options) {
				options.forEach(function (option) {
					city.append(new Option(option.name, option.id));
				});
				city.trigger('change.select2');
			});
		});
	});
</script>

<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Appointment Settings</h2>
			<p>Add a new rule</p>
		</div>
		<div class="box-rt-ctn">
			<a href="/appointments/settings">
				<button class="action-btn align-middle">
					<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back
				</button>
			</a>
		</div>
	</div>

	<?php
	$action = '/appointments/settings/rules/store';
	$submitLabel = 'Add rule';
	require __DIR__ . '/form.php';
	?>
</div>

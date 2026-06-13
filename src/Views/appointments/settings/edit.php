<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Appointment Settings</h2>
			<p>Update rule</p>
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
	$action = '/appointments/settings/rules/update';
	$submitLabel = 'Update rule';
	require __DIR__ . '/form.php';
	?>
</div>

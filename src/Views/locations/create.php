<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Locations</h2>
			<p>Add your new location</p>
		</div>
		<div class="box-rt-ctn">
			<a href="/locations">
				<button class="action-btn align-middle">
					<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back
				</button>
			</a>
		</div>
	</div>

	<?php
	$action = '/locations/store';
	$submitLabel = 'Add location';
	require __DIR__ . '/form.php';
	?>
</div>

<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Roles</h2>
			<p>Add your new role and assign its permissions</p>
		</div>
		<div class="box-rt-ctn">
			<a href="/roles">
				<button class="action-btn align-middle">
					<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back
				</button>
			</a>
		</div>
	</div>

	<?php
	$action = '/roles/store';
	$submitLabel = 'Add role';
	require __DIR__ . '/form.php';
	?>
</div>

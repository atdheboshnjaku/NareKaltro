<div class="profile-box-ctn">
	<div class="profile-box-header">
		<div class="box-lf-ctn">
			<h2>Clients</h2>
			<p>Add your new client</p>
		</div>
		<div class="box-rt-ctn">
			<a href="/clients">
				<button class="action-btn align-middle">
					<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back
				</button>
			</a>
		</div>
	</div>

	<?php
	$action = '/clients/store';
	$submitLabel = 'Add client';
	require __DIR__ . '/form.php';
	?>
</div>

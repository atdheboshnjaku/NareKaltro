<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Users</h2>
			<p>Add your new user</p>
		</div>
		<div class="box-rt-ctn">
			<a href="/users">
				<button class="action-btn align-middle">
					<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back
				</button>
			</a>
		</div>
	</div>

	<?php
	$action = '/users/store';
	$submitLabel = 'Add user';
	require __DIR__ . '/form.php';
	?>
</div>

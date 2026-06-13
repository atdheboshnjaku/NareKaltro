<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Roles</h2>
			<p>Update <?php echo e($role['name']); ?> permissions</p>
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
	$action = '/roles/update';
	$submitLabel = 'Update role';
	require __DIR__ . '/form.php';
	?>
</div>

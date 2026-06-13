<div class="box">
	<div class="box-header">
		<div class="box-lf-ctn">
			<h2>Services</h2>
			<p>Update your service</p>
		</div>
		<div class="box-rt-ctn">
			<a href="/services">
				<button class="action-btn align-middle">
					<i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>&nbsp; Go Back
				</button>
			</a>
		</div>
	</div>

	<?php
	$action = '/services/update';
	$submitLabel = 'Update service';
	require __DIR__ . '/form.php';
	?>
</div>

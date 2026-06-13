<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo e($title ?? 'Fin NK'); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
	<link rel="stylesheet" type="text/css" href="/assets/libraries/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/main.css">
	<link rel="stylesheet" type="text/css" href="/assets/libraries/poppins/poppins.css">
	<script src="/assets/libraries/jquery/jquery-3.6.0.min.js"></script>
	<script src="/assets/libraries/sweetalert/sweetalert.min.js"></script>
</head>
<body>
	<?php echo $content; ?>
	<script src="/assets/libraries/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php

$activeNav = $activeNav ?? '';
$navigationAccess = $navigationAccess ?? [];
$initials = '';

if (isset($currentUser)) {
	$words = preg_split('/\s+/', trim($currentUser->name)) ?: [];
	foreach (array_slice($words, 0, 2) as $word) {
		$initials .= strtoupper(substr($word, 0, 1));
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo e($title ?? 'Fin NK'); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
	<link rel="stylesheet" type="text/css" href="/assets/libraries/bootstrap/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/main.css">
	<link rel="stylesheet" type="text/css" href="/assets/css/select2-min.css">
	<link rel="stylesheet" type="text/css" href="/assets/libraries/poppins/poppins.css">
	<link rel="stylesheet" type="text/css" href="/assets/libraries/font-awesome/css/font-awesome.css">
	<script src="/assets/libraries/jquery/jquery-3.6.0.min.js"></script>
	<script src="/assets/libraries/sweetalert/sweetalert.min.js"></script>
	<script src="/assets/libraries/select2/select2.min.js"></script>
	<?php if ($fullCalendarAssets ?? false): ?>
		<link rel="stylesheet" type="text/css" href="/assets/libraries/fullcalendar/main.min.css">
		<script src="/assets/libraries/fullcalendar/index.global.min.js"></script>
	<?php endif; ?>
	<?php if ($chartAssets ?? false): ?>
		<script src="/assets/libraries/chartjs/chart.umd.min.js"></script>
	<?php endif; ?>
</head>
<body>
	<div class="fluid-ctn">
		<aside>
			<div class="logo-ctn">
				<a href="/">
					<img src="/assets/img/logo-wide.png" title="BluBook" alt="BluBook">
				</a>
			</div>
			<div class="menu-toggle-ctn mob-menu-2">
				<i class="fa fa-times-circle" aria-hidden="true"></i>
			</div>
			<?php if ($navigationAccess['dashboard'] ?? false): ?>
				<div class="menu-item <?php echo $activeNav === 'dashboard' ? 'active' : ''; ?>">
					<a class="menu-link" href="/">
						<span class="menu-icon"><i class="fa fa-th-large" aria-hidden="true"></i></span>
						<span class="menu-title">Dashboard</span>
					</a>
				</div>
			<?php endif; ?>
			<?php if ($navigationAccess['appointments'] ?? false): ?>
				<div class="menu-item <?php echo $activeNav === 'appointments' ? 'active' : ''; ?>">
					<a class="menu-link" href="/appointments">
						<span class="menu-icon"><i class="fa fa-calendar-o" aria-hidden="true"></i></span>
						<span class="menu-title">Appointments</span>
					</a>
				</div>
			<?php endif; ?>
			<?php if ($navigationAccess['locations'] ?? false): ?>
				<div class="menu-item <?php echo $activeNav === 'locations' ? 'active' : ''; ?>">
					<a class="menu-link" href="/locations">
						<span class="menu-icon"><i class="fa fa-building-o" aria-hidden="true"></i></span>
						<span class="menu-title">Locations</span>
					</a>
				</div>
			<?php endif; ?>
			<?php if ($navigationAccess['services'] ?? false): ?>
				<div class="menu-item <?php echo $activeNav === 'services' ? 'active' : ''; ?>">
					<a class="menu-link" href="/services">
						<span class="menu-icon"><i class="fa fa-list-ul" aria-hidden="true"></i></span>
						<span class="menu-title">Services</span>
					</a>
				</div>
			<?php endif; ?>
			<?php if ($navigationAccess['users'] ?? false): ?>
				<div class="menu-item <?php echo $activeNav === 'users' ? 'active' : ''; ?>">
					<a class="menu-link" href="/users">
						<span class="menu-icon"><i class="fa fa-user-o" aria-hidden="true"></i></span>
						<span class="menu-title">Users</span>
					</a>
				</div>
			<?php endif; ?>
			<?php if ($navigationAccess['roles'] ?? false): ?>
				<div class="menu-item <?php echo $activeNav === 'roles' ? 'active' : ''; ?>">
					<a class="menu-link" href="/roles">
						<span class="menu-icon"><i class="fa fa-id-badge" aria-hidden="true"></i></span>
						<span class="menu-title">Roles</span>
					</a>
				</div>
			<?php endif; ?>
			<?php if ($navigationAccess['clients'] ?? false): ?>
				<div class="menu-item <?php echo $activeNav === 'clients' ? 'active' : ''; ?>">
					<a class="menu-link" href="/clients">
						<span class="menu-icon"><i class="fa fa-address-card-o" aria-hidden="true"></i></span>
						<span class="menu-title">Clients</span>
					</a>
				</div>
			<?php endif; ?>
			<?php if ($navigationAccess['reports'] ?? false): ?>
				<div class="menu-item <?php echo $activeNav === 'reports' ? 'active' : ''; ?>">
					<a class="menu-link" href="/reports">
						<span class="menu-icon"><i class="fa fa-area-chart" aria-hidden="true"></i></span>
						<span class="menu-title">Reports</span>
					</a>
				</div>
			<?php endif; ?>
			<div class="menu-item">
				<form class="menu-link-form" method="post" action="/logout">
					<?php echo csrf_field(); ?>
					<button class="menu-link" type="submit">
						<span class="menu-icon"><i class="fa fa-sign-out" aria-hidden="true"></i></span>
						<span class="menu-title">Logout</span>
					</button>
				</form>
			</div>
		</aside>

		<div class="app-view">
			<div class="top-bar">
				<?php if (isset($currentUser)): ?>
					<div class="client-pic-ctn fl-rt mg-rt-15 mg-tp-7">
						<?php echo e($initials); ?>
					</div>
					<div class="client-info-ctn fl-rt mg-rt-15 mg-tp-7">
						<div class="rec-ctn txt-grey"><?php echo e($currentUser->accountId); ?></div>
						<div class="rec-ctn txt-blue"><?php echo e($currentUser->name); ?></div>
					</div>
				<?php endif; ?>
				<div class="mob-menu client-pic-ctn fl-lt mg-rt-5 mg-lt-5 mg-tp-7">
					<i class="fa fa-bars" aria-hidden="true"></i>
				</div>
			</div>

			<?php echo $content; ?>

			<div class="footer">
				<p>
					<?php echo e(date('Y')); ?> &copy;
					<a href="https://bluwebs.com" target="_blank" rel="noopener">Bluwebs</a> - All Rights Reserved
				</p>
			</div>
		</div>
	</div>

	<script>
		const asideElement = document.querySelector('aside');
		const menuOpenButton = document.querySelector('.mob-menu');
		const menuCloseButton = document.querySelector('.mob-menu-2');

		menuOpenButton?.addEventListener('click', () => {
			asideElement?.classList.toggle('show-aside');
		});

		menuCloseButton?.addEventListener('click', () => {
			asideElement?.classList.toggle('show-aside');
		});
	</script>
	<script src="/assets/libraries/bootstrap/bootstrap.bundle.min.js"></script>
</body>
</html>

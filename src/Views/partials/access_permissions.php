<?php
$scopePermissions = [];
$standardPermissions = [];

foreach ($permissions as $group => $items) {
	foreach ($items as $permission) {
		$target = str_contains((string) $permission['slug'], '.scope.')
			? 'scopePermissions'
			: 'standardPermissions';
		${$target}[$group][] = $permission;
	}
}

$renderRows = static function (array $groups) use ($permissionMode, $selectedAllow, $selectedDeny): void {
	foreach ($groups as $group => $items) {
		foreach ($items as $permission) {
			$slug = (string) $permission['slug'];
			?>
			<tr>
				<td>
					<?php echo e($permission['label']); ?>
					<p><?php echo e($permission['description'] ?? ''); ?></p>
				</td>
				<td><p class="badge badge-blue"><?php echo e(ucfirst((string) $group)); ?></p></td>
				<td>
					<input
						type="checkbox"
						name="<?php echo $permissionMode === 'role' ? 'permissions[]' : 'allow[]'; ?>"
						value="<?php echo e($slug); ?>"
						<?php echo in_array($slug, $selectedAllow, true) ? 'checked' : ''; ?>
						aria-label="<?php echo $permissionMode === 'role' ? 'Allow' : 'Explicitly allow'; ?> <?php echo e($permission['label']); ?>"
					>
				</td>
				<?php if ($permissionMode === 'user'): ?>
					<td>
						<input
							type="checkbox"
							name="deny[]"
							value="<?php echo e($slug); ?>"
							<?php echo in_array($slug, $selectedDeny, true) ? 'checked' : ''; ?>
							aria-label="Explicitly deny <?php echo e($permission['label']); ?>"
						>
					</td>
				<?php endif; ?>
			</tr>
			<?php
		}
	}
};
?>

<?php if ($scopePermissions !== []): ?>
	<div class="permission-section-title">
		<h3>Visibility Scope</h3>
	</div>
	<table class="action-table align-middle permission-table">
		<thead>
			<tr>
				<th>Permission</th>
				<th>Group</th>
				<th>Allow</th>
				<?php if ($permissionMode === 'user'): ?>
					<th>Deny</th>
				<?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php $renderRows($scopePermissions); ?>
		</tbody>
	</table>
<?php endif; ?>

<div class="permission-section-title">
	<h3>Actions</h3>
</div>
<table class="action-table align-middle permission-table">
	<thead>
		<tr>
			<th>Permission</th>
			<th>Group</th>
			<th>Allow</th>
			<?php if ($permissionMode === 'user'): ?>
				<th>Deny</th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
		<?php $renderRows($standardPermissions); ?>
	</tbody>
</table>

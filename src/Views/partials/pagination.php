<?php

use Fin\Narekaltro\Domain\Shared\PageResult;

/** @var PageResult|null $pagination */
if (!isset($pagination) || !$pagination instanceof PageResult || $pagination->totalPages() <= 1) {
	return;
}

$paginationRoute = isset($paginationRoute) ? (string) $paginationRoute : '';
$paginationQuery = isset($paginationQuery) && is_array($paginationQuery) ? $paginationQuery : [];
$currentPage = $pagination->request->page;
$totalPages = $pagination->totalPages();
$startPage = max(1, $currentPage - 2);
$endPage = min($totalPages, $currentPage + 2);

$pageUrl = static function (int $page) use ($paginationRoute, $paginationQuery, $pagination): string {
	$query = array_filter(
		array_merge($paginationQuery, [
			'page' => $page,
			'per_page' => $pagination->request->perPage,
		]),
		static fn (mixed $value): bool => $value !== '' && $value !== null
	);

	return $paginationRoute . '?' . http_build_query($query);
};
?>

<div class="pagination-bar" aria-label="Pagination">
	<div class="pagination-meta">
		<?php echo e($pagination->firstItemNumber()); ?>-<?php echo e($pagination->lastItemNumber()); ?>
		of <?php echo e($pagination->total); ?>
	</div>
	<div class="pagination-actions">
		<?php if ($pagination->hasPrevious()): ?>
			<a class="pagination-link pagination-arrow" href="<?php echo e($pageUrl($currentPage - 1)); ?>" title="Previous page" aria-label="Previous page">
				<i class="fa fa-angle-left" aria-hidden="true"></i>
			</a>
		<?php endif; ?>

		<?php if ($startPage > 1): ?>
			<a class="pagination-link" href="<?php echo e($pageUrl(1)); ?>">1</a>
			<?php if ($startPage > 2): ?>
				<span class="pagination-ellipsis">...</span>
			<?php endif; ?>
		<?php endif; ?>

		<?php for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++): ?>
			<?php if ($pageNumber === $currentPage): ?>
				<span class="pagination-link is-active" aria-current="page"><?php echo e($pageNumber); ?></span>
			<?php else: ?>
				<a class="pagination-link" href="<?php echo e($pageUrl($pageNumber)); ?>"><?php echo e($pageNumber); ?></a>
			<?php endif; ?>
		<?php endfor; ?>

		<?php if ($endPage < $totalPages): ?>
			<?php if ($endPage < $totalPages - 1): ?>
				<span class="pagination-ellipsis">...</span>
			<?php endif; ?>
			<a class="pagination-link" href="<?php echo e($pageUrl($totalPages)); ?>"><?php echo e($totalPages); ?></a>
		<?php endif; ?>

		<?php if ($pagination->hasNext()): ?>
			<a class="pagination-link pagination-arrow" href="<?php echo e($pageUrl($currentPage + 1)); ?>" title="Next page" aria-label="Next page">
				<i class="fa fa-angle-right" aria-hidden="true"></i>
			</a>
		<?php endif; ?>
	</div>
</div>

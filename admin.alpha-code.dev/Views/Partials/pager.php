<?php
/** @var \Crystal\Components\Pager $pager_object */
$pager = $pager_object;
?>

<nav aria-label="Pagination">
<ul class="pagination">

<?php if ($pager->first !== null): ?>
    <li><a href="<?= $pager->first ?>" aria-label="First page">First</a></li>
<?php endif; ?>

<?php if ($pager->previous !== null): ?>
    <li><a href="<?= $pager->previous ?>" aria-label="Previous page">Previous</a></li>
<?php endif; ?>

<?php foreach ($pager->pagerItems as $page): ?>
    <li>
        <a href="<?= $this->e($page['url']) ?>"<?= $page['is_active'] ? ' aria-current="page"' : '' ?>>
            <?= $page['page_number'] ?>
        </a>
    </li>
<?php endforeach; ?>

<?php if ($pager->next !== null): ?>
    <li><a href="<?= $pager->next ?>" aria-label="Next page">Next</a></li>
<?php endif; ?>

<?php if ($pager->last !== null): ?>
    <li><a href="<?= $pager->last ?>" aria-label="Last page">Last</a></li>
<?php endif; ?>

</ul>
</nav>

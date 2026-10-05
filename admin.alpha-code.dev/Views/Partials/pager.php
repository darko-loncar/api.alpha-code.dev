<?php
$pager = $pager_object;
$pagerLinks = '';

// First
if ($pager->first !== null) {
    $pagerLinks .= '<li><a href="'.$this->e($pager->first).'" aria-label="First page">First</a></li>';
}

// Previous
if ($pager->previous !== null) {
    $pagerLinks .= '<li><a href="'.$this->e($pager->previous).'" aria-label="Previous page">Previous</a></li>';
}

// Page numbers
foreach ($pager->pagerItems as $page) {
    $ariaActiveAttr = ($page['is_active'])
        ? 'aria-current="page"'
        : '';

    $url = $this->e($page['url']);
    $pageNumber = $page['page_number'];

    $pagerLinks .= ($ariaActiveAttr)
        ? "<li><a $ariaActiveAttr href='$url'>$pageNumber</a></li>"
        : "<li><a href='$url'>$pageNumber</a></li>";
}

// Next
if ($pager->next !== null) {
    $pagerLinks .= '<li><a href="'.$this->e($pager->next).'" aria-label="Next page">Next</a></li>';
}

// Last
if ($pager->last !== null) {
    $pagerLinks .= '<li><a href="'.$this->e($pager->last).'" aria-label="Last page">Last</a></li>';
}
?>

<nav aria-label="Pagination">
    <ul class="pagination">
        <?= $pagerLinks ?>
    </ul>
</nav>

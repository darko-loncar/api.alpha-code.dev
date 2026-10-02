<?php
$pager = $this->getVar('pager_object');

$pagerLinks = '';

// First
if (!empty($pager->first)) {
    $pagerLinks .= sprintf(
        '<li><a href="%s" aria-label="First page">First</a></li>',
        htmlspecialchars($pager->first['url'], ENT_QUOTES, 'UTF-8')
    );
}

// Previous
if (!empty($pager->previous)) {
    $pagerLinks .= sprintf(
        '<li><a href="%s" aria-label="Previous page">Previous</a></li>',
        htmlspecialchars($pager->previous['url'], ENT_QUOTES, 'UTF-8')
    );
}

// Page numbers
foreach ($pager->pagerItems as $page) {
    $ariaActiveAttr = ($page['is_active'])
        ? 'aria-current="page"'
        : '';

    $url = htmlspecialchars(
        $page['url'],
        ENT_QUOTES,
        'UTF-8'
    );

    $pageNumber = htmlspecialchars(
        (string) $page['page_number'],
        ENT_QUOTES,
        'UTF-8'
    );

    $pagerLinks .= ($ariaActiveAttr)
        ? "<li $ariaActiveAttr><a $ariaActiveAttr href='$url'>$pageNumber</a></li>"
        : "<li><a href='$url'>$pageNumber</a></li>";
}

// Next
if (!empty($pager->next)) {
    $pagerLinks .= sprintf(
        '<li><a href="%s" aria-label="Next page">Next</a></li>',
        htmlspecialchars($pager->next['url'], ENT_QUOTES, 'UTF-8')
    );
}

// Last
if (!empty($pager->last)) {
    $pagerLinks .= sprintf(
        '<li><a href="%s" aria-label="Last page">Last</a></li>',
        htmlspecialchars($pager->last['url'], ENT_QUOTES, 'UTF-8')
    );
}
?>

<nav aria-label="Pagination">
    <ul class="pagination">
        <?= $pagerLinks ?>
    </ul>
</nav>

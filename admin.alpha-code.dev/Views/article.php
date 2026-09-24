<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>
<main class="ct-center">

<?php
// Sanitize HTML coming from db
$article["content"] = strip_tags(
    $article["content"],
    '<p><h1><h2><h3><h4><h5><h6><br><em><abbr><ul><ol><li><img><blockquote><pre><code><strong><cite><a>
    ');
?>

<h1>Article editor</h1>
<form class="gr-7-3">
<section class="editor">
    <label>Article title</label>
    <input value="<?= $article["title"] ?>">

    <label>Main text</label>
    <textarea rows="50"><?= $article["content"] ?></textarea>
</section>
<aside class="context-bar">
    <?= $this->e($article["id"]) ?>
    <?= $this->e($article["is_published"]) ?>
    <?= $this->e($article["title"]) ?>
    <?= $this->e($article["slug"]) ?>
    <?= $this->e($article["user__first_name"]." ".$article["user__last_name"]) ?>
    <?= $this->e($article["created_at"]) ?>
    <?= $this->e($article["published_at"]) ?>
    <?= $this->e($article["updated_at"]) ?>
</aside>
</form>
</main>
<?php echo $this->partial("footer") ?>

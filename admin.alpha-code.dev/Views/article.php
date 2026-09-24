<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>


<section class="ct-center gr-7-3">

<?php
// Sanitize HTML coming from db
$article["content"] = strip_tags(
    $article["content"],
    '<p><h1><h2><h3><h4><h5><h6><br><em><abbr><ul><ol><li><img><blockquote><pre><code><strong><cite><a>
    ');
?>

<div>
    <h1>Article editor</h1>
    <textarea id="editor" rows="50"><?= $article["content"] ?></textarea>
</div>
<div>
    <?= $this->e($article["id"]) ?>
    <?= $this->e($article["is_published"]) ?>
    <?= $this->e($article["title"]) ?>
    <?= $this->e($article["slug"]) ?>
    <?= $this->e($article["user__first_name"]." ".$article["user__last_name"]) ?>
    <?= $this->e($article["created_at"]) ?>
    <?= $this->e($article["published_at"]) ?>
    <?= $this->e($article["updated_at"]) ?>
</div>

</section>
<?php echo $this->partial("footer") ?>

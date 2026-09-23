<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>
<main>
<body>
<section class="ct-center">
<h1>Edit article</h1>

<table>
    <thead>
        <tr><th>ID</th><th>Published</th><th>Title</th><th>Slug</th><th>Author</th><th>Created At</th><th>Published At</th><th>Updated At</th><th>Actions</th></tr>
    </thead>
    <tbody>
        <tr>
            <th><?= $this->e($article["id"]) ?></th>
            <td><?= $this->e($article["is_published"]) ?></td>
            <td><?= $this->e($article["title"]) ?></td>
            <td><?= $this->e($article["slug"]) ?></td>
            <td><?= $this->e($article["user__first_name"]." ".$article["user__last_name"]) ?></td>
            <td><?= $this->e($article["created_at"]) ?></td>
            <td><?= $this->e($article["published_at"]) ?></td>
            <td><?= $this->e($article["updated_at"]) ?></td>
        </tr>
     </tbody>
</table>

<?php
    $clean = strip_tags($article["content"], '<p><h1><h2><h3><h4><h5><h6><br><em><abbr><ul><ol><li><img><blockquote><pre><code><strong><cite><a>');
?>
<div contenteditable="true">
<?= $clean ?>
</div>

</section>
</body>
<?php echo $this->partial("footer") ?>

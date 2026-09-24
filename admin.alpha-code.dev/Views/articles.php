<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>
<main>
<body>
<main class="ct-center">
<h1>Articles</h1>

<table>
    <thead>
        <tr><th>ID</th><th>Published</th><th>Title</th><th>Slug</th><th>Author</th><th>Created At</th><th>Published At</th><th>Updated At</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach($this->getVar("articleData") as $article) : ?>
        <tr>
            <th><?= $this->e($article["id"]) ?></th>
            <td><?= $this->e($article["is_published"]) ?></td>
            <td><?= $this->e($article["title"]) ?></td>
            <td><?= $this->e($article["slug"]) ?></td>
            <td><?= $this->e($article["user__first_name"]." ".$article["user__last_name"]) ?></td>
            <td><?= $this->e($article["created_at"]) ?></td>
            <td><?= $this->e($article["published_at"]) ?></td>
            <td><?= $this->e($article["updated_at"]) ?></td>
            <td><a href=/article/<?= $this->e($article["id"]) ?>>Edit</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</main>
<?php echo $this->partial("footer") ?>

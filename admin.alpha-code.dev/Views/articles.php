<?php echo $this->partial('head') ?>
<?php echo $this->partial('main-menu') ?>

<main class="wrapper-app">
    <h1>Articles</h1>
    <p><a href="/new-article">Create article</a></p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Published</th>
                <th>Title</th>
                <th>Slug</th>
                <th>Author</th>
                <th>Created</th>
                <th>Published at</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->getVar('articleData') as $article) : ?>
            <tr>
                <th><?= $this->e($article['id'] ?? '') ?></th>
                <td><?= !empty($article['is_published']) ? 'Yes' : 'No' ?></td>
                <td><?= $this->e($article['title'] ?? '') ?></td>
                <td><?= $this->e($article['slug'] ?? '') ?></td>
                <td><?= $this->e(trim(($article['user__first_name'] ?? '') . ' ' . ($article['user__last_name'] ?? ''))) ?></td>
                <td><?= $this->e($article['created_at'] ?? '') ?></td>
                <td><?= $this->e($article['published_at'] ?? '') ?></td>
                <td><?= $this->e($article['updated_at'] ?? '') ?></td>
                <td>
                    <a href="/article/<?= $this->e($article['id'] ?? '') ?>">Edit</a>
                    <form method="post" action="/article/delete/<?= $this->e($article['id'] ?? '') ?>" onsubmit="return confirm('Delete this article?')">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php echo $this->partial('footer') ?>

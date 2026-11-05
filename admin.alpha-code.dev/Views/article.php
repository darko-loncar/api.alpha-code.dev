<?php echo $this->partial('head') ?>
<?php echo $this->partial('main-menu') ?>

<main class="wrapper">
    <h1><?= $isNew ? 'Create article' : 'Edit article' ?></h1>

    <?php if ($errors !== []) : ?>
        <div class="form-errors" role="alert">
            <p>Please correct the following:</p>
            <ul>
                <?php foreach ($errors as $error) : ?>
                    <li><?= $this->e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="gr-7-3" method="post" action="<?= $this->e($formAction) ?>">
        <section>
            <label for="title">Title</label>
            <input id="title" name="title" class="article-title" required maxlength="255"
                   placeholder="Main article title..." value="<?= $this->e($article['title'] ?? '') ?>">

            <label for="slug">Slug</label>
            <input id="slug" name="slug" required maxlength="255"
                   placeholder="article-url-slug" value="<?= $this->e($article['slug'] ?? '') ?>">

            <label for="editor">Article content</label>
            <textarea id="editor" name="content" required><?= $this->e($article['content'] ?? '') ?></textarea>
        </section>

        <aside class="context-bar">
            <h3>Article info</h3>
            <?php if (!empty($article['user__first_name']) || !empty($article['user__last_name'])) : ?>
                <p>Author: <?= $this->e(trim(($article['user__first_name'] ?? '') . ' ' . ($article['user__last_name'] ?? ''))) ?></p>
            <?php endif; ?>
            <?php if (!empty($article['created_at'])) : ?>
                <p>Created: <?= $this->e($article['created_at']) ?></p>
            <?php endif; ?>
            <?php if (!empty($article['updated_at'])) : ?>
                <p>Last modified: <?= $this->e($article['updated_at']) ?></p>
            <?php endif; ?>

            <label>
                <input type="checkbox" name="is_published" value="1" <?= !empty($article['is_published']) ? 'checked' : '' ?>>
                Published
            </label>

            <p>
                <button type="submit"><?= $isNew ? 'Create article' : 'Save changes' ?></button>
                <a href="/articles">Cancel</a>
            </p>
        </aside>
    </form>
</main>

<script src="/vendor/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#editor',
    license_key: 'gpl',
    height: 600,
    menubar: false,
    plugins: ['lists', 'link', 'table', 'image', 'code'],
    toolbar: 'undo redo | blocks | fontfamily fontsize bold italic underline | bullist numlist table code | link image | crystalmore',
    branding: false,
    block_formats: 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6',
    font_family_formats: 'Segoe UI=system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;Arial=Arial,Helvetica,sans-serif;Verdana=Verdana,sans-serif',
    setup: function (editor) {
        editor.ui.registry.addButton('crystalmore', {
            text: 'More',
            tooltip: 'Insert Read More marker',
            onAction: function () {
                editor.insertContent('<hr data-crystal="more">');
            }
        });
    },
    file_picker_callback: (callback, value, meta) => {
        if (meta.filetype !== 'image') return;
        openImagePicker((imageUrl) => callback(imageUrl));
    }
});

function openImagePicker(callback) {
    const modal = document.getElementById('image-picker');
    if (!modal) return;

    modal.classList.add('open');
    modal.addEventListener('click', function handler(event) {
        const image = event.target.closest('[data-image-url]');
        if (!image) return;

        callback(image.dataset.imageUrl);
        modal.classList.remove('open');
        modal.removeEventListener('click', handler);
    });
}
</script>

<div id="image-picker" class="image-picker">
    <div class="image-picker-dialog">
        <div class="image-grid">
            <button type="button" data-image-url="/uploads/1.jpg">
                <img src="/uploads/1.jpg" alt="Select this image">
            </button>
        </div>
    </div>
</div>

<?php echo $this->partial('footer') ?>

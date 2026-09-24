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
        <section>
            <label>Main title</label>
            <input class="article-title" placeholder="Main article title..."value="<?= $article["title"] ?>">

            <label>Subtitle</label>
            <input placeholder="Subtitle" value="">

            <label>Main text</label>
            <textarea id="editor">
                <?= $article["content"] ?>
            </textarea>
        </section>
        <aside class="context-bar">

            <h3>Article info</h3>
            <span>Author: </span>
            <span>Created: </span>
            <span>Last modified: </span>

            <label>Page Slug</label>
            <input placeholder="Subtitle" value="">

            <label>Category</label>
            <select>
                <option>-- uncategorized --
                <option>Computers
                <option>Health
                <option>Psychology
                <option>Programming
            </select>

            <h3>Page Meta</h3>
            <label>Meta Description</label>
            <input placeholder="Subtitle" value="">

            <label>Canonical URL</label>
            <input placeholder="Subtitle" value="">

            <h3>Social</h3>
            <label>OG Title</label>
            <input placeholder="Subtitle" value="">

            <label>OG Description</label>
            <input placeholder="Subtitle" value="">

            <label>OG Image</label>
            <input placeholder="Subtitle" value="">


                <!-- --
            <?= $this->e($article["id"]) ?>
            <?= $this->e($article["is_published"]) ?>
            <?= $this->e($article["title"]) ?>
            <?= $this->e($article["slug"]) ?>
            <?= $this->e($article["user__first_name"]." ".$article["user__last_name"]) ?>
            <?= $this->e($article["created_at"]) ?>
            <?= $this->e($article["published_at"]) ?>
            <?= $this->e($article["updated_at"]) ?>-->
        </aside>
    </form>
</main>

<script>
tinymce.init({
    selector: '#editor',
    license_key: 'gpl',
    height: 600,
    menubar: false,
    skin: 'oxide-dark',
    content_css: 'dark',
    plugins: ['lists','link','table','image', 'code'],
    toolbar: 'undo redo | blocks | fontfamily fontsize bold italic underline | bullist numlist table code | link image | crystalmore',
    branding: false,
    //content_style:'body {font-family: 'System=system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;}'',

    block_formats:
        'Paragraph=p;' +
        'Heading 2=h2;' +
        'Heading 3=h3;' +
        'Heading 4=h4;' +
        'Heading 5=h5;' +
        'Heading 6=h6',

    font_family_formats:
        'Segoe UI=system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;' +
        'Arial=Arial,Helvetica,sans-serif;' +
        'Verdana=Verdana,sans-serif;',

    setup: function (editor) {
        editor.ui.registry.addButton('crystalmore', {
            text: 'More',
            tooltip: 'Insert Read More marker',
            onAction: function () {
                editor.insertContent(
                    '<hr data-crystal="more">'
                );
            }
        });
    },

    file_picker_callback: (callback, value, meta) => {
           if (meta.filetype !== 'image') {
               return;
           }

           openImagePicker((imageUrl) => {
               callback(imageUrl);
           });
       }


});

function openImagePicker(callback) {
    const modal = document.getElementById('image-picker');

    modal.classList.add('open');

    modal.addEventListener('click', function handler(event) {
        const image = event.target.closest('[data-image-url]');

        if (!image) {
            return;
        }

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
                <img src="/uploads/1.jpg">
            </button>
        </div>

    </div>
</div>

<?php echo $this->partial("footer") ?>

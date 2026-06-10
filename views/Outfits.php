<?php $this->includeTemplatePart("Header"); ?>

<section role="region">
    <h2>Outfits</h2>
    <table>
        <tr>
            <th>Naziv</th>
            <th>Vrijednost</th>
            <th>Kreirano</th>
            <th>Izmijenjeno</th>
        </tr>
    <?php foreach($outfits as $outfit): ?>
        <tr>
            <td><a href="/outfit/<?= $outfit['id']?>"><?= $outfit['name'] ?></a></td>
            <td><?= $outfit['value'] ?></td>
            <td><?= $outfit['created_at'] ?></td>
            <td><?= $outfit['updated_at'] ?></td>
        </tr>
    <?php endforeach; ?>
    </table>
</section>

<aside role="complementary">
    <p>Lorem Ipsum...</p>
</aside>

<?php $this->includeTemplatePart("Footer"); ?>

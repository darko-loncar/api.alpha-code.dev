<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>

<main class="wrapper">
    <h1>Users</h1>

    <?php $table = $this->getVar("dataTable"); ?>

    <table>
        <thead>
            <tr>
                <?php foreach ($table->getColumns() as $column) : ?>
                    <th><?= $this->e($column->getLabel()) ?></th>
                <?php endforeach; ?>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($table->getRows() as $row) : ?>
                <tr>
                    <?php foreach ($table->getColumns() as $column) : ?>
                        <td>
                            <?= $this->e(
                                $row[$column->getField()] ?? ''
                            ) ?>
                        </td>
                    <?php endforeach; ?>

                    <td>
                        <a href="/users/<?= $this->e($row['uuid']) ?>">
                            View
                        </a>
                        <a href="/users/delete/<?= $this->e($row['uuid']) ?>">
                            Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php echo $this->partial("Partials.pager") ?>
</main>

<?php echo $this->partial("footer") ?>

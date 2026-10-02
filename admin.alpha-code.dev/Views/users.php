<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>
<main class="ct-center">
<h1>Users</h1>

<table>
    <thead>
        <tr><th>ID</th><th>First name</th><th>Last name</th><th>Email</th><th>Phone</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach($this->getVar("userData") as $user) : ?>
        <tr>
            <th><?= $this->e($user["id"]) ?></th>
            <td><?= $this->e($user["first_name"]) ?></td>
            <td><?= $this->e($user["last_name"]) ?></td>
            <td><?= $this->e($user["email"]) ?></td>
            <td><?= $this->e($user["phone"]) ?></td>
            <td><a href="/user-profile/<?= $this->e($user["uuid"]) ?>">Edit</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php echo $this->partial("Partials.pager") ?>

</main>
<?php echo $this->partial("footer") ?>

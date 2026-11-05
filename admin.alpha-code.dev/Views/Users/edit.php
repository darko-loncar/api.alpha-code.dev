<?php echo $this->partial('head') ?>
<?php echo $this->partial('main-menu') ?>

<main class="ct-center">
    <h1>Edit user</h1>
    <?= $this->partial('Users.form', [
        'userData' => $userData,
        'errors' => $errors,
        'formAction' => '/users/update/' . rawurlencode($userData['uuid']),
        'isEdit' => true,
    ]) ?>
</main>

<?php echo $this->partial('footer') ?>

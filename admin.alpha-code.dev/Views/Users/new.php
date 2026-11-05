<?php echo $this->partial('head') ?>
<?php echo $this->partial('main-menu') ?>

<main class="ct-center">
    <h1>Create user</h1>
    <?= $this->partial('Users.form', [
        'userData' => $userData,
        'errors' => $errors,
        'formAction' => '/users/create',
        'isEdit' => false,
    ]) ?>
</main>

<?php echo $this->partial('footer') ?>

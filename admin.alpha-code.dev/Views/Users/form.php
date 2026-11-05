<?php $user = $userData ?? []; ?>

<?php if (!empty($errors)) : ?>
    <div class="form-errors" role="alert">
        <p>Please correct the following:</p>
        <ul>
            <?php foreach ($errors as $error) : ?>
                <li><?= $this->e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= $this->e($formAction) ?>" class="user-form">
    <label for="first_name">First name</label>
    <input id="first_name" name="first_name" type="text" required maxlength="100"
           value="<?= $this->e($user['first_name'] ?? '') ?>">

    <label for="last_name">Last name</label>
    <input id="last_name" name="last_name" type="text" required maxlength="100"
           value="<?= $this->e($user['last_name'] ?? '') ?>">

    <label for="email">Email</label>
    <input id="email" name="email" type="email" required maxlength="255"
           value="<?= $this->e($user['email'] ?? '') ?>">

    <label for="phone">Phone</label>
    <input id="phone" name="phone" type="tel" maxlength="50"
           value="<?= $this->e($user['phone'] ?? '') ?>">

    <label for="password">
        <?= $isEdit ? 'New password (leave blank to keep the current password)' : 'Password' ?>
    </label>
    <input id="password" name="password" type="password" autocomplete="new-password"
           <?= $isEdit ? '' : 'required' ?>>

    <button type="submit"><?= $isEdit ? 'Save changes' : 'Create user' ?></button>
    <a href="/users">Cancel</a>
</form>

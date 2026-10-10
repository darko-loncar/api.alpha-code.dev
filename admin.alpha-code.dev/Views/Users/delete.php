<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>
<main class="ct-center">
<h1>Delete user</h1>
<form method="post" action="/users/delete/<?= $this->e($userData["uuid"]); ?>">
    <input type="submit" value="Delete user">
</form>
</main>
<?php echo $this->partial("footer") ?>

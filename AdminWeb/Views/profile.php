<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>
<main>
<body>
<section class="ct-center">
<h1>User Profile</h1>

<?php
$user = $this->getVar("userData");
?>

<div class="user-card">

    <div class="user-card-header">
        <div class="user-avatar">
            <?= $this->escape(mb_substr($user["first_name"], 0, 1) . mb_substr($user["last_name"], 0, 1)); ?>
        </div>

        <div>
            <h2>
                <?= $this->escape($user["first_name"] . " " . $user["last_name"]); ?>
            </h2>
            <div class="user-card-email">
                <?= $this->escape($user["email"]); ?>
            </div>
        </div>
    </div>

    <div class="user-card-details">

        <div class="user-detail">
            <span class="user-detail-label">Phone</span>
            <span class="user-detail-value">
                <?= $this->escape($user["phone"]); ?>
            </span>
        </div>

        <div class="user-detail">
            <span class="user-detail-label">User ID</span>
            <span class="user-detail-value">
                <?= $this->escape($user["id"]); ?>
            </span>
        </div>

        <div class="user-detail user-detail-full">
            <span class="user-detail-label">UUID</span>
            <span class="user-detail-value user-uuid">
                <?= $this->escape($user["uuid"]); ?>
            </span>
        </div>

    </div>

    <div class="user-card-actions">
        <a href="/users/<?= $this->escape($user["uuid"]); ?>/edit">Edit user</a>
    </div>

</div>

</section>
</body>
<?php echo $this->partial("footer") ?>

<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>
<main class="ct-center">
<h1>Users</h1>

<?php

$scheme = $_SERVER['REQUEST_SCHEME'];
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$query = $_GET;
$url = $scheme . '://' . $host . $uri;


$pagerHtml = "";
foreach ($this->getVar("pager")->pagerItems as $page) {

    $pager_query = http_build_query([
        'page' => $page,
        'display_records' => 10,
    ]);

    if ($query) {
        $query = '?' . http_build_query($query);
        }

        dd($query, $pager_query);


    $pagerHtml .= "<a href='$url'>$page</a>";
}

echo $pagerHtml;
?>

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
            <td><a href=/user-profile/<?= $this->e($user["uuid"]) ?>>Edit</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</main>
<?php echo $this->partial("footer") ?>

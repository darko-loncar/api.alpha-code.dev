<?php echo $this->partial("head") ?>
<?php echo $this->partial("main-menu") ?>
<main>
<body>
<section class="ct-center">
<h1>User Profile</h1>

<pre>
<?php print_r($this->getVar("userData")); ?>
<pre>

</section>
</body>
<?php echo $this->partial("footer") ?>

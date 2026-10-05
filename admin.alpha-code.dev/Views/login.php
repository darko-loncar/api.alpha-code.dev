<?php echo $this->partial("head") ?>

<div class="ct-center ct-center--50">
<img src=gfx/logo.svg alt="Alpha Code" width="120" height="120" loading="eager" decoding="async" fetchpriority="high">
<h1>Admin panel</h1>
</div><!--/.ct-center-->

<main>
<body>
<section class="ct-center ct-center--50">
<h2>Login</h2>
<form method=post action="/login">
<label for=username>Username
<input type=text id=username name=username autocomplete=username required>
</label>
<label for=password>Password
<input type=password id=password name=password autocomplete=current-password required>
</label>
<button type=submit>Log in</button>
</form>
</section>
</body>
<?php echo $this->partial("footer") ?>

<?php echo $this->partial("head") ?>
<header>
<div class="ct-center top-bar">
<a href="/" id=logo>
<img src=gfx/logo.svg alt="Alpha Code" width="120" height="120" loading="eager" decoding="async" fetchpriority="high">
<span>Alpha Code</span>
</a>
</button>
</div><!--/.ct-center-->
</header>
<main>
<body>
<section class="ct-center ct-center--50">
<h1>Login</h1>
<form class="" method=post action="/login">
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
</html>

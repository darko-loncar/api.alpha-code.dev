<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">

    <title>Nadzorna ploča odnosa između Ane i Darka</title>    
    
    <meta name="description" content="This is a testground">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="robots" content="no-index,no-follow">

    <!--Preload resources-->
    <link rel="preload" href="<?= url("css/styles.css") ?>" as="style">
    <link rel="preload" href="<?= url("fonts/roboto-latin-regular.woff2") ?>" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="<?= url("fonts/righteous-latin-ext-regular.woff2") ?>" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="<?= url("gfx/grassy-flowers.webp") ?>" as="image">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= url("css/styles.css") ?>">
    <link rel="stylesheet" href="<?= url("css/animations.css") ?>">

    <!-- Favicons for multiple platforms -->
    <link rel="icon" type="image/png" href="<?= url("gfx/favicon.png") ?>">
    <link rel="icon" type="image/webp" sizes="32x32 64x64 192x192" href="<?= url("gfx/favicon.webp") ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= url("gfx/favicon.webp") ?>">
    
</head>

<body>

<header class="wrap" role="banner">    
    <div id="branding" class="wrap-center">
        <img id="main-logo" src="<?= url("gfx/favicon-192x192.webp") ?>" height="128" width="128" alt="Logo of Aplikacija za praćenje napretka u vezi">
        <h1>Aplikacija za praćenje napretka u vezi</h1>
    </div>

    <nav aria-label="Main Navigation" class="wrap-center">
        <ul class="nav-list">
            <li><a <?php if($pageId == "index") {echo "class='active' aria-current='page'";} ?> href="<?= url("/") ?>">Nadzorna ploča</a></li>
            <li><a <?php if($pageId == "reporting") {echo "class='active' aria-current='page'";} ?> href="<?= url("reporting") ?>">Obrazac prijave nepravilnosti</a></li>
            <li><a <?php if($pageId == "work-orders") {echo "class='active' aria-current='page'";} ?> href="<?= url("work-orders") ?>">Radni nalozi</a></li>
            <li><a <?php if($pageId == "outfits") {echo "class='active' aria-current='page'";} ?> href="<?= url("outfits") ?>">Pregled outfita</a></li>
        </ul>
    </nav>
</header>

<main role="main" class="wrap-center">
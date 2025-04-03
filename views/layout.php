<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/public/assets/css/output.css" rel="stylesheet">
    <title>Konzultace FaME <?= $title ?? "" ?></title>
    <link rel="icon" type="image/x-icon" href="/public/assets/images/favicon.svg">
    <script>console.log(atob("Q3JlYXRlZCBieSBKaW5kcmljaCBDYWxldGth"))</script>
</head>

<body class="max-w-[1664px] mx-auto p-2 w-full h-screen dark:text-[var(--color-text-base)] background">
    <div class="fixed -z-10 bg-[rgba(255,255,255,.1) dark:bg-[rgba(0,0,0,.1) backdrop-blur-xs inset-0"></div>
    <?php if (isset($_SESSION['user_profile']))
        include "views/partials/header.php"; ?>

    <?= $content ?? '' ?>

</body>

</html>
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

<body
    class="max-w-[2400px] w-dvw h-dvh mx-auto overflow-y-hidden dark:text-[var(--color-text-base)] bg-norm flex flex-col">
    <?php if (isset($_SESSION['user_profile']))
        include "views/partials/header.php"; ?>

    <?= $content ?? '' ?>
</body>

</html>
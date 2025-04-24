<?php

use Helpers\RoleAccess;

require "vendor/autoload.php";

if ((isset($_GET['logout'])) && ($_GET['logout'] == 1)) {
    session_destroy();
    header("Location: /");
}
?>

<header class="w-full flex flex-col gap-2 sm:flex-row md:gap-0 justify-between items-center py-2">
    <a href="/" class="text-xl">Evidence konzultací FaME</a>
    <div class="flex items-center justify-center gap-6 px-2 sm:p-0">
        <a href="/" class="text-sm sm:text-base"><?php echo $_SESSION["user_profile"]["name"] ?></a>
        <?php if (RoleAccess::checkNameAccess()) { ?>
            <a href="/admin" class="inline-block text-xs sm:text-sm font-medium button-alt p-2!">Administrace</a>
        <?php } ?>
        <a class="inline-block text-xs sm:text-sm font-medium button p-2! whitespace-nowrap" href="?logout=1"> Odhlásit
            se </a>
    </div>
</header>
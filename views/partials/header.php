<?php

use Helpers\RoleAccess;

require "vendor/autoload.php";

if ((isset($_GET['logout'])) && ($_GET['logout'] == 1)) {
    session_destroy();
    header("Location: /");
}
?>

<header
    class="w-full relative inset-x-0 flex flex-col gap-2 sm:flex-row md:gap-0 justify-between items-center py-2 px-2 xl:px-4">
    <a href="/" class="text-xl whitespace-nowrap">Evidence konzultací FaME</a>
    <div class="w-full flex px-2 gap-4 lg:gap-10 sm:p-0 flex-col items-end lg:flex-row lg:justify-end">
        <div class="flex justify-end gap-4 items-center lg:order-2">
            <a href="/" class="text-sm sm:text-base"><?php echo $_SESSION["user_profile"]["name"] ?></a>
            <a class="inline-block text-xs sm:text-sm font-medium button-accent p-2! whitespace-nowrap"
                href="?logout=1">
                Odhlásit
                se </a>
        </div>
        <?php if (RoleAccess::hasRoleAccess()) { ?>
            <div class="flex gap-4">
                <a class="button text-xs sm:text-sm inline-block whitespace-nowrap" href="/handle_session">Přidat nový
                    záznam</a>
                <?php if (RoleAccess::checkNameAccess()) { ?>
                    <a href="/admin" class="inline-block text-xs sm:text-sm font-medium button-alt p-2!">Administrace</a>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</header>
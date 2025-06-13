<?php

namespace Helpers;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class RoleAccess
{
    /**
     * Zkontroluje přístup k celé stránce a přesměruje v případě nedostatečných práv
     */
    public static function checkRoleAccess($allowedRoles = ['admin', 'utb_zam']): void
    {
        if (!self::hasRoleAccess($allowedRoles)) {
            header('Location: /');
            exit();
        }
    }

    /**
     * Zkontroluje, zda uživatel má alespoň jednu z požadovaných rolí
     * Vrací boolean hodnotu pro použití v podmínkách
     */
    public static function hasRoleAccess($allowedRoles = ['admin', 'utb_zam']): bool
    {
        // Pokud není nastavený user_profile nebo role, nemá přístup
        if (!isset($_SESSION['user_profile']) || !isset($_SESSION['user_profile']['roles'])) {
            return false;
        }

        foreach ($_SESSION['user_profile']['roles'] as $role) {
            if (in_array($role, $allowedRoles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Zkontroluje, zda má uživatel přístup na základě povolených jmen.
     */
    public static function checkNameAccess(array $allowedNames = ['Jindřich Caletka', 'Pavla Habrovanská', 'Jana Přílučíková', 'Šárka Juříková', 'Eva Kadlečková', 'Yvona Žáčková', 'Bronislava Neubauerová']): bool
    {
        if (!isset($_SESSION['user_profile']['name']) || !in_array($_SESSION['user_profile']['name'], $allowedNames, true)) {
            return false;
        }

        return true;
    }
}

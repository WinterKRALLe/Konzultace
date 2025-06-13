<?php

use League\OAuth2\Client\Token\AccessToken;
use TheNetworg\OAuth2\Client\Provider\Azure;

session_start();

require 'vendor/autoload.php';

$config = require "config/entra_config.php";

$provider = new Azure([
    'clientId' => $config['client_id'],
    'clientSecret' => $config['client_secret'],
    'redirectUri' => $config['redirect_uri'],
    'tenant' => $config['tenant_id'],
    'scope' => $config['scopes'],
    'urlAPI' => 'https://graph.microsoft.com/'
]);

// Pokud není v URL "code", přesměruj uživatele na přihlašovací stránku Microsoftu
if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl(['prompt' => 'select_account']);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
}

// Ověření OAuth2 stavu
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Neplatný OAuth state');
}

try {
    // Výměna kódu za přístupový token
    $token = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);

    if (!$token instanceof AccessToken) {
        throw new Exception('Access token is not valid');
    }

    $_SESSION['token_data'] = $token->jsonSerialize();

    $idTokenClaims = $token->getIdTokenClaims();

    if (isset($idTokenClaims['groups'])) {
        $groups = $idTokenClaims['groups'];
    }

    $roles = [];

    $groupRoles = [
        'c35357e8-3f4b-4793-8df5-dfe1894b3ea8' => 'utb_stu', // utb_stu
        '546d5f43-0eb3-4c94-91d5-2c1f82bc1f56' => 'utb_zam', // grp-FAME-users-konzultace
        'c90839a6-36f7-4a82-bc0e-3b113b0d1e58' => 'utb_zam', // utb_zam
        '3dbfa8fd-25dd-41c5-ba7f-5f43d54c3f17' => 'utb_zam'  // utb_zam_fame
    ];

    foreach ($groups as $group) {
        if (isset($groupRoles[$group])) {
            if (!in_array($groupRoles[$group], $roles)) {
                $roles[] = $groupRoles[$group];
            }
        }
    }

    if (in_array("utb_stu", $roles) && in_array("utb_zam", $roles)) {
        $roles = array_diff($roles, ["utb_zam"]);
        $roles = array_values($roles);
    }

    $_SESSION['user_profile'] = [
        'name' => $idTokenClaims['name'] ?? null,
        'mail' => $idTokenClaims['upn'] ? explode('@', $idTokenClaims['upn'])[0] : null,
        'roles' => $roles,
    ];

    header('Location: /');
    exit;

} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    exit('Chyba při získávání dat z Microsoft Graph API');
}

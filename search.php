<?php
session_start();
use TheNetworg\OAuth2\Client\Provider\Azure;
use League\OAuth2\Client\Token\AccessToken;

require 'vendor/autoload.php';
$config = require 'config/entra_config.php';

$provider = new Azure([
    'clientId' => $config['client_id'],
    'clientSecret' => $config['client_secret'],
    'redirectUri' => $config['redirect_uri'],
    'tenantId' => $config['tenant_id'],
    'scope' => $config['scopes']
]);
$provider->urlAPI = 'https://graph.microsoft.com/';

if (!isset($_SESSION['token_data'])) {
    echo json_encode(['error' => 'Není k dispozici přístupový token']);
    exit;
}

try {
    $token = new AccessToken($_SESSION['token_data']);

    if ($token->hasExpired()) {
        if (isset($_SESSION['token_data']['refresh_token'])) {
            $token = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $_SESSION['token_data']['refresh_token']
            ]);
            $_SESSION['token_data'] = $token->jsonSerialize();
        } else {
            echo json_encode(['error' => 'Token vypršel, přihlaste se znovu']);
            exit;
        }
    }

    $query = $_GET['q'] ?? '';
    if (strlen($query) < 3) {
        echo json_encode(['users' => []]);
        exit;
    }

    $searchUrl = "https://graph.microsoft.com/v1.0/groups/{$config['utb_stu_id']}/members?"
        . '$select=displayName,mail'
        . '&$search="displayName:' . $query . '"'
        . '&$top=10';

    // Přidáme hlavičku ConsistencyLevel: eventual
    $request = $provider->getAuthenticatedRequest('GET', $searchUrl, $token);
    $request = $request->withHeader('ConsistencyLevel', 'eventual');

    $response = $provider->getParsedResponse($request);

    $users = [];
    if (isset($response['value'])) {
        foreach ($response['value'] as $user) {
            $users[] = [
                'displayName' => $user['displayName'],
                'email' => $user['mail']
            ];
        }
    }

    echo json_encode(['users' => $users]);

} catch (Exception $e) {
    error_log('Graph API Error: ' . $e->getMessage());
    echo json_encode(['error' => 'Chyba při vyhledávání']);
}
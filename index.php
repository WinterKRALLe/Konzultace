<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/vendor/autoload.php';

$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('\\', '/', dirname($scriptName));

$request = trim(preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $requestUri), '/');
if (false !== $pos = strpos($request, '?')) {
    $request = substr($request, 0, $pos);
}

$route = $request ?: 'home';

$routes = [
    'home' => __DIR__ . '/public/pages/home.php',
    'handle_session' => __DIR__ . '/public/pages/handle_session.php',
    'admin' => __DIR__ . '/public/pages/admin.php',
];

if (isset($routes[$route]) && file_exists($routes[$route])) {
    ob_start();
    require $routes[$route];
    $content = ob_get_clean();
    require __DIR__ . '/views/layout.php';

} else {
    http_response_code(404);
    echo '404 - Stránka nebyla nalezena';
}

<?php 

include_once "./app/autoloader.php";

use Controllers\auth\LoginController as LoginController;
$ua = new LoginController;
$ua->sessionValidate();
if (!$ua->sv) { $ua->attemptRememberLogin(); }


// Verifica si la sesión está validada
function isSessionValid() {
    // Use centralized validator result
    global $ua;
    return ($ua && $ua->sv === true);
}

// Resolve locale from URL prefix: /en/... or /es/...
$requestUri = strtok($_SERVER['REQUEST_URI'], '?');
$segments = array_values(array_filter(explode('/', trim((string)$requestUri, '/')), 'strlen'));
$lang = 'es';
if (!empty($segments) && in_array($segments[0], ['es', 'en'], true)) {
    $lang = $segments[0];
    array_shift($segments);
}
$request = '/' . implode('/', $segments);
if ($request === '/' || $request === '') {
    $request = '/';
}

if (!isSessionValid() && !in_array($request, ['/login', '/register'])) {
    header('Location: ' . ($lang === 'en' ? '/en/login' : '/login'));
    exit;
}

// Función para incluir una vista
function render($view, $layout = true, $data = [], $title = "Monitor FV", $lang = 'es') {
    extract($data); // Extrae las variables de $data para usarlas en la vista
    $viewBase = __DIR__ . "/resources/views/" . ($lang === 'en' ? 'en' : 'es');
    if ($layout) {
        include_once $viewBase . '/layouts/main.php';
        head(new LoginController(), $title); 
        body();
        include_once $viewBase . "/$view.php";
        scripts();
        foot();
    } else {
        include_once $viewBase . "/$view.php";
    }
}

// Rutas protegidas con el layout principal
$routes_with_layout = [
    '/' => 'sections/home',
    '/usuarios' => 'sections/users',
    '/matrices' => 'sections/matrices',
    '/matriz' => 'sections/matrices/matrix',
    '/puntos' => 'sections/matrices/matrix/points',
    '/puntos/punto' => 'sections/matrices/matrix/points/point',
    '/tiempo_real' => 'sections/live-readings',
    '/historico' => 'sections/history',
    '/perfil' => 'sections/profile',
];

// Rutas sin layout
$routes_without_layout = [
    '/login' => 'auth/login',
    '/register' => 'auth/register',
];

// Manejo de rutas dinámico
if (array_key_exists($request, $routes_with_layout)) {
    $titles = $lang === 'en'
        ? [
            '/' => 'Home | Monitor FV',
            '/usuarios' => 'Users | Monitor FV',
            '/matrices' => 'Matrices | Monitor FV',
            '/matriz' => 'Matrix | Monitor FV',
            '/puntos' => 'Points | Monitor FV',
            '/puntos/punto' => 'Point Detail | Monitor FV',
            '/tiempo_real' => 'Real Time | Monitor FV',
            '/historico' => 'Historical | Monitor FV',
            '/perfil' => 'Profile | Monitor FV',
        ]
        : [
            '/' => 'Inicio | Monitor FV',
            '/usuarios' => 'Usuarios | Monitor FV',
            '/matrices' => 'Matrices | Monitor FV',
            '/matriz' => 'Matriz | Monitor FV',
            '/puntos' => 'Puntos | Monitor FV',
            '/puntos/punto' => 'Detalle Punto | Monitor FV',
            '/tiempo_real' => 'Tiempo Real | Monitor FV',
            '/historico' => 'Histórico | Monitor FV',
            '/perfil' => 'Perfil | Monitor FV',
        ];
    $title = $titles[$request] ?? "Monitor FV";
    render($routes_with_layout[$request], true, [], $title, $lang); // Pass the title
    // render($routes_with_layout[$request]); // Con layout
} elseif (array_key_exists($request, $routes_without_layout)) {
    render($routes_without_layout[$request], false, [], 'Monitor FV', $lang); // Sin layout
} else {
    // Ruta no encontrada
    http_response_code(404);
    render('errors/404', false, [], '404', $lang); // Página 404 sin layout
}

?>



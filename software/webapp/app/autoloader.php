<?php 

namespace {

define('ROOT',dirname(__FILE__));
define('DS',DIRECTORY_SEPARATOR);

require_once __DIR__ . '/config.php';

spl_autoload_register('autoload');

function autoload($class){
    $class = ROOT . DS . str_replace("\\",DS,$class) . '.php';
    if(!file_exists($class)){
        throw new \Exception("Error, clase no encontrada " . $class, 1);
    }

    require_once($class);
}

// Secure session bootstrap and CSRF utils
if (session_status() === PHP_SESSION_NONE) {
    // Configure cookie flags; 'secure' should be true on HTTPS
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => $secure,
        'samesite' => 'Lax'
    ]);
    session_start();
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function csrf_token(){ return $_SESSION['csrf'] ?? ''; }
function verify_csrf($token){ return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$token); }

function is_valid_device_api_key($token){
    $expected = (string) app_env('DEVICE_API_KEY', '');
    return $expected !== '' && is_string($token) && hash_equals($expected, $token);
}

function is_device_telemetry_request($postData){
    if (!is_array($postData)) {
        return false;
    }

    $hasDeviceMarker = isset($postData['_np']);
    $hasApiKey = is_valid_device_api_key($postData['api_key'] ?? '');

    return $hasDeviceMarker && $hasApiKey;
}
}

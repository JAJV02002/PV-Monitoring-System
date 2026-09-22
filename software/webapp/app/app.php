<?php

namespace app;

require_once __DIR__ . '/autoloader.php';
date_default_timezone_set('America/Mexico_City');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

use Controllers\auth\LoginController as LoginController;
use Controllers\MatrixController as MatrixController;
use Controllers\ReadingController;
use Controllers\UserController as UserController;
use Models\users as UsersModel;

$postData = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?: [];
$postKeys = array_keys($postData);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    $postData = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW) ?: [];

    // Determine whether this is a monitoring-device request
    $isDeviceRequest = isset($postData['_np']);

    if ($isDeviceRequest) {
        // Validate the ESP32 using its API key
        $receivedApiKey = $postData['api_key'] ?? '';

        if (
            !is_valid_device_api_key($receivedApiKey)
        ) {
            http_response_code(401);
            header('Content-Type: application/json');

            echo json_encode([
                'error' => 'Invalid API key'
            ]);

            exit;
        }
    } else {
        // Browser POST requests must still provide a CSRF token
        if (
            !isset($postData['_csrf']) ||
            !verify_csrf($postData['_csrf'])
        ) {
            http_response_code(403);
            header('Content-Type: application/json');

            echo json_encode([
                'error' => 'Invalid CSRF token'
            ]);

            exit;
        }
    }
    
    /*******************LOGIN */
    $login = in_array('_login', $postKeys);
    if($login){
        $datos = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?: [];
        $userLogin = new LoginController();
        print_r($userLogin->userAuth($datos));
    }

    /*************SIGN UP */
    $signup = in_array('_register', $postKeys);
    if($signup){
        $datos = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?: [];
        $userSignup = new LoginController();
        $userLogin = new LoginController();
        print_r($userSignup->userSignup($datos));
        print_r($userLogin->userAuth($datos));
    }

    /*********************AGREGAR PUNTO */
    $ap = in_array('_ap', $postKeys);
    if($ap){
        $datos = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?: [];
        $point = new MatrixController();
        print_r($point->newPoint($datos));
        header('Location: /puntos');
        
    }

    /*********************AGREGAR MATRIZ */
    // nm: Nueva matriz
    $nm = in_array('_nm', $postKeys);
    if($nm){
        $datos = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?: [];
        $matriz = new MatrixController();
        print_r($matriz->newMatrix($datos));

        $punto = new MatrixController();
        print_r($punto->newCentralPoint($datos,$matriz->mid));
        header('Location: /matrices');
        
    }

    /*********************CONTROL DEL PUNTO */
    $np = in_array('_np', $postKeys);
    if($np){
        $datos = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) ?: [];
        $point = new MatrixController();

        $deviceResult = $point->getDevice($datos);
        $deviceRows = json_decode((string) $deviceResult, true);
        if (!is_array($deviceRows)) {
            $deviceRows = [];
        }

        if (count($deviceRows) > 0) {
            $deviceId = $deviceRows[0]['id'] ?? null;
            $registeredPointsResult = $point->getRegisteredPoints((string) $deviceId);
            $registeredPoints = json_decode((string) $registeredPointsResult, true);
            if (!is_array($registeredPoints)) {
                $registeredPoints = [];
            }

            if (count($registeredPoints) > 0) {
                $pointId = $point->pid ?? ($registeredPoints[0]['id'] ?? null);
                if ($pointId !== null) {
                    $pointRead = new ReadingController();
                    print_r($pointRead->captureReading($datos, $pointId));
                } else {
                    echo "Punto no encontrado";
                }
            } else {
                echo "Dispositivo no registrado";
            }
        } else {
            print_r($point->newDevice($datos));
            echo "Dispositivo insertado";
        }
    }
    
}

$getData = filter_input_array(INPUT_GET, FILTER_UNSAFE_RAW) ?: [];
$getKeys = array_keys($getData);
if(!empty($getData)){
    $logout = in_array('_logout', $getKeys);    
    if($logout){        
        $lg = new LoginController();
        $lg->logout();
        $lang = $_GET['lang'] ?? 'es';
        header('Location: ' . ($lang === 'en' ? '/en/login' : '/login'));
    }

    /************************CARGAR USUARIOS */
    $lu = in_array('_lu', $getKeys);
    if($lu){
        $users = new UserController();
        print_r($users->getUsers());
    }

    /************************CARGAR DISPOSITIVOS */
    $ld = in_array('_ld', $getKeys);
    if($ld){
        $devices = new MatrixController;
        print_r($devices->getDevices());
    }

    /************************CARGAR MATRICES */
    $lm = in_array('_lm', $getKeys);
    if($lm){
        $matrices = new MatrixController;
        print_r($matrices->getMatrices());
    }
    
    /************************CARGAR PUNTOS */
    $lp = in_array('_lp', $getKeys);
    if($lp){
        $points = new MatrixController;
        print_r($points->getPoints());
    }

    /*****************************CARGAR PUNTOS DE USUARIO*/
    $lup = in_array('_lup', $getKeys);
    if($lup){
        $upoints = new MatrixController();
        print_r($upoints->getUserPoints());
    }

    // Profile data endpoint (AJAX) - returns JSON for the current user
    $profileData = in_array('_profile_data', $getKeys);
    if ($profileData) {
        $uid = $_GET['uid'] ?? $GLOBALS['uid'] ?? null;
        if ($uid) {
            $u = new UsersModel();
            $res = $u->where([["id", $uid]])->limit('1')->get();
            $rows = json_decode($res, true);
            if ($rows && count($rows) > 0) {
                echo json_encode(['r' => true, 'user' => $rows[0]]);
            } else {
                echo json_encode(['r' => false]);
            }
        } else {
            echo json_encode(['r' => false]);
        }
        exit;
    }

    // Removed GET-based delete or update to prevent CSRF via simple links. Use POST with CSRF.

    // Historical list endpoint
    $lh = in_array('_hist', $getKeys);
    if($lh){
        $rc = new ReadingController();
        $rc->apiGetHistoryList($_GET);
    }

    // Stats endpoint for historico dashboards
    $hs = in_array('_hist_stats', $getKeys);
    if($hs){
        $rc = new ReadingController();
        $rc->apiGetStats($_GET);
    }

    // JSON readings for dashboards
    $rd = in_array('_readings', $getKeys);
    if($rd){
        $rc = new ReadingController();
        $rc->apiGetReadings($_GET);
    }
}

// Secure POST-only mutations
if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)){
    // Already CSRF-validated earlier

    // Delete point
    $postKeys = array_keys($postData);
    $dp_post = in_array('_dp', $postKeys);
    if ($dp_post) {
        $pid = $postData['pid'] ?? null;
        if ($pid === null) { echo json_encode(['r' => false, 'error' => 'Missing pid']); exit; }
        $point = new MatrixController();
        echo json_encode(['r' => $point->deletePoint($pid)]);
        exit;
    }

    // Update profile
    $updateProfile = in_array('_update_profile', $postKeys);
    if ($updateProfile) {
        $uid = $postData['uid'] ?? $_SESSION['uid'] ?? $GLOBALS['uid'] ?? null;
        if (!$uid) { echo json_encode(['r' => false, 'error' => 'Sin uid']); exit; }
        $name = trim($postData['name'] ?? '');
        $email = trim($postData['email'] ?? '');
        $passwd = trim($postData['passwd'] ?? '');

        $u = new UsersModel();
        $vals = [];
        if ($name !== '') $vals['name'] = $name;
        if ($email !== '') $vals['email'] = $email;
        if ($passwd !== '') $vals['password'] = sha1($passwd);

        if (count($vals) === 0) { echo json_encode(['r' => false, 'error' => 'No changes']); exit; }

        $u->valores = $vals;
        $ok = $u->where([["id", $uid]])->update();
        if ($ok) {
            // Update session email if present
            if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
            if (!empty($email)) { $_SESSION['email'] = $email; }
            session_write_close();
            echo json_encode(['r' => true]);
        } else {
            echo json_encode(['r' => false]);
        }
        exit;
    }

    // Future: update point via POST (_up) if needed
}
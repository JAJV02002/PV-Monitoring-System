<?php

namespace Controllers\auth;

use Models\users;
use Models\remember_tokens;

class LoginController {

    public $sv; //Sesión Válida
    public $name;
    public $uid;
    public $type;

    public function __construct(){
        $this->sv = false;
    }

    public function userAuth($datos){
        $user = new users;
        $result = $user->where([["email", $datos["email"]],
                                ["password",sha1($datos["passwd"])]])->get();
        if(count(json_decode($result)) > 0){
            //Se registra la sesión
            $u = json_decode($result)[0];
            $resp = $this->sessionRegister(['id' => $u->id, 'email' => $u->email]);
            // Remember me
            $remember = isset($datos['remember']) && ($datos['remember'] === '1' || $datos['remember'] === 'true');
            if ($remember) {
                $this->createRememberToken($u->id);
            }
            return $resp;
        }else{
            $this->sessionDestroy();
            echo json_encode(["r"=>false]);
        }
    }

    public function userSignup($datos){
        $user = new users();

        $user->valores = [
            $datos['username'],
            $datos['name'],
            $datos['email'],
            sha1($datos['passwd'])
        ];
        $result = $user->create();
        return $result;
        die;
    }

    public function sessionRegister($datos){
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        session_regenerate_id(true);
        $_SESSION['IP'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['uid'] = $datos['id'] ?? null;
        $_SESSION['email'] = $datos['email'] ?? '';
        // Do NOT store plaintext password
        session_write_close();
        return json_encode(["r"=>true]);
    }

    public function sessionValidate(){
        $user = new users;
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        if(session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION)){
            $datos = $_SESSION;
            // Ensure required fields exist
            if (!isset($datos['email']) || !isset($datos['IP'])) {
                // unauthenticated: keep session (preserve CSRF), clear auth keys
                unset($_SESSION['uid']);
                unset($_SESSION['email']);
                session_write_close();
                $this->sv = false;
                return null;
            }

            // Validate request IP matches session IP
            if ($datos['IP'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
                // IP mismatch: clear auth but preserve session and CSRF
                unset($_SESSION['uid']);
                unset($_SESSION['email']);
                session_write_close();
                $this->sv = false;
                return null;
            }

            // Prefer validating by email
            $result = $user->where([["email", $datos["email"]]])->get();
            $rows = json_decode($result);
            if (is_array($rows) && count($rows) > 0) {
                session_write_close();
                $this->sv = true;
                $this->name = $rows[0]->name ?? '';
                $this->uid = $datos['uid'] ?? ($rows[0]->id ?? null);
                $this->type = $rows[0]->rol ?? '';
                return $result;
            }
            // No user: clear auth keys and keep session
            unset($_SESSION['uid']);
            unset($_SESSION['email']);
            session_write_close();
            $this->sv = false;
            return null;
        }
        // No session data: keep session open and CSRF alive
        session_write_close();
        $this->sv = false;
        return null;
    }

    private function sessionDestroy(){
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        $_SESSION = [];
        session_destroy();
        session_write_close();
        $this->sv = false;
        $this->name = "";
        $this->uid = "";
        return;
    }

    public function logout(){
        // Clear remember token for current user and cookie
        $this->clearRememberCookie();
        $this->clearRememberTokensForCurrent();
        $this->sessionDestroy();
        return;
    }

    private function cookieOptions(){
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        return [
            'expires' => time() + 60*60*24*30, // 30 days
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => $secure
        ];
    }

    private function createRememberToken($userId){
        // selector + validator design
        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $hash = hash('sha256', $validator);
        $expires = date('Y-m-d H:i:s', time() + 60*60*24*30);

    // Store in DB (do not delete other device tokens)
        $rt = new remember_tokens();
        $rt->valores = [ (string)$userId, $selector, $hash, $expires ];
        $rt->create();

        // Set cookie: selector:validator
        $value = $selector . ':' . $validator;
        setcookie('remember_me', $value, $this->cookieOptions());
    }

    private function deleteRememberTokensByUser($userId){
        $rt = new remember_tokens();
        $rt->where([[ 'user_id', (string)$userId ]])->delete();
    }

    private function deleteRememberTokenBySelector($selector){
        $rt = new remember_tokens();
        $rt->where([[ 'selector', $selector ]])->delete();
    }

    private function clearRememberTokensForCurrent(){
        // Only delete current device token if cookie present; otherwise skip
        if (!empty($_COOKIE['remember_me']) && strpos($_COOKIE['remember_me'], ':') !== false) {
            list($selector) = explode(':', $_COOKIE['remember_me'], 2);
            $this->deleteRememberTokenBySelector($selector);
        }
    }

    private function clearRememberCookie(){
        if (isset($_COOKIE['remember_me'])) {
            // Expire immediately
            setcookie('remember_me', '', [ 'expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax' ]);
        }
    }

    // Attempt auto-login via remember_me cookie when session is not valid
    public function attemptRememberLogin(){
        if ($this->sv) { return false; }
        if (empty($_COOKIE['remember_me'])) { return false; }
        $cookie = $_COOKIE['remember_me'];
        if (strpos($cookie, ':') === false) { return false; }
        list($selector, $validator) = explode(':', $cookie, 2);
        $hash = hash('sha256', $validator);

        $rt = new remember_tokens();
        $rows = json_decode(
            $rt->where([[ 'selector', $selector ]])->limit('1')->get(), true
        );
        if (!$rows || count($rows) === 0) { $this->clearRememberCookie(); return false; }
        $row = $rows[0];
        // Verify hash and expiry
        if (!hash_equals($row['validator_hash'] ?? '', $hash)) { $this->clearRememberCookie(); return false; }
        if (!empty($row['expires_at']) && strtotime($row['expires_at']) < time()) { $this->clearRememberCookie(); $this->deleteRememberTokensByUser($row['user_id']); return false; }

        // Load user and register session
        $user = new users();
        $urows = json_decode($user->where([[ 'id', (string)$row['user_id'] ]])->limit('1')->get(), true);
        if (!$urows || count($urows) === 0) { $this->clearRememberCookie(); return false; }
        $u = $urows[0];
    $this->sessionRegister(['id' => $u['id'], 'email' => $u['email']]);
    // Rotate token: delete only the one we used and set a new one
    $this->deleteRememberTokenBySelector($selector);
    $this->createRememberToken($u['id']);
        $this->sv = true;
        $this->name = $u['name'] ?? '';
        $this->uid = $u['id'];
        $this->type = $u['rol'] ?? '';
        return true;
    }
}
<?php
namespace App\Controllers;

use Crystal\Components\Query\Query;
use Crystal\Components\View;
use App\Databases\MainDb;

class AuthController
{
    public function __construct(private MainDb $db){}

    public function loginPage() {
        return View::response('login');
    }

    public function doWebAuth(){

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            // Show login error
            return;
        }

        $user = Query::select("user_auth")
            ->where("identifier", "=", $username)
            ->leftJoin("user","user_id","id", ["uuid"])
            ->first($this->db->getConnection());

        if (empty($user)) {
            echo "User not found";
            // Non existing user identifier
            return;
        }

        $storedHash = $user["password_hash"];
        if(!password_verify($password, $storedHash)) {
            echo "Invalid credentials";
            // Invalid credentials
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_uuid'] = $user['user__uuid'];

        header("Location:/");
        exit();
    }

    public function doLogout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        header("Location:/login");
        exit();
    }

}

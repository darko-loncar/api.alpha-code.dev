<?php
namespace App\Controllers;

use Core\Db;
use Components\Query\Query;
use Components\View;

class AuthController
{
    public function __construct(
        private Db $db, 
        ){}
    
    public function loginPage() {
        return (new View('login', [
            'pageId' => 'index'
        ]))->getHtml();
    }

    public function doWebAuth(){

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            // Show login error
            return;
        }

        $q = Query::select("user_auth")
            ->where("identifier", "=", $username)
            ->leftJoin("user","user_id","id", ["uuid"]);

        $user_auth = $this->db->first(
            $q->getQuery(), 
            $q->getBindings()
            );

        if (empty($user_auth)) {
            echo "User not found";
            // Non existing user identifier
            return;
        }

        $storedHash = $user_auth["password_hash"];
        if(!password_verify($password, $storedHash)) {
            echo "Invalid credentials";
            // Invalid credentials
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_uuid'] = $user_auth['user__uuid'];

        header("Location:/");
        exit();
    }

}

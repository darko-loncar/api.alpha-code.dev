<?php
declare(strict_types=1);
namespace App\Services;

class AuthService
{
    public function requireLogin(): void
    {
        if (!isset($_SESSION['user_uuid'])) {
            header('Location: /login');
            exit;
        }
    }
}

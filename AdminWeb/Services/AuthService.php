<?php
declare(strict_types=1);
namespace AdminWeb\Services;

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

<?php
namespace AdminWeb\Controllers;

use Crystal\Components\Query\Query;
use Crystal\Components\View;
use AdminWeb\Databases\MainDb;

class IndexController
{
    /**
     * @param MainDb $mainDb
     */
    public function __construct(private MainDb $mainDb)
    {
    }

    public function dashboard(): callable
    {
        if (!isset($_SESSION['user_uuid'])) {
            header('Location: /login');
            exit;
        }

        return View::response('dashboard', ['pageId' => 'dashboard']);
    }

    public function myProfile(): callable
    {
        if (!isset($_SESSION['user_uuid'])) {
            header('Location: /login');
            exit;
        }

        $conn = $this->mainDb->getConnection();
        $userData = Query::select("user")
            ->where("user.uuid", "=", $_SESSION["user_uuid"])
            ->leftJoin("user_auth", "id", "id", ["id", "identifier", "last_login_at"])
            ->first($conn);

        return View::response('profile', ['pageId' => 'dashboard', 'userData' => $userData]);
    }

    public function page(int $id, string $slug): void
    {
        dd($id, $slug, $_GET);
    }
}

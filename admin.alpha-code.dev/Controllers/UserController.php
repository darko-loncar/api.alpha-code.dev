<?php
namespace App\Controllers;

use Crystal\Components\Query\Query;
use Crystal\Components\View;
use Crystal\Components\Pager;
use App\Services\AuthService;
use App\Databases\MainDb;

class UserController
{
    /**
     * @param MainDb $mainDb
     */
    public function __construct(private MainDb $mainDb, private AuthService $auth)
    {
    }

    public function users(): callable
    {
        // Require login
        $this->auth->requireLogin();

        // Get pagination state
        $currentPage = $_GET["page"] ?? 1;
        $itemsPerPage = 10;

        // Count users and create pager
        $userCountQ = Query::select("user", ["id"]);
        $countData = $this->mainDb->getAll($userCountQ->getQuery());
        $userCount = count($countData);
        //$userCount = 200;
        $pager = new Pager($_SERVER['REQUEST_URI'], $currentPage, $itemsPerPage, $userCount);
        //$pager = new Pager('/users', $currentPage, 10, 200);

        // Load users page data
        $userQ = Query::select("user")
            ->where("user.is_active", true)
            ->leftJoin("user_auth", "id", "id", ["id", "identifier", "last_login_at"])
            //->orderBy("user.id", "desc")
            ->limit($itemsPerPage, ($currentPage - 1) * $itemsPerPage);

        $userData = $this->mainDb->getAll(
            $userQ->getQuery(),
            $userQ->getBindings()
            );

        // Return view
        return View::response('users', ['pageId' => 'dashboard', 'userData' => $userData, 'pager_object' => $pager]);
    }

    public function user(string $uuid): callable
    {
        $this->auth->requireLogin();

        $userQ = Query::select("user")
            ->where("user.uuid", $uuid)
            ->where("user.is_active", true)
            ->leftJoin("user_auth", "id", "id", ["id", "identifier", "last_login_at"]);

        $userData = $this->mainDb->first(
            $userQ->getQuery(),
            $userQ->getBindings()
            );

        if($userData === null) {
            http_response_code(404);
            exit;
        }

        return View::response('profile', ['pageId' => 'dashboard', 'userData' => $userData]);
    }
}

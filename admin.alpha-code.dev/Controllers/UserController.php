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
     * @param AuthService $auth
     */
    public function __construct(private MainDb $mainDb, private AuthService $auth)
    {
    }

    public function users()
    {
        // Require login
        $this->auth->requireLogin();

        // Get pagination state
        $currentPage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $itemsPerPage = 12;

        // Users query
        $userQ = Query::select("user")
            ->leftJoin("user_auth", "id", "id", ["id", "identifier", "last_login_at"])
            ->orderBy("user.id", "desc")
            ->page($currentPage, $itemsPerPage);

        // Load users page data and get total count
        $userData = $this->mainDb->getAll(
            $userQ->getQuery(),
            $userQ->getBindings()
        );

        $userCount = (int)$this->mainDb->firstValue($userQ->getCountQuery());

        // Create pager
        $pager = new Pager($currentPage, $itemsPerPage, $userCount, 5, $_SERVER['REQUEST_URI']);

        // Insert user query
        $insertUserQ = Query::insert("user")
            ->set("first_name", "John22122")
            ->set("last_name", "Doe")
            ->set("email", "john.doe@email25.com");

        // Insert user
        //$this->mainDb->getAll($insertUserQ->getQuery(), $insertUserQ->getBindings());

        // Return view
        return View::render('users', [
            'pageId' => 'dashboard',
            'userData' => $userData,
            'pager_object' => $pager
        ]);
    }

    public function user(string $uuid)
    {
        $this->auth->requireLogin();

        $userQ = Query::select("user")
            ->where("user.uuid", $uuid)
            ->where("user.is_active", true);

        $userData = $this->mainDb->first(
            $userQ->getQuery(),
            $userQ->getBindings()
        );

        return View::render('profile', ['pageId' => 'dashboard', 'userData' => $userData]);
    }
}

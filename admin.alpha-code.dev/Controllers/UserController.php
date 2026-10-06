<?php
namespace App\Controllers;

use Crystal\Components\Query\Query;
use Crystal\Components\View;
use Crystal\Components\Pager;
use Crystal\Support\UUID;
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
        $itemsPerPage = 5;

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
            ->set("uuid", UUID::generate())
            ->set("first_name", "John")
            ->set("last_name", "Doe")
            ->set("email", "john.doe@email1.com");

        // Insert user
        //$this->mainDb->first($insertUserQ->getQuery(), $insertUserQ->getBindings());

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

        return View::render('profile', ['pageId' => 'dashboard', 'userData' => $userData]);
    }
}

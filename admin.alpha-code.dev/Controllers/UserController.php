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
        $this->auth->requireLogin();

        $userQ = Query::select("user")
            ->where("user.is_active", true)
            ->leftJoin("user_auth", "id", "id", ["id", "identifier", "last_login_at"])
            ->orderBy("user.id", "desc")
            ->orderBy("user_auth.id", "desc")
            ->limit(25, 0);

        $userData = $this->mainDb->getAll(
            $userQ->getQuery(),
            $userQ->getBindings()
            );

            $pager = new Pager(0, 20, 102);


        return View::response('users', ['pageId' => 'dashboard', 'userData' => $userData, 'pager' => $pager]);
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

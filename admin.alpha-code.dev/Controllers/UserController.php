<?php
namespace App\Controllers;

use Crystal\Components\Response;
use Crystal\Components\Query\Query;
use Crystal\Components\View;
use Crystal\Components\Pager;
use Crystal\Components\DataTable;

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

    /**
     * Users main index view
     */
    public function index()
    {
        // Require login
        $this->auth->requireLogin();

        $insertUserQ = Query::insert("user")
            ->set("first_name", "John22122")
            ->set("last_name", "Doe")
            ->set("email", "john.doe@email31.com");
        //$this->mainDb->execute($insertUserQ->getQuery(), $insertUserQ->getBindings());

        // Get pagination state
        $currentPage = isset($_GET["page"]) ? $_GET["page"] : 1;
        $itemsPerPage = 2;

        // Users query
        $userQ = Query::select("user")
            ->leftJoin("user_auth", "id", "id", ["id", "identifier", "last_login_at"])
            ->orderBy("user.id", "desc")
            ->page($currentPage, $itemsPerPage);

        // Load users page data and get total count
        $userData = $this->mainDb->execute($userQ->getQuery(), $userQ->getBindings())->getAll();
        $userCount = (int)$this->mainDb->execute($userQ->getCountQuery())->firstValue();

        $userTable = (new DataTable($userData))
            ->addColumn('id', 'ID')
            ->addColumn('first_name', 'First name')
            ->addColumn('last_name', 'Last name')
            ->addColumn('email', 'Email')
            ->addColumn('phone', 'Phone');

        // Create pager
        $pager = new Pager($currentPage, $itemsPerPage, $userCount, 2, $_SERVER['REQUEST_URI']);

        // Return view
        return View::render('Users.index', ['dataTable' => $userTable, 'pager_object' => $pager]);
    }

    /**
     * Delete confirmation page
     */
    public function delete(string $uuid)
    {
        $this->auth->requireLogin();
        $userQ = Query::select("user")->where("user.uuid", $uuid);
        $userData = $this->mainDb->execute($userQ->getQuery(), $userQ->getBindings())->first();
        return View::render('Users.delete', ['userData' => $userData]);
    }

    /**
     * Delete user action
     */
    public function actionDelete(string $uuid)
    {
        // Require login
        $this->auth->requireLogin();
        $this->mainDb->execute("DELETE FROM user WHERE uuid = ?", [$uuid]);
        Response::redirect("/users");
    }

    public function view(string $uuid)
    {
        $this->auth->requireLogin();

        $userQ = Query::select("user")
            ->where("user.uuid", $uuid)
            ->where("user.is_active", true);

        $userData = $this->mainDb->execute(
            $userQ->getQuery(),
            $userQ->getBindings()
        )->first();

        return View::render('Users.profile', ['pageId' => 'dashboard', 'userData' => $userData]);
    }
}

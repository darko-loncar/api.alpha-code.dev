<?php
namespace App\Controllers;

use Crystal\Components\Query\Query;
use Crystal\Components\View;
use App\Databases\MainDb;
use App\Services\AuthService;

class ArticleController
{
    /**
     * @param MainDb $mainDb
     */
    public function __construct(private MainDb $mainDb, private AuthService $auth)
    {
    }

    public function index(): callable
    {
        $this->auth->requireLogin();

        // Query article
        $userQ = Query::select("article")
            ->leftJoin("user", "id", "id", ["first_name", "last_name"])
            ->orderBy("article.id", "desc")
            ->limit(25, 0);

        // Get data
        $data = $this->mainDb->getAll(
            $userQ->getQuery(),
            $userQ->getBindings()
            );

        // Return View
        return View::response('articles', ['pageId' => 'dashboard', 'articleData' => $data]);

    }

    public function article(int $id): callable
    {
        $this->auth->requireLogin();

        $userQ = Query::select("article")
            ->leftJoin("user", "id", "id", ["first_name", "last_name"])
            ->orderBy("article.id", "desc")
            ->where("article.id", $id)
            ->limit(25, 0);

        $data = $this->mainDb->first(
            $userQ->getQuery(),
            $userQ->getBindings()
            );

        return View::response('article', ['pageId' => 'dashboard', 'article' => $data]);

    }
}

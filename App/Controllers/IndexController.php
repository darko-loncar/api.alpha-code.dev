<?php
namespace App\Controllers;

use Components\Query\Query;
use Core\Db;
use Core\Response;
use Components\View;
use App\Services\ExchangeRateFetcher;

class IndexController
{
    public function __construct(
        private Db $db, 
        ){}
    
    // Typical old school route rendered by View Component 
    public function dashboard()
    {

        if (!isset($_SESSION['user_uuid'])) {
            header('Location: /login');
            exit;
        }

        $q = Query::select("user", [])
            ->limit(10)
            ->orderBy("id")
            ->leftJoin("user_auth", "id", "id", ["id"]);
        
        $results = $this->db->first(
            $q->getQuery(), 
            $q->getBindings()
            );

        return Response::json($results);
    }

}

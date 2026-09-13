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
        private ExchangeRateFetcher $fx,
        private Db $db, 
        ){}

    // Typical old school route rendered by View Component 
    public function homepage()
    {
        $q = Query::select("currency", ["id", Query::max("name"), "code"])
            ->whereIn("currency.id", [1, 52,43])
            ->limit(10)
            //->where("currency.id", "=", "43")
            //->where("currency.id", "=", "52")
            ->orderBy("id")
            ->leftJoin("outfit", "id", "id", ["id", "name"])
            ->leftJoin("fx_rate", "id", "id", ["id"]);

        dd($q->getQuery(), $q);
        
        $results = $this->db->getAll(
            $q->getQuery(), 
            $q->getBindings()
            );


        return Response::json($results);

        //$html = (new View("Homepage", ['pageId' => 'index', 'data' => []]))->getHtml();
        //return Response::html($html);
    }

    public function aboutUs()
    {
        $html = "<h1>About Us</h1><p>Lorem Ipsum dolor sit amet...</p>";
        return $html;
    }

    // Api route that fetches external data through service and returns Response in json format 
    public function fxRates(){
        return Response::json($this->fx->fetch());        
    }

    public function viewOutfit(int $id, string $slug){

        return "ID:" . $id;
    }

}


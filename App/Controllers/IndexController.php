<?php
namespace App\Controllers;

use Components\QueryBuilder;
use Core\Response;
use Core\Container;
use Components\View;
use App\Services\ExchangeRateFetcher;

class IndexController
{
    public function __construct(
        private ExchangeRateFetcher $fx, 
        private QueryBuilder $q, 
        private Container $c
        ){}

    // Typical old school route rendered by View Component 
    public function homepage()
    {
        // $this->c->get("reportingDb") in case we need another database from container with aux name instead of passing through constructor
        // $this->q->select("users") in case we want to use query builder

        $html = (new View("Homepage", ['pageId' => 'index', 'data' => []]))->getHtml();
        return Response::html($html);
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


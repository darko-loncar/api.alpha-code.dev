<?php
namespace App\Controllers;

use Core\Db;
use Core\Response;
use Modules\QueryBuilder;
use Modules\View;

class IndexController
{
    public function __construct(
        private DB $db
    ){}

    public function homepage()
    {
        $html = (new View("Homepage", ['pageId' => 'index', 'data' => []]))->getHtml();
        return Response::html($html);
    }

    public function aboutUs()
    {
        $html = "<h1>About Us</h1><p>Lorem Ipsum</p>";
        return $html;
    }
}


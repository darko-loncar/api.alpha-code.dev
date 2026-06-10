<?php
namespace App\Controllers;

use Core\Response;
use Core\Db;
use App\Entities\OutfitMid;
use Modules\View;

class IndexController
{

    public function homepage()
    {
        $hair = OutfitMid::find(1);
        $html = (new View("Homepage", ['pageId' => 'index', 'data' => $hair]))->getHtml();
        return Response::html($html);
    }
}


<?php
namespace App\Controllers;

use Modules\QueryBuilder;
use App\Entities\Outfit;
use Core\Response;

class ApiController
{
    public function viewOutfitAll()
    {
        $outfits = (new QueryBuilder("outfit"))
            ->leftJoin("outfit_hair","fk_outfit_hair","id", ["id", "name", "value"])
            ->leftJoin("outfit_face","fk_outfit_face","id", ["id", "name", "value"])
            ->leftJoin("outfit_upper","fk_outfit_upper","id", ["id", "name", "value"])
            ->leftJoin("outfit_mid","fk_outfit_mid","id", ["id", "name", "value"])
            ->leftJoin("outfit_between_legs","fk_outfit_between_legs","id", ["id", "name", "value"])
            ->leftJoin("outfit_legs","fk_outfit_legs","id", ["id", "name", "value"])
            ->leftJoin("outfit_footwear","fk_outfit_footwear","id", ["id", "name", "value"])
            ->get();

        return Response::json($outfits);
    }
}
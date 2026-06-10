<?php
namespace App\Controllers;

use Core\Exceptions\NotFoundException;
use Core\Request;
use Modules\QueryBuilder;
use Modules\EntitySelect;
use Modules\View;
use App\Entities\Outfit;
use App\Entities\OutfitHair;
use App\Entities\OutfitFace;
use App\Entities\OutfitUpper;
use App\Entities\OutfitBra;
use App\Entities\OutfitMid;
use App\Entities\OutfitBetweenLegs;
use App\Entities\OutfitLegs;
use App\Entities\OutfitFootwear;
use Core\Response;

class OutfitController
{
    public function viewAll()
    {
        $outfits = (new QueryBuilder(Outfit::getTableName()))->get();
        $html = (new View("Outfits", [
            'pageTitle' => "Pregled Outfita",
            'pageId' => "outfits",
            'outfits' => $outfits
        ]))->getHtml();

        return Response::html($html);
    }

    public function view(int $id)
    {
        $outfit = Outfit::find($id) ?? throw new NotFoundException(404, "Outfit not found");

        $hairSelect = new EntitySelect("hair", OutfitHair::class, $this->processSelText(), $outfit->fk_outfit_hair);
        $faceSelect = new EntitySelect("face", OutfitFace::class, $this->processSelText(), $outfit->fk_outfit_face);
        $upperSelect = new EntitySelect("upper", OutfitUpper::class, $this->processSelText(), $outfit->fk_outfit_upper);
        $braSelect = new EntitySelect("bra", OutfitBra::class, $this->processSelText(), $outfit->fk_outfit_bra);
        $midSelect = new EntitySelect("mid", OutfitMid::class, $this->processSelText(), $outfit->fk_outfit_mid);
        $betweenLegsSelect = new EntitySelect("between-legs", OutfitBetweenLegs::class, $this->processSelText(), $outfit->fk_outfit_between_legs);
        $legsSelect = new EntitySelect("legs", OutfitLegs::class, $this->processSelText(), $outfit->fk_outfit_legs);
        $footwearSelect = new EntitySelect("footwear", OutfitFootwear::class, $this->processSelText(), $outfit->fk_outfit_footwear);

        $html = (new View(
            'OutfitEditor', 
            [
                'pageTitle' => "Uređivanje outfita",
                'pageId' => "outfit-editor", 
                'formAction' => "/outfit/".$outfit->getId(),
                'outfit' => $outfit, 
                'hairSelect' => $hairSelect->getHtml(),
                'faceSelect' => $faceSelect->getHtml(),
                'upperSelect' => $upperSelect->getHtml(),
                'braSelect' => $braSelect->getHtml(),
                'midSelect' => $midSelect->getHtml(),
                'betweenLegsSelect' => $betweenLegsSelect->getHtml(),
                'legsSelect' => $legsSelect->getHtml(),
                'footwearSelect' => $footwearSelect->getHtml(),
            ]
        ))->getHtml();

        return Response::html($html);
    }

    public function create()
    {
        $outfit = Outfit::create(); // new Entity class of Outfit subclass type

        $hairSelect = new EntitySelect("hair", OutfitHair::class, $this->processSelText(), $outfit->fk_outfit_hair);
        $faceSelect = new EntitySelect("face", OutfitFace::class, $this->processSelText(), $outfit->fk_outfit_face);
        $upperSelect = new EntitySelect("upper", OutfitUpper::class, $this->processSelText(), $outfit->fk_outfit_upper);
        $braSelect = new EntitySelect("bra", OutfitBra::class, $this->processSelText(), $outfit->fk_outfit_bra);
        $midSelect = new EntitySelect("mid", OutfitMid::class, $this->processSelText(), $outfit->fk_outfit_mid);
        $betweenLegsSelect = new EntitySelect("between-legs", OutfitBetweenLegs::class, $this->processSelText(), $outfit->fk_outfit_between_legs);
        $legsSelect = new EntitySelect("legs", OutfitLegs::class, $this->processSelText(), $outfit->fk_outfit_legs);
        $footwearSelect = new EntitySelect("footwear", OutfitFootwear::class, $this->processSelText(), $outfit->fk_outfit_footwear);

        $html = (new View(
            'OutfitEditor', 
            [
                'pageTitle' => "Novi Outfit",
                'pageId' => "outfit-editor", 
                'formAction' => "/outfit",
                'outfit' => $outfit, 
                'hairSelect' => $hairSelect->getHtml(),
                'faceSelect' => $faceSelect->getHtml(),
                'upperSelect' => $upperSelect->getHtml(),
                'braSelect' => $braSelect->getHtml(),
                'midSelect' => $midSelect->getHtml(),
                'betweenLegsSelect' => $betweenLegsSelect->getHtml(),
                'legsSelect' => $legsSelect->getHtml(),
                'footwearSelect' => $footwearSelect->getHtml(),
            ]
        ))->getHtml();

        return Response::html($html);
    }

    public function update(int $id)
    {
        $request = Request::getPostData();
        $outfit = Outfit::find($id) ?? throw new NotFoundException(404, "Outfit not found");

        $heel_height = ($request->heel_height ?? 0);
        $skirt_length = ($request->skirt_length ?? 0);
        $skirt_slit = !empty($request->skirt_slit);

        // Map request data from form to entity
        $outfit->name = $request->name;
        $outfit->fk_outfit_hair = $request->hair;
        $outfit->fk_outfit_face = $request->face;
        $outfit->fk_outfit_upper = $request->upper;
        $outfit->fk_outfit_bra = $request->bra;
        $outfit->fk_outfit_mid = $request->mid;
        $outfit->fk_outfit_legs = $request->legs;
        $outfit->fk_outfit_between_legs = $request->between_legs;
        $outfit->fk_outfit_footwear = $request->footwear;
        $outfit->heel_height = $heel_height;
        $outfit->skirt_length = $skirt_length;
        $outfit->skirt_slit = $skirt_slit;
        $outfit->glasses = $request->glasses;
        
        // Update
        $outfit->update();

        // Redirect to edit form
        $this->redirect("/outfit/$id");
    }

    public function store()
    {
        $request = Request::getPostData();

        // Get entity
        $outfit = new Outfit;

        $heel_height = ($request->heel_height ?? 0);
        $skirt_length = ($request->skirt_length ?? 0);
        $skirt_slit = !empty($request->skirt_slit);

        // Map request data from form to entity
        $outfit->name = $request->name;
        $outfit->fk_outfit_hair = $request->hair;
        $outfit->fk_outfit_face = $request->face;
        $outfit->fk_outfit_upper = $request->upper;
        $outfit->fk_outfit_bra = $request->bra;
        $outfit->fk_outfit_mid = $request->mid;
        $outfit->fk_outfit_legs = $request->legs;
        $outfit->fk_outfit_between_legs = $request->between_legs;
        $outfit->fk_outfit_footwear = $request->footwear;
        $outfit->heel_height = $heel_height;
        $outfit->skirt_length = $skirt_length;
        $outfit->skirt_slit = $skirt_slit;
        $outfit->glasses = $request->glasses;

        // Insert
        $outfit->insert();

        // Redirect to edit form
        $this->redirect("/outfit/".$outfit->getId());
    }

    private function processSelText() 
    {
        return function($e){
            
            $prefix = "";
            if($e["value"] > 0) $prefix = "+";
            return "{$e['name']}" . " $prefix{$e['value']}"; 
        };
    }
}
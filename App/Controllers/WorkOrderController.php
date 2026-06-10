<?php
namespace App\Controllers;

use Core\Response;
use Modules\EntitySelect;
use Modules\View;
use App\Entities\WorkItem;

class WorkOrderController
{
    public function workOrders()
    {
        $select = new EntitySelect("work_item", WorkItem::class, function($e){ return $e["name"]; }, 0);
        $select->setAllowNull();
        $select->setId("workItemSelect");

        $html = (new View('WorkOrders', [
            'pageId' => 'work-orders',
            'workItemsSelect' => $select->getHtml(),
        ]))->getHtml();

        return Response::html($html);
    }
}


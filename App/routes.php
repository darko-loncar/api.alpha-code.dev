<?php
use Core\Router;
use App\Controllers\ApiController;
use App\Controllers\IndexController;
use App\Controllers\OutfitController;
use App\Controllers\WorkOrderController;

// API Routes
Router::get("api/outfits", [ApiController::class, "viewOutfitAll"]);

// Page Routes
Router::get("", [IndexController::class, 'homepage']);

Router::get("outfits", [OutfitController::class, "viewAll"]);
Router::get("outfit/create", [OutfitController::class, "create"]);
Router::post("outfit", [OutfitController::class, "store"]);
Router::get("outfit/{id}", [OutfitController::class, 'view']);
Router::post("outfit/{id}", [OutfitController::class, "update"]);
Router::get("work-orders", [WorkOrderController::class, "workOrders"]);
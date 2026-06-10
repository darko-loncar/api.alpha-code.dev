<?php
require_once __DIR__ . "/../Core/Kernel.php";
$response = (new Core\Kernel)->run();

if ($response instanceof \Core\Response) {
    $response->render();
} else {
    echo $response;
}
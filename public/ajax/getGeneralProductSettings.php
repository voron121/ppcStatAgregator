<?php

use PPCSoft\Logger\Log;
use Controllers\cabinet\ProductsController;
use PPCSoft\Notification;

require_once __DIR__ . "/../init.php";

try {
    $response = ["status" => "error", "message" => ""];
    $controller = new ProductsController();
    $ajaxNotification = new Notification();
    $response["product"] = $controller->getGeneralProductSettings();
    $response["status"] = "success";
    echo json_encode($response);
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file"]);
}
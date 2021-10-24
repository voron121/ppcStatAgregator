<?php

use PPCSoft\Logger\Log;
use Controllers\cabinet\ProductsController;
use PPCSoft\Notification;

require_once __DIR__ . "/../init.php";

try {
    $response = ["status" => "error", "message" => ""];
    $controller = new ProductsController();
    $ajaxNotification = new Notification();
    $products = $controller->getUserSponsoredProductsNamesList();

    if (empty($products)) {
        $ajaxNotification->putMessage("Данные о товаре получить не удалось!", "alert");
    } else {
        $response["status"] = "success";
        $response["products"] = $products;
        echo json_encode($response);
    }
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file"]);
}
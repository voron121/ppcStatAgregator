<?php

use PPCSoft\Logger\Log;
use Controllers\cabinet\ProductsController;
use PPCSoft\Notification;

require_once __DIR__ . "/../init.php";

try {
    $response = ["status" => "error", "message" => ""];
    $controller = new ProductsController();
    $ajaxNotification = new Notification();
    if (!isset($_POST["productId"])) {
        $ajaxNotification->putMessage("Данные о товаре получить не удалось!", "alert");
    } else {
        $product = $controller->getSponsoredProductById((int)$_POST["productId"]);
        $settings = $product->getSettings();
        $response["product"] = [
            "id" => $product->getId(),
            "name" => $product->getProductSynonym(),
        ];
        if (!isset($settings["hideProduct"])) {
            $response["product"]["hideProduct"] = "no";
        } else {
            $response["product"]["hideProduct"] = $settings["hideProduct"];
        }
        if (isset($settings["minAcos"])) {
            $response["product"]["minAcos"] = $settings["minAcos"];
        }
        if (isset($settings["minSales"])) {
            $response["product"]["minSales"] = $settings["minSales"];
        }
        if (isset($settings["minSpend"])) {
            $response["product"]["minSpend"] = $settings["minSpend"];
        }
        if (isset($settings["minConversion"])) {
            $response["product"]["minConversion"] = $settings["minConversion"];
        }
        $response["status"] = "success";
        echo json_encode($response);
    }
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file"]);
}
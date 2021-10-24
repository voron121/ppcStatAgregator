<?php

use PPCSoft\Logger\Log;
use Models\ProductsGroup;
use PPCSoft\Notification;

require_once __DIR__ . "/../init.php";

try {
    $response = ["status" => "error", "message" => ""];
    $ajaxNotification = new Notification();
    $id = isset($_POST["groupId"]) ? (int)$_POST["groupId"] : null;

    if (!isset($id)) {
        $ajaxNotification->putMessage("Данные о группе получить не удалось!", "alert");
    } else {
        $productGroup = (new ProductsGroup())->find($id);
        $response["group"] = $productGroup;
        $settings = $productGroup->getSettings();
        $response["group"] = [
            "id" => $productGroup->getId(),
            "name" => $productGroup->getName(),
            "asins" => $productGroup->getAsins(),
            "minAcos" => $settings["minAcos"] ?? 0,
            "minSpend" => $settings["minSpend"] ??  0,
            "minSales" => $settings["minSales"] ??  0,
            "minConversion" => $settings["minConversion"] ?? 0
        ];
        $response["status"] = "success";
        echo json_encode($response);
    }
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file"]);
}
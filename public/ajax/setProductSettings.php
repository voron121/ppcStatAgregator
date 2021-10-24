<?php

use PPCSoft\Logger\Log;
use Controllers\cabinet\ProductsController;
use PPCSoft\Notification;

require_once __DIR__ . "/../init.php";

try {
    $productId = (int)$_POST["productId"];
    $ajaxNotification = new Notification();
    if (is_null($_POST["productId"]) || empty($_POST["productId"])) {
        $ajaxNotification->putMessage("Не удалось сохранить настройки", "warning");
        throw new Exception("ProductId is null");
    }

    $controller = new ProductsController();
    $product = $controller->getSponsoredProductById($productId);
    $settings = $product->getSettings();

    if (isset($_POST["productSynonym"])) {
        $productSynonym = trim($_POST["productSynonym"]);
    } elseif (!is_null($product->getProductSynonym())) {
        $productSynonym = $product->getProductSynonym();
    }
    if (isset($_POST["minAcos"]) && !empty(trim($_POST["minAcos"]))) {
        $settings["minAcos"] = trim($_POST["minAcos"]);
    }
    if (isset($_POST["minSpend"]) && !empty(trim($_POST["minSpend"]))) {
        $settings["minSpend"] = trim($_POST["minSpend"]);
    }
    if (isset($_POST["minSales"]) && !empty(trim($_POST["minSales"]))) {
        $settings["minSales"] = trim($_POST["minSales"]);
    }
    if (isset($_POST["minConversion"]) && !empty(trim($_POST["minConversion"]))) {
        $settings["minConversion"] = trim($_POST["minConversion"]);
    }
    $settings["hideProduct"] = $_POST["hideProduct"];
    $controller->saveSponsoredProduct([
        "productId" => $productId,
        "productSynonym" => $productSynonym,
        "settings" => $settings
    ]);
    $ajaxNotification->putMessage("Настройки для товара успешно сохранены!", "success");
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "exception" => $e]);
}
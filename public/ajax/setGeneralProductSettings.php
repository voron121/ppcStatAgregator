<?php

use PPCSoft\Logger\Log;
use Controllers\cabinet\ProductsController;
use PPCSoft\Notification;

require_once __DIR__ . "/../init.php";

try {
    $settings = [];
    $productId = isset($_POST["productId"]) ?  (int)$_POST["productId"] : null;
    $minAcos = isset($_POST["minAcos"]) && !empty(trim($_POST["minAcos"])) ? trim($_POST["minAcos"]) : null;
    $minSpend = isset($_POST["minSpend"]) && !empty(trim($_POST["minSpend"])) ? trim($_POST["minSpend"]) : null;
    $minSales = isset($_POST["minSales"]) && !empty(trim($_POST["minSales"])) ? trim($_POST["minSales"]) : null;
    $minConversion = isset($_POST["minConversion"]) && !empty(trim($_POST["minConversion"])) ? trim($_POST["minConversion"]) : null;

    $controller = new ProductsController();
    $ajaxNotification = new Notification();

    if (is_null($productId) || 0 != $productId) {
        $ajaxNotification->putMessage("Не удалось сохранить настройки", "warning");
        throw new Exception("ProductId is null");
    }

    if (!is_null($minAcos)) {
        $settings["minAcos"] = $minAcos;
    }
    if (!is_null($minSpend)) {
        $settings["minSpend"] = $minSpend;
    }
    if (!is_null($minSales)) {
        $settings["minSales"] = $minSales;
    }
    if (!is_null($minConversion)) {
        $settings["minConversion"] = $minConversion;
    }
    $controller->saveSponsoredProductGeneralSettings(["generalSettings" => $settings]);
    $ajaxNotification->putMessage("Настройки для товара успешно сохранены!", "success");
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "exception" => $e]);
}
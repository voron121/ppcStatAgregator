<?php

use PPCSoft\Logger\Log;
use PPCSoft\Notification;
use Models\cabinet\Product;
use Models\ProductsGroup;
use Controllers\cabinet\ProductsController;

require_once __DIR__ . "/../init.php";

try {
    $asins = [];
    $response = ["status" => "error", "message" => ""];
    $ajaxNotification = new Notification();
    $products = isset($_POST["products"]) ? $_POST["products"] : [];

    if (empty($products)) {
        $ajaxNotification->putMessage("Не переданы товары для обработки!", "alert");
        exit;
    }elseif (count($products) < 2) {
        $ajaxNotification->putMessage("В группе не может быть только один товар!", "alert");
        exit;
    }

    // Проверим принадлежность товара к существующей группе и соберем асины
    foreach($products as $productId) {
        $product = (new Product())->getSponsoredProductById($productId);
        if ($product->getGroupId() != 0) {
            $ajaxNotification->putMessage("Товар с асином " . $product->getAsin() . " уже добавлен в другую группу", "alert");
            exit;
        }
        $asins[] = $product->getAsin();
    }

    $id = isset($_POST["id"]) ? (int)$_POST["id"] : null;
    $asins = implode(",", $asins);
    $settings = (new ProductsController())->getGeneralProductSettings();

    // Создадим новую группу или изменим существующую
    $productGroup = new ProductsGroup();
    $productGroup->setAsins($asins);
    $productGroup->setSettings(json_encode($settings, JSON_NUMERIC_CHECK));
    $groupId = $productGroup->save();

    // Добавим группу в товары
    foreach($products as $productId) {
        $product = (new Product())->getSponsoredProductById($productId);
        $product->setGroupId($groupId);
        $product->save();
    }
    $ajaxNotification->putMessage("Группа успешно создана", "info");
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "exception" => $e]);
}
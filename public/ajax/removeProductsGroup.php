<?php

use PPCSoft\Logger\Log;
use PPCSoft\Notification;
use Models\cabinet\Product;
use Models\ProductsGroup;
use Models\cabinet\Products;

require_once __DIR__ . "/../init.php";

try {
    $asins = [];
    $response = ["status" => "error", "message" => ""];
    $ajaxNotification = new Notification();
    $id = isset($_POST["groupId"]) ? (int)$_POST["groupId"] : null;

    if (!isset($id)) {
        $ajaxNotification->putMessage("Не переданы товары для обработки!", "alert");
        exit;
    }

    $groupedProducts = (new Products())->getGroupedSponsoredProductsByGroupId($id);
    foreach ($groupedProducts as $groupedProduct) {
        $product = (new Product())->getSponsoredProductById($groupedProduct->id);
        $product->setGroupId(0);
        $product->save();
    }
    $productGroup = (new ProductsGroup())->removeGroup($id);
    $ajaxNotification->putMessage("Товары разгруппированы. Группа удалена", "success");
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "exception" => $e]);
}
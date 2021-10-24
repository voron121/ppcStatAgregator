<?php

use PPCSoft\Logger\Log;
use PPCSoft\Notification;
use Models\cabinet\Product;
use Models\ProductsGroup;

require_once __DIR__ . "/../init.php";

try {
    $asins = [];
    $response = ["status" => "error", "message" => ""];
    $ajaxNotification = new Notification();
    if (is_null($_POST["groupId"]) || empty($_POST["groupId"])) {
        $ajaxNotification->putMessage("Не удалось сохранить настройки", "warning");
        throw new Exception("Group id is null");
    }

    $settings = [];
    $groupId = (int)$_POST["groupId"];
    $productId = !empty($_POST["productId"]) ? (int)$_POST["productId"] : null;
    $groupName = isset($_POST["groupName"]) ? trim($_POST["groupName"]) : "";

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
    $productGroup = (new ProductsGroup())->find($groupId);
    $asins = explode(",", $productGroup->getAsins());

    if (!is_null($productId)) {
        $product = (new Product())->getSponsoredProductById($productId);
        $productAsin = $product->getAsin();

        // TODO: в переспективе просто выводить список доступных для группы товаров (товаров без группы)
        if (0 != $product->getGroupId() && $product->getGroupId() != $groupId) {
            $ajaxNotification->putMessage("Товар присвоен другой группе!", "warning");
            exit;
        }

        // Добавим товар в группу иначе удалим товар из группы
        if (0 == $product->getGroupId()) {
            $asins[] = $productAsin;
            $product->setGroupId($groupId);
            $product->save();
        } else {
            unset($asins[array_search($productAsin, $asins)]);
            if (count($asins) < 2) {
                $ajaxNotification->putMessage("В группе не может быть менее двух товаров!", "warning");
                exit;
            }
            $product->setGroupId(0);
            $product->save();
        }
    }

    // обновим параметры группы
    $productGroup->setName($groupName);
    $productGroup->setAsins(implode(",", $asins));
    $productGroup->setSettings(json_encode($settings, JSON_NUMERIC_CHECK));
    $groupId = $productGroup->save();
    $ajaxNotification->putMessage("Настройки для группы успешно сохранены!", "success");
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "exception" => $e]);
}
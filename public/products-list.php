<?php

use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;
use Controllers\cabinet\ProductsController;

require_once __DIR__ . "/init.php";

try {
    $data = [];
    $controller = new ProductsController();
    $items = $controller->getSponsoredItems();
    $data["items"] = $items;
    $data["dates"] = $controller->getReportDateInterval();
    $data["stat"] = !empty($items) ? $controller->getStat($items) : [];
    $data["itemsCount"] = $controller->getSponsoredProductsCount();

    Registry::set("templateData", $data);
    Template::loadTemplate("cabinet/products-list");
} catch(Throwable $e) {
    Log::write(
        "alert",
        $e->getMessage(),
        ["level" => "file", "exception" => $e]
    );
    Template::loadTemplate("cabinet/products-list");
}
<?php

use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;
use Controllers\cabinet\AdsController;

require_once __DIR__ . "/init.php";

try {
    $data = [];
    $controller = new AdsController();
    $ads = $controller->getSponsoredProductsAdsByUser();
    $data["ads"] = $ads;
    $data["dates"] = $controller->getReportDateInterval();
    $data["stat"] = !empty($ads) ? $controller->getSponsoredProductsAdsStatByIds(array_column($ads, "adId", "adId")) : [];
    $data["itemsCount"] = $controller->getSponsoredProductsAdsCount();

    Registry::set("templateData", $data);
    Template::loadTemplate("cabinet/ads-list");
} catch(Throwable $e) {
    Log::write(
        "alert",
        $e->getMessage(),
        ["level" => "file", "exception" => $e]
    );
    Template::loadTemplate("cabinet/ads-list");
}
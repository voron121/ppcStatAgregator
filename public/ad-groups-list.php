<?php

use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;
use Controllers\cabinet\AdGroupsController;

require_once __DIR__ . "/init.php";

try {
    $data = [];
    $controller = new AdGroupsController();
    $adGroups = $controller->getSponsoredProductsAdGroups();
    $data["adGroups"] = $adGroups;
    $data["dates"] = $controller->getReportDateInterval();
    $data["stat"] = !empty($adGroups) ? $controller->getSponsoredProductsAdGroupsStatByIds(array_column($adGroups, "adId", "adId")) : [];
    $data["itemsCount"] = $controller->getSponsoredProductsAdGroupsCount();

    Registry::set("templateData", $data);
    Template::loadTemplate("cabinet/ad-groups-list", $data);
} catch(Throwable $e) {
    Log::write(
        "alert",
        $e->getMessage(),
        ["level" => "file", "exception" => $e]
    );
    Template::loadTemplate("cabinet/ad-groups-list", $data);
}

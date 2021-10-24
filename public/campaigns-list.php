<?php

use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;
use Controllers\cabinet\CampaignsController;

require_once __DIR__ . "/init.php";

try {
    $controller = new CampaignsController();
    $campaigns = $controller->getSponsoredProductsCampaigns();
    $data["campaigns"] = $campaigns;
    $data["dates"] = $controller->getReportDateInterval();
    $data["stat"] = !empty($campaigns) ? $controller->getSponsoredProductsCampaignsStatByIds(array_column($campaigns, "campaignId", "campaignId")) : [];
    $data["itemsCount"] = $controller->getSponsoredProductsCampaignsCount();
} catch(Throwable $e) {
    Log::write(
        "alert",
        $e->getMessage(),
        ["level" => "file", "exception" => $e]
    );
}
Registry::set("templateData", $data);
Template::loadTemplate("cabinet/campaigns-list");
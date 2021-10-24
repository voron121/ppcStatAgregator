<?php
/**
 * Робот синхронизации кампаний на уровне аккаунта клиента
 */
use PPCSoft\Logger\Log;
use PPCSoft\Tools\CLITools;

require_once __DIR__ . "/../vendor/autoload.php";
//--------------------------------------------------------------------------------------------------------------------//
try {
    $commandLineArgs = CLITools::getCommandLineArgs($argv, $argc);
    if (!isset($commandLineArgs["account"])) {
        CLITools::printMessage("Missing required param: account", "error");
        throw new Exception("missing required param: account");
    }

    $account = $commandLineArgs["account"];
    $userId = CLITools::getUserIdByAccount($account);
    // Если робот с прошлого запуска еще не завершил работу - бросим эксепшн
    if (CLITools::isRobotLock($argv[0], $account)) {
        CLITools::printMessage("Robot ".CLITools::isRobotLock($argv[0])." is lock", "warning");
        throw new Exception("robot ".CLITools::isRobotLock($argv[0])." is lock!");
    }
    //----------------------------------------------------------------------------------------------------------------//

    Log::write("info", "robot start work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Start synch campaigns amazon", "success");

    $cacheDB = new PDO('mysql:host=' . CACHE_DB_HOST . ';dbname=' . CACHE_DB_NAME . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
    $cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cacheDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Получим sponsored products campaigns из БД с кешем для аккаунта
    $query = "SELECT Campaign_ID, 
                     Portfolio_ID, 
                     Campaign, 
                     Campaign_Daily_Budget, 
                     Campaign_Start_Date, 
                     Campaign_End_Date, 
                     Campaign_Targeting_Type, 
                     Campaign_Status, 
                     Bidding_strategy
                FROM sponsored_products_campaigns
                WHERE Record_Type = 'Campaign'
                    AND account_id = :account_id";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account_id" => $account]);
    $sponsoredProductsCampaigns = $stmt->fetchAll();

    Log::write("info",
        "get ".count($sponsoredProductsCampaigns)." sponsored products campaigns ",
        ["level" => "robot", "robot" => $argv[0], "account" => $account]
    );
    CLITools::printMessage("Get ".count($sponsoredProductsCampaigns). " Sponsored Products Campaigns", "success");

    // Запишем данные для sponsored products campaigns в БД пользователя
    $db = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->query("USE user_" . $userId);

    $updateQuery = "INSERT INTO sponsored_products_campaigns
                            SET campaignId = :campaignId,
                                portfolioId = :portfolioId,     
                                accountId = :accountId,
                                campaign = :campaign,
                                campaignDailyBudget = :campaignDailyBudget,
                                campaignStartDate = :campaignStartDate,
                                campaignEndDate = :campaignEndDate,
                                campaignTargetingType = :campaignTargetingType,
                                campaignStatus = :campaignStatus,
                                biddingStrategy = :biddingStrategy
                            ON DUPLICATE KEY UPDATE 
                                portfolioId = :portfolioId,
                                campaign = :campaign,
                                campaignDailyBudget = :campaignDailyBudget,
                                campaignStartDate = :campaignStartDate,
                                campaignEndDate = :campaignEndDate,
                                campaignTargetingType = :campaignTargetingType,
                                campaignStatus = :campaignStatus,
                                biddingStrategy = :biddingStrategy";
    $updateStmt = $db->prepare($updateQuery);

    for ($i = 0; $i < count($sponsoredProductsCampaigns); $i++) {
        $updateStmt->execute([
            "campaignId" => $sponsoredProductsCampaigns[$i]["Campaign_ID"],
            "portfolioId" => $sponsoredProductsCampaigns[$i]["Portfolio_ID"],
            "accountId" => $account,
            "campaign" => $sponsoredProductsCampaigns[$i]["Campaign"],
            "campaignDailyBudget" => CLITools::convertFloatToInt($sponsoredProductsCampaigns[$i]["Campaign_Daily_Budget"]),
            "campaignStartDate" => $sponsoredProductsCampaigns[$i]["Campaign_Start_Date"],
            "campaignEndDate" => $sponsoredProductsCampaigns[$i]["Campaign_End_Date"],
            "campaignTargetingType" => $sponsoredProductsCampaigns[$i]["Campaign_Targeting_Type"],
            "campaignStatus" => $sponsoredProductsCampaigns[$i]["Campaign_Status"],
            "biddingStrategy" => $sponsoredProductsCampaigns[$i]["Bidding_strategy"]
        ]);
    }
    Log::write("info",
        "write ".count($sponsoredProductsCampaigns)." sponsored products campaigns ",
        ["level" => "robot", "robot" => $argv[0], "account" => $account]
    );
    CLITools::printMessage("Write ".count($sponsoredProductsCampaigns). " Sponsored Products Campaigns", "success");
    $sponsoredProductsCampaigns = null;

    // Получим sponsored brands campaigns из БД с кешем для аккаунта
    $query = "SELECT Campaign_ID, 
                     Portfolio_ID, 
                     Campaign, 
                     Campaign_Type, 
                     Ad_Format,
                     Budget,
                     Budget_Type,
                     Campaign_Start_Date,
                     Campaign_End_Date,
                     Landing_Page_Url,
                     Landing_Page_ASINs,
                     Brand_Name,
                     Brand_Entity_ID,
                     Brand_Logo_Asset_ID,
                     Headline,
                     Creative_ASINs,
                     Media_ID,
                     Automated_Bidding,
                     Bid_Multiplier,
                     Campaign_Status
                FROM sponsored_brands_campaigns
                WHERE Record_Type = 'Campaign'
                    AND account_id = :account_id";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account_id" => $account]);
    $sponsoredBrandsCampaigns = $stmt->fetchAll();
    // КОСТЫЛЬ: отфильтруем кампании у которых отсутствует тип кампании
    $sponsoredBrandsCampaigns = array_filter($sponsoredBrandsCampaigns, function($item) {
        return in_array($item["Campaign_Type"], ['Sponsored Brands','Sponsored Brands Draft']);
    });

    if (empty($sponsoredBrandsCampaigns)) {
        Log::write("info",
            "sponsored brands campaigns not found or in exist campaigns missing campaign type.",
            ["level" => "robot", "robot" => $argv[0], "account" => $account]
        );
        Log::write("info",
            "robot finish work",
            ["level" => "robot", "robot" => $argv[0], "account" => $account]
        );
        CLITools::printMessage("sponsored brands campaigns not found or in exist campaigns missing campaign type. Robot finish work", "warning");
        exit;
    }
    Log::write("info", "get ".count($sponsoredBrandsCampaigns)." sponsored brands campaigns ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Get ".count($sponsoredBrandsCampaigns). " Sponsored brands Campaigns", "success");

    $sponsoredBrandsCampaigns = array_values($sponsoredBrandsCampaigns);
    // Проставим дефолтные значения для полей, которые могут быть пустыми (https://advertising.amazon.com/API/docs/en-us/bulksheets/sb/sb-entities/sb-entity-campaign)
    array_walk($sponsoredBrandsCampaigns, function (&$item) {
        $item["Automated_Bidding"] = "" != $item["Automated_Bidding"] ? $item["Automated_Bidding"] : "disabled";
        $item["Campaign_Status"] = "" != $item["Campaign_Status"] ? $item["Campaign_Status"] : "Enabled";
    });

    // Запишем данные для sponsored brands campaigns в БД пользователя
    $updateQuery = "INSERT INTO sponsored_brands_campaigns
                            SET campaignId = :campaignId,
                                portfolioId = :portfolioId,
                                accountId = :accountId,
                                campaign = :campaign,
                                campaignType = :campaignType,
                                adFormat = :adFormat,
                                budget = :budget,
                                budgetType = :budgetType,
                                сampaignStartDate = :startDate,
                                сampaignEndDate = :endDate,
                                landingPageUrl = :landingPageUrl,
                                landingPageAsins = :landingPageAsins,
                                brandName = :brandName,
                                brandEntityId = :brandEntityId,
                                brandLogoAssetId = :brandLogoAssetId,
                                headline = :headline,
                                creativeAsins = :creativeAsins,
                                mediaId = :mediaId,
                                automatedBidding = :automatedBidding,
                                bidMultiplier = :bidMultiplier,
                                campaignStatus = :campaignStatus
                            ON DUPLICATE KEY UPDATE 
                                portfolioId = :portfolioId,
                                campaign = :campaign,
                                campaignType = :campaignType,
                                adFormat = :adFormat,
                                budget = :budget,
                                budgetType = :budgetType,
                                сampaignStartDate = :startDate,
                                сampaignEndDate = :endDate,
                                landingPageUrl = :landingPageUrl,
                                landingPageAsins = :landingPageAsins,
                                brandName = :brandName,
                                brandEntityId = :brandEntityId,
                                brandLogoAssetId = :brandLogoAssetId,
                                headline = :headline,
                                creativeAsins = :creativeAsins,
                                mediaId = :mediaId,
                                automatedBidding = :automatedBidding,
                                bidMultiplier = :bidMultiplier,
                                campaignStatus = :campaignStatus";
    $updateStmt = $db->prepare($updateQuery);
    for ($i = 0; $i < count($sponsoredBrandsCampaigns); $i++) {
        $updateStmt->execute([
            "campaignId" => $sponsoredBrandsCampaigns[$i]["Campaign_ID"],
            "portfolioId" => $sponsoredBrandsCampaigns[$i]["Portfolio_ID"],
            "accountId" => $account,
            "campaign" => $sponsoredBrandsCampaigns[$i]["Campaign"],
            "campaignType" => $sponsoredBrandsCampaigns[$i]["Campaign_Type"],
            "adFormat" => $sponsoredBrandsCampaigns[$i]["Ad_Format"],
            "budget" => CLITools::convertFloatToInt($sponsoredBrandsCampaigns[$i]["Budget"]),
            "budgetType" => $sponsoredBrandsCampaigns[$i]["Budget_Type"],
            "startDate" => $sponsoredBrandsCampaigns[$i]["Campaign_Start_Date"],
            "endDate" => $sponsoredBrandsCampaigns[$i]["Campaign_End_Date"],
            "landingPageUrl" => $sponsoredBrandsCampaigns[$i]["Landing_Page_Url"],
            "landingPageAsins" => $sponsoredBrandsCampaigns[$i]["Landing_Page_ASINs"],
            "brandName" => $sponsoredBrandsCampaigns[$i]["Brand_Name"],
            "brandEntityId" => $sponsoredBrandsCampaigns[$i]["Brand_Entity_ID"],
            "brandLogoAssetId" => $sponsoredBrandsCampaigns[$i]["Brand_Logo_Asset_ID"],
            "headline" => $sponsoredBrandsCampaigns[$i]["Headline"],
            "creativeAsins" => $sponsoredBrandsCampaigns[$i]["Creative_ASINs"],
            "mediaId" => $sponsoredBrandsCampaigns[$i]["Media_ID"],
            "automatedBidding" => $sponsoredBrandsCampaigns[$i]["Automated_Bidding"],
            "bidMultiplier" => $sponsoredBrandsCampaigns[$i]["Bid_Multiplier"],
            "campaignStatus" => $sponsoredBrandsCampaigns[$i]["Campaign_Status"]
        ]);
    }
    Log::write("info", "write ".count($sponsoredBrandsCampaigns)." sponsored brands campaigns ",
        ["level" => "robot", "robot" => $argv[0], "account" => $account]
    );
    CLITools::printMessage("Write ".count($sponsoredBrandsCampaigns). " Sponsored brands Campaigns", "success");
    //----------------------------------------------------------------------------------------------------------------//
    Log::write("info", "robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Robot finish synch campaigns amazon", "success");
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $argv[0], "exception" => $e, "account" => $account]);
}
?>
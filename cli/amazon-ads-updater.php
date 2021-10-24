<?php
/**
 * Робот синхронизации объявлений на уровне аккаунта клиента
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
    CLITools::printMessage("Start synch ads amazon", "success");

    $cacheDB = new PDO('mysql:host=' . CACHE_DB_HOST . ';dbname=' . CACHE_DB_NAME . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
    $cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cacheDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Получим sponsored products ads из БД с кешем для аккаунта
    $query = "SELECT Record_ID, 
                     Campaign_ID, 
                     Portfolio_ID, 
                     SKU,
                     Status
                FROM sponsored_products_campaigns
                WHERE Record_Type = 'Ad'
                    AND account_id = :account_id";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account_id" => $account]);
    $sponsoredProductsAds = $stmt->fetchAll();
    if (empty($sponsoredProductsAds)) {
        Log::write("info", "sponsored products ads not found.", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        Log::write("info", "robot finish work.", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Sponsored products ads not found. Robot finish work.", "warning");
        exit;
    }

    /*
     * КОСТЫЛЬ: Получим название товара и asin из таблицы со статистикой.
     * Решение вынужденное т.к asin для товаров не передается в bulk файле.
     */
    $query = "SELECT sponsored_products_campaigns.Record_ID AS adId,
                     products.name AS `name`,
                     sponsored_products_advertised.Advertised_ASIN AS `asin`	 
                FROM sponsored_products_advertised
                    JOIN sponsored_products_campaigns ON sponsored_products_campaigns.Campaign = sponsored_products_advertised.Campaign_Name
                    JOIN products ON sponsored_products_advertised.Advertised_ASIN = products.asin
                WHERE sponsored_products_advertised.account_id = :account_id 
                    AND sponsored_products_campaigns.Record_Type = 'ad' 
                GROUP BY adId";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account_id" => $account]);
    $sponsoredProductsAdsNames = $stmt->fetchAll();
    $sponsoredProductsAdsNames = array_column($sponsoredProductsAdsNames, null, "adId");

    // Добавим в массив с объявлениями название товара и ASIN
    array_walk($sponsoredProductsAds, function(&$item) use ($sponsoredProductsAdsNames) {
        $item["name"] = isset($sponsoredProductsAdsNames[$item["Record_ID"]]["name"]) ? $sponsoredProductsAdsNames[$item["Record_ID"]]["name"] : null;
        $item["asin"] = isset($sponsoredProductsAdsNames[$item["Record_ID"]]["asin"]) ? $sponsoredProductsAdsNames[$item["Record_ID"]]["asin"] : null;
    });
    Log::write("info", "get ".count($sponsoredProductsAds)." sponsored products ads ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Get ".count($sponsoredProductsAds). " Sponsored Products ads", "success");

    // Запишем данные для sponsored products campaigns в БД пользователя
    $db = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->query("USE user_" . $userId);

    $updateQuery = "INSERT INTO sponsored_products_ads
                            SET adId = :adId,
                                campaignId = :campaignId,
                                portfolioId = :portfolioId,
                                accountId = :accountId,
                                sku = :sku,
                                asin = :asin,
                                productName = :productName,
                                status = :status
                            ON DUPLICATE KEY UPDATE 
                                portfolioId = :portfolioId,
                                asin = :asin,
                                productName = :productName,
                                status = :status";
    $updateStmt = $db->prepare($updateQuery);

    for ($i = 0; $i < count($sponsoredProductsAds); $i++) {
        $updateStmt->execute([
            "adId" => $sponsoredProductsAds[$i]["Record_ID"],
            "campaignId" => $sponsoredProductsAds[$i]["Campaign_ID"],
            "portfolioId" => $sponsoredProductsAds[$i]["Portfolio_ID"],
            "accountId" => $account,
            "sku" => $sponsoredProductsAds[$i]["SKU"],
            "asin" => $sponsoredProductsAds[$i]["asin"],
            "productName" => $sponsoredProductsAds[$i]["name"],
            "status" => $sponsoredProductsAds[$i]["Status"]
        ]);
    }
    Log::write("info", "write ".count($sponsoredProductsAds)." sponsored products ads ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Write ".count($sponsoredProductsAds). " Sponsored Products Ads", "success");
    $sponsoredProductsAds = null;

    //----------------------------------------------------------------------------------------------------------------//
    Log::write("info", "robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Robot finish synch ads amazon", "success");
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $argv[0], "exception" => $e, "account" => $account]);
}
?>
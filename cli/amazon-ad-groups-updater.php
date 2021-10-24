<?php
/**
 * Робот синхронизации групп объявлений на уровне аккаунта клиента
 */
use PPCSoft\Logger\Log;
use PPCSoft\Tools\CLITools;

require_once __DIR__ . "/../vendor/autoload.php";
//--------------------------------------------------------------------------------------------------------------------//
try {
    $commandLineArgs = CLITools::getCommandLineArgs($argv, $argc);
    if (!isset($commandLineArgs["account"])) {
        CLITools::printMessage("Missing required param: account", "error");
        throw new Exception("Missing required param: account");
    }

    $account = $commandLineArgs["account"];
    $userId = CLITools::getUserIdByAccount($account);
    // Если робот с прошлого запуска еще не завершил работу - бросим эксепшн
    if (CLITools::isRobotLock($argv[0], $account)) {
        CLITools::printMessage("Robot " . CLITools::isRobotLock($argv[0]) . " is lock", "warning");
        throw new Exception("Robot " . CLITools::isRobotLock($argv[0]) . " is lock!");
    }
    //----------------------------------------------------------------------------------------------------------------//

    Log::write("info", "robot start work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Start synch ad groups amazon", "success");

    $cacheDB = new PDO('mysql:host=' . CACHE_DB_HOST . ';dbname=' . CACHE_DB_NAME . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
    $cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cacheDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Получим sponsored products ad groups из БД с кешем для аккаунта
    $query = "SELECT Record_ID, 
                     Campaign_ID, 
                     Portfolio_ID, 
                     Ad_Group,
                     Max_Bid
                FROM sponsored_products_campaigns
                WHERE Record_Type = 'Ad Group'
                    AND account_id = :account_id";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account_id" => $account]);
    $sponsoredProductsAdGroups = $stmt->fetchAll();

    if (empty($sponsoredProductsAdGroups)) {
        Log::write("info", "sponsored products ad groups not found. ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        Log::write("info", "robot finish work. ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Sponsored products Ad Groups not found. Robot finish work.", "warning");
    } else {
        Log::write("info", "get " . count($sponsoredProductsAdGroups) . " sponsored products ad groups ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Get " . count($sponsoredProductsAdGroups) . " Sponsored Products Ad Groups", "success");

        // Запишем данные для sponsored products campaigns в БД пользователя
        $db = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->query("USE user_" . $userId);

        $updateQuery = "INSERT INTO sponsored_products_ad_groups
                                SET adGroupId = :adGroupId,
                                    campaignId = :campaignId,
                                    portfolioId = :portfolioId,
                                    accountId = :accountId,
                                    adGroup = :adGroup,
                                    maxBid = :maxBid
                                ON DUPLICATE KEY UPDATE 
                                    adGroup = :adGroup,
                                    maxBid = :maxBid";
        $updateStmt = $db->prepare($updateQuery);

        for ($i = 0; $i < count($sponsoredProductsAdGroups); $i++) {
            $updateStmt->execute([
                "adGroupId" => $sponsoredProductsAdGroups[$i]["Record_ID"],
                "campaignId" => $sponsoredProductsAdGroups[$i]["Campaign_ID"],
                "portfolioId" => $sponsoredProductsAdGroups[$i]["Portfolio_ID"],
                "accountId" => $account,
                "adGroup" => $sponsoredProductsAdGroups[$i]["Ad_Group"],
                "maxBid" => CLITools::convertFloatToInt($sponsoredProductsAdGroups[$i]["Max_Bid"])
            ]);
        }
        Log::write("info", "write " . count($sponsoredProductsAdGroups) . " sponsored products ad groups ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Write " . count($sponsoredProductsAdGroups) . " Sponsored Products Ad Groups", "success");
        $sponsoredProductsAdGroups = null;
    }
    //----------------------------------------------------------------------------------------------------------------//
    /*
    // Получим sponsored products ad groups из БД с кешем для аккаунта
    $query = "SELECT Record_ID, 
                     Campaign_ID, 
                     Portfolio_ID, 
                     Ad_Group,
                     Max_Bid
                FROM sponsored_brands_campaigns
                WHERE Record_Type = 'Ad Group'
                    AND account_id = :account_id";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account_id" => $account]);
    $sponsoredBrandsAdGroups = $stmt->fetchAll();

    if (empty($sponsoredBrandsAdGroups)) {
        Log::write("info",
            "Sponsored brands ad groups not found. Robot finish work. ",
            ["level" => "robot", "robot" => $argv[0], "account" => $account]
        );
        CLITools::printMessage("Sponsored Brands Ad Groups not found. Robot finish work.", "warning");
    } else {
        Log::write("info", "get " . count($sponsoredBrandsAdGroups) . " sponsored brands ad groups ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Get " . count($sponsoredBrandsAdGroups) . " Sponsored Brands Ad Groups", "success");

        $updateQuery = "INSERT INTO sponsored_products_ad_groups
                                SET adGroupId = :adGroupId,
                                    campaignId = :campaignId,
                                    portfolioId = :portfolioId,
                                    accountId = :accountId,
                                    adGroup = :adGroup,
                                    maxBid = :maxBid
                                ON DUPLICATE KEY UPDATE 
                                    adGroup = :adGroup,
                                    maxBid = :maxBid";
        $updateStmt = $db->prepare($updateQuery);

        for ($i = 0; $i < count($sponsoredProductsAdGroups); $i++) {
            $updateStmt->execute([
                "adGroupId" => $sponsoredProductsAdGroups[$i]["Record_ID"],
                "campaignId" => $sponsoredProductsAdGroups[$i]["Campaign_ID"],
                "portfolioId" => $sponsoredProductsAdGroups[$i]["Portfolio_ID"],
                "accountId" => $account,
                "adGroup" => $sponsoredProductsAdGroups[$i]["Ad_Group"],
                "maxBid" => CLITools::convertFloatToInt($sponsoredProductsAdGroups[$i]["Max_Bid"])
            ]);
        }
        Log::write("info", "write " . count($sponsoredProductsAdGroups) . " sponsored products ad groups ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Write " . count($sponsoredProductsAdGroups) . " Sponsored Products Ad Groups", "success");
        $sponsoredProductsAdGroups = null;
    }
    */
    //----------------------------------------------------------------------------------------------------------------//
    Log::write("info", "robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Robot finish synch ad groups amazon", "success");
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $argv[0], "exception" => $e, "account" => $account]);
}
?>
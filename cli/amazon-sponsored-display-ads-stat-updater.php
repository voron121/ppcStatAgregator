<?php
/**
 * Робот обновления данных о кликах и расходах для sponsored display ads
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
    CLITools::printMessage("Start update sponsored display ads stat amazon", "success");

    $cacheDB = new PDO('mysql:host=' . CACHE_DB_HOST . ';dbname=' . CACHE_DB_NAME . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
    $cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cacheDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $query = "SELECT `date`,
                     Sponsored_Display_Advertised.clicks,
                     Sponsored_Display_Advertised.Impressions,
                     Sponsored_Display_Advertised.Spend,
                     Sponsored_Display_Advertised.Click_Thru_Rate AS ctr,
                     Sponsored_Display_Advertised.Cost_Per_Click AS cpc,
                     Sponsored_Display_Advertised.Total_Advertising_Cost_of_Sales AS acos,
                     Sponsored_Display_Advertised.Total_Return_on_Advertising_Spend AS roas,
                     Sponsored_Display_Advertised.Campaign_Name,
                     Sponsored_Display_Advertised.Advertised_SKU AS sku, 
                     Sponsored_Display_Advertised.Advertised_ASIN AS asin,
                     Sponsored_Display_Advertised.Campaign_Name,
                     Sponsored_Display_Advertised.Ad_Group_Name,
                     Sponsored_Display_Advertised.Fourteen_Day_Total_Orders AS orders,
                     Sponsored_Display_Advertised.account_id
                FROM Sponsored_Display_Advertised
                WHERE `date` BETWEEN (CURDATE() - INTERVAL 14 DAY) AND CURDATE()
                    AND Sponsored_Display_Advertised.account_id = :account";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account" => $account]);
    $adsStat = $stmt->fetchAll();

    // Завершим работу робота если нет статистики
    if (empty($adsStat)) {
        Log::write("info", "Stats is empty.", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        Log::write("info", "Robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Stats is empty. Robot finish work", "warning");
        exit;
    }
    Log::write("info",
        "get {count} stat items",
        ["level" => "robot", "count" => count($adsStat), "robot" => $argv[0], "account" => $account]
    );
    CLITools::printMessage("Get ".count($adsStat)." stat items", "success");

    // Установим коннект к БД пользователя
    $db = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->query("USE user_" . $userId);

    // Запишим статистику в БД юзера
    $query = "INSERT INTO sponsored_display_ads_stat
                    SET campaignName = :campaignName,
                        adGroupName = :adGroupName,
                        impressions = :impressions,
                        clicks = :clicks,
                        spend = :spend,
                        sale = :sale,
                        cpc = :cpc,
                        ctr = :ctr,
                        orders = :orders,
                        acos = :acos,
                        roas = :roas,
                        sku = :sku,
                        asin = :asin,
                        `date` = :date
                    ON DUPLICATE KEY UPDATE 
                        impressions = :impressions,
                        clicks = :clicks,
                        spend = :spend,
                        sale = :sale,
                        cpc = :cpc,
                        ctr = :ctr,
                        orders = :orders,
                        acos = :acos,
                        roas = :roas,
                        sku = :sku,
                        asin = :asin";
    $stmt = $db->prepare($query);

    // Запишем данные со статистикой в БД пользователя
    $writeCount = 0;
    foreach ($adsStat as $statItem) {
        $stmt->execute([
            "campaignName" => $statItem["Campaign_Name"],
            "adGroupName" => $statItem["Ad_Group_Name"],
            "impressions" => $statItem["Impressions"],
            "clicks" => $statItem["clicks"],
            "spend" => CLITools::convertFloatToInt($statItem["Spend"]),
            "sale" => CLITools::convertFloatToInt(CLITools::getSalesFromAcos($statItem["acos"], $statItem["Spend"])),
            "cpc" => CLITools::convertFloatToInt($statItem["cpc"]),
            "ctr" => CLITools::convertFloatToInt($statItem["ctr"]),
            "orders" => $statItem["orders"],
            "acos" => CLITools::convertFloatToInt($statItem["acos"]),
            "roas" => CLITools::convertFloatToInt($statItem["roas"]),
            "sku" => $statItem["sku"],
            "asin" => $statItem["asin"],
            "date" => $statItem["date"]
        ]);
        $writeCount++;
    }
    Log::write("info",
        "write {count} stat items ",
        ["level" => "robot", "count" => $writeCount, "robot" => $argv[0], "account" => $account]
    );
    CLITools::printMessage("Write ".$writeCount. " stat items", "success");
    $adsStat = null;
    //----------------------------------------------------------------------------------------------------------------//
    Log::write("info", "robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Robot finish update sponsored display ads stat amazon", "success");
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $argv[0], "exception" => $e, "account" => $account]);
}
?>
<?php
/**
 * Робот обновления данных о кликах и расходах для sponsored brands
 *
 * Алгоритм работы робота:
 * Получим список товаров с sku и asin
 * Соберем sku и asin товаров в массив где ключь => sku, а значение => asin
 * Получим из кеша bulk файла название кампаний sponsored brands и строку с асинами для этих кампаний
 * В цикле пройдемся по кампаниям:
 *      Получим sku из названия кампании
 *      Проверим существует ли для данного sku asin
 *      Если у кампании есть несколько асинов:
 *              Ассоциируем кампанию с тем асином, который соответствует sku из названия кампании
 *              Запишем в логи для какой кампании была замечена ситуация
 *      Если у кампании только один асин:
 *              Ассоциируем кампанию с тем асином, который соответствует sku из названия кампании
 * Соберем статистику для sponsored brands из кеша
 * Отфильтруем из статистики данные для кампаний, которых нет в массиве ассоциации названия кампаний к sku и asins
 * Добавим в массив со статистикой sku и asin из массива ассоциации названия кампаний к sku и asins
 * Запишем статистику в БД
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
    $robot = $argv[0];
    $userId = CLITools::getUserIdByAccount($account);
    // Если робот с прошлого запуска еще не завершил работу - бросим эксепшн
    if (CLITools::isRobotLock($robot, $account)) {
        CLITools::printMessage("Robot ".CLITools::isRobotLock($robot)." is lock", "warning");
        throw new Exception("robot ".CLITools::isRobotLock($robot)." is lock!");
    }
    //----------------------------------------------------------------------------------------------------------------//

    Log::write("info", "robot start work", ["level" => "robot", "robot" => $robot, "account" => $account]);
    CLITools::printMessage("Start update sponsored brands stat amazon", "success");

    // Соберем массив asin to sku
    $cacheDB = new PDO('mysql:host=' . CACHE_DB_HOST . ';dbname=' . CACHE_DB_NAME . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
    $cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cacheDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Получим товары из таблицы с товарами для ассоциации SKU => ASIN
    $query = "SELECT asin, 
                     sku
                 FROM products 
                 WHERE account_id = :account";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account" => $account]);
    $products = $stmt->fetchAll();

    Log::write("info",
        "get {count} products",
        ["level" => "robot", "count" => count($products), "robot" => $robot, "account" => $account]
    );
    CLITools::printMessage("Get ".count($products)." products", "success");

    // Проассоциируем SKU к ASIN товаров
    $skuToAsin = [];
    array_walk($products, function($item) use (&$skuToAsin) {
        $skuToAsin[$item["sku"]] = $item["asin"];
    });

    // Получим список кампаний и асинов, которые привязаны к ним из bulk кеша SB
    $campaignsToAsin = [];
    $query = "SELECT Campaign, 
                     Creative_ASINs 
                FROM sponsored_brands_campaigns 
                WHERE Record_Type = 'Campaign' 
                    AND account_id = :account";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account" => $account]);
    $sponsoredBrandsCampaigns = $stmt->fetchAll();

    Log::write("info",
        "get {count} sponsored brands campaigns to associated campaign to asin by sku",
        ["level" => "robot", "count" => count($sponsoredBrandsCampaigns), "robot" => $robot, "account" => $account]
    );
    CLITools::printMessage("Get get ".count($sponsoredBrandsCampaigns)." sponsored brands campaigns to associated campaign to asin by sku", "success");

    /*
     * Привяжем asin к кампании по sku. Если для кампании есть более одного асина, привяжем кампанию к асину, SKU
     * которого указан в названии кампании. Ситуацию логируем
     */
    $sponsoredBrandsCampaignsToAsin = [];
    array_walk($sponsoredBrandsCampaigns, function($item) use (&$sponsoredBrandsCampaignsToAsin, $skuToAsin, $account, $robot) {
        $sku = CLITools::getSKUFromCampaignName($item["Campaign"]);
        if (isset($skuToAsin[$sku])) {
            if (count(explode(",", $item["Creative_ASINs"])) > 1) {
                Log::write("warning", "in company \" {company} \" exist {count} asins. Stat associated to asin {asin}",
                    [
                        "level" => "robot",
                        "robot" => $robot,
                        "company" => $item["Campaign"],
                        "count" => count(explode(",", $item["Creative_ASINs"])),
                        "asin" => $skuToAsin[$sku],
                        "account" => $account
                    ]
                );
                CLITools::printMessage("In company \"" . $item["Campaign"] . "\" exist " . count(explode(",", $item["Creative_ASINs"])) . " asins. Stat associated to asin " . $skuToAsin[$sku], "warning");
            }
            $sponsoredBrandsCampaignsToAsin[$item["Campaign"]] = [
                "asin" => $skuToAsin[CLITools::getSKUFromCampaignName($item["Campaign"])],
                "sku" => $sku
            ];
        }
    });

    Log::write("info",
        "get {count} sponsored brands campaigns after associated campaign to asin by sku",
        ["level" => "robot", "count" => count($sponsoredBrandsCampaignsToAsin), "robot" => $robot, "account" => $account]
    );
    CLITools::printMessage("Get ".count($sponsoredBrandsCampaignsToAsin)." sponsored brands campaigns after associated campaign to asin by sku", "success");
    // Забота о памяти: удалим лишние данные, которые нам более не требуются в работе робота
    unset($sponsoredBrandsCampaigns, $skuToAsin, $products);

    // Получим статистику sponsored brands
    $query = "SELECT `date`,
                     (SELECT Record_ID FROM sponsored_brands_campaigns WHERE Campaign = Campaign_Name AND Record_Type = 'Campaign' LIMIT 0,1) AS campaignId,
                     Sponsored_Brands.clicks,
                     Sponsored_Brands.Impressions,
                     Sponsored_Brands.Clicks,
                     Sponsored_Brands.Spend,
                     Sponsored_Brands.Click_Thru_Rate AS ctr,
                     Sponsored_Brands.Cost_Per_Click AS cpc,
                     Sponsored_Brands.Total_Advertising_Cost_of_Sales AS acos,
                     Sponsored_Brands.Total_Return_on_Advertising_Spend AS roas,
                     Sponsored_Brands.Campaign_Name,
                     Sponsored_Brands.Fourteen_Day_Total_Orders,
                     Sponsored_Brands.account_id
                FROM Sponsored_Brands
                WHERE `date` BETWEEN (CURDATE() - INTERVAL 140 DAY) AND CURDATE()
                    AND Sponsored_Brands.account_id = :account";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account" => $account]);
    $sponsoredBrandsStat = $stmt->fetchAll();

    Log::write("info",
        "get {count} sponsored brands stat items",
        ["level" => "robot", "count" => count($sponsoredBrandsStat), "robot" => $robot, "account" => $account]
    );
    CLITools::printMessage("Get ".count($sponsoredBrandsStat)." sponsored brands stat items", "success");

    // Получим статистику sponsored brands video
    $query = "SELECT `date`,
                     (SELECT Record_ID FROM sponsored_brands_campaigns WHERE Campaign = Campaign_Name AND Record_Type = 'Campaign' LIMIT 0,1) AS campaignId,
                     Sponsored_Brands_Video.clicks,
                     Sponsored_Brands_Video.Impressions,
                     Sponsored_Brands_Video.Clicks,
                     Sponsored_Brands_Video.Spend,
                     Sponsored_Brands_Video.Click_Thru_Rate AS ctr,
                     Sponsored_Brands_Video.Cost_Per_Click AS cpc,
                     Sponsored_Brands_Video.Total_Advertising_Cost_of_Sales AS acos,
                     Sponsored_Brands_Video.Total_Return_on_Advertising_Spend AS roas,
                     Sponsored_Brands_Video.Campaign_Name,
                     Sponsored_Brands_Video.Fourteen_Day_Total_Orders,
                     Sponsored_Brands_Video.account_id
                FROM Sponsored_Brands_Video
                WHERE `date` BETWEEN (CURDATE() - INTERVAL 140 DAY) AND CURDATE()
                    AND Sponsored_Brands_Video.account_id = :account";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account" => $account]);
    $sponsoredBrandsVideoStat = $stmt->fetchAll();

    Log::write("info",
        "get {count} sponsored brands video stat items",
        ["level" => "robot", "count" => count($sponsoredBrandsVideoStat), "robot" => $robot, "account" => $account]
    );
    CLITools::printMessage("Get ".count($sponsoredBrandsVideoStat)." sponsored brands video stat items", "success");

    // Отфильтруем из статистики кампании, для которых не удалось привязать asin по sku
    $sponsoredBrandsStat = array_filter($sponsoredBrandsStat, function($item) use ($sponsoredBrandsCampaignsToAsin) {
        return isset($sponsoredBrandsCampaignsToAsin[$item["Campaign_Name"]]);
    });
    $sponsoredBrandsVideoStat = array_filter($sponsoredBrandsVideoStat, function($item) use ($sponsoredBrandsCampaignsToAsin) {
        return isset($sponsoredBrandsCampaignsToAsin[$item["Campaign_Name"]]);
    });

    if (empty($sponsoredBrandsStat)) {
        Log::write("warning", "Sponsored brands stat is empty after filtering data by sku from campaign.", ["level" => "robot", "robot" => $robot, "account" => $account]);
    }
    if (empty($sponsoredBrandsVideoStat)) {
        Log::write("warning", "Sponsored brands video stat is empty after filtering data by sku from campaign.", ["level" => "robot", "robot" => $robot, "account" => $account]);
    }
    // Если после фильтраций статистика для sponsored brands и sponsored brands video отсутствует - завершим работу
    if (empty($sponsoredBrandsVideoStat) && empty($sponsoredBrandsStat)) {
        Log::write("warning", "Stat is empty after filtering data by sku from campaign.", ["level" => "robot", "robot" => $robot, "account" => $account]);
        Log::write("info", "Robot finish work", ["level" => "robot", "robot" => $robot, "account" => $account]);
        CLITools::printMessage("Stats is empty. Robot finish work", "warning");
        exit;
    }
    // Залогируем прогресс работы
    Log::write("info",
        "get {count} sponsored brands stat items after filtering data by sku from campaign",
        ["level" => "robot", "count" => count($sponsoredBrandsStat), "robot" => $robot, "account" => $account]
    );
    CLITools::printMessage("Get " . count($sponsoredBrandsStat) . " sponsored brands stat items after filtering data by sku from campaign", "success");
    Log::write("info",
        "get {count} sponsored brands video stat items after filtering data by sku from campaign",
        ["level" => "robot", "count" => count($sponsoredBrandsVideoStat), "robot" => $robot, "account" => $account]
    );
    CLITools::printMessage("Get " . count($sponsoredBrandsVideoStat) . " sponsored brands video stat items after filtering data by sku from campaign", "success");

    // Добавим в массив со статистикой SB SKU и ASIN из названия кампании
    array_walk($sponsoredBrandsStat, function(&$item) use ($sponsoredBrandsCampaignsToAsin) {
        $item["sku"] = $sponsoredBrandsCampaignsToAsin[$item["Campaign_Name"]]["sku"];
        $item["asin"] = $sponsoredBrandsCampaignsToAsin[$item["Campaign_Name"]]["asin"];;
    });
    // Добавим в массив со статистикой SB Video SKU и ASIN из названия кампании
    array_walk($sponsoredBrandsVideoStat, function(&$item) use ($sponsoredBrandsCampaignsToAsin) {
        $item["sku"] = $sponsoredBrandsCampaignsToAsin[$item["Campaign_Name"]]["sku"];
        $item["asin"] = $sponsoredBrandsCampaignsToAsin[$item["Campaign_Name"]]["asin"];;
    });
    unset($cacheDB);

    // Установим коннект к БД пользователя
    $db = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->query("USE user_" . $userId);

    // Запишем статистику SB в БД юзера
    if (!empty($sponsoredBrandsStat)) {
        $query = "INSERT INTO sponsored_brands_ads_stat
                        SET campaignId = :campaignId,
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
        foreach ($sponsoredBrandsStat as $statItem) {
            $stmt->execute([
                "campaignId" => $statItem["campaignId"],
                "impressions" => $statItem["Impressions"],
                "clicks" => $statItem["clicks"],
                "spend" => CLITools::convertFloatToInt($statItem["Spend"]),
                "sale" => CLITools::convertFloatToInt(CLITools::getSalesFromAcos($statItem["acos"], $statItem["Spend"])),
                "cpc" => CLITools::convertFloatToInt($statItem["cpc"]),
                "ctr" => CLITools::convertFloatToInt($statItem["ctr"]),
                "orders" => $statItem["Fourteen_Day_Total_Orders"],
                "acos" => CLITools::convertFloatToInt($statItem["acos"]),
                "roas" => CLITools::convertFloatToInt($statItem["roas"]),
                "sku" => $statItem["sku"],
                "asin" => $statItem["asin"],
                "date" => $statItem["date"]
            ]);
        }
        Log::write("info",
            "write {count} sponsored brands stat items ",
            ["level" => "robot", "count" => count($sponsoredBrandsStat), "robot" => $robot, "account" => $account]
        );
        CLITools::printMessage("Write ".count($sponsoredBrandsStat). " sponsored brands stat items", "success");
    }

    // Запишем статистику SB Video в БД юзера
    if (!empty($sponsoredBrandsVideoStat)) {
        $query = "INSERT INTO sponsored_brands_video_ads_stat
                        SET campaignId = :campaignId,
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
        foreach ($sponsoredBrandsVideoStat as $statItem) {
            $stmt->execute([
                "campaignId" => $statItem["campaignId"],
                "impressions" => $statItem["Impressions"],
                "clicks" => $statItem["clicks"],
                "spend" => CLITools::convertFloatToInt($statItem["Spend"]),
                "sale" => CLITools::convertFloatToInt(CLITools::getSalesFromAcos($statItem["acos"], $statItem["Spend"])),
                "cpc" => CLITools::convertFloatToInt($statItem["cpc"]),
                "ctr" => CLITools::convertFloatToInt($statItem["ctr"]),
                "orders" => $statItem["Fourteen_Day_Total_Orders"],
                "acos" => CLITools::convertFloatToInt($statItem["acos"]),
                "roas" => CLITools::convertFloatToInt($statItem["roas"]),
                "sku" => $statItem["sku"],
                "asin" => $statItem["asin"],
                "date" => $statItem["date"]
            ]);
        }
        Log::write("info",
            "write {count} sponsored brands video stat items ",
            ["level" => "robot", "count" => count($sponsoredBrandsVideoStat), "robot" => $robot, "account" => $account]
        );
        CLITools::printMessage("Write ".count($sponsoredBrandsVideoStat). " sponsored brands video stat items", "success");
    }
    //----------------------------------------------------------------------------------------------------------------//
    Log::write("info", "robot finish work", ["level" => "robot", "robot" => $robot, "account" => $account]);
    CLITools::printMessage("Robot finish update sponsored brands stat amazon", "success");
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $robot, "exception" => $e, "account" => $account]);
}
?>
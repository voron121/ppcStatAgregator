<?php
/**
 * Робот синхронизации товаров на уровне аккаунта аккаунта
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
    // Массив со стандартным набором параметров для товара
    $defaultProductSettings = [
        "minAcos" => 40,
        "minSales" => 2000,
        "minSpend" => 100,
        "minConversion" => 10
    ];

    Log::write("info", "robot start work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Start synch products amazon", "success");

    $cacheDB = new PDO('mysql:host=' . CACHE_DB_HOST . ';dbname=' . CACHE_DB_NAME . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
    $cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cacheDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Получим список товаров из БД с кешем для аккаунта
    $query = "SELECT name, 
                     sku,
                     asin, 
                     account_id
                FROM products
                WHERE account_id = :account_id";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account_id" => $account]);
    $sponsoredProducts = $stmt->fetchAll();
    if (empty($sponsoredProducts)) {
        Log::write("info", "sponsored products not found.", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        Log::write("info", "robot finish work. ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Sponsored products not found. Robot finish work.", "warning");
        exit;
    }
    Log::write("info", "get ".count($sponsoredProducts)." sponsored products ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Get ".count($sponsoredProducts). " Sponsored Products", "success");

    // Запишем данные для sponsored products campaigns в БД пользователя
    $db = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASSWORD);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->query("USE user_" . $userId);

    $updateQuery = "INSERT INTO sponsored_products
                            SET name = :name,
                                sku = :sku,
                                asin = :asin,
                                accountId = :accountId,
                                settings = :settings
                            ON DUPLICATE KEY UPDATE 
                                name = :name";
    $updateStmt = $db->prepare($updateQuery);

    for ($i = 0; $i < count($sponsoredProducts); $i++) {
        $updateStmt->execute([
            "name" => $sponsoredProducts[$i]["name"],
            "sku" => $sponsoredProducts[$i]["sku"],
            "asin" => $sponsoredProducts[$i]["asin"],
            "accountId" => $account,
            "settings" => json_encode($defaultProductSettings, JSON_NUMERIC_CHECK)
        ]);
    }
    Log::write("info", "write ".count($sponsoredProducts)." sponsored products ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Write ".count($sponsoredProducts). " Sponsored Products", "success");
    $sponsoredProducts = null;
    //----------------------------------------------------------------------------------------------------------------//
    Log::write("info", "robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Robot finish synch products amazon", "success");
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $argv[0], "exception" => $e, "account" => $account]);
}
?>
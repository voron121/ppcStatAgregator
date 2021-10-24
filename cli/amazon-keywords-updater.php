<?php
/**
 * Робот синхронизации ключевых слов на уровне аккаунта клиента
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
    CLITools::printMessage("Start synch keywords amazon", "success");

    $cacheDB = new PDO('mysql:host=' . CACHE_DB_HOST . ';dbname=' . CACHE_DB_NAME . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
    $cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cacheDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Получим sponsored products ads из БД с кешем для аккаунта
    $query = "SELECT Record_ID, 
                     Campaign_ID, 
                     Portfolio_ID,
                     Max_Bid,
                     Match_Type,
                     Keyword_or_Product_Targeting,  
                     Status
                FROM sponsored_products_campaigns
                WHERE Record_Type = 'Keyword'
                    AND account_id = :account_id";
    $stmt = $cacheDB->prepare($query);
    $stmt->execute(["account_id" => $account]);
    $sponsoredProductsKeywords = $stmt->fetchAll();
    if (empty($sponsoredProductsKeywords)) {
        Log::write("info", "sponsored products keywords not found.", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        Log::write("info", "robot finish work. ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
        CLITools::printMessage("Sponsored products keywords not found. Robot finish work.", "warning");
        exit;
    }
    Log::write("info", "get ".count($sponsoredProductsKeywords)." sponsored products keywords ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Get ".count($sponsoredProductsKeywords). " Sponsored Products Keywords", "success");

    // Запишем данные во временный фаил
    $data = "";
    $sponsoredProductsKeywordsCount = 0;
    do {
        $data .= $sponsoredProductsKeywords[$sponsoredProductsKeywordsCount]["Record_ID"].",";
        $data .= $sponsoredProductsKeywords[$sponsoredProductsKeywordsCount]["Campaign_ID"].",";
        $data .= $sponsoredProductsKeywords[$sponsoredProductsKeywordsCount]["Portfolio_ID"].",";
        $data .= $account.",";
        $data .= $sponsoredProductsKeywords[$sponsoredProductsKeywordsCount]["Max_Bid"].",";
        $data .= $sponsoredProductsKeywords[$sponsoredProductsKeywordsCount]["Keyword_or_Product_Targeting"].",";
        $data .= $sponsoredProductsKeywords[$sponsoredProductsKeywordsCount]["Match_Type"].",";
        $data .= $sponsoredProductsKeywords[$sponsoredProductsKeywordsCount]["Status"]."\n";
        unset($sponsoredProductsKeywords[$sponsoredProductsKeywordsCount]);
        $sponsoredProductsKeywordsCount++;
    } while(!empty($sponsoredProductsKeywords));

    // создаем временный файл для записи
    $fh = tmpfile();
    if (!$fh) {
        throw new \Exception('Ошибка создания временного файла для записи истории ставок');
    }
    $file_name = str_replace("\\", "/", stream_get_meta_data($fh)['uri']); // Хак для windows: обратный слеш MySQL не воспринимает в пути к файлу
    fwrite($fh, $data);

    // Запишем данные для sponsored products campaigns в БД пользователя
    $db = new PDO('mysql:host=' . DB_HOST . ';charset=utf8', DB_USER, DB_PASSWORD, [PDO::MYSQL_ATTR_LOCAL_INFILE => true]);
    $db->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE,  true);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->query("USE user_" . $userId);

    $query = "LOAD DATA LOCAL INFILE '" . $file_name . "'
                REPLACE INTO TABLE `sponsored_products_keywords`
                FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n'
                (keywordId,campaignId,portfolioId,accountId,maxBid,keyword,matchType,status)";
    $db->query($query);

    Log::write("info", "write ".$sponsoredProductsKeywordsCount." sponsored products keywords ", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Write ".$sponsoredProductsKeywordsCount. " Sponsored Products Keywords", "success");
    fclose($fh);
    //----------------------------------------------------------------------------------------------------------------//
    Log::write("info", "robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => $account]);
    CLITools::printMessage("Robot finish synch keywords amazon", "success");
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $argv[0], "exception" => $e, "account" => $account]);
}
?>
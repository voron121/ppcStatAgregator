<?php
/**
 * Робот синхронизации данных аккаунтов амазон
 */

use PPCSoft\Logger\Log;
use PPCSoft\Tools\CLITools;

require_once __DIR__ . "/../vendor/autoload.php";
//--------------------------------------------------------------------------------------------------------------------//
try {
    Log::write("info", "robot start work", ["level" => "robot", "robot" => $argv[0], "account" => ""]);
    CLITools::printMessage("Start synch accounts amazon", "success");

    $userId = DEFAULT_USER_ID; // ХАРДКОД ДЛЯ ОТЛАДКИ
    // Если робот с прошлого запуска еще не завершил работу - бросим эксепшн
    if (CLITools::isRobotLock($argv[0])) {
        CLITools::printMessage("Robot ".CLITools::isRobotLock($argv[0])." is lock", "warning");
        throw new Exception("Robot ".CLITools::isRobotLock($argv[0])." is lock!");
    }
    //----------------------------------------------------------------------------------------------------------------//

    $cacheDB = new PDO('mysql:host=' . CACHE_DB_HOST . ';dbname=' . CACHE_DB_NAME . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
    $cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cacheDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $cacheDB->query("USE bots");
    // Получим аккаунты из временной БД
    $query = "SELECT `account_id`, 
                     `sclogin`,
                     `error_login`
                FROM config
                WHERE account_id IS NOT NULL 
                  AND account_id != '' ";
    $stmt = $cacheDB->query($query);
    $stmt->execute();
    $accounts = $stmt->fetchAll();
    // Если аккаунтов нет - прекратим выполнение робота
    if (empty($accounts)) {
        Log::write("info", "accounts is empty", ["level" => "robot", "robot" => $argv[0], "account" => ""]);
        Log::write("info", "robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => ""]);
        CLITools::printMessage("Robot finish work. Accounts is empty", "warning");
        exit;
    }
    // Приступим к переносу аккаунтов в БД сервиса
    CLITools::printMessage("Get " . count($accounts) . " accounts to processed", "success");
    //  Инстанс Бд центра авторизации
    $authDB = new PDO('mysql:host=' . AUTH_DB_HOST . ';dbname=' . AUTH_DB_NAME . ';charset=utf8', AUTH_DB_USER, AUTH_DB_PASSWORD);
    $authDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $authDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $query = "INSERT INTO `accounts` 
                    SET accountId = :accountId,
                        userId = :userId,
                        email = :email,
                        isError = :isError,
                        errorText = :errorText
                    ON DUPLICATE KEY UPDATE
                        email = :email,
                        isError = :isError,
                        errorText = :errorText";
    $stmt = $authDB->prepare($query);

    for ($i = 0; $i < count($accounts); $i++) {
        $isError = "-" === $accounts[$i]["error_login"] ?  "no" : "yes";
        $error = "-" != $accounts[$i]["error_login"] ? $accounts[$i]["error_login"] : null;
        $stmt->execute([
            "accountId" => $accounts[$i]["account_id"],
            "userId" => $userId,
            "email" => $accounts[$i]["email"],
            "isError" => $isError,
            "errorText" => $error
        ]);
    }

    CLITools::printMessage("Robot finish synch accounts amazon", "success");
    Log::write("info", "robot finish work", ["level" => "robot", "robot" => $argv[0], "account" => ""]);
} catch (Throwable $e) {
    CLITools::printMessage($e->getMessage(), "error");
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $argv[0], "exception" => $e, "account" => ""]);
}
?>
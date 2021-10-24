<?php
/**
 * Единый стартер роботов
 */

use PPCSoft\Logger\Log;
use PPCSoft\Tools\CLITools;

require_once __DIR__ . "/../vendor/autoload.php";

try {
    Log::write("info", "robots runner start work", ["level" => "robot", "robot" => $argv[0], "account" => ""]);
    CLITools::printMessage("Robots runner start work");
    //----------------------------------------------------------------------------------------------------------------//
    $accountsByUsers = [];
    $commands = [];

    // Получим список пользователей и аккаунтов из центра авторизации
    $authDB = new PDO('mysql:host=' . AUTH_DB_HOST . ';dbname=' . AUTH_DB_NAME . ';charset=utf8', AUTH_DB_USER, AUTH_DB_PASSWORD);
    $authDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $authDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // TODO: добавить проверку статуса пользователя на активность
    $query = "SELECT userId, accountId FROM accounts";
    $stmt = $authDB->query($query);
    $stmt->execute();
    $users = $stmt->fetchAll();
    if (empty($users)) {
        Log::write("warning", "don't find users for processing", ["level" => "robot", "robot" => $argv[0], "account" => ""]);
        CLITools::printMessage("Robots runner finish work");
    }
    array_walk($users, function ($item) use (&$accountsByUsers) {
        $accountsByUsers[$item["userId"]][] = $item["accountId"];
    });
    unset($users);

    $commands = array_merge($commands, CLITools::getRobotsCommandByUserLevel(array_keys($accountsByUsers)));
    foreach ($accountsByUsers as $userId => $accounts) {
        $commands = array_merge($commands, CLITools::getRobotsCommandByAccountLevel($accounts));
    }
    unset($accountsByUsers);

    Log::write("info", "create {count} commands for start", ["count" => count($commands), "level" => "robot", "robot" => $argv[0], "account" => ""]);
    CLITools::printMessage("create ".count($commands)." commands for start");
    // Запустим роботы
    foreach ($commands as $command) {
        echo exec($command, $output, $code)." \n\r";
    }
    Log::write("info", "robots runner finish work", ["level" => "robot", "robot" => $argv[0], "account" => ""]);
    CLITools::printMessage("Robots runner finish work");
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file", "robot" => $argv[0], "exception" => $e]);
}

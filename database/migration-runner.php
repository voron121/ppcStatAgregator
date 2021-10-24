<?php
/**
 * Скрипт раскатки миграций на серверах с учетом распределенных баз данных.
 * В качестве библиотеки для миграций используется Phinx.
 * Принимает не обзательный параметр user={ид пользователя в сервисе}
 * Принимает не обязательный параметр mode=deploy - создаст все нужные таблицы при первой расктке сервиса на сервере
 */

use PPCSoft\Logger\Log;
use PPCSoft\Tools\CLITools;

require_once __DIR__ . "/../vendor/autoload.php";

try {
    $phinxConfig = include __DIR__ . "/../configs/phinx.php";
    $commands = [];
    $environments = [];
    $commandLineArgs = CLITools::getCommandLineArgs($argv, $argc);

    array_walk($phinxConfig["environments"], function ($item, $key) use (&$environments) {
        if (is_array($item)) {
            $environments[] = $key;
        }
    });

    // Создание всех нужных БД при первом демлое сервиса на сервер
    if (isset($commandLineArgs["mode"]) && "deploy" === $commandLineArgs["mode"]) {
        // Создадим центр авторизации
        $db_config = $phinxConfig["environments"]["auth"];
        $db = new \PDO('mysql:host='.$db_config['host'].';charset=utf8', $db_config['user'], $db_config['pass']);
        $db->query("CREATE DATABASE IF NOT EXISTS `".AUTH_DB_NAME."` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */");

        // Создадим БД для логирования api
        $db_config = $phinxConfig["environments"]["api_log"];
        $db = new \PDO('mysql:host='.$db_config['host'].';charset=utf8', $db_config['user'], $db_config['pass']);
        $db->query("CREATE DATABASE  IF NOT EXISTS `".API_LOG_DB_NAME."` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */");

        // Создадим БД для логирования роботов
        $db_config = $phinxConfig["environments"]["robots_log"];
        $db = new \PDO('mysql:host='.$db_config['host'].';charset=utf8', $db_config['user'], $db_config['pass']);
        $db->query("CREATE DATABASE  IF NOT EXISTS `".ROBOTS_LOG_DB_NAME."` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */");

        // Создадим БД для логирования действий пользователя
        $db_config = $phinxConfig["environments"]["users_log"];
        $db = new \PDO('mysql:host='.$db_config['host'].';charset=utf8', $db_config['user'], $db_config['pass']);
        $db->query("CREATE DATABASE  IF NOT EXISTS `".USERS_LOG_DB_NAME."` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */");
        $db_config = null;
        $db = null;
    }

    $db_config = $phinxConfig["environments"]["production"];
    $user_db = new \PDO('mysql:host='.$db_config['host'].';charset=utf8', $db_config['user'], $db_config['pass']);

    // Получим массив БД для раскатки в них миграций
    if (isset($commandLineArgs["user"])) {
        $userDBName = "user_" . $commandLineArgs["user"];
        // Создадим БД для нового пользователя
        $user_db->query("CREATE DATABASE IF NOT EXISTS `" . $userDBName . "` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */");
        $databases = [$userDBName];
    } else {
        $databases = $user_db->query("SHOW DATABASES LIKE 'user_%'")->fetchAll(PDO::FETCH_COLUMN);
    }

    // Отфильтруем возможные не консистентные название БД для пользователей
    $databases = array_filter($databases, function($item) {
        return preg_match("/(user_\d)/", $item);
    });

    // Сформируем команду для запуска миграции с учетом окружения
    foreach ($environments as $environment) {
        $phinxCommand = ("WINNT" === PHP_OS) ? "phinx.bat" : "phinx";
        $commands[$environment] = __DIR__ . "/../vendor/bin/".$phinxCommand." migrate -e " . $environment . " -c ".__DIR__ . "/../configs/phinx.php";
    }

    foreach ($commands as $environment => $command) {
        if (in_array($environment, ["production", "development"])) {
            CLITools::printMessage($environment . " environment");
            foreach ($databases as $database) {
                CLITools::printMessage($database);
                putenv('PHINX_DBNAME='.$database);
                echo exec($command, $output, $code)." \n\r";
                if ($code != 0) {
                    Log::write("alert",
                        "Ошибка применения миграции для БД {db} в окружении {environment} Причина:  {reason}",
                        ["environment" => $environment, "db" => $database, "level" => "file", "reason" => $output[13]]
                    );
                }
            }
        }
        CLITools::printMessage($environment . " environment");
        echo exec($command)." \n\r";
    }
} catch (Throwable $e) {
    Log::write("alert", $e->getMessage(), ["exception" => $e, "level" => "file"]);
}

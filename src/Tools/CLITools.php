<?php
/**
 * Утилитарный класс для реализации различных не значительных задач и функциональностей для консольных утилит и роботов
 */
namespace PPCSoft\Tools;

use PDO;
use PDOException;

class CLITools
{
    /* Множитель для преобразования float в int и наоборот */
    const FLOAT_CONVERT_NUMBER = 1000000;

    /* 100% из формулы расчета ACOS  */
    const PERCENTAGE_MULTIPLIER_ACOS = 100;

    /**
     * Парсит параметры командной строки при запуске скрипта. Вернет ассоциативный массив
     * где ключь - название параметра, значение - значение параметра
     * @param array $argv - Массив параметров
     * @param int $argc - Количество параметров
     * @return array - Ассоциативный массив с параметрами
     */
    public static function getCommandLineArgs(array $argv, int $argc) : array
    {
        $arguments = [];
        for ($i = 0; $i < $argc; $i++) {
            $command = explode("=", $argv[$i]);
            if (isset($command[0], $command[1])) {
                $arguments[$command[0]] = trim($command[1]);
            }
        }
        return $arguments;
    }

    /**
     * Конвертирует цену для записи в БД: умножит цену на 1000000
     * @param $price
     * @return int
     */
    public static function convertFloatToInt($price) : int
    {
        return round($price, 6) * self::FLOAT_CONVERT_NUMBER;
    }

    /**
     * Конфертирует цену для записи полученную из  БД: разделит цену на 1000000
     * @param $price
     * @param $precision
     */
    public static function convertIntToFloat(int $price, $precision = 2)
    {
        return $price > 0 ? round($price / self::FLOAT_CONVERT_NUMBER, $precision) : 0.00;
    }

    /**
     * Выводит сообщение в консоль
     * @param  string   $message    - Сообщение, которое нужно вывести
     * @param  string   $state      - Статус сообщения. По умолчанию null(белый)
     * Параметры: success(зеленый) | warning(желтый) | error(красный)
     */
    public static function printMessage(string $message, $state = null) : void
    {
        if ('success' == $state) {
            echo "\033[0;32m".$message."\033[0m\n";
        } elseif ('warning' == $state) {
            echo "\033[0;33m".$message."\033[0m\n";
        } elseif ('error' == $state) {
            echo "\033[0;31m".$message."\033[0m\n";
        } else {
            echo "\033[0;37m".$message."\033[0m\n";
        }
    }

    /**
     * вернет абсолютный путь к дирректории с роботами
     * @return string
     */
    public static function getRobotsRootPath() : string
    {
        return __DIR__ . "/../../cli/";
    }

    /**
     * Вернет ассоциативный массим со списком роботов к запуску
     * индексированный по уровню запсука робота (user, account)
     * @return array
     */
    public static function getRobotsList() : array
    {
        return [
            "user" => [
                "amazon-accounts-updater.php"
            ],
            "account" => [
                "amazon-products-updater.php",
                "amazon-ad-groups-updater.php",
                "amazon-ads-updater.php",
                "amazon-campaigns-updater.php",
                "amazon-keywords-updater.php",
                "amazon-sponsored-products-ads-stat-updater.php",
                "amazon-sponsored-display-ads-stat-updater.php",
                "amazon-sponsored-brands-stat-updater.php",
            ]
        ];
    }

    /**
     * Вернет массив команд для запуска роботов на уровне пользователя
     * @param array $users
     * @return array
     */
    public static function getRobotsCommandByUserLevel(array $users) : array
    {
        $commands = [];
        $robots = self::getRobotsList();
        foreach ($users as $userId) {
            foreach ($robots["user"] as $robot) {
                $commands[] = "php ". self::getRobotsRootPath() . $robot." user=".$userId." &";
            }
        }
        return $commands;
    }

    /**
     * Вернет массив команд для запуска роботов на уровне аккаунтов
     * @param array $accounts
     * @return array
     */
    public static function getRobotsCommandByAccountLevel(array $accounts) : array
    {
        $commands = [];
        $robots = self::getRobotsList();
        foreach ($accounts as $account) {
            foreach ($robots["account"] as $robot) {
                $commands[] = "php ". self::getRobotsRootPath() . $robot." account=".$account." &";
            }
        }
        return $commands;
    }

    /**
     * Вернет ид пользователя из ЦА по ид аккаунта
     * @param string $account
     * @return int
     */
    public static function getUserIdByAccount(string $account) : int
    {
        $db = new PDO('mysql:host=' . AUTH_DB_HOST . ';dbname=' . AUTH_DB_NAME . ';charset=utf8', AUTH_DB_USER, AUTH_DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $query = "SELECT userId
                    FROM accounts 
                    WHERE accountId = :accountId";
        $stmt = $db->prepare($query);
        $stmt->execute(["accountId" => $account]);
        $user = $stmt->fetchColumn();
        if (!$user) {
            throw new Exception("User not found");
        }
        return $user;
    }

    /**
     * Вернет имя робота из абсолютного пути к исполняемому файлу
     * @param string $robot
     * @return string
     */
    public static function getRobotNameByPath(string $robot) : string
    {
        return preg_replace("/(\w.*\/cli\/)/", "", $robot);
    }

    /**
     * Проверит запущен ли робот для конкретного пользователя
     * @param string $robot
     * @param string $account
     * @return bool
     */
    public static function isRobotLock(string $robot, string $account = "") : bool
    {
        static $lock;
        $is_lock    = true;
        if(!is_dir(self::getRobotsRootPath()."locks/")) {
            mkdir(self::getRobotsRootPath()."locks/", 0755);
        }
        $lock_file  = md5(self::getRobotNameByPath($robot).$account).".txt";
        $lock       = fopen( self::getRobotsRootPath()."locks/".$lock_file, 'w+');
        if (flock($lock, LOCK_EX | LOCK_NB)) {
            $is_lock = false;
        }
        return $is_lock;
    }

    /**
     * Расчитает сумму проданных товаров за день из AcOS и spend
     * Формула расчета acos: (spend / sales) * 100% = acos
     * Формула расчета sales: (100% / acos) * spend = sales где 100 - это 100% из формуллы acos.
     * Важно: т.к acos из отчетов приходит в виде числа с плавающей точкой (пример: 156% = 1.56) - приведем acos к целому числу
     * TODO: учесть возможный acos с более чем двумя числами после запятой. Например 1.567
     * @param float $acos
     * @param float $spend
     * @return float
     */
    public static function getSalesFromAcos($acos, $spend) : float
    {
        if (round($acos * 100, 0) == 0 || $spend == 0) {
            return 0.00;
        }
        return (self::PERCENTAGE_MULTIPLIER_ACOS / round($acos * 100, 0)) * $spend;
    }

    /**
     * @param string $campaignName
     * @return bool
     */
    public static function isSKUInCampaignNameExist(string $campaignName) : bool
    {
        return preg_match_all("/([[A-Z0-9]+\-]*)+([[A-Z0-9]+\s]*)/u", $campaignName);
    }

    /**
     * @param string $campaignName
     * @return string
     */
    public static function getSKUFromCampaignName(string $campaignName) : string
    {
        preg_match("/([[A-Z0-9]+\-]*)+([[A-Z0-9]+\s]*)/u", $campaignName, $matches);
        return trim($matches[0]);
    }

    /**
     * @param int $orders
     * @param int $clicks
     * @return float
     */
    public static function calculateConversion(int $orders, int $clicks) : float
    {
        $conversion = 0.00;
        if ($orders != 0 && $clicks != 0) {
            $conversion = $orders / $clicks;
        }
        return round($conversion, 2);
    }

}
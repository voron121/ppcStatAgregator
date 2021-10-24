<?php
/**
 * Базовая логика фильтров
 * TODO: класс объемный, возможно стоит разделить его логику
 */
namespace Models;

use DateTime;
use PPCSoft\Registry;

abstract class Filter
{
    /* Название конфигурационного файла для набора полей фильтров */
    const FILTER_CONFIG = null;

    /* Массив с параметрами полей фильтрации */
    protected $filterConfigs = null;

    /* Объект PDO */
    protected $db = null;

    /* Массив соответсвия знака равенства к текстовому представлению */
    const ADDITIONAL_FILTER_CLAUSE_DICT = [
        "more" => ">",
        "less" => "<",
        "equal" => "=",
    ];

    public function __construct()
    {
        $this->filterConfigs = $this->getFiltersConfig();
        $this->db = Registry::get("db");
    }

    /**
     * Получит конфиг фильтра из файла
     * @return array
     */
    protected function getFiltersConfig() : array
    {
        $config = file_get_contents(FILTER_CONFIG_PATH . static::FILTER_CONFIG);
        return json_decode($config, true);
    }

    /**
     * Получит массив с параметрами для фильтрации сущностей (товаров, кампаний и тд).
     * Вернет ассоциативный массив вида:
     * [
     *      {название поля в БД для фильтрации} => [
     *          "value" => значение для фильтрации,
     *          "table" => название таблицы в которой будет происходить выборка
     *      ]
     * ]
     * @return array - ассоциативный массив с параметрами для фильтрации где ключь - название поля в БД для фильтрации
     */
    protected function getItemsFilterArgs() : array
    {
        $args = [];
        foreach ($this->filterConfigs["items"] as $filter => $params) {
            if (isset($_GET[$filter]) && "0" != $_GET[$filter]) {
                $args[$params["field"]] = [
                    "value" => $_GET[$filter],
                    "table" => $params["table"],
                    "excluded" => $params["excluded"],
                ];
            }
        }
        return $args;
    }

    /**
     * Получит массив с параметрами для фильтрации статистики сущностей (товаров, кампаний и тд).
     * Вернет ассоциативный массив вида:
     * [
     *      {название поля в БД для фильтрации} => [
     *          "value" => значение для фильтрации,
     *          "table" => название таблицы в которой будет происходить выборка
     *          "clause" => условие для фильтрации (<,>,=, != и тд)
     *      ]
     * ]
     * @return array - ассоциативный массив с параметрами для фильтрации где ключь - название поля в БД для фильтрации
     */
    protected function getItemsStatFilterArgs() : array
    {
        $args = [];
        foreach ($this->filterConfigs["stat"] as $filter => $params) {
            $clause = "clause".$filter;
            if (isset($_GET[$filter])) {
                $args[$params["field"]] = [
                    "value" => true === $params['multiplier'] ? $_GET[$filter] * 1000000 : $_GET[$filter],
                    "table" => $params["table"],
                    "clause" => self::ADDITIONAL_FILTER_CLAUSE_DICT[$_GET[$clause]]
                ];
            }
        }
        return $args;
    }

    /**
     * Сформирует строку для регулярного выражения на основе параметров фильтрации.
     * Необходим для формирования урл фильтра
     * @param array $options
     * @return string
     */
    protected function getFilterRegexp(array $options) : string
    {
        $patter = "";
        foreach ($options as $option => $value) {
            $patter .= "(\?" . $option . "=" . $value . ")|(\&" . $option . "=" . $value . ")|";
        }
        return rtrim($patter, "|");
    }

    /**
     * Проверит url на наличие в ней гет параметров
     * @param string $url
     * @return bool
     */
    protected function isUrlHasGetParams(string $url) : bool
    {
        return preg_match("/(\?\w.*=)/", $url);
    }

    /**
     * Обработает урл для сохранения параметров фильтрации и остальных гет параметров
     * @return string
     */
    public function getFilterUrl() : string
    {
        $url = $_SERVER['REQUEST_URI'];
        if (!$this->isFilterExist()) {
            return $url;
        }
        // Обработаем урл
        $filters = [];
        // Соберем основные фильтры
        foreach ($this->getItemsFilterArgs() as $option => $params) {
            if (isset($_GET[$option])) {
                $filters[$option] = $_GET[$option];
            }
        }
        // Соберем дополнительные фильтры
        foreach ($this->getItemsStatFilterArgs() as $option => $params) {
            if (isset($_GET[$option])) {
                $filters[$option] = $_GET[$option];
            }
            if (isset($_GET["clause".$option])) {
                $filters["clause".$option] = $_GET["clause".$option];
            }
        }
        // Если в урл есть гет параметры
        if ($this->isUrlHasGetParams($url)) {
            $url = preg_replace("/".$this->getFilterRegexp($filters)."/", "", $url);
            array_walk($filters, function($value, $option) use (&$url) {
                $url .= "&".$option."=".$value;
            });
            //удалим возможный первый символ & в строке
            $url = preg_replace("/\.php&/", ".php?", $url);
        }
        return $url;
    }

    /**
     * Вернет самую раннюю дату статистики для Sponsored Products
     * @return string
     */
    public function getSponsoredProductsMinStatDate() : DateTime
    {
        $stmt = $this->db->query("SELECT MIN(date) FROM sponsored_products_ads_stat");
        $minDate = $stmt->fetchColumn();
        return new DateTime($minDate);
    }

    /**
     * Вернет самую позднюю дату статистики для Sponsored Products
     * @return string
     */
    public function getSponsoredProductsMaxStatDate() : DateTime
    {
        $stmt = $this->db->query("SELECT MAX(date) FROM sponsored_products_ads_stat");
        $maxDate = $stmt->fetchColumn();
        return new DateTime($maxDate);
    }

    /**
     * Вернет ассоциативный массив с набором дополнительный фильтров (clicks, spends, acos и тд) для товаров
     * @return array
     */
    public function getAdditionalFilters() : array
    {
        $filters = [];
        foreach ($this->filterConfigs["stat"] as $filter => $params) {
            $filters[$filter] = $params["name"];
        }
        return $filters;
    }

    /**
     * Проверит существут ли параметры фильтра в гет параметрах
     * TODO: убрать вложенный цикл
     * @return bool
     */
    public function isFilterExist() : bool
    {
        $filters = $this->getFiltersConfig();
        foreach ($filters as $filterItems => $filters) {
            if ("items" === $filterItems) {
                foreach ($filters as $filter => $params) {
                    if (isset($_GET[$filter]) && $_GET[$filter] != "0") {
                        return true;
                    }
                }
            } else {
                if (isset($_GET[$filter])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Метод проверит сущестсует ли фильтрации по дате
     * @return bool
     */
    public function isDateFilterExist() : bool
    {
        if (isset($_GET["startDate"]) && "0" != $_GET["startDate"]
            && isset($_GET["endDate"]) && "0" != $_GET["endDate"]
        ) {
            return true;
        }
        return false;
    }

    /**
     * Вернет массив с ид аккаунтов amazon для пользователя.
     * Используется для отображения поля с выбором аккаунтов в фильтрах
     * @return array
     */
    public function getAccounts() : array
    {
        $db = Registry::get("authDB");
        $user = Registry::get("user");
        $query = "SELECT accountId FROM accounts WHERE userId = :userId";
        $stmt = $db->prepare($query);
        $stmt->execute(["userId" => $user->getUserId()]);
        return $stmt->fetchAll();
    }

    /**
     * Вернет массив с асинами для товаров пользователя.
     * Используется для отображения поля с выбором асинов в фильтрах
     * @return array
     */
    public function getAsins() : array
    {
        $query = "SELECT DISTINCT(asin) FROM sponsored_products";
        $stmt = $this->db->query($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Вернет массив со статусами для дальнейшего рендера в форме
     * TODO: возможно стоит данную часть фильтров рендерит с помощью javascript
     * @return string[]
     */
    public function getStatuses() : array
    {
        return [
            "Enabled" => "Enabled",
            "Paused" => "Paused",
            "Archived" => "Archived"
        ];
    }

    /**
     * @return string[]
     */
    public function getCampaignsType() : array
    {
        return [
            "Sponsored products" => "sponsored_products_ads_stat",
            "Sponsored display" => "sponsored_display_ads_stat",
            "Sponsored brands" => "sponsored_brands_ads_stat",
            "Sponsored brands video" => "sponsored_brands_video_ads_stat",
        ];
    }

    /**
     * Сформирует запрос SQL с учетом параметров фильтрации для рекламной сущности
     * @param string $query - SQL запрос без операторов WHERE ORDER GROUP LIMIT
     * @param $offset - Сдвиг для пагинации.
     * @return string - SQL запрос с учетом параметров фильтрации
     */
    public function getItemsFilterQuery(string $query, $offset = null) : string
    {
        $whereQuery = "";
        if ($this->isFilterExist()) {
            $filters = $this->getItemsFilterArgs();
            foreach ($filters as $field => $params) {
                $whereQuery .= " AND " . $params["table"] . "." . $field . " = " . $this->db->quote($params["value"]);
            }
        }
        if (!empty($whereQuery)) {
            $whereQuery = substr_replace($whereQuery, " WHERE ", 0, 4);
        }
        if (!is_null($offset)) {
            $whereQuery .= " LIMIT " . (int)$offset . ", " . ITEMS_ON_PAGE_LIMIT;
        }
        return $query . $whereQuery;
    }

    /**
     * Сформирует SQL запрос для получения статистики рекламной сущности с учетом параметров фильтрации
     * @param string $query
     * @param array $asins
     * @return array|string
     */
    abstract function getItemsStatFilterQuery(string $query, array $asins) : string;
}
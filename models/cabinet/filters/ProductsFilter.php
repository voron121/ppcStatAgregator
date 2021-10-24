<?php
/**
 * Реализация логики для фильтра товаров
 */
namespace Models\cabinet\filters;

use Models\Filter;

class ProductsFilter extends Filter
{
    const FILTER_CONFIG = "product-filters-config.json";

    /**
     * TODO: добавить проверку таблицы на существование (ограничить режим только теми таблицами которые есть через массив)
     * TODO: Изменить название метода на более локаничное
     * @return array
     */
    public function getStatTable() : string
    {
        $filterArgs = $this->getItemsFilterArgs();
        return isset($filterArgs["campaignsType"]) ? $filterArgs["campaignsType"]["value"] : "all";
    }

    /**
     * Сформирует запрос SQL с учетом параметров фильтрации для рекламной сущности
     * @param string $query - SQL запрос без операторов WHERE ORDER GROUP LIMIT
     * @param $offset - Сдвиг для пагинации.
     * @return string - SQL запрос с учетом параметров фильтрации
     */
    public function getItemsFilterQuery(string $query, $offset = null) : string
    {
        $whereQuery = " WHERE (JSON_UNQUOTE(JSON_EXTRACT(settings, '$.hideProduct')) != 'yes' OR JSON_UNQUOTE(JSON_EXTRACT(settings, '$.hideProduct')) IS NULL)";
        if ($this->isFilterExist()) {
            $filters = $this->getItemsFilterArgs();
            foreach ($filters as $field => $params) {
                if ($params["excluded"] === false) {
                    $whereQuery .= " AND " . $params["table"] ."." . $field . " = " . $this->db->quote($params["value"]);
                }
            }
        }
        // TODO: подумать нужно-ли группировать данные по ASIN и как это отобразится на статистике
        if (!is_null($offset)) {
            $whereQuery .= " GROUP BY asin ";
            $whereQuery .= " ORDER BY id DESC";
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
    public function getItemsStatFilterQuery(string $query, array $asins) : string
    {
        if ($this->isDateFilterExist()) {
            $whereQuery = " WHERE date BETWEEN " . $this->db->quote($_GET["startDate"]) . " AND " . $this->db->quote($_GET["endDate"]);
        } else {
            $whereQuery = " WHERE date BETWEEN (CURDATE() - INTERVAL 1 MONTH) AND CURDATE()";
        }
        $whereQuery .= " AND asin IN (".implode(",", $asins).")";
        if ($this->isDateFilterExist()) {
            $whereQuery .= " GROUP BY `asin`";
        } else {
            $whereQuery .= " GROUP BY `date`, `asin`";
        }

        if (!empty($this->getItemsStatFilterArgs())) {
            $i = 0;
            foreach($this->getItemsStatFilterArgs() as $field => $params) {
                if ($i === 0) {
                    $whereQuery .= " HAVING " . $field . $params["clause"] . $this->db->quote($params["value"]);
                } else {
                    $whereQuery .= " AND " . $field . $params["clause"] . $this->db->quote($params["value"]);
                }
                $i++;
            }
        }
        $whereQuery .= " ORDER BY `asin` DESC";
        return $query . $whereQuery;
    }
}
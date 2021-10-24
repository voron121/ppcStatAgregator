<?php
/**
 * Реализация логики для фильтра групп объявлений
 */
namespace Models\cabinet\filters;

use Models\Filter;

class AdGroupsFilter extends Filter
{
    const FILTER_CONFIG = "ad-groups-filters-config.json";

    /**
     * Сформирует запрос SQL с учетом параметров фильтрации для списка групп объявлений
     * @param string $query - SQL запрос без операторов WHERE ORDER GROUP LIMIT
     * @param $offset - Сдвиг для пагинации.
     * @return string - SQL запрос с учетом параметров фильтрации
     */
    public function getItemsStatFilterQuery(string $query, array $adsIds) : string
    {
        if ($this->isDateFilterExist()) {
            $whereQuery = " WHERE sponsored_products_ads_stat.date BETWEEN " . $this->db->quote($_GET["startDate"]) . " AND " . $this->db->quote($_GET["endDate"]);
        } else {
            $whereQuery = " WHERE sponsored_products_ads_stat.date BETWEEN (CURDATE() - INTERVAL 1 MONTH) AND CURDATE()";
        }
        $whereQuery .= " AND sponsored_products_ads_stat.adId IN (".implode(",", $adsIds).")";

        if ($this->isDateFilterExist()) {
            $whereQuery .= " GROUP BY `campaignId`";
        } else {
            $whereQuery .= " GROUP BY sponsored_products_ad_groups.adGroupId, sponsored_products_ads_stat.date";
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
        $whereQuery .= " ORDER BY sponsored_products_ads_stat.`date`";
        return $query . $whereQuery;
    }
}
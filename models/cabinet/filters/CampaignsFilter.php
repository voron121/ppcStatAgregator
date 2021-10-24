<?php
/**
 * Реализация логики для фильтра кампаний
 */
namespace Models\cabinet\filters;

use Models\Filter;

class CampaignsFilter extends Filter
{
    const FILTER_CONFIG = "campaigns-filters-config.json";

    /**
     * Сформирует запрос SQL с учетом параметров фильтрации для списка кампаний
     * @param string $query - SQL запрос без операторов WHERE ORDER GROUP LIMIT
     * @param array $campaignIds
     * @return string - SQL запрос с учетом параметров фильтрации
     */
    public function getItemsStatFilterQuery(string $query, array $campaignIds) : string
    {
        if ($this->isDateFilterExist()) {
            $whereQuery = " WHERE sponsored_products_ads_stat.date BETWEEN " . $this->db->quote($_GET["startDate"]) . " AND " . $this->db->quote($_GET["endDate"]);
        } else {
            $whereQuery = " WHERE sponsored_products_ads_stat.date BETWEEN (CURDATE() - INTERVAL 1 MONTH) AND CURDATE()";
        }
        $whereQuery .= " AND sponsored_products_ads_stat.campaignId IN (".implode(",", $campaignIds).")";

        if ($this->isDateFilterExist()) {
            $whereQuery .= " GROUP BY `campaignId`";
        } else {
            $whereQuery .= " GROUP BY `date`, campaignId";
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
        $whereQuery .= " ORDER BY sponsored_products_ads_stat.`date` DESC";
        return $query . $whereQuery;
    }
}
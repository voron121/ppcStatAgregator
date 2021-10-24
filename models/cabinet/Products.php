<?php


namespace Models\cabinet;

use Models\BaseModel;
use Models\cabinet\filters\ProductsFilter;
use PDO;

class Products extends BaseModel
{
    public $pageLimit = ITEMS_ON_PAGE_LIMIT;

    public function __construct()
    {
        $this->filter = new ProductsFilter();
        parent::__construct();
    }

    public function isGroupedProductExist(int $offset) : bool
    {
        $query = "SELECT id
                    FROM sponsored_products 
                    WHERE groupId != 0 
                        LIMIT " . (int)$offset . ", " . ITEMS_ON_PAGE_LIMIT;
        $stmt = $this->db->query($query);
        $stmt->execute();
        return !is_null($stmt->fetchAll()) ? true : false;
    }

    /**
     * @return int
     */
    public function getSponsoredProductsCount() : int
    {
        $query = "SELECT COUNT(DISTINCT(asin)) FROM sponsored_products";
        $stmt = $this->db->query($this->filter->getItemsFilterQuery($query));
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @param int $groupId
     * @return array
     */
    public function getGroupedSponsoredProductsByGroupId(int $groupId) : array
    {
        $query = "SELECT id,
                         groupId,
                         name,
                         asin,
                         accountId,
                         productSynonym,
                         settings,
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minAcos')) AS 'minAcos',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minSales')) AS 'minSales',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minSpend')) AS 'minSpend',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minConversion')) AS 'minConversion',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.hideProduct')) AS 'hideProduct'
                    FROM sponsored_products
                        WHERE groupId = :groupId";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["groupId" => $groupId]);
        return $stmt->fetchAll();
    }

    /**
     * Вернет список все sponsored products пользователя без учета фильтров
     * TODO: внедрить лимиты для пакетной выборки
     * @return array
     */
    public function getUserSponsoredProductsList() : array
    {
        $query = "SELECT id,
                         groupId,
                         name,
                         asin,
                         accountId,
                         productSynonym,
                         settings,
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minAcos')) AS 'minAcos',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minSales')) AS 'minSales',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minSpend')) AS 'minSpend',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minConversion')) AS 'minConversion',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.hideProduct')) AS 'hideProduct'
                    FROM sponsored_products";
        $stmt = $this->db->query($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Вернет список sponsored products для пользователя.
     * @param int $offset
     * @return array
     */
    public function getSponsoredProducts(int $offset = 0) : array
    {
        $query = "SELECT id,
                         groupId,
                         name,
                         asin,
                         accountId,
                         productSynonym,
                         settings,
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minAcos')) AS 'minAcos',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minSales')) AS 'minSales',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minSpend')) AS 'minSpend',
                         JSON_UNQUOTE(JSON_EXTRACT(settings, '$.minConversion')) AS 'minConversion'
                    FROM sponsored_products";
        $stmt = $this->db->query($this->filter->getItemsFilterQuery($query, $offset));
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param array $asins
     * @return array
     */
    protected function prepareAsins(array $asins) : array
    {
        $db = $this->db;
        $asins = array_map(function($item) use ($db) {
            return  $db->quote($item);
        }, $asins);
        return $asins;
    }

    /**
     * @param array $asins
     * @param string $table
     * @return array
     */
    public function getStat(array $asins, string $table) : array
    {
        $query = "SELECT COALESCE(SUM(impressions), 0) AS impressions,
                         COALESCE(SUM(clicks), 0) AS clicks,
                         COALESCE(SUM(spend), 0) AS spend, 
                         COALESCE(SUM(sale), 0) AS sales, 
                         COALESCE(SUM(orders), 0) AS orders,
                         (SUM(spend) / SUM(sale)) * 100 AS avgAcos,
                         CASE 
                             WHEN SUM(clicks) = 0 
                                 THEN 0 
                             ELSE 
                                 SUM(spend) / SUM(clicks) 
                         END AS cpc,
                         CASE 
                             WHEN SUM(impressions) = 0 
                                 THEN 0 
                             ELSE 
                                 SUM(clicks) / SUM(impressions) * 100
                         END AS ctr,
                         asin,
                         date
                     FROM {$table}";
        $stmt = $this->db->query($this->filter->getItemsStatFilterQuery($query, $this->prepareAsins($asins)));
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
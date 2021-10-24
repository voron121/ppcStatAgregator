<?php


namespace Models\cabinet;

use Models\BaseModel;
use Models\cabinet\filters\AdsFilter;

class Ads extends BaseModel
{
    const TABLE = "sponsored_products_ads";

    public function __construct()
    {
        $this->filter = new AdsFilter();
        parent::__construct();
    }


    /**
     * @return int
     */
    public function getSponsoredProductsAdsCount() : int
    {
        $query = "SELECT COUNT(id) FROM sponsored_products_ads";
        $stmt = $this->db->query($this->filter->getItemsFilterQuery($query));
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Вернет список sponsored products ads для пользователя.
     * Массив индексирован по ид объявления
     * @param int $offset
     * @return array
     */
    public function getSponsoredProductsAdsByUser(int $offset = 0) : array
    {
        $query = "SELECT sponsored_products_ads.id,
                         sponsored_products_ads.adId,
                         sponsored_products_campaigns.Campaign as campaignName,
                         sponsored_products_ads.campaignId,
                         sponsored_products_ads.portfolioId,
                         sponsored_products_ads.accountId,
                         sponsored_products_ads.sku,   
                         sponsored_products_ads.asin,
                         sponsored_products_ads.status,
                         sponsored_products_ads.productName
                    FROM sponsored_products_ads
                        JOIN sponsored_products_campaigns USING (campaignId)";
        $stmt = $this->db->query($this->filter->getItemsFilterQuery($query, $offset));
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Вернет массив со статистикой для объявлений
     * @param array $adIds
     * @return array
     */
    public function getSponsoredProductsAdsStatByIds(array $adIds) : array
    {
        $adIds = array_map(function($item) {
            return (int) $item;
        },$adIds);

        $query = "SELECT sponsored_products_ads_stat.adId,
                         sponsored_products_ads_stat.campaignId,
                         sponsored_products_ads_stat.impressions,
                         sponsored_products_ads_stat.clicks,
                         sponsored_products_ads_stat.spend, 
                         sponsored_products_ads_stat.sale, 
                         sponsored_products_ads_stat.cpc,
                         sponsored_products_ads_stat.ctr,
                         sponsored_products_ads_stat.acos,
                         sponsored_products_ads_stat.sku,
                         sponsored_products_ads_stat.asin,
                         sponsored_products_ads_stat.`date`,
                         sponsored_products_ads.status
                     FROM sponsored_products_ads_stat
                        LEFT JOIN sponsored_products_ads USING(adId)";
        $stmt = $this->db->query($this->filter->getItemsStatFilterQuery($query, $adIds));
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
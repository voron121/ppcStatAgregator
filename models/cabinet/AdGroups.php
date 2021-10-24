<?php


namespace Models\cabinet;

use Models\BaseModel;
use Models\cabinet\filters\AdGroupsFilter;

class AdGroups extends BaseModel
{
    public function __construct()
    {
        $this->filter = new AdGroupsFilter();
        parent::__construct();
    }


    /**
     * @return int
     */
    public function getSponsoredProductsAdGroupsCount() : int
    {
        $query = "SELECT COUNT(id) FROM sponsored_products_ad_groups";
        $stmt = $this->db->query($this->filter->getItemsFilterQuery($query));
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Вернет список sponsored products ads groups.
     * @param int $offset
     * @return array
     */
    public function getSponsoredProductsAdGroups(int $offset = 0) : array
    {
        $query = "SELECT sponsored_products_ad_groups.id,
                         sponsored_products_ad_groups.adGroupId,
                         sponsored_products_campaigns.Campaign as campaignName,
                         sponsored_products_ad_groups.campaignId,
                         sponsored_products_ad_groups.portfolioId,
                         sponsored_products_ad_groups.accountId,
                         sponsored_products_ad_groups.adGroup,   
                         sponsored_products_ad_groups.maxBid,
                         sponsored_products_ad_groups.adGroupStatus,
                         sponsored_products_ads.adId AS adId
                    FROM sponsored_products_ad_groups
                        JOIN sponsored_products_campaigns USING (campaignId)
                        JOIN sponsored_products_ads ON sponsored_products_ads.campaignId = sponsored_products_ad_groups.campaignId";
        $stmt = $this->db->query($this->filter->getItemsFilterQuery($query, $offset));
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Вернет массив со статистикой для групп объявлений
     * @param array $adsIds
     * @return array
     */
    public function getSponsoredProductsAdGroupsStatByIds(array $adsIds) : array
    {
        $adsIds = array_map(function($item) {
            return (int) $item;
        },$adsIds);

        $query = "SELECT sponsored_products_ads_stat.adId, 
                         sponsored_products_ads_stat.campaignId, 
                         SUM(sponsored_products_ads_stat.impressions) AS impressions, 
                         SUM(sponsored_products_ads_stat.clicks) AS clicks, 
                         SUM(sponsored_products_ads_stat.spend) AS spend, 
                         SUM(sponsored_products_ads_stat.sale) AS sale, 
                         SUM(sponsored_products_ads_stat.cpc) AS cpc, 
                         SUM(sponsored_products_ads_stat.ctr) AS ctr, 
                         (SUM(sponsored_products_ads_stat.spend) / SUM(sponsored_products_ads_stat.sale)) * 100 AS avgAcos, 
                         sponsored_products_ads_stat.asin, sponsored_products_ads_stat.`date`,
                         sponsored_products_ad_groups.adGroupId AS adGroupId
                 FROM sponsored_products_ads_stat 
                    JOIN sponsored_products_ad_groups USING (campaignId)";
        //echo $this->filter->getItemsStatFilterQuery($query, $adsIds); die();
        $stmt = $this->db->query($this->filter->getItemsStatFilterQuery($query, $adsIds));
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
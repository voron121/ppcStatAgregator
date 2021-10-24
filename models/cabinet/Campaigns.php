<?php


namespace Models\cabinet;

use Models\cabinet\filters\CampaignsFilter;
use PPCSoft\Registry;
use Models\BaseModel;

class Campaigns extends BaseModel
{
    public $user = null;

    public function __construct()
    {
        $this->filter = new CampaignsFilter();
        $this->user = Registry::get("user");
        parent::__construct();
    }

    /**
     * @return int
     */
    public function getSponsoredProductsCampaignsCount() : int
    {
        $query = "SELECT COUNT(sponsored_products_campaigns.id) 
                    FROM sponsored_products_campaigns 
                        LEFT JOIN sponsored_products_ads USING(campaignId)";
        $stmt = $this->db->query($this->filter->getItemsFilterQuery($query));
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @param int $offset
     * @return array
     */
    public function getSponsoredProductsCampaigns(int $offset = 0) : array
    {
        $query = "SELECT sponsored_products_campaigns.id,
                         sponsored_products_campaigns.campaignId,
                         sponsored_products_campaigns.portfolioId,
                         sponsored_products_campaigns.accountId,
                         sponsored_products_campaigns.campaign,
                         sponsored_products_campaigns.campaignDailyBudget,
                         sponsored_products_campaigns.campaignStartDate,
                         sponsored_products_campaigns.campaignEndDate,
                         sponsored_products_campaigns.campaignTargetingType,
                         sponsored_products_campaigns.campaignStatus,
                         sponsored_products_campaigns.biddingStrategy,   
                         sponsored_products_ads.asin as asin
                    FROM sponsored_products_campaigns
                        LEFT JOIN sponsored_products_ads USING(campaignId)";
        $stmt = $this->db->query($this->filter->getItemsFilterQuery($query, $offset));
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Вернет массив со статистикой для кампаний
     * @param array $campaignIds
     * @return array
     */
    public function getSponsoredProductsCampaignsStatByIds(array $campaignIds) : array
    {
        $campaignIds = array_map(function ($item) {
            return (int)$item;
        }, $campaignIds);

        $query = "SELECT campaignId,
                         SUM(impressions) AS impressions,
                         SUM(clicks) AS clicks,
                         SUM(spend) AS spend,
                         SUM(sale) AS sales,
                         (SUM(spend) / SUM(sale)) * 100 AS avgAcos,
                         SUM(cpc) AS cpc,
                         SUM(ctr) AS ctr,
                         sponsored_products_ads_stat.asin,
                         sponsored_products_ads_stat.`date`
                     FROM sponsored_products_ads_stat";
        $stmt = $this->db->query($this->filter->getItemsStatFilterQuery($query, $campaignIds));
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
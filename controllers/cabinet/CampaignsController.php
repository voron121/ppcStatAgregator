<?php

namespace Controllers\cabinet;

use Controllers\BaseController;
use Models\cabinet\Campaigns;
use PPCSoft\Tools\CLITools;

class CampaignsController extends BaseController
{
    protected $campaigns = null;

    public function __construct()
    {
        $this->campaigns = new Campaigns();
        parent::__construct();
    }

    /**
     * @return array
     */
    public function getSponsoredProductsCampaigns() : array
    {
        $campaigns = $this->campaigns->getSponsoredProductsCampaigns($this->getOffset());
        if (empty($campaigns)) {
            $this->notification->putMessage("Кампании отсутствуют", "warning");
        }
        array_walk($campaigns, function (&$item) {
            $item->campaignStartDate = is_null($item->campaignStartDate) ? "-" : $item->campaignStartDate;
            $item->campaignEndDate = is_null($item->campaignEndDate) ? "-" : $item->campaignEndDate;
            $item->campaignDailyBudget = CLITools::convertIntToFloat($item->campaignDailyBudget);
        });
        return $campaigns;
    }

    /**
     * @return int
     */
    public function getSponsoredProductsCampaignsCount() :int
    {
        return $this->campaigns->getSponsoredProductsCampaignsCount();
    }

    /**
     * @param array $campaignIds
     * @return array
     */
    public function getSponsoredProductsCampaignsStatByIds(array $campaignIds) : array
    {
        $campaignStat = $this->campaigns->getSponsoredProductsCampaignsStatByIds($campaignIds);
        $stat = [];
        if (empty($campaignStat)) {
            $this->notification->putMessage("Статистика для кампаний отсутствует", "warning");
        } else {
            for ($i = 0; $i < count($campaignStat); $i++) {
                $stat[$campaignStat[$i]->campaignId][$campaignStat[$i]->date] = [
                    "impressions" => $campaignStat[$i]->impressions,
                    "clicks" => $campaignStat[$i]->clicks,
                    "spend" => CLITools::convertIntToFloat($campaignStat[$i]->spend),
                    "sales" => !is_null($campaignStat[$i]->sales) ? CLITools::convertIntToFloat($campaignStat[$i]->sales) : 0,
                    "acos" => !is_null($campaignStat[$i]->avgAcos) ? round($campaignStat[$i]->avgAcos, 2) : 100,
                    "cpc" => CLITools::convertIntToFloat($campaignStat[$i]->cpc),
                    "ctr" => CLITools::convertIntToFloat($campaignStat[$i]->ctr)
                ];
            }
        }
        return $stat;
    }

}
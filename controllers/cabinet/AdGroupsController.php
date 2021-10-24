<?php


namespace Controllers\cabinet;

use Controllers\BaseController;
use Models\cabinet\AdGroups;
use PPCSoft\Registry;
use PPCSoft\Tools\CLITools;

class AdGroupsController extends BaseController
{
    protected $adGroups = null;
    public $user = null;

    public function __construct()
    {
        $this->adGroups = new AdGroups();
        $this->user = Registry::get("user");
        parent::__construct();
    }

    /**
     * @return int
     */
    public function getSponsoredProductsAdGroupsCount() : int
    {
        return $this->adGroups->getSponsoredProductsAdGroupsCount();
    }

    /**
     * Вернет список объявлений для пользователя с разбивкой по страницам
     * @return array
     */
    public function getSponsoredProductsAdGroups() : array
    {
        $adGroups = $this->adGroups->getSponsoredProductsAdGroups($this->getOffset());
        if (empty($adGroups)) {
            $this->notification->putMessage("Группы не найдены", "warning");
        }
        return $adGroups;
    }

    /**
     * Вернет массив со статистикой
     * @return array
     */
    public function getSponsoredProductsAdGroupsStatByIds(array $adGroupsIds) : array
    {
        $stat = [];
        $adGroupsStat = $this->adGroups->getSponsoredProductsAdGroupsStatByIds($adGroupsIds);
        if (empty($adGroupsStat)) {
            $this->notification->putMessage("Статистика для групп объявлений отсутствует", "warning");
        } else {
            for ($i = 0; $i < count($adGroupsStat); $i++) {
                $stat[$adGroupsStat[$i]->adGroupId][$adGroupsStat[$i]->date] = [
                    "impressions" => $adGroupsStat[$i]->impressions,
                    "clicks" => $adGroupsStat[$i]->clicks,
                    "spend" => CLITools::convertIntToFloat($adGroupsStat[$i]->spend),
                    "sale" => CLITools::convertIntToFloat($adGroupsStat[$i]->sale),
                    "cpc" => CLITools::convertIntToFloat($adGroupsStat[$i]->cpc),
                    "ctr" => CLITools::convertIntToFloat($adGroupsStat[$i]->ctr),
                    "acos" => !is_null($adGroupsStat[$i]->avgAcos) ? round($adGroupsStat[$i]->avgAcos, 2) : 100
                ];
            }
        }
        return $stat;
    }
}
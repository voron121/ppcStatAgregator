<?php


namespace Controllers\cabinet;

use Controllers\BaseController;
use Models\cabinet\Ads;
use PPCSoft\Registry;
use PPCSoft\Tools\CLITools;

class AdsController extends BaseController
{
    protected $ads = null;
    public $user = null;

    public function __construct()
    {
        $this->ads = new Ads();
        $this->user = Registry::get("user");
        parent::__construct();
    }

    /**
     * @param int $userId
     * @return int
     */
    public function getSponsoredProductsAdsCount() : int
    {
        return $this->ads->getSponsoredProductsAdsCount();
    }

    /**
     * Вернет список объявлений для пользователя с разбивкой по страницам
     * @param int $userId
     * @return array
     */
    public function getSponsoredProductsAdsByUser() : array
    {
        $ads = $this->ads->getSponsoredProductsAdsByUser($this->getOffset());
        if (empty($ads)) {
            $this->notification->putMessage("Объявления не найдены", "warning");
        }
        return $ads;
    }

    /**
     * Вернет массив со статистикой
     * @return array
     */
    public function getSponsoredProductsAdsStatByIds(array $adIds) : array
    {
        $stat = [];
        $adsStat = $this->ads->getSponsoredProductsAdsStatByIds($adIds);
        if (empty($adsStat)) {
            $this->notification->putMessage("Статистика для объявлений отсутствует", "warning");
        } else {
            for ($i = 0; $i < count($adsStat); $i++) {
                $stat[$adsStat[$i]->campaignId][$adsStat[$i]->adId][$adsStat[$i]->date] = [
                    "impressions" => $adsStat[$i]->impressions,
                    "clicks" => $adsStat[$i]->clicks,
                    "spend" => CLITools::convertIntToFloat($adsStat[$i]->spend),
                    "sale" => CLITools::convertIntToFloat($adsStat[$i]->sale),
                    "cpc" => CLITools::convertIntToFloat($adsStat[$i]->cpc),
                    "ctr" => CLITools::convertIntToFloat($adsStat[$i]->ctr),
                    "acos" => $adsStat[$i]->acos != 0 ? CLITools::convertIntToFloat($adsStat[$i]->acos) : 100
                ];
            }
        }
        return $stat;
    }
}
<?php


namespace Controllers\cabinet;

use \Controllers\BaseController;
use Models\cabinet\Products;
use Models\cabinet\Product;
use Models\ProductsGroup;
use PPCSoft\Tools\CLITools;
use PPCSoft\Logger\Log;
use Models\Settings;
use Models\cabinet\filters\ProductsFilter;

class ProductsController extends BaseController
{
    protected $products = null;
    protected $product = null;
    protected $productGroup = null;
    protected $productSettings = null;
    protected $filter = null;
    protected $statTables = [
        "sponsored_brands_ads_stat",
        "sponsored_display_ads_stat",
        "sponsored_products_ads_stat",
        "sponsored_brands_video_ads_stat"
    ];

    public function __construct()
    {
        $this->products = new Products();
        $this->product = new Product();
        $this->productSettings = new Settings();
        $this->productGroup = new ProductsGroup();
        $this->filter = new ProductsFilter();
        parent::__construct();
    }

    /**
     * @param array $products
     * @return bool
     */
    protected function isGroupedProductsExist(array $products) : bool
    {
        $isGroupedProductsExist = false;
        for ($i = 0; $i < count($products); $i++) {
            if ($products[$i]->groupId != 0) {
                $isGroupedProductsExist = true;
                break;
            }
        }
        return $isGroupedProductsExist;
    }

    /**
     * Получит список товаров с учетом групп товаров и создаст структуру в виде
     * многоуровневого массива с группировкой товаров и групп в отдельных наборах
     * TODO: получить данные группы(название и параметры) и добавить в массив с группами в структуре данных items
     * TODO: не получать товары для группы. Количество групп берем по ключам. Товары нам не нужны в группе
     * TODO: разделить метод на 2: отдельно получать группы и отдельно получать товары. При этом логика дополучения товара будет прежней
     * @return array|array[]
     */
    protected function getStructuredSponsoredProductsWithGroups() : array
    {
        $items = ["products" => [], "groups" => []];
        $i = 0;
        do {
            $sponsoredProducts = $this->products->getSponsoredProducts($this->getOffset() + $i);
            if (empty($sponsoredProducts)) {
                break;
            }
            if ($this->isGroupedProductsExist($sponsoredProducts)) {
                // TODO: отрефакторить, есть паста и не оптимальные решения с циклами
                // Проверим есть ли в наборе товаров все товары из групп
                $groupedProductsByGroupId = [];
                foreach ($sponsoredProducts as $sponsoredProduct) {
                    if ($sponsoredProduct->groupId == 0) {
                        continue;
                    }
                    $groupedProductsByGroupId[$sponsoredProduct->groupId][] = $sponsoredProduct->asin;
                }
                // Если товаров в наборе данных не хватает для формирования групп - дополучим товары для групп
                foreach ($groupedProductsByGroupId as $groupId => $asins) {
                    $groupedProducts = $this->products->getGroupedSponsoredProductsByGroupId($groupId);
                    foreach ($groupedProducts as $groupedProduct) {
                        $group = $this->productGroup->find($groupId);
                        $items["groups"][$groupedProduct->groupId] = [
                            "name" => $group->getName() ? $group->getName() : "Группа " . $group->getId(),
                            "asins" => $group->getAsins(),
                            "settings" => $group->getSettings(),
                        ];
                    }
                }
            }
            foreach ($sponsoredProducts as $sponsoredProduct) {
                if ($sponsoredProduct->groupId == 0) {
                    $items["products"][$sponsoredProduct->id] = $sponsoredProduct;
                }
            }
            $i++;
            $itemsCount = count($items["products"]) + count($items["groups"]);
        } while($itemsCount < ITEMS_ON_PAGE_LIMIT);
        return $items;
    }

    /**
     * @return int
     */
    public function getSponsoredProductsCount() : int
    {
        return $this->products->getSponsoredProductsCount();
    }

    /**
     * @return array
     */
    public function getSponsoredItems() : array
    {
        $items = $this->getStructuredSponsoredProductsWithGroups();
        if (empty($items)) {
            $this->notification->putMessage("Товары и группы товаров отсутствуют", "warning");
        }
        return $items;
    }

    /**
     * TODO: подставить значение параметров по дефолту для товаров и групп
     * @return array
     */
    public function getSponsoredProducts() : array
    {
        $products = $this->products->getSponsoredProducts($this->getOffset());
        $generalProductSettings = $this->getGeneralProductSettings();
        if (empty($products)) {
            $this->notification->putMessage("Товары отсутствуют", "warning");
        }
        // TODO: подставить значение параметров по дефолту для товаров и групп
        /*
        array_walk($products, function(&$item) use ($generalProductSettings) {
            if (empty(json_decode($item->settings, true))) {
                $item->minAcos = $generalProductSettings["minAcos"];
                $item->minSales = $generalProductSettings["minSales"];
                $item->minSpend = $generalProductSettings["minSpend"];
                $item->minConversion = $generalProductSettings["minConversion"];
            }
            unset($item->settings);
        });
        */
        return $products;
    }

    /**
     * Вернет массив названий товаров пользователя
     * @return array
     */
    public function getUserSponsoredProductsNamesList() : array
    {
        $productsNames = [];
        $products  = $this->products->getUserSponsoredProductsList();
        array_walk($products, function ($item) use (&$productsNames) {
            $productsNames[$item->id] = [
                "name" => $item->name,
                "groupId" => $item->groupId,
                "hideProduct" => is_null($item->hideProduct) ? "no" : $item->hideProduct
            ];
        });
        return $productsNames;
    }

    /**
     * @param array $stat
     * @param object $statToSum
     * @param string $type
     * @param string $key
     * @return array
     */
    protected function sumStat(array $stat, object $statToSum, string $type, string $key) : array
    {
        if (isset($stat[$type][$key][$statToSum->date])) {
            $stat[$type][$key][$statToSum->date]["impressions"] += $statToSum->impressions;
            $stat[$type][$key][$statToSum->date]["clicks"] += $statToSum->clicks;
            $stat[$type][$key][$statToSum->date]["spend"] += $statToSum->spend;
            $stat[$type][$key][$statToSum->date]["sales"] += $statToSum->sales;
            $stat[$type][$key][$statToSum->date]["orders"] += $statToSum->orders;
        }  else {
            $stat[$type][$key][$statToSum->date] = [
                "impressions" => $statToSum->impressions,
                "clicks" => $statToSum->clicks,
                "spend" => $statToSum->spend,
                "sales" => $statToSum->sales,
                "orders" => $statToSum->orders,
            ];
        }
        return $stat;
    }

    /**
     * Сгруппирует статистику в массив по асину и дате
     * Если в массиве статистики есть данные которые нужно суммировать - просуммирует статистику
     * @param array $productsStat
     * @return array
     */
    protected function groupStat(array $productsStat, array $groups) : array
    {
        $asinsToGroupId = [];
        $stat = ["products" => [], "groups" => []];
        array_walk($groups, function($item, $groupId) use (&$asinsToGroupId) {
            $asins = explode(",", $item["asins"]);
            foreach ($asins as $asin) {
                $asinsToGroupId[$asin] = $groupId;
            }
        });
        for ($i = 0; $i < count($productsStat); $i++) {
            $type = isset($asinsToGroupId[$productsStat[$i]->asin]) ? "groups" : "products";
            $key = $asinsToGroupId[$productsStat[$i]->asin] ?? $productsStat[$i]->asin;
            $stat = $this->sumStat($stat, $productsStat[$i], $type, $key);
        }
        return $stat;
    }

    /**
     * Расчитает cps ctr acos для статистики и добавит их в массив с данными статистики
     * TODO: паста в расчете параметров. Унифицировать до одного метода
     * @param array $stat
     * @return array
     */
    protected function calculatStatAdvertisingIndicators(array $stat) : array
    {
        array_walk($stat["groups"], function(&$statData) {
            foreach($statData as $date => &$item) {
                $item["acos"] = (int)$item["sales"] === 0 ? 0 : round(($item["spend"] / $item["sales"]) * 100, 2);
                $item["cpc"] = (int)$item["clicks"] === 0 ? 0 : round(($item["spend"] / $item["clicks"]), 2);
                $item["ctr"] = (int)$item["impressions"] === 0 ? 0 : round(($item["clicks"] / $item["impressions"]) * 100, 2);
                $item["conversion"] = CLITools::calculateConversion($item["orders"], $item["clicks"]);
            }
        });
        array_walk($stat["products"], function(&$statData) {
            foreach($statData as $date => &$item) {
                $item["acos"] = (int)$item["sales"] === 0 ? 0 : round(($item["spend"] / $item["sales"]) * 100, 2);
                $item["cpc"] = (int)$item["clicks"] === 0 ? 0 : round(($item["spend"] / $item["clicks"]), 2);
                $item["ctr"] = (int)$item["impressions"] === 0 ? 0 : round(($item["clicks"] / $item["impressions"]) * 100, 2);
                $item["conversion"] = CLITools::calculateConversion($item["orders"], $item["clicks"]);
            }
        });
        return $stat;
    }

    /**
     * Подготовит даннные со статистикой для рендера во вьюхе
     * TODO: паста в расчете параметров. Унифицировать до одного метода
     * @param array $stat
     * @return array
     */
    protected function prepareStatForOutput(array $stat) : array
    {
        $stat = $this->calculatStatAdvertisingIndicators($stat);
        array_walk($stat["groups"], function(&$statData) {
            foreach($statData as $date => &$item) {
                $item["spend"] = CLITools::convertIntToFloat($item["spend"]);
                $item["sales"] = CLITools::convertIntToFloat($item["sales"]);
                $item["cpc"] = CLITools::convertIntToFloat($item["cpc"], 2);
            }
        });
        array_walk($stat["products"], function(&$statData) {
            foreach($statData as $date => &$item) {
                $item["spend"] = CLITools::convertIntToFloat($item["spend"]);
                $item["sales"] = CLITools::convertIntToFloat($item["sales"]);
                $item["cpc"] = CLITools::convertIntToFloat($item["cpc"], 2);
            }
        });
        return $stat;
    }

    /**
     * @param array $asins
     * @return array
     */
    protected function getAggregatedStat(array $asins) : array
    {
        $stat = [];
        foreach ($this->statTables as $table) {
            $stat = array_merge($stat, $this->products->getStat($asins, $table));
        }
        return $stat;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function getAsinsForStat(array $items) : array
    {
        $asins = [];
        if (!empty($items["groups"])) {
            array_walk($items["groups"], function($item) use (&$asins) {
                $asins = array_merge($asins, explode(",",$item["asins"]));
            });
        }
        if (!empty($items["products"])) {
            foreach ($items["products"] as $productId => $product) {
                $asins[] = $product->asin;
            }
        }
        return array_flip(array_flip($asins));
    }

    /**
     * @param array $items
     * @return array
     */
    public function getStat(array $items) : array
    {
        $asins = $this->getAsinsForStat($items);
        $filterMode = $this->filter->getStatTable();
        if ("all" === $filterMode) {
            $stat = $this->groupStat($this->getAggregatedStat($asins), $items["groups"]);
        } else {
            $stat = $this->groupStat($this->products->getStat($asins, $filterMode), $items["groups"]);
        }
        if (empty($stat)) {
            $this->notification->putMessage("Статистика для товаров отсутствует", "warning");
        }
        return $this->prepareStatForOutput($stat);
    }

    /**
     * @param int $productId
     * @return Products
     */
    public function getSponsoredProductById(int $productId) : Product
    {
        $product = $this->product->getSponsoredProductById($productId);
        // Если у товара нет параметров назначим товару параметры из глобальных настроек товара
        if (empty($product->getSettings())) {
            $generalProductSettings = $this->getGeneralProductSettings();
            $product->setSettings(json_encode($generalProductSettings));
        }
        return $product;
    }

    /**
     * Вернет массив с глобальными параметрами для товаров
     * @return array
     */
    public function getGeneralProductSettings() : array
    {
        return $this->productSettings->getGeneralProductSettings();
    }

    /**
     * @param array $product
     */
    public function saveSponsoredProduct(array $productData) : void
    {
        try {
            $productData = $this->prepareInput($productData);
            $product = $this->product->getSponsoredProductById($productData["productId"]);
            $product->setProductSynonym($productData["productSynonym"]);
            $product->setSettings(json_encode($productData["settings"], JSON_NUMERIC_CHECK));
            $product->save();
        } catch (\Throwable $e) {
            $this->ajaxNotification->putMessage("Ошибка сохранения настроек товара!" , "alert");
            Log::write("alert", $e->getMessage(), ["level" => "file", "exception" => $e]);
        }
    }

    /**
     * @param array $product
     */
    public function saveSponsoredProductGeneralSettings(array $productData) : void
    {
        try {
            $productData = $this->prepareInput($productData["generalSettings"]);
            $this->productSettings->setGeneralProductSettings(json_encode($productData, JSON_NUMERIC_CHECK));
        } catch (\Throwable $e) {
            $this->ajaxNotification->putMessage("Ошибка сохранения настроек товара!" , "alert");
            Log::write("alert", $e->getMessage(), ["level" => "file", "exception" => $e]);
        }
    }
}
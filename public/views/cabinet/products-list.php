<?php
use \PPCSoft\Tools\Tools;
use \Models\cabinet\filters\ProductsFilter;

$items = $data["items"];
$stats = $data["stat"];
$dates = $data["dates"];
$filter = new ProductsFilter();
?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.24/fc-3.3.2/fh-3.1.8/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.24/fc-3.3.2/fh-3.1.8/datatables.min.js"></script>
<div class="container-fluid">
    <h1>
        Список товаров
        <a href="#"
           class="btn btn-sm btn-light"
           data-toggle="modal"
           data-param="generalProductSettings"
           data-target="#productSetting">
            <i class="fa fa-cog"></i></a>
        </a>
    </h1>
    <?php if(!empty($items)):?>
        <?php require_once __DIR__ . "/../filters/products-filter.php";?>
        <div class="table-wraper">
        <div class="btn btn-sm btn-secondary" id="groupProduct">Объединить товары</div>
        <table id="products-list" class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th scope="col"></td>
                    <th scope="col" class="product-info">Товар:</th>
                    <?php if($filter->isDateFilterExist()):?>
                        <th scope="col">Impressions:</td>
                        <th scope="col">Clicks:</td>
                        <th scope="col">CPC:</td>
                        <th scope="col">CTR:</td>
                        <th scope="col">Spend:</td>
                        <th scope="col">Sales:</td>
                        <th scope="col">Conversion:</td>
                        <th scope="col">Orders:</td>
                        <th scope="col">ACoS:</td>
                        </th>
                    <?php else:?>
                        <?php foreach($dates as $date):?>
                            <th scope="col">
                                <div class="ad-stat-date-wrapper"><?=Tools::humanizedDate($date)?></div>
                            </th>
                        <?php endforeach;?>
                    <?php endif;?>
                </tr>
            </thead>
            <tbody>

            <?php if(isset($items["groups"])):?>
                <?php foreach ($items["groups"] as $groupId => $groupData):?>
                    <tr>
                        <td>

                        </td>
                        <td>
                            <div class="ad-snippet">
                                <div class="float-right">
                                    <a href="#"
                                       class="btn btn-sm btn-settings-light groupSetting"
                                       data-group-id="<?=$groupId;?>"
                                       data-toggle="modal"
                                       data-param="groupSetting"
                                       data-target="#groupSetting">
                                        <i class="fa fa-cog"></i>
                                    </a>
                                </div>
                                <?=$groupData["name"];?><br>
                                <p>
                                    <b>ASIN'S: </b>
                                    <?php foreach(explode(",", $groupData["asins"]) as $asin):?>
                                        <a href="https://amazon.com/dp/<?=$asin;?>" target="blank"><?=$asin;?></a>
                                    <?php endforeach;?>
                                </p>
                            </div>
                        </td>
                        <!-- Статитсика для групп -->
                            <?php if($filter->isDateFilterExist()):?>
                                <?php $statItem = array_values($stats["groups"][$groupId]); ?>
                                <td><?=$statItem[0]["impressions"];?></td>
                                <td><?=$statItem[0]["clicks"];?></td>
                                <td><?=$statItem[0]["cpc"];?></td>
                                <td><?=$statItem[0]["ctr"];?></td>
                                <td><?=$statItem[0]["spend"];?></td>
                                <td><?=$statItem[0]["sales"];?></td>
                                <td><?=$statItem[0]["conversion"];?></td>
                                <td><?=$statItem[0]["orders"];?></td>
                                <td class="<?=Tools::getAcosCSSLabel($product->minAcos, $statItem[0]["acos"])?>"><?=$statItem[0]["acos"];?>%</td>
                            <?php else:?>
                                <?php foreach($dates as $date):?>
                                    <td>
                                        <?php if(isset($stats["groups"][$groupId][$date->format("Y-m-d")])):?>
                                            <?php $statItem = $stats["groups"][$groupId][$date->format("Y-m-d")]; ?>
                                            <table class="table table-sm table-striped table-bordered product-daily-stat-table">
                                                <tr>
                                                    <td>Impressions:</td>
                                                    <td><?=$statItem["impressions"];?></td>
                                                    <td>Clicks:</td>
                                                    <td><?=$statItem["clicks"];?></td>
                                                </tr>
                                                <tr>
                                                    <td>CPC:</td>
                                                    <td><?=$statItem["cpc"];?> $</td>
                                                    <td>CTR:</td>
                                                    <td><?=$statItem["ctr"];?> %</td>
                                                </tr>
                                                <tr>
                                                    <td>Spend:</td>
                                                    <td><?=$statItem["spend"];?> $</td>
                                                    <td>Sales:</td>
                                                    <td><?=$statItem["sales"];?> $</td>
                                                </tr>
                                                <tr>
                                                    <td>Conversion:</td>
                                                    <td><?=$statItem["conversion"];?></td>
                                                    <td>Orders:</td>
                                                    <td><?=$statItem["orders"];?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2" class="<?=Tools::getAcosCSSLabel($product->minAcos, $statItem["acos"])?>">ACoS:</td>
                                                    <td colspan="2"class="<?=Tools::getAcosCSSLabel($product->minAcos, $statItem["acos"])?>"><?=$statItem["acos"];?> %</td>
                                                </tr>
                                            </table>
                                        <?php else:?>
                                            <div class="span">-</div>
                                        <?php endif;?>
                                    </td>
                                <?php endforeach;?>
                            <?php endif;?>
                        <!-- Статитсика для групп -->
                    </tr>
                <?php endforeach;?>
            <?php endif;?>

            <?php if(isset($items["products"])):?>
                <?php foreach ($items["products"] as $productId => $product):?>
                    <tr>
                        <td>
                            <div class="text-center">
                                <input type="checkbox" name="groupProduct" value="<?=$product->id;?>" >
                            </div>
                        </td>
                        <td>
                            <div class="ad-snippet">
                                <div class="float-right">
                                    <a href="#"
                                       class="btn btn-sm btn-settings-light productSetting"
                                       data-product-id="<?=$product->id;?>"
                                       data-toggle="modal"
                                       data-param="productSettings"
                                       data-target="#productSetting">
                                        <i class="fa fa-cog"></i>
                                    </a>
                                </div>
                                <div>
                                    <a href="/campaigns-list.php?asin=<?=$product->asin;?>"><?=!empty($product->productSynonym) ? $product->productSynonym : $product->name;?></a>
                                </div>
                                <p><b>ASIN: </b> <a href="https://amazon.com/dp/<?=$product->asin;?>" target="blank"><?=$product->asin;?></a></p>
                                <p><b>Аккаунт: </b><?=$product->accountId;?></p>
                            </div>
                        </td>
                        <!-- Статистика товаров -->
                        <?php if($filter->isDateFilterExist()):?>
                        <?php if(!is_null($stats["products"][$product->asin])):?>
                            <?php $statItem = array_values($stats["products"][$product->asin]); ?>
                            <td><?=$statItem[0]["impressions"];?></td>
                            <td><?=$statItem[0]["clicks"];?></td>
                            <td><?=$statItem[0]["cpc"];?></td>
                            <td><?=$statItem[0]["ctr"];?></td>
                            <td><?=$statItem[0]["spend"];?></td>
                            <td><?=$statItem[0]["sales"];?></td>
                            <td><?=$statItem[0]["conversion"];?></td>
                            <td><?=$statItem[0]["orders"];?></td>
                            <td class="<?=Tools::getAcosCSSLabel($product->minAcos, $statItem[0]["acos"])?>"><?=$statItem[0]["acos"];?>%</td>
                        <?php endif;?>
                        <?php else:?>
                            <?php foreach($dates as $date):?>
                                <td>
                                    <?php if(isset($stats["products"][$product->asin][$date->format("Y-m-d")])):?>
                                        <?php $statItem = $stats["products"][$product->asin][$date->format("Y-m-d")]; ?>
                                        <table class="table table-sm table-striped table-bordered product-daily-stat-table">
                                            <tr>
                                                <td>Impressions:</td>
                                                <td><?=$statItem["impressions"];?></td>
                                                <td>Clicks:</td>
                                                <td><?=$statItem["clicks"];?></td>
                                            </tr>
                                            <tr>
                                                <td>CPC:</td>
                                                <td><?=$statItem["cpc"];?> $</td>
                                                <td>CTR:</td>
                                                <td><?=$statItem["ctr"];?> %</td>
                                            </tr>
                                            <tr>
                                                <td>Spend:</td>
                                                <td><?=$statItem["spend"];?> $</td>
                                                <td>Sales:</td>
                                                <td><?=$statItem["sales"];?> $</td>
                                            </tr>
                                            <tr>
                                                <td>Conversion:</td>
                                                <td><?=$statItem["conversion"];?></td>
                                                <td>Orders:</td>
                                                <td><?=$statItem["orders"];?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="<?=Tools::getAcosCSSLabel($product->minAcos, $statItem["acos"])?>">ACoS:</td>
                                                <td colspan="2" class="<?=Tools::getAcosCSSLabel($product->minAcos, $statItem["acos"])?>"><?=$statItem["acos"];?> %</td>
                                            </tr>
                                        </table>
                                    <?php else:?>
                                        <div class="span">-</div>
                                    <?php endif;?>
                                </td>
                            <?php endforeach;?>
                        <?php endif;?>
                        <!-- Статистика товаров -->
                    </tr>
                <?php endforeach;?>
            <?php endif;?>
            </tbody>
        </table>
        </div>
    <?php endif;?>
</div>

<!-- Modal group settings-->
<div class="modal fade" id="groupSetting" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered mt--100" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Настройки группы</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container ajaxNotification p-0"></div>
                <div class="container p-0">
                    <input type="hidden" name="groupId">
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="groupName">Название группы:</label>
                                <input type="text" name="groupName" id="groupName" class="form-control form-control-sm" placeholder="Название группы:">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="minAcos">Минимальное допустимый Acos:</label>
                                <input type="number" name="minAcos" id="minAcos" class="form-control form-control-sm" min="0" placeholder="Минимальное допустимое значение для Acos:">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="minSpend">Минимальное допустимый spend:</label>
                                <input type="number" name="minSpend" id="minSpend" class="form-control form-control-sm" min="0" placeholder="Минимальное допустимое значение для spend:">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="minSales">Минимальное допустимый sales:</label>
                                <input type="number" name="minSales" id="minSales" class="form-control form-control-sm" min="0"placeholder="Минимальное допустимое значение для sales:">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="minConversion">Минимальная конверсия:</label>
                                <input type="number" name="minConversion" id="minConversion" class="form-control form-control-sm" min="0" placeholder="Минимальная конверсия:">
                            </div>
                        </div>
                    </div>
                    <div class="groupProductList pr-1"></div>
                    <div class="additionalProductSettings">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-warning" id="ungroupProducts">Разгруппировать товары</button>
                <button type="button" class="btn btn-sm btn-success" id="groupSettingsSave">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal product settings-->
<div class="modal fade" id="productSetting" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered mt--100" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Настройки</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body productModal">
                <div class="container ajaxNotification p-0"></div>
                <div class="container p-0">
                    <input type="hidden" name="productId">
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="productSynonym">Псевдоним товара:</label>
                                <input type="text" name="productSynonym" id="productSynonym" class="form-control form-control-sm" placeholder="Кастомное название товара:">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="minAcos">Минимальное допустимый Acos:</label>
                                <input type="number" name="minAcos" id="minAcos" class="form-control form-control-sm" min="0" placeholder="Минимальное допустимое значение для Acos:">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="minSpend">Минимальное допустимый spend:</label>
                                <input type="number" name="minSpend" id="minSpend" class="form-control form-control-sm" min="0" placeholder="Минимальное допустимое значение для spend:">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="minSales">Минимальное допустимый sales:</label>
                                <input type="number" name="minSales" id="minSales" class="form-control form-control-sm" min="0"placeholder="Минимальное допустимое значение для sales:">
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="minConversion">Минимальная конверсия:</label>
                                <input type="number" name="minConversion" id="minConversion" class="form-control form-control-sm" min="0" placeholder="Минимальная конверсия:">
                            </div>
                        </div>
                    </div>
                    <div class="productList pr-1"></div>
                    <div class="additionalProductSettings">
                        <hr>
                        <div class="row">
                            <div class="col-sm-2">
                                <span>Скрыть товар: </span>
                            </div>
                            <div class="col-sm-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="hideProduct" class="custom-control-input" id="hiddeProduct">
                                    <label class="custom-control-label" for="hiddeProduct"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-success" id="productSettingsSave">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script src="/../views/js/products.js">></script>
<script src="/../views/js/productGroups.js">></script>

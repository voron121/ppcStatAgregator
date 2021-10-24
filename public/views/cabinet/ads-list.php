<?php
use \PPCSoft\Tools\Tools;

$ads = $data["ads"];
$stats = $data["stat"];
$dates = $data["dates"];
?>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.24/fc-3.3.2/fh-3.1.8/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.24/fc-3.3.2/fh-3.1.8/datatables.min.js"></script>
<div class="container-fluid">
    <h1>Список объявлений</h1>
    <?php if(!empty($ads)):?>
        <?php require_once __DIR__ . "/../filters/ads-filter.php";?>
        <div class="table-wraper">
        <table id="ads-list" class="table table-bordered" >
            <thead>
                <tr>
                    <th scope="col" class="product-info">Объявление</th>
                    <?php foreach($dates as $date):?>
                        <th scope="col">
                            <div class="ad-stat-date-wrapper"><?=Tools::humanizedDate($date)?></div>
                        </th>
                    <?php endforeach;?>
                </tr>
            </thead>
            <tbody>
            <?php foreach($ads as $ad):?>
                <tr>
                    <td>
                        <div class="ad-snippet">
                            <p><?=$ad->productName;?></p>
                            <p><b>Ид объявления:</b> <?=$ad->adId;?></p>
                            <p><b>sku:</b> <?=$ad->sku;?></p>
                            <p><b>Статус:</b> <?=$ad->status;?></p>
                            <p><b>Кампания:</b> <?=$ad->campaignName;?> (<?=$ad->campaignId;?>)</p>
                        </div>
                    </td>
                    <?php foreach($dates as $date):?>
                        <td>
                            <?php if(isset($stats[$ad->campaignId][$ad->adId][$date->format("Y-m-d")])):?>
                            <?php $statItem = $stats[$ad->campaignId][$ad->adId][$date->format("Y-m-d")]; ?>
                                <table class="table table-striped table-bordered">
                                    <tr>
                                        <td>Impressions:</td>
                                        <td><?=$statItem["impressions"];?></td>
                                        <td>Clicks:</td>
                                        <td><?=$statItem["clicks"];?></td>
                                    </tr>
                                    <tr>
                                        <td>Spend:</td>
                                        <td><?=$statItem["spend"];?></td>
                                        <td>Sale:</td>
                                        <td><?=$statItem["sale"];?></td>
                                    </tr>
                                    <tr>
                                        <td>CTR:</td>
                                        <td><?=$statItem["ctr"];?></td>
                                        <td>CPC:</td>
                                        <td><?=$statItem["cpc"];?></td>
                                    </tr>
                                    <tr>
                                        <td>ACoS:</td>
                                        <td><?=$statItem["acos"];?>%</td>
                                    </tr>
                                </table>
                            <?php else:?>
                                <div class="span">-</div>
                            <?php endif;?>
                        </td>
                    <?php endforeach;?>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
        </div>
    <?php endif;?>
</div>
<script src="/../views/js/ads.js"></script>

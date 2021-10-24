<?php
use Models\cabinet\filters\ProductsFilter;
$filter = new ProductsFilter();
$minDate = $filter->getSponsoredProductsMinStatDate();
$maxDate = $filter->getSponsoredProductsMaxStatDate();
?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<div class="filter-wraper">
    <div class="container-fluid">
        <div class="row" id="additionalFilter"></div>
    </div>
    <form action="<?=$filter->getFilterUrl()?>" method="get">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm">
                    <label class="mb-1">Аккаунт:</label>
                    <select name="account" class="form-control form-control-sm">
                        <option value="0">не выбрано</option>
                        <?php foreach ($filter->getAccounts() as $account):?>
                            <option value="<?=$account->accountId;?>" <?=isset($_GET["account"]) && $_GET["account"] === $account->accountId ? "selected" : "" ?>>
                                <?=$account->accountId;?>
                            </option>
                        <?php endforeach;?>
                    </select>
                </div>

                <div class="col-sm">
                    <label class="mb-1">ASIN:</label>
                    <select name="asin" class="form-control form-control-sm">
                        <option value="0">не выбрано</option>
                        <?php foreach ($filter->getAsins() as $asin):?>
                            <option value="<?=$asin->asin;?>" <?=isset($_GET["asin"]) && $_GET["asin"] === $asin->asin ? "selected" : "" ?>>
                                <?=$asin->asin;?>
                            </option>
                        <?php endforeach;?>
                    </select>
                </div>

                <div class="col-sm">
                    <label class="mb-1">Тип кампаний:</label>
                    <select name="campaignsType" class="form-control form-control-sm">
                        <option value="all">Все</option>
                        <?php foreach ($filter->getCampaignsType() as $campaignsName => $campaignsType):?>
                            <option value="<?=$campaignsType;?>" <?=isset($_GET["campaignsType"]) && $_GET["campaignsType"] === $campaignsType ? "selected" : "" ?>>
                                <?=$campaignsName;?>
                            </option>
                        <?php endforeach;?>
                    </select>
                </div>

                <div class="col-sm">
                    <label class="mb-1">Дополнительные фильтры:</label>
                    <select name="additionalFilter" class="form-control form-control-sm">
                        <option value="0">не выбрано</option>
                        <?php foreach ($filter->getAdditionalFilters() as $additionalFilter => $additionalFilterName):?>
                            <option value="<?=$additionalFilter;?>">
                                <?=$additionalFilterName;?>
                            </option>
                        <?php endforeach;?>
                    </select>
                </div>

                <div class="col-sm">
                    <label class="mb-1">Дата:</label>
                    <div>
                        <div class="btn btn-sm btn-secondary filter-date-range" id="filter-date-range">Не указано</div>
                        <div class="date-range-modal" id="date-range-modal">
                            <input type="text" name="startDate" value="<?=isset($_GET["startDate"]) ? $_GET["startDate"] : 0;?>">
                            <input type="text" name="endDate" value="<?=isset($_GET["endDate"]) ? $_GET["endDate"] : 0;?>">
                            <input type="text" name="minStatDate" value="<?=$minDate->format("m/d/Y");?>">
                            <input type="text" name="maxStatDate" value="<?=$maxDate->format("m/d/Y");?>">
                        </div>
                        <input type="submit" value="Применить фильтр" class="btn btn-sm btn-warning ml-2">
                    </div>
                </div>
            </div>
            <div class="row"><div class="col-sm pt-3" id="additionalFilterParams"></div></div>
        </div>
    </form>
</div>
<script src="/../views/js/filter.js"></script>
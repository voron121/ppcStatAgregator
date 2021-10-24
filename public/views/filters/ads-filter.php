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
                    <label class="mb-1">Статус:</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="0">не выбрано</option>
                        <?php foreach ($filter->getStatuses() as $status):?>
                            <option value="<?=$status;?>" <?=isset($_GET["status"]) && $_GET["status"] === $status ? "selected" : "" ?>>
                                <?=$status;?>
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
                <!--
                <div class="col-sm">
                    <label class="mb-1">Дата:</label>
                    <div>
                        <div class="btn btn-sm btn-secondary filter-date-range" id="filter-date-range">Не указано</div>
                        <div class="date-range-modal" id="date-range-modal">
                            <input type="text" name="startDate" value="<?=isset($_GET["startDate"]) ? $_GET["startDate"] : 0;?>">
                            <input type="text" name="endDate" value="<?=isset($_GET["endDate"]) ? $_GET["endDate"] : 0;?>">
                            <div class="minStatDate" date="<?=$minDate->format("d/m/Y")?>"></div>
                            <div class="maxStatDate" date="<?=$maxDate->format("d/m/Y")?>"></div>
                        </div>
                    </div>
                </div>
                -->
                <div class="col-sm align-self-end">
                    <input type="submit" value="применить" class="btn btn-sm btn-warning">
                </div>
            </div>
            <div class="row"><div class="col-sm pt-3" id="additionalFilterParams"></div></div>
        </div>
    </form>
</div>
<script src="/../views/js/filter.js"></script>
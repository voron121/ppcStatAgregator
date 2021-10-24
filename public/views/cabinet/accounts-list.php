<?php
$accounts = $data["accounts"];
?>

<div class="container p-0">
    <h1>Список аккаунтов</h1>
    <div class="d-flex justify-content-end p-0 mb-3 align-self-end">
        <a href="/account.php" class="btn btn-sm btn-warning"><i class="fa fa-plus"></i> Добавить</a>
    </div>
    <?php if(!empty($accounts)):?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col" class="product-info">Аккаунт:</th>
                    <th scope="col" class="product-info">Email:</th>
                    <th scope="col" class="product-info">Статус:</th>
                    <th scope="col" class="product-info">Действие:</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($accounts as $account):?>
                    <tr>
                        <td>
                            <a href="/products-list.php?account=<?=$account->getAccountId();?>"><?=$account->getAccountId();?></a>
                        </td>
                        <td>
                            <?=$account->getEmail();?>
                        </td>
                        <td class="text-center">
                            <?php if($account->getIsError()):?>
                                <i class="text-danger fa fa-exclamation-triangle"></i>
                            <?php else:?>
                                <i class="text-success fa fa-check-circle"></i>
                            <?php endif;?>
                        </td>
                        <td> <!--
                            <a href="/accounts-list.php?action=delete&id=<?=$account->getId;?>" class="btn btn-sm btn-secondary">
                                <i class="fa fa-times"></i> Удалить
                            </a>
                            -->
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php endif;?>
</div>
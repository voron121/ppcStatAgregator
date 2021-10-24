<?php
$accountId = isset($data["formInput"]["accountId"]) ? $data["formInput"]["accountId"] : "";
$shopName = isset($data["formInput"]["shopName"]) ? $data["formInput"]["shopName"] : "";
$accountName = isset($data["formInput"]["accountName"]) ? $data["formInput"]["accountName"] : "";
$accountLogin = isset($data["formInput"]["accountLogin"]) ? $data["formInput"]["accountLogin"] : "";
$accountPassword = isset($data["formInput"]["accountPassword"]) ? $data["formInput"]["accountPassword"] : "";
$scLoginId = isset($data["formInput"]["scloginid"]) ? $data["formInput"]["scloginid"] : "";
$shopUrl = isset($data["formInput"]["shopUrl"]) ? $data["formInput"]["shopUrl"] : "";
?>

<div class="container p-0">
    <h2>Аккаунт</h2>
    <form action="/account.php?action=add" method="post">
        <div class="form-group">
            <label for="accountId">
                Account ID:
            </label>
            <input type="text" name="accountId" id="accountId" class="form-control  form-control-sm" placeholder="Account Id:" value="<?=$accountId;?>">
        </div>
        <div class="form-group">
            <label for="scloginid">Ссылка на страницу генерации otp кода:</label>
            <input type="text" name="scloginid" id="scloginid" class="form-control  form-control-sm" placeholder="Ссылка на страницу генерации otp кода:" value="<?=$scLoginId;?>">
        </div>
        <div class="form-group">
            <label for="shopUrl">Ссылка на страницу магазина:</label>
            <input type="text" name="shopUrl" id="scloginid" class="form-control  form-control-sm" placeholder="Ссылка на страницу магазина:" value="<?=$shopUrl;?>">
        </div>
        <div class="form-group">
            <label for="accountLogin">Email аккаунта:</label>
            <input type="text" name="accountLogin" id="accountLogin" class="form-control form-control-sm" placeholder="Email аккаунта:" value="<?=$accountLogin;?>">
        </div>
        <div class="form-group">
            <label for="accountPassword">Пароль аккаунта:</label>
            <input type="text" name="accountPassword" id="accountPassword" class="form-control form-control-sm" placeholder="Пароль аккаунта:" value="<?=$accountPassword;?>">
        </div>
        <div class="form-group">
            <input type="submit" value="Сохранить" class="btn btn-sm btn-success">
        </div>
    </form>
</div>
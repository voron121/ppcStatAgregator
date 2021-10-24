<?php
$login = "";
$password = "";
if (isset($data["formInput"])) {
    $login = $data["formInput"]["login"];
    $password = $data["formInput"]["password"];
}
?>
<div class="container p-o">
    <div class="d-flex justify-content-center">
        <div class="auth-wraper">
            <h2>Авторизация</h2>
            <hr>
            <form action="/auth.php" method="post">
                <div class="form-group">
                    <label for="login">Ваш логин:</label>
                    <input type="text" name="login" id="login" class="form-control" placeholder="Ваш логин:" value="<?=$login?>">
                </div>
                <div class="form-group">
                    <label for="password">Ваш пароль:</label>
                    <input type="text" name="password" id="password" class="form-control" placeholder="Ваш пароль:" value="<?=$password?>">
                </div>
                <div class="form-group justify-content-end">
                    <input type="submit" class="btn btn-success float-right">
                </div>
            </form>
        </div>
    </div>
</div>
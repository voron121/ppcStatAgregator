<?php
$login = "";
$password = "";
$password2 = "";
$email = "";
if (isset($data["formInput"])) {
    $login = $data["formInput"]["login"];
    $password = $data["formInput"]["password"];
    $password2 = $data["formInput"]["password2"];
    $email = $data["formInput"]["email"];
}
?>
<div class="container p-o">
    <div class="d-flex justify-content-center">
        <div class="auth-wraper">
            <h2>Регистрация</h2>
            <hr>
            <form action="/registration.php" method="post">
                <div class="form-group">
                    <label for="login">Придумайте логин:</label>
                    <input type="text" name="login" id="login" class="form-control" placeholder="Придумайте логин:" value="<?=$login?>">
                </div>
                <div class="form-group">
                    <label for="password">Придумайте пароль:</label>
                    <input type="text" name="password" id="password" class="form-control" placeholder="Придумайте пароль:" value="<?=$password?>">
                </div>
                <div class="form-group">
                    <label for="password2">Повторите пароль:</label>
                    <input type="text" name="password2" id="password2" class="form-control" placeholder="Повторите пароль:" value="<?=$password2?>">
                </div>
                <div class="form-group">
                    <label for="email">Ваш email:</label>
                    <input type="text" name="email" id="email" class="form-control" placeholder="Ваш email:" value="<?=$email?>">
                </div>
                <div class="form-group justify-content-end">
                    <input type="submit" class="btn btn-success float-right">
                </div>
            </form>
        </div>
    </div>
</div>



<div class="col-md-4">

</div>
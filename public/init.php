<?php

use PPCSoft\Registry;
use PPCSoft\Logger\Log;
use Models\User;
use \PPCSoft\Tools\Tools;

include __DIR__ . "/../vendor/autoload.php";

try {
    $authDB = new PDO('mysql:host=' . AUTH_DB_HOST . ';dbname=' . AUTH_DB_NAME . ';charset=utf8', AUTH_DB_USER, AUTH_DB_PASSWORD);
    $authDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $authDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    Registry::set("authDB", $authDB);

    // Прокинем объект класса User для авторизированного ранее пользователя
    session_name(md5("usid" . date("Y-m-d")));
    session_set_cookie_params(0, '/', SESSION_DOMAIN);
    session_start();
    if (isset($_SESSION["uid"])) {
        $user = User::findById((int)$_SESSION["uid"]);
        $db = new PDO('mysql:host=' . AUTH_DB_HOST . ';charset=utf8', AUTH_DB_USER, AUTH_DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $db->query("USE user_".$user->getUserId());
        // TODO: сделать проверку на браузер ip и сессию дабы усложнить потенциальную подмену сессий (секьюрность)
        Registry::set("user", $user);
        Registry::set("db", $db);
    }
    // Если пользователь не авторизирован и страница не общедоступная
    if (!in_array(Tools::getCurrentPage(), Tools::getPublicPages()) && !Registry::get("user")) {
        header("Location: /");
    }
} catch (Throwable $e) {;
    Log::write("critical", $e->getMessage(), ["level" => "file", "exception" => $e]);
    session_destroy();
}

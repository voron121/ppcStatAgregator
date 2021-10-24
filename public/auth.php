<?php

use Controllers\AuthController;
use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;

require_once __DIR__ . "/init.php";

try {
    $postData = [];
    if (!empty($_POST)) {
        $postData["formInput"] = $_POST;
        $registration = new AuthController();
        $registration->authUser($_POST);
    }
    $user = Registry::get("user");
    if (isset($_GET["action"]) && "logout" === $_GET["action"]) {
        session_destroy();
        Log::write(
            "info",
            "Пользователь с ид {userId} логин: {login} вышел из сервиса",
            ["level" => "user", "login" => $user->getLogin(), "userId" => $user->getUserId(), "email" => $user->getEmail()]
        );
        header("Location: /");
    }
    if ($user) {
        header("Location: /cabinet.php");
    }
    Registry::set("templateData", $postData);
    Template::loadTemplate("auth/auth");
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file"]);
    Template::loadTemplate("auth/auth");
}
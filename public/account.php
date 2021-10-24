<?php

use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;
use Controllers\cabinet\AccountsController;

require_once __DIR__ . "/init.php";

try {
    $data = [];
    $controller = new AccountsController();

    if (isset($_GET["action"]) && "add" === $_GET["action"]) {
        if (!empty($_POST)) {
            $data["formInput"] = $_POST;
            $controller->addAccount($_POST);
        }
    }
    Registry::set("templateData", $data);
    Template::loadTemplate("cabinet/account");
} catch(Throwable $e) {
    Log::write(
        "alert",
        $e->getMessage(),
        ["level" => "file", "exception" => $e]
    );
    Template::loadTemplate("cabinet/accounts-list");
}
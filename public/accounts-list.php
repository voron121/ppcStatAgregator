<?php

use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;
use Controllers\cabinet\AccountsController;

require_once __DIR__ . "/init.php";

try {
    $data = [];
    $controller = new AccountsController();
    $data["accounts"] = $controller->getAccountsList();
    Registry::set("templateData", $data);
    Template::loadTemplate("cabinet/accounts-list");
} catch(Throwable $e) {
    Log::write(
        "alert",
        $e->getMessage(),
        ["level" => "file", "exception" => $e]
    );
    Template::loadTemplate("cabinet/accounts-list");
}
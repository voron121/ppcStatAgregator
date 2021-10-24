<?php

use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;

require_once __DIR__ . "/init.php";

try {
    $postData = [];
} catch(Throwable $e) {
    Log::write(
        "alert",
        $e->getMessage(),
        ["level" => "file", "exception" => $e]
    );
}
Registry::set("templateData", $postData);
Template::loadTemplate("cabinet/clients-list");
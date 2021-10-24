<?php

use PPCSoft\Template;
use PPCSoft\Logger\Log;

require_once __DIR__ . "/init.php";

try {
    Template::loadTemplate("main/main", []);
} catch (Throwable $e) {
    Log::write("error", $e->getMessage(), ["level" => "file", "exception" => $e]);
    Template::loadTemplate("main/main");
}

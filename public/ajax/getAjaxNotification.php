<?php

use PPCSoft\Logger\Log;
use PPCSoft\Notification;

require_once __DIR__ . "/../init.php";

try {
    $ajaxNotification = new Notification();
    echo $ajaxNotification->showMessages();
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file"]);
}
<?php

use PPCSoft\Logger\Log;
use PPCSoft\Notification;

require_once __DIR__ . "/../init.php";

try {
    $level = isset($_POST["level"]) ? $_POST["level"] : "info";
    $message = isset($_POST["message"]) ? $_POST["message"] : "";
    $notification = new Notification();
    $notification->putMessage($message, $level);
} catch(Throwable $e) {
    Log::write("alert", $e->getMessage(), ["level" => "file"]);
}
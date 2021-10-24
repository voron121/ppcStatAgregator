<?php

use Controllers\RegistrationController;
use PPCSoft\Logger\Log;
use PPCSoft\Template;
use PPCSoft\Registry;

require_once __DIR__ . "/init.php";

try {
    $postData = [];
    if (!empty($_POST)) {
        $postData["formInput"] = $_POST;
        $registration = new RegistrationController();
        $registration->registrationNewUser($_POST);
    }
    if (Registry::get("user")) {
        header("Location: /cabinet.php");
    }
    Registry::set("templateData", $postData);
    Template::loadTemplate("registration/registration");
} catch(Throwable $e) {
    Log::write(
        "alert",
        $e->getMessage(),
        ["level" => "file", "exception" => $e]
    );
    Template::loadTemplate("registration/registration");
}
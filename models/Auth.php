<?php

namespace Models;

use PPCSoft\Registry;
use PPCSoft\Logger\Log;
use Exception;

class Auth
{
    protected $db = null;

    public function __construct()
    {
        $this->db = Registry::get("authDB");
    }

    /**
     * @param string $login
     * @param string $password
     * @throws Exception
     */
    public function authUser(array $postData) : void
    {
        $user = User::findByLogin($postData["login"]);
        $_SESSION["uid"] = $user->getUserId();
        // Обновим дату последней авторизации пользователя
        $user->setLastActivity(date("Y-m-d H:i:s"));
        $user->save();
        Log::write(
            "info",
            "Пользователь с логином {userLogin} с ид {userId} авторизировался",
            ["level" => "user", "userLogin" => $user->getLogin(), "userId" => $user->getUserId()]
        );
    }

}
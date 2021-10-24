<?php

namespace Controllers;

use Models\Registration;
use Models\Auth;
use Models\User;
use Exception;

class AuthController extends BaseController
{
    /**
     * @param array $postData
     * @return bool
     */
    protected function validate(array $postData) : bool
    {
        $postData = $this->prepareInput($postData);
        if (!isset($postData["login"])  || "" === $postData["login"]) {
            $this->notification->putMessage("Введите логин!", "warning");
            return false;
        }
        if (!isset($postData["password"])  || "" === $postData["password"]) {
            $this->notification->putMessage("Введите пароль!", "warning");
            return false;
        }
        try {
            $user = User::findByLogin($postData["login"]);
        } catch (Exception $e) {
            $this->notification->putMessage("Пользователь не найден!", "alert");
        }
        if ($user->getPassword() != Registration::generateUserPasswordHash($postData["password"])) {
            $this->notification->putMessage("Пароль не верен!", "warning");
            return false;
        }
        if ("Aprove" != $user->getStatus()) {
            $this->notification->putMessage("Пользователь не активирован!", "warning");
            return false;
        }
        return true;
    }

    /**
     * @param array $postData
     * @throws \Exception
     */
    public function authUser(array $postData) : void
    {
        if ($this->validate($postData)) {
            (new Auth())->authUser($postData);
            header("Location: /cabinet.php");
        }
    }




}
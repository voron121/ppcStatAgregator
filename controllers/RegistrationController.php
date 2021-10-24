<?php

namespace Controllers;

use Models\User;
use Models\Registration;
use PPCSoft\Logger\Log;
use PPCSoft\Registry;

class RegistrationController extends BaseController
{
    protected $db = null;
    protected $registrationModel  = null;

    public function __construct()
    {
        $this->db = Registry::get("authDB");
        $this->registrationModel = new Registration();
        parent::__construct();
    }

    /**
     * TODO: реализовать проверку email
     * @param array $postData
     * @return bool
     */
    protected function validateData(array $postData) : bool
    {
        if (empty($postData)) {
            $this->notification->putMessage("Пожалуйста заполните все поля формы", "warning");
            return false;
        }
        if (!isset($postData['login']) || empty($postData['login'])) {
            $this->notification->putMessage("Пожалуйста выберите логин!", "warning");
            return false;
        }
        if (!isset($postData['password']) || !isset($postData['password2']) || empty($postData['password']) || empty($postData['password2']))
        {
            $this->notification->putMessage("Пожалуйста придумайте пароль!", "warning");
            return false;
        }
        if (!isset($postData['email']) || empty($postData['email'])) {
            $this->notification->putMessage("Пожалуйста укажите ваш email!", "warning");
            return false;
        }
        if ($postData['password'] != $postData['password2']) {
            $this->notification->putMessage("Пароли не совпадают!", "warning");
            return false;
        }
        $emailPattern = "/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD";
        /*
        if (!preg_match($emailPattern, $data['email'])) {
            throw new \Exception("Email is not valid");
        }
        */
        if ($this->registrationModel->isEmailBusy($postData['email'])) {
            $this->notification->putMessage("Email " . $postData['email'] ." занят!", "warning");
            return false;
        }
        if ($this->registrationModel->isLoginBusy($postData['login'])) {
            $this->notification->putMessage("Логин " . $postData['login'] . " занят!", "warning");
            return false;
        }
        return true;
    }

    /**
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function registrationNewUser(array $data) : int
    {
        if ($this->validateData($this->prepareInput($data))) {
            // Создадим нового пользователя
            $user = new User();
            $user->setLogin($data["login"]);
            $user->setEmail($data["email"]);
            $user->setPassword($data["password"]);
            $user->setUserGroupId(DEFAULT_NEW_USER_GROUP);
            $user->setStatus(DEFAULT_NEW_USER_STATUS);
            $userId = $user->save();
            // Создадим БД для нового пользователя
            exec("php " . __DIR__ . "/../database/migration-runner.php user=" . $userId . " &");
            $this->notification->putMessage("Вы успешно зарегистрированны!<br> Можете авторизироваться в личном кабинете", "success");
            Log::write(
                "info",
                "Зарегистрировался пользователь: ид {userId} логин: {login} email: {email}",
                ["level" => "user", "login" => $user->getLogin(), "userId" => $userId, "email" => $user->getEmail()]
            );
            return $userId;
        }
        throw new \Exception("Error registration new user!");
    }
}
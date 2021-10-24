<?php

namespace Models;

use PPCSoft\Registry;

class Registration
{
    private $db = null;

    public function __construct()
    {
        $this->db = Registry::get("authDB");
    }

    /**
     * Вспомогательный метод для шифрования паролей пользователя.
     * @param string $password
     * @return string
     */
    public static function generateUserPasswordHash(string $password) : string
    {
        return md5(implode(array_reverse(str_split(md5(strip_tags(trim($password))), 1))));
    }

    /**
     * @param string $email
     * @return bool
     */
    public function isEmailBusy(string $email) : bool
    {
        $query = "SELECT userId FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["email" => $email]);
        $userId = $stmt->fetch();
        return $userId ? true : false;
    }

    /**
     * @param string $login
     * @return bool
     */
    public function isLoginBusy(string $login) : bool
    {
        $query = "SELECT userId FROM users WHERE login = :login";
        $stmt = $this->db->prepare($query);
        $stmt->execute(["login" => $login]);
        $userId = $stmt->fetch();
        return $userId ? true : false;
    }
}
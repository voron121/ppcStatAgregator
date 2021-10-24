<?php

namespace Models;

use PPCSoft\Registry;
use Exception;
use PDO;

class User
{
    private $userId;
    private $email;
    private $login;
    private $password;
    private $userGroupId;
    private $registrationDate;
    private $lastActivity;
    private $status;

    /**
     * @param int $userId
     */
    public function setUserId(int $userId) : void
    {
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login) : void
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getLogin() : string
    {
        return $this->login;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password) : void
    {
        $this->password = Registration::generateUserPasswordHash($password);
    }

    /**
     * @return string
     */
    public function getPassword() : string
    {
        return $this->password;
    }

    /**
     * @param int $userGroupId
     */
    public function setUserGroupId(int $userGroupId) : void
    {
        $this->userGroupId = $userGroupId;
    }

    /**
     * @return int
     */
    public function getUserGroupId() : int
    {
        return $this->userGroupId;
    }

    /**
     * @param string $registrationDate
     */
    public function setRegistrationDate(string $registrationDate) : void
    {
        $this->registrationDate = $registrationDate;
    }

    /**
     * @return string
     */
    public function getRegistrationDate() : string
    {
        return $this->registrationDate;
    }

    /**
     * @param string $lastActivity
     */
    public function setLastActivity(string $lastActivity = null) : void
    {
        $this->lastActivity = $lastActivity;
    }

    /**
     * @return string
     */
    public function geLastActivity() : string
    {
        return $this->lastActivity;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status) : void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * @param int $userId
     * @return mixed
     * @throws \Exception
     */
    public static function findById(int $userId) : User
    {
        $db = Registry::get("authDB");
        $query = "SELECT userId,          
                         email,            
                         login,         
                         password,      
                         userGroupId, 
                         registrationDate,
                         lastActivity,
                         status
                    FROM users 
                    WHERE userId = :userId";
        $stmt = $db->prepare($query);
        $stmt->execute(["userId" => $userId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
        $user = $stmt->fetch();
        if (!$user) {
            throw new Exception("User not found");
        }
        return $user;
    }

    /**
     * @param string $login
     * @return mixed
     * @throws \Exception
     */
    public static function findByLogin(string $login) : User
    {
        $db = Registry::get("authDB");
        $query = "SELECT userId,            
                         email,            
                         login,         
                         password,      
                         userGroupId, 
                         registrationDate,
                         lastActivity,
                         status
                    FROM users 
                    WHERE login = :login";
        $stmt = $db->prepare($query);
        $stmt->execute(["login" => $login]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\User');
        $user = $stmt->fetch();
        if (!$user) {
            throw new Exception("User not found");
        }
        return $user;
    }

    /**
     * @param string $account
     * @return int
     * @throws Exception
     */
    public static function getUserIdByAccount(string $account) : int
    {
        $db = Registry::get("authDB");
        $query = "SELECT userId FROM accounts WHERE account = :account";
        $stmt = $db->prepare($query);
        $stmt->execute(["account" => $account]);
        $userId = $stmt->fetchColumn();
        if (!$userId) {
            throw new Exception("User not found");
        }
        return $userId;
    }

    /**
     * @return int
     */
    public function save() : int
    {
        $db = Registry::get("authDB");
        $fields  = " `email` = " . $db->quote($this->email) . " , ";
        $fields .= " `login` = " . $db->quote($this->login) . " , ";
        $fields .= " `password` = " . $db->quote($this->password) . " , ";
        $fields .= " `userGroupId` = " . $db->quote($this->userGroupId) . " , ";
        if (!is_null($this->lastActivity)) {
            $fields .= " `lastActivity` = " . $db->quote($this->lastActivity) . " , ";
        }
        $fields .= " `status` = " . $db->quote($this->status);

        if (isset($this->userId)) {
            $query = "UPDATE users SET " . $fields . " WHERE userId = :userId";
            $stmt = $db->prepare($query);
            $stmt->execute(["userId" => $this->userId]);
        } else {
            $fields .= " , `registrationDate` = NOW()";
            $query = "INSERT INTO users SET " . $fields;
            $db->query($query);
        }
        return $db->lastInsertId();
    }
}
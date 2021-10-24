<?php


namespace Controllers\cabinet;

use Controllers\BaseController;
use Models\cabinet\Account;
use PPCSoft\Registry;
use PPCSoft\Logger\Log;

class AccountsController extends BaseController
{
    protected $account = null;
    public $user = null;

    public function __construct()
    {
        $this->account = new Account();
        $this->user = Registry::get("user");
        parent::__construct();
    }

    /**
     * @param array $postData
     * @return bool
     */
    protected function validateData(array $postData) : bool
    {
        if (empty($postData)) {
            $this->notification->putMessage("Пожалуйста заполните все поля формы", "warning");
            return false;
        }
        if (!isset($postData['accountId']) || empty($postData['accountId'])) {
            $this->notification->putMessage("Пожалуйста укажите ид аккаунта!", "warning");
            return false;
        }
        if (!isset($postData['accountLogin']) || empty($postData['accountLogin'])) {
            $this->notification->putMessage("Пожалуйста укажите логин аккаунта!", "warning");
            return false;
        }
        if (!isset($postData['scloginid']) || empty($postData['scloginid'])) {
            $this->notification->putMessage("Пожалуйста укажите ссылку на идентификатор входа в центр продаж!", "warning");
            return false;
        }
        if (!isset($postData['shopUrl']) || empty($postData['shopUrl'])) {
            $this->notification->putMessage("Пожалуйста укажите ссылку на магазин!", "warning");
            return false;
        }
        if (!isset($postData['accountPassword']) || empty($postData['accountPassword'])) {
            $this->notification->putMessage("Пожалуйста укажите пароль аккаунта!", "warning");
            return false;
        }
        if ($this->account->isAccountExist($postData['accountId'])) {
            $this->notification->putMessage("Аккаунт ".$postData['accountId']." уже добавлен!", "warning");
            return false;
        }
        return true;
    }

    /**
     * TODO: убрать лапшу с if/else
     * @param array $data
     * @return bool
     */
    public function addAccount(array $data) : void
    {
        if ($this->validateData($data)) {
            if (!$this->account->isFreeProxyExist()) {
                $this->notification->putMessage("Ошибка добавления аккаунта!<br>Нет свободных прокси!", "warning");
            } else {
                // Добавим новый аккаунт
                $this->account->setUserId($this->user->getUserId());
                $this->account->setAccountId($data["accountId"]);
                $this->account->setAccountLogin($data["accountLogin"]);
                $this->account->setAccountPassword($data["accountPassword"]);
                $this->account->setSellerCenterLoginId($data["scloginid"]);
                $this->account->setShopUrl($data["shopUrl"]);
                if ($this->account->save()) {
                    $this->notification->putMessage("Аккаунт успешно добавлен!", "success");
                    Log::write(
                        "info",
                        "Пользователь с ид {userId} добавил аккаунт амазон с account ID: {accountId}",
                        ["level" => "user", "userId" => $this->user->getUserId(), "accountId" => $this->account->getAccountId()]
                    );
                } else {
                    $this->notification->putMessage("Ошибка добавления аккаунта!", "warning");
                }
            }
        }
    }

    /**
     * Вернет список объявлений для пользователя с разбивкой по страницам
     * @param int $userId
     * @return array
     */
    public function getAccountsList() : array
    {
        $accounts = $this->account->getAccountsList();
        if (empty($accounts)) {
            $this->notification->putMessage("Аккаунты не подключены!", "warning");
        }
        return $accounts;
    }

}
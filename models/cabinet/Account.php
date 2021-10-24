<?php


namespace Models\cabinet;

use Models\BaseModel;
use PPCSoft\Registry;
use Throwable;
use PDO;
use PPCSoft\Tools\Tools;
use PPCSoft\Logger\Log;
use DateTime;

class Account extends BaseModel
{
    private $id;
    private $userId;
    private $accountId;
    private $name;
    private $email;
    private $shopName;
    private $accountLogin;
    private $accountPassword;
    private $sellerCenterLoginId;
    private $shopUrl;
    private $isError;
    private $errorText;
    private $authDB = null;
    private $user = null;

    public function __construct()
    {
        $this->user = Registry::get("user");
        $this->authDB = Registry::get("authDB");
        parent::__construct();
    }

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
     * @param string $accountId
     */
    public function setAccountId(string $accountId) : void
    {
        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function getAccountId() : string
    {
        return $this->accountId;
    }

    /**
     * @param string $name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
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
        return !is_null($this->email) ? $this->email : "";
    }

    /**
     * @return string
     */
    public function getIsError() : bool
    {
        return "yes" === $this->isError ? true : false;
    }

    /**
     * @return string
     */
    public function getErrorText() : string
    {
        return $this->errorText;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @param string $shopName
     */
    public function setShopName(string $shopName) : void
    {
        $this->shopName = $shopName;
    }

    /**
     * @param string $accountLogin
     */
    public function setAccountLogin(string $accountLogin) : void
    {
        $this->accountLogin = $accountLogin;
    }

    /**
     * @param string $accountPassword
     */
    public function setAccountPassword(string $accountPassword) : void
    {
        $this->accountPassword = $accountPassword;
    }

    /**
     * @param string $scLoginId
     */
    public function setSellerCenterLoginId(string $scLoginId) : void
    {
        $this->sellerCenterLoginId = $scLoginId;
    }

    /**
     * @param string $shopUrl
     */
    public function setShopUrl(string $shopUrl) : void
    {
        $this->shopUrl = $shopUrl;
    }

    /**
     * @param string $accountId
     * @return bool
     */
    public function isAccountExist(string $accountId) : bool
    {
        $query = "SELECT id FROM accounts WHERE userId = :userId AND accountId = :accountId";
        $stmt = $this->authDB->prepare($query);
        $stmt->execute([
            "userId" => $this->user->getUserId(),
            "accountId" => $accountId
        ]);
        $account = $stmt->fetchAll();
        if (!$account) {
            return false;
        }
        return true;
    }

    /**
     * @param string $accountId
     * @return Account
     * @throws Exception
     */
    public function getAccountByAccountId(string $accountId) : Account
    {
        $query = "SELECT id, 
                         userId, 
                         accountId, 
                         email 
                    FROM accounts 
                    WHERE userId = :userId
                        AND accountId = :accountId";
        $stmt = $this->authDB->prepare($query);
        $stmt->execute([
            "userId" => $this->user->getUserId(),
            "accountId" => $accountId
        ]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\Cabinet\Account');
        $account = $stmt->fetch();
        if (!$account) {
            throw new Exception("Account not found");
        }
        return $account;
    }

    /**
     * @return array
     */
    public function getAccountsList() : array
    {
        $query = "SELECT id, 
                         userId, 
                         accountId,
                         email,
                         isError,
                         errorText
                    FROM accounts 
                    WHERE userId = :userId";
        $stmt = $this->authDB->prepare($query);
        $stmt->execute([
            "userId" => $this->user->getUserId()
        ]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Models\Cabinet\Account');
        return $stmt->fetchAll();
    }

    /**
     * Сохранит данные аккаунта в ЦА
     * @return int
     */
    private function saveAccountInToAuth() : int
    {
        $fields  = " `userId` = " . $this->authDB->quote($this->userId) . " , ";
        $fields .= " `accountId` = " . $this->authDB->quote($this->accountId) . " , ";
        $fields .= " `email` = " . $this->authDB->quote($this->accountLogin);

        if (isset($this->id)) {
            $query = "UPDATE accounts SET " . $fields . " WHERE id = :id";
            $stmt = $this->authDB->prepare($query);
            $stmt->execute(["id" => $this->id]);
        } else {
            $query = "INSERT INTO accounts SET " . $fields;
            $this->authDB->query($query);
        }
        return $this->authDB->lastInsertId();
    }

    /**
     * @return PDO
     */
    private function getBotDB() : PDO
    {
        $db = new PDO('mysql:host=' . CACHE_DB_HOST . ';charset=utf8', CACHE_DB_USER, CACHE_DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    }

    /**
     * Проверит есть ли свободные прокси в БД
     * @return bool
     */
    public function isFreeProxyExist() : bool
    {
        $db = $this->getBotDB();
        $db->query("USE bots");
        $stmt = $db->query("SELECT COUNT(proxy) FROM proxy WHERE sclogin IS NULL");
        return $stmt->fetchColumn() > 0 ? true : false;
    }

    /**
     * @return string
     */
    protected function getFreeProxy() : string
    {
        $db = $this->getBotDB();
        $db->query("USE bots");
        $stmt = $db->query("SELECT `proxy` FROM proxy WHERE `sclogin` IS NULL AND `proxy` != '' LIMIT 0, 1");
        return $stmt->fetchColumn();
    }

    /**
     * Вернет прокси для родительского аккаунта
     * Мы можем прикрепить несколько аккаунтов к одному прокси если они расшарены
     * в одном из аккаунтов (родительском)
     * @return string
     */
    protected function getUserAccountProxy() : string
    {
        $db = $this->getBotDB();
        $db->query("USE bots");
        $stmt = $db->prepare("SELECT proxy FROM proxy WHERE sclogin = :sclogin");
        $stmt->execute(["sclogin" => $this->accountLogin]);
        $accountLoginProxy = $stmt->fetchColumn();
        return !is_null($accountLoginProxy) ? $accountLoginProxy : "";
    }

    /**
     * Вернет массив с модулями для ZennoPoster с параметрами для запуска
     * @return array[]
     */
    protected function getZennoposterModules() : array
    {
        return [
            "sponsored_products_advertised.zp" => [
                "enabled" => "Yes",
                "name_module" => "sponsored_products_advertised.zp",
                "interval_zapuska_min" => 720,
                "date_update" => (new DateTime())->modify("-1 days")->format("Y-m-d H:i:s")
            ],
            "sponsored_products_campaigns.zp" => [
                "enabled" => "Yes",
                "name_module" => "sponsored_products_campaigns.zp",
                "interval_zapuska_min" => 720,
                "date_update" => (new DateTime())->modify("-1 days")->format("Y-m-d H:i:s")
            ],
        ];
    }

    /**
     * Удалит таблицу с параметрами ZennoPoster
     * Удаление и создание вне контекста транзакции оправданно не явными коммитами MySQL при создании таблицы
     * @param string $tableName
     * @return string
     */
    protected function createZennoPosterFunctionalityTableQuery(string $tableName) : void
    {
        $db = $this->getBotDB();
        $db->query("USE bots");
        $db->query("CREATE TABLE IF NOT EXISTS " . $tableName . "( 
                            `id` INT(11) NOT NULL AUTO_INCREMENT, 
                            `enabled` SET('Yes','No') NOT NULL, 
                            `name_module` VARCHAR(50) NOT NULL, 
                            `dop_parametr` TEXT NULL, 
                            `interval_zapuska_min` INT(11) NOT NULL DEFAULT '0', 
                            `date_update` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
                            PRIMARY KEY (id), 
                            UNIQUE INDEX name_module (name_module) 
                      ) COLLATE='utf8_general_ci' ENGINE=InnoDB ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1;"
        );
    }

    /**
     * Удалит таблицу с параметрами ZennoPoster
     * Удаление и создание вне контекста транзакции оправданно не явными коммитами MySQL при создании таблицы
     * @param string $tableName
     */
    protected function removeZennoPosterFunctionalityTableQuery(string $tableName) : void
    {
        $db = $this->getBotDB();
        $db->query("USE bots");
        $db->query("DROP TABLE ".$tableName);
    }

    /**
     * Сформирует SQL запрос для добавления акаунта в БД ZennoPoster
     * @param string $tableName
     * @param string $proxy
     * @return string
     */
    protected function getZennoPosterAccountAddQuery(string $tableName, string $proxy) : string
    {
        $db = $this->getBotDB();
        $fields  = " `enabled` = 'Yes' , ";
        $fields .= " `table_name` = " . $db->quote($tableName) . " , ";
        $fields .= " `sclogin` = " . $db->quote($this->accountLogin) . " , ";
        $fields .= " `shop_url` = " . $db->quote($this->shopUrl) . " , ";
        $fields .= " `scpass` = " . $db->quote($this->accountPassword) . " , ";
        $fields .= " `scloginid` = " . $db->quote($this->sellerCenterLoginId) . " , ";
        $fields .= " `proxy` = " . $db->quote($proxy) . " , ";
        $fields .= " `account_id` = " . $db->quote($this->accountId);
        $query = "INSERT INTO config SET " . $fields;
        return $query;
    }

    /**
     * Сохранит данные аккаунта в БД с ботами
     * Использует транзакции MySQL
     * @return bool
     */
    private function saveAccountInToBothDB() : bool
    {
        $accountBotTableName = Tools::getBotTableFromAccountLogin($this->accountLogin);
        $db = $this->getBotDB();
        $db->query("USE bots");
        // Попробуем добавить новый аккаунт амазон
        try {

            $proxy = empty($this->getUserAccountProxy()) ? $this->getFreeProxy() : $this->getUserAccountProxy();
            $db->beginTransaction();
            // Займем для нового клиента прокси
            $stmt = $db->prepare("UPDATE proxy SET sclogin = :sclogin WHERE proxy = :proxy");
            $stmt->execute([
                "sclogin" => $this->accountLogin,
                "proxy" => $proxy
            ]);
            // Создадим для нового клиента операционные таблицы
            $this->createZennoPosterFunctionalityTableQuery($accountBotTableName);
            // Добавим аккаунт клиента в БД с ботами
            $db->query($this->getZennoPosterAccountAddQuery($accountBotTableName, $proxy));
            // Подключим модули ZennoPoster
            $query = "INSERT INTO " . $accountBotTableName . " 
                        SET `enabled` = :enabled, 
                            `name_module` = :name_module,
                            `interval_zapuska_min` = :interval_zapuska_min,
                            `date_update` = :date_update";
            $stmt = $db->prepare($query);
            foreach($this->getZennoposterModules() as $module) {
                $stmt->execute([
                    "enabled" => $module["enabled"],
                    "name_module" => $module["name_module"],
                    "interval_zapuska_min" => $module["interval_zapuska_min"],
                    "date_update" => $module["date_update"]
                ]);
            }
            $db->commit();
        } catch (Throwable $e) {
            Log::write("alert", "Ошибка добавления нового клиента в БД ZennoPoster: " . $e->getMessage(), ["level" => "file", "exception" => $e]);
            $db->rollBack();
            $this->removeZennoPosterFunctionalityTableQuery($accountBotTableName);
            throw new \Exception($e->getMessage(), $e->getCode());
        }
        return true;
    }

    /**
     * Удалит аккаунт из ЦА
     */
    private function deleteAccountFromAuth() : void
    {
        $query = "DELETE FROM accounts WHERE accountId = :accountId";
        $stmt = $this->authDB->prepare($query);
        $stmt->execute(["accountId" => $this->accountId]);
    }

    /**
     * Удалит аккаунт из БД с ботами
     */
    private function deleteAccountFromBothDB() : void
    {
        $db = $this->getBotDB();
        $db->query("USE bots");
        $query = "DELETE FROM config WHERE account_id = :accountId";
        $stmt = $db->prepare($query);
        $stmt->execute(["accountId" => $this->accountId]);
    }

    /**
     * @return bool
     */
    public function save() : bool
    {
        try {
            $this->saveAccountInToBothDB();
            $this->saveAccountInToAuth();
            return true;
        } catch (Throwable $e) {
            $this->deleteAccountFromAuth();
            $this->deleteAccountFromBothDB();
            Log::write(
                "critical",
                "Ошибка добавления аккаунта амазон: {message}",
                ["level" => "file", "message" => $e->getMessage(), "exception" => $e]
            );
            return false;
        }
    }

}
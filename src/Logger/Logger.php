<?php
/**
 * Общий класс для логирования в сервисе. Логирование устроено с использованием PSR-3. Это дает возможность
 * настроить дополнительные обработчики для логов различных уровней в отдельных, одноименных методах. Например
 * можно настроить отправку сообщений на email для ошибок типа alert.
 * Логирование устроено следующим образом: по умолчанию все пишем в БД. Есть несколько основных
 * уровней логирования для различных типов событий: события пользователя, события из api события из роботов и тд.
 * Для каждого уровня есть своя отдельная таблица в БД логов. Уровень логирования передается в массиве контекста
 * под ключем "level". Если уровень не передан то логи будут писаться в отдельный фаил на сервере.
 */

namespace PPCSoft\Logger;

use Psr\Log\AbstractLogger;
use PDO;
use PPCSoft\Mailer;
use PPCSoft\Tools\CLITools;
use PPCSoft\Tools\Tools;

class Logger extends AbstractLogger
{
    public $host = "";

    protected $db = null;
    protected $source = "file";
    protected $mailer = null;

    CONST LEVELS = ["user", "api", "robot"];
    CONST LOG_FILE = "log.log";

    public function __construct()
    {
        $this->db = new PDO('mysql:host=' . LOG_DB_HOST . ';charset=utf8', LOG_DB_USER, LOG_DB_PASSWORD);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $this->mailer = new Mailer();
        $this->host = getHostByName(getHostName());
    }

    /**
     * Сформирует сообщение для логирования с учетом обработки плейсхолдеров
     * todo: отрефакторить, проблема с циклом, проблема с неймингом
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function getMessage(string $message, array $context) : string
    {
        if (empty($context)) {
            return $message;
        }
        $messageForProcessing = explode(" ", $message);
        foreach ($messageForProcessing as &$messageString) {
            if (!Tools::isPlaceholder($messageString) || empty(Tools::getPlaceholderValueFromArray($messageString, $context))) {
                continue;
            }
            $messageString = Tools::getPlaceholderValueFromArray($messageString, $context);
        }
        $message = implode(" ",$messageForProcessing);

        if (isset($context["exception"])) { // Расширенное логирование ошибки в случае наличия объекта Throwable в контексте
            $exception = $context["exception"];
            $message = $message . ": File: " . $exception->getFile() . " Line: " . $exception->getLine()
                        . " Message: ". $exception->getMessage();
        }
        return $message;
    }

    /**
     * Создаст папку для логов на сервере
     */
    protected function createLogDir() : void
    {
        if(!is_dir(LOG_FILE)) {
            mkdir(LOG_FILE, 0777);
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @throws \Exception
     */
    public function alert($message, array $context = [])  : void
    {
        $this->mailer->setSubject("PPCSoft: ошибка! Сервер:" . $this->host);
        $this->mailer->setRecipients(ADMIN_EMAIL);
        $this->mailer->setMessage($this->getMessage($message, $context));
        $this->mailer->send();
    }

    /**
     * @param string $message
     * @param array $context
     * @throws \Exception
     */
    public function critical($message, array $context = [])  : void
    {
        $this->mailer->setSubject("PPCSoft: критическая ошибка! Сервер:" . $this->host);
        $this->mailer->setRecipients(ADMIN_EMAIL);
        $this->mailer->setMessage($this->getMessage($message, $context));
        $this->mailer->send();
    }

    /**
     * Запишет лог в фаил
     * @param string $message
     * @param array $context
     */
    protected function writeToFile(string $level, string $message, array $context = []) : void
    {
        $this->createLogDir();
        $message = "[".date("Y-m-d H:i:s") . "] " . $level ." : ". $this->getMessage($message, $context). PHP_EOL;
        file_put_contents(LOG_FILE."/".self::LOG_FILE, $message, FILE_APPEND);
    }

    /**
     * TODO: потенциальная паста, отрефакторить в дальнейшем.
     * TODO: Добавить возможность выбирать индивидуальные БД для логов (к примеру отдельная БД для каждого юзера...)
     * Запишем лог в БД
     * @param string $message
     * @param array $context
     */
    protected function writeToDB(string $level, string $message, array $context = []) : void
    {
        if ("user" === $context["level"]) {
            // Логируем уровень пользователя в БД с логами пользователей
            $this->db->query("USE " . USERS_LOG_DB_NAME);
            $query = "INSERT INTO userslog 
                            SET userId = :userId,
                                level = :level,
                                message = :message,
                                context = :context,
                                ip = :ip";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                "userId" => $context["userId"],
                "level" => $level,
                "message" => $this->getMessage($message, $context),
                "context" => json_encode($context),
                "ip" => $_SERVER["REMOTE_ADDR"]
            ]);
        } elseif ("robot" === $context["level"]) {
            // Логируем уровень робоота в БД с логами роботов
            $this->db->query("USE " . ROBOTS_LOG_DB_NAME);
            $query = "INSERT INTO robotslog 
                            SET robot = :robot,
                                level = :level,
                                message = :message,
                                context = :context,
                                account = :account";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                "robot" => CLITools::getRobotNameByPath($context["robot"]),
                "level" => $level,
                "message" => $this->getMessage($message, $context),
                "context" => json_encode($context),
                "account" => isset($context["account"]) ? $context["account"] : null
            ]);
        } else {
            // Логируем уровень api в БД с логами api
            $this->db->query("USE " . API_LOG_DB_NAME);
            $query = "INSERT INTO apilog 
                            SET level = :level,
                                message = :message,
                                request = :request,
                                response = :response,
                                backtrace = :backtrace,
                                context = :context";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                "level" => $level,
                "message" => $this->getMessage($message, $context),
                "request" => json_encode($context["request"]),
                "response" => json_encode($context["response"]),
                "backtrace" => $context["backtrace"],
                "context" => json_encode($context)
            ]);
        }
    }

    /**
     * Общий метод для логирования
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = []) : void
    {
        if (!empty($context) && isset($context["level"]) && in_array($context["level"], SELF::LEVELS)) {
            $this->source = "db";
        }
        try {
            if ("alert" === $level) {
                $this->alert($message, $context);
            } elseif ("critical" === $level) {
                $this->critical($message, $context);
            }
            // Запишем сообщение
            if ("file" === $this->source) {
                $this->writeToFile($level, $message, $context);
            } else {
                $this->writeToDB($level, $message, $context);
            }
        } catch(\Throwable $e) {
            $this->writeToFile("alert", $e->getMessage(), ["exception" => $e]);
        }
    }
}

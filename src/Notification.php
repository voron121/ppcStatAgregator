<?php
/**
 * Класс для реализации уведомлений в сервисе
 * TODO: использовать декоратор для уменьшения пасты
 * TODO: возможно стоит использовать хранение объекта в памяти с возможностью доступа другим классам(смотреть в сторону патерна репозиторий)
 */

namespace PPCSoft;
use PPCSoft\Registry;

class Notification
{
    const MESSAGE_QUEUE = "messages";

    /**
     * @param $message
     */
    protected function alert($message) : void
    {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . $message .
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'.
            '<span aria-hidden="true">&times;</span></button></div>';
    }

    /**
     * @param $message
     */
    protected function info($message) : void
    {
        echo '<div class="alert alert-info alert-dismissible fade show" role="alert">' . $message .
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'.
            '<span aria-hidden="true">&times;</span></button></div>';
    }

    /**
     * @param $message
     */
    protected function warning($message) : void
    {
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">' . $message .
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'.
            '<span aria-hidden="true">&times;</span></button></div>';
    }

    /**
     * @param $message
     */
    protected function success($message) : void
    {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . $message .
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'.
            '<span aria-hidden="true">&times;</span></button></div>';
    }

    /**
     * @param string $message
     * @param string $level
     */
    public function putMessage(string $message, string $level = "info") : void
    {
        $messages = $_SESSION[static::MESSAGE_QUEUE];
        if (!isset($messages) || empty($messages)) {
            $_SESSION[static::MESSAGE_QUEUE] = [["text" => $message, "level" => $level]];
        } else {
            $_SESSION[static::MESSAGE_QUEUE] = array_merge($messages, [["text" => $message, "level" => $level]]);
        }
    }

    /**
     * @param int $messageId
     */
    protected static function deleteMessage(int $messageId) : void {
        $messages = static::getMessages();
        unset($messages[$messageId]);
        $messages = !is_null($messages) ? array_values($messages) : [];
        $_SESSION[static::MESSAGE_QUEUE] = $messages;
    }

    /**
     * @return array
     */
    protected static function getMessages() : array
    {
        $messages = $_SESSION[static::MESSAGE_QUEUE];
        return !is_null($messages) ? $messages : [];
    }

    /**
     * Отобразит сообщения из регистра
     */
    public function showMessages() : void
    {
        foreach (static::getMessages() as $messageId => $message) {
            if ("alert" === $message["level"]) {
                $this->alert($message["text"]);
            } elseif ("warning" === $message["level"]) {
                $this->warning($message["text"]);
            } elseif ("success" === $message["level"]) {
                $this->success($message["text"]);
            } else {
                $this->info($message["text"]);
            }
            static::deleteMessage($messageId);
        }
    }
}
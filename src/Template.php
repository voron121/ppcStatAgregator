<?php

namespace PPCSoft;

use PPCSoft\Logger\Log;
use PPCSoft\Notification;
use PPCSoft\Registry;
use PPCSoft\AjaxNotification;
use PPCSoft\Paginator;

class Template
{
    /**
     * Вспомогательный метод для подгрузки шаблонов относительно директории views
     * @param string $template  - название шаблона вида диектория шаблона / название шаблона.
     * @param array $meta       - ассоциативный массив с мета данными вида ["title" => "Главная страница","description" => "Описание","keywords" => "Ключевые слова"]
     */
    public static function loadTemplate(string $template, array $meta = []) : void
    {
        try {
            $notifications = new Notification(); // Нужен для рендера сообщений во вьюхах
            $user = Registry::get("user");  // Нужен для взаимодействия с объектом пользователя во вьюхах
            $data = Registry::get("templateData");
            if (empty($meta)) {
                $meta = [
                    "title" => "PPCSoft",
                    "description" => "Описание",
                    "keywords" => "Ключевые слова"

                ];
            }
            $itemsCount = isset($data["itemsCount"]) && !is_null($data["itemsCount"]) ? (int)$data["itemsCount"] : 0;
            require_once __DIR__ . "/../public/views/layouts/header.php";
            require_once __DIR__ . "/../public/views/" .$template.".php";
            if ($itemsCount > 0) {
                Paginator::getPagination($itemsCount);
            }
            require_once __DIR__ . "/../public/views/layouts/footer.php";
        } catch (\Throwable $e) {
            Log::write("alert", "Ошибка при подключении шаблона", ["level" => "file", "exception" => $e]);
        }
    }

}
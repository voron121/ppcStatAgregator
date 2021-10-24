<?php
/**
 * Вспомогательный класс для логириования.
 * Создан для синтаксического сахар.
 * Реализует логику класса Logger
 * TODO: добавить методы для уровней логирования (info alert ...) как статические для расширения функционала логирования
 */

namespace PPCSoft\Logger;

class Log
{
    /**
     * Запишет данные в лог.
     * @param $level
     * @param $message
     * @param array $context
     */
    public static function write($level, string $message, array $context = []) : void
    {
        $loger = new Logger();
        $loger->log($level, $message, $context);
    }
}
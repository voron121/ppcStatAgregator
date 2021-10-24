<?php
/**
 * Утилитарный класс для реализации различных не значительных задач и функциональностей
 */
namespace PPCSoft\Tools;

use DateTime;
use PPCSoft\Registry;

class Tools
{
    const LIMITING_FACTOR_ACOS = 1.51;

    /**
     * Проверит наличие плейсхолдера в строке
     * @param string $string
     * @return bool
     */
    public static function isPlaceholder(string $string) : bool
    {
        return preg_match("/({\w.*})|({\d.*})/", $string);
    }

    /**
     * Получит пдейсхолдер без фигурных скобок
     * @param string $string
     * @return string
     */
    public static function getPlaceholder(string $string) : string
    {
        preg_match("/{(\w.*?)}|{(\d.*?)}/", $string, $matches);
        return isset($matches[1]) && !empty($matches[1]) ? $matches[1] : "";
    }

    /**
     * Получит значение плейсхолдера из массива
     * @param string $string
     * @param array $context
     * @return string
     */
    public static function getPlaceholderValueFromArray(string $string, array $context) : string
    {
        return isset($context[self::getPlaceholder($string)]) ? $context[self::getPlaceholder($string)] : "";
    }

    /**
     * вернет список страниц, доступ к которым открыт всем
     * @return array
     */
    public static function getPublicPages() : array
    {
        return ["main","auth","registration"];
    }

    /**
     * Вернет текущую страницу
     * @return string
     */
    public static function getCurrentPage() : string
    {
        $url = explode('?', $_SERVER['REQUEST_URI']);
        $url = preg_replace("/.php|.html|.htm/", "", $url[0]);
        return "/" === $url ? "main" : substr($url, 1);
    }

    /**
     * Словарь дней недели
     * @return array
     */
    public static function getWeekDaysRU() : array
    {
        return [
            1 => "понедельник",
            2 => "вторник",
            3 => "среда",
            4 => "четверг",
            5 => "пятница",
            6 => "суббота",
            0 => "воскресенье"
        ];
    }

    /**
     * Словарь месяцев
     * @return array
     */
    public static function getMonthsRU() : array
    {
        return [
            "01" => "январь",
            "02" => "февраль",
            "03" => "март",
            "04" => "апрель",
            "05" => "май",
            "06" => "июнь",
            "07" => "июль",
            "08" => "август",
            "09" => "сентябрь",
            "10" => "октябрь",
            "11" => "ноябрь",
            "12" => "декабрь"
        ];
    }

    /**
     * Вернет кирилическое представление даты
     * @param \DateTime $date
     * @return string
     */
    public static function humanizedDate(\DateTime $date) : string
    {
        $days = self::getWeekDaysRU();
        $monts = self::getMonthsRU();
        return $days[$date->format("w")] . " " . $date->format("d") . " "
            . $monts[$date->format("m")] . " " . $date->format("Y");
    }

    /**
     * Сгенерирует название таблицы для БД с ботами из логина амазон клиента
     * TODO: потенциальный баг если в строке будут символы типа апострофа. Такое имя будет не валидно. Нужно обработать данную ситуацию
     * @param string $login
     * @return string
     */
    public static function getBotTableFromAccountLogin(string $login) : string
    {
        preg_match('#PPC\.(.+?)@#is', $login, $mathes);
        $table = preg_replace("/\-|\.|\_/", "", $mathes[1]);
        return $table === "" ? md5(date("Y-m-d h:i-s")) : $table;
    }

    /**
     * Вернет стиль для отображения ячейки с параметрами acos;
     * Если параметр acos входит в диапазон числа ($minAcos + 50%) - подсветим ячейку желтым
     * Если параметр acos выше ($minAcos + 51.1%) - подсветим ячейку красным
     * Во всех остальных случаях (фактически когда acos меньше $minAcos) - подсветим ячейку зеленым
     * @param $minAcos - Пользовательский параметр минимально допустимого acos
     * @param $acos - Acos для айтема
     * @return string
     */
    public static function getAcosCSSLabel($minAcos, $acos) : string
    {
        if ($acos >= $minAcos && $acos < ($minAcos * self::LIMITING_FACTOR_ACOS)) {
            $status = "table-warning";
        } elseif ($acos >= ($minAcos * self::LIMITING_FACTOR_ACOS)) {
            $status = "table-danger";
        } else {
            $status = "table-success";
        }
        return $status;
    }
}
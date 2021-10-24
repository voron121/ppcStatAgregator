<?php


namespace Controllers;

use PPCSoft\Notification;
use DateTime;
use DateInterval;
use DatePeriod;

class BaseController
{
    /* Хранилище для объекта с уведомлениями */
    protected $notification = null;

    public function __construct()
    {
        $this->notification = new Notification();
    }

    /**
     * Вернет офсет для пагинации
     * @param int $page
     * @return int
     */
    protected function getOffset() : int
    {
        $page = isset($_GET["page"]) ? $_GET["page"] : 0;
        return ($page > 1) ?  $page * ITEMS_ON_PAGE_LIMIT - ITEMS_ON_PAGE_LIMIT : 0;
    }

    /**
     * Вернет офсет для пагинации
     * @param int $page
     * @return int
     */
    protected function getOffsetByPage(int $page = null) : int
    {
        $page = !isset($page) ? $_GET["page"] : $page;
        return ($page > 1) ?  $page * ITEMS_ON_PAGE_LIMIT - ITEMS_ON_PAGE_LIMIT : 0;
    }

    /**
     * Очистит пользовательский ввод от возможных не консистентных данных или инъекций
     * @param array $postData
     * @return array
     */
    protected function prepareInput(array $postData) : array
    {
        array_map(function($item) {
            return strip_tags(trim($item));
        }, $postData);
        return $postData;
    }

    /**
     * Вернет массив с датами для отчета за период в месяц
     * TODO: добавить в сигнатуры метода поддержку ручного указания интервала статистики
     * @return DatePeriod
     */
    public function getReportDateInterval() : DatePeriod
    {
        $start = new DateTime(date("Y-m-d"));
        $end = new DateTime(date('Y-m-d',(strtotime('-1 month',strtotime(date("Y-m-d"))))));
        $diff = $end->diff($start);
        $interval = DateInterval::createFromDateString('-1 day');
        $daterange = new DatePeriod($start, $interval, $diff->days);
        return $daterange;
    }

}
<?php
/**
 * Класс реализует пагинацию на странице
 */
namespace PPCSoft;


class Paginator
{
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    /**
     * Сгенерирует url с учетом гет параметров
     * Если в url есть гет параметры и есть параметр page = обновим параметр page
     * Если в url есть гет параметры но нет page - добавим page в url
     * Если в строке нет гет параметров  - добавим page
     * @param int $page
     * @return string
     */
    private static function getNavigationUrl(int $page) : string
    {
        $url = $_SERVER['REQUEST_URI'];
        if (preg_match("/(\?\w.*=)/", $_SERVER['REQUEST_URI'])
            && preg_match("/(page=\d{1,})/", $_SERVER['REQUEST_URI']))
        { // Если в url есть гет параметры и есть параметр page = обновим параметр page
            $url = preg_replace("/(page=\d{1,})/", "page=" . $page, $_SERVER['REQUEST_URI']);
        } elseif(preg_match("/(\?\w.*=)/", $_SERVER['REQUEST_URI'])
            && !preg_match("/(page=\d{1,})/", $_SERVER['REQUEST_URI']))
        { // Если в url есть гет параметры но нет page - добавим page в url
            $url = $_SERVER['REQUEST_URI'] . "&page=" . $page;
        } else { // Если в строке нет гет параметров  - добавим page
            $url = $_SERVER['REQUEST_URI'] . "?page=" . $page;
        }
        return $url;
    }

    /**
     * Создаст HTML сущность навигационного айтема
     * @param int $page - страница
     * @param string $text - текст в кнопке
     * @param int $curPage - текущая страницу
     * @return string
     */
    private static function getPaginationItem(int $page, string $text, int $curPage = 0) : string
    {
        $activeItemClass = ($curPage == $page) ? "active" : "" ;
        return '<li class="page-item '.$activeItemClass.'">
                <a class="page-link" href="'.self::getNavigationUrl($page) .'">'.$text.'</a></li>';
    }

    /**
     * Создаст кнопку "Назад"
     * @param int $page
     * @return string
     */
    private static function getPrevPageItem(int $page) : string
    {
        return self::getPaginationItem(self::getPrevPage($page), "Назад");
    }

    /**
     * Посчитает номер предыдущей страницы
     * @param   int $page - Текущая страница
     * @return  int $prevPage - Предыдущая страница
     */
    private static function getPrevPage(int $page)  : int
    {
        if ($page == 1 || $page == 0) {
            $prevPage = 1;
        } else {
            $prevPage = $page - 1;
        }
        return $prevPage;
    }

    /**
     * Посчитает номер следующей страницы
     * @param   int $page - Текущая страница
     * @return  int $nextPage - Следующая страница
     */
    private static function getNextPage(int $page) : int
    {
        if ($page == 1) {
            $nextPage = 2;
        } else {
            $nextPage = $page + 1;
        }
        return $nextPage;
    }

    /**
     * Сгенерирует HTML сущность для кнопки "Вперед"
     * @param int $page
     * @return string
     */
    private static function getNextPageItem(int $page) : string
    {
        return self::getPaginationItem(self::getNextPage($page), "Вперед");
    }

    /**
     * Сгенерирует HTML сущность для кнопки "Вначало"
     * @return string
     */
    private static function getFirstPageItem() : string
    {
        return self::getPaginationItem(1, "Вначало");
    }

    /**
     * Сгенерирует HTML сущность для кнопки "Вконец"
     * @param int $page
     * @return string
     */
    private static function getLastPageItem(int $page) : string
    {
        return self::getPaginationItem($page, "Вконец");
    }

    /**
     * Отрисует навигацию с кнопками
     * @param int $navItemsOffset - Смещение страницы
     * @param int $counter - Общее количество навигационных айтемов для вывода
     * @param int $page - Текущая страница
     * @param int $pageCount - Общее количество страниц
     */
    private static function renderPagination(int $navItemsOffset, int $counter, int $page, int $pageCount) : void
    {
        $pagination =  '<div class="container"><div class="clearfix"></div>
                        <div style="margin: 20px 0px">
                        <div class="text-center"  style="margin: 20px 0px">Страница '.$page.' из '.$pageCount.' </div>
                        <div class="text-center" style="margin: -10px 0px 0px 0px;">
                        <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-center">';
        $pagination .= self::getFirstPageItem();
        if (($page - 1) <= $pageCount && 1 != $page) {
            $pagination .= self::getPrevPageItem($page);
        }
        for ($i = $navItemsOffset; $i <= $counter; $i++) {
            $pagination .= self::getPaginationItem($i+1, $i+1, $page);
        }
        if (($page + 1) <= $pageCount) {
            $pagination .= self::getNextPageItem($page);
        }
        $pagination .= self::getLastPageItem($pageCount);
        $pagination .= '</ul></nav></div></div></div>';
        echo $pagination;
    }

    /**
     * инициализирует вывод пагинации
     * @param int $count - Количество записей
     */
    public static function getPagination(int $count) : void
    {
        $page = (isset($_GET["page"]) && is_numeric($_GET["page"])) ? (int)$_GET["page"] : 1;
        $navItemsOffset = 0;
        $pageCount = ceil($count / ITEMS_ON_PAGE_LIMIT);
        if ($pageCount == 0 || $count <= ITEMS_ON_PAGE_LIMIT) {
            echo "";
        } else {
            if ($pageCount > ITEMS_ON_PAGE_LIMIT) {
                // Расчитаем количество отображаемых страниц с учетом смещения
                $navItemsShift = ceil($page / ITEMS_ON_PAGE_LIMIT);
                $counter = $navItemsShift * ITEMS_ON_PAGE_LIMIT;
                $navItemsOffset = $counter - ITEMS_ON_PAGE_LIMIT;
                // Скорректируем количество страниц если подошли к концу списка
                // TODO: веро но паста, подумать может стоит рефакторить
                if ($counter >= $pageCount) {
                    $counter = $pageCount - 1;
                }
            } else {
                $counter = $pageCount - 1;
            }
            self::renderPagination($navItemsOffset, $counter, $page, $pageCount);
        }
    }
}
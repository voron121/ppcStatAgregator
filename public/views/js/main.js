/**
 * Метод отобразит сообщение для модалки
 */
function getAjaxNotification(messageBlock = ".ajaxNotification") {
    $.get( "/ajax/getAjaxNotification.php", function(msg) {
        $(messageBlock).html(msg);
    });
}
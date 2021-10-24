/**
 * Словарь соответствия условия равенства
 * @type {{equal: string, more: string, less: string}}
 */
var ADDITIONAL_FILTER_CLAUSE_DICT = {
    "equal": "=",
    "more": ">",
    "less": "<",
};

/**
 * Словарь соответствия параметра фильтра
 * TODO: получать массив на основе json конфига
 * @type {{equal: string, more: string, less: string}}
 */
var ADDITIONAL_FILTER_DICT = {
    "spend" : "Spends",
    "ctr" : "CTR",
    "clicks" : "Clicks",
    "sales" : "Sales",
    "acos" : "Acos"
};

/**
 * Ренедрит кнопку с выбором даты
 */
function fillFilterButton() {
    if (
        "" != $("input[name=startDate]").val() && "" != $("input[name=endDate]").val()
        && 0 != $("input[name=startDate]").val() && 0 != $("input[name=endDate]").val()
    ) {
        $("#filter-date-range").text($("input[name=startDate]").val() + ' - ' + $("input[name=endDate]").val());
    } else {
        $("#filter-date-range").text("Не указано");
    }
}

/**
 * Ренедерит html элемент select для дополнительного фильтра с условием равенства
 * @param filter
 * @returns {string|*}
 */
function rendereAdditionalFilterСondition(filter) {
    item = '<label class="mb-1">' + filter + ':</label>';
    item += '<select name="'+filter+'" class="form-control form-control-sm">';
    item += '<option value="more">Больше</option>';
    item += '<option value="less">Меньше</option>';
    item += '<option value="equal">Равно</option>';
    item += '</select>';
    return item;
}

/**
 * Ренедерит html элемент input для дополнительного фильтра
 * @param filter
 * @returns {string|*}
 */
function rendereAdditionalFilterValueInput(filter) {
    item = '<input name="'+filter+'" type="number" min="0" value="0" class="form-control form-control-sm mt-25px">';
    return item;
}

/**
 * Ренедерит html элемент кнопка для дополнительного фильтра
 * @param filter
 * @returns {string|*}
 */
function rendereAdditionalFilterButton(filter) {
    item = '<div data-additional-filter="' + filter + '"';
    item += 'class="btn btn-sm btn-secondary mt-25px additionalFilterButton">Применить</div>';
    return item;
}

/**
 * Ренедерит элемент дополнительного фильтра
 * @param filter
 * @returns {string|*}
 */
function rendereAdditionalFilter(filter) {
    item = '<div class="col-sm">' + rendereAdditionalFilterСondition(filter) + '</div>';
    item += '<div class="col-sm">' + rendereAdditionalFilterValueInput(filter) + '</div>';
    item += '<div class="col-sm">' + rendereAdditionalFilterButton(filter) + '</div>';
    $("#additionalFilter").empty().append(item);
}

/**
 * Вернет html input
 * @param filter
 * @returns {string}
 */
function renderAdditionalFilterInput(filter) {
    value = $("input[name=" + filter + "]").val();
    clause = $("select[name=" + filter + "] :selected").val();
    item = '<input name="' + filter + '" type="hidden" value="' + value + '">';
    item += '<input name="clause' + filter + '" type="hidden" value="' + clause + '">';
    return item;
}

/**
 * Вернет html бандл для фильтра с полем input и бандлом
 * @param filter
 * @returns {string}
 */
function renderAdditionalFilterBundle(filter) {
    value = $("input[name=" + filter + "]").val();
    clause = $("select[name=" + filter + "] option:selected").val();
    bundleText = ADDITIONAL_FILTER_DICT[filter] + " " + ADDITIONAL_FILTER_CLAUSE_DICT[clause] + " " + value + ' ';
    item = '<span class="AdditionalFilterBundle" data-filter="' + filter + '">' + bundleText;
    item += '<i class="fa fa-times-circle"></i>';
    item += '</span>';
    return item;
}

/**
 * Обработчик события нажатия кнопки "применить" для дополнительного фильтра
 * @param filter
 */
function applyAdditionalFilter(filter) {
    item = renderAdditionalFilterBundle(filter) + renderAdditionalFilterInput(filter);
    removeAdditionalFilter(filter);
    $("#additionalFilterParams").append(item);
    $("#additionalFilter").empty();
}

/**
 * Удалит бандл и поле с параметрром дополнительного фильтра
 * @param filter
 */
function removeAdditionalFilter(filter) {
    $("span.AdditionalFilterBundle").filter("[data-filter=" + filter + "]").remove();
    $("input[name=" + filter + "]").remove();
    $("input[name=clause" + filter + "]").remove();
}

/**
 * Рендерит бандлы и инпуты доп фильтров на основе параметров адресной строки
 * @param filter
 */
function renderAdditionalFilter() {
    var url = new URL(window.location.href);
    var searchParams = new URLSearchParams(url.search.substring(1));
    $.each(ADDITIONAL_FILTER_DICT, function (index) {
        if (searchParams.get(index)) {
            value = searchParams.get(index);
            clause = searchParams.get("clause" + index);
            bundleText = ADDITIONAL_FILTER_DICT[index] + " " + ADDITIONAL_FILTER_CLAUSE_DICT[clause] + " " + value + ' ';
            item = '<span class="AdditionalFilterBundle" data-filter="' + index + '">' + bundleText;
            item += '<i class="fa fa-times-circle"></i>';
            item += '</span>';
            item += '<input name="' + index + '" type="hidden" value="' + value + '">';
            item += '<input name="clause' + index + '" type="hidden" value="' + clause + '">';
            $("#additionalFilterParams").append(item);
        }
    });
}

//--------------------------------------------------------------------------------------------------------------------//

$(document).ready(function () {
    /**
     * Обработка события кнопки "применить" для дополнительных фильтров
     */
    $("body").on("click", "div.additionalFilterButton", function() {
        filter = $(this).attr("data-additional-filter");
        applyAdditionalFilter(filter);
    });

    /**
     * Обработка события "удалить" для дополнительных фильтров
     */
    $("body").on("click", "span.AdditionalFilterBundle", function() {
        filter = $(this).attr("data-filter");
        removeAdditionalFilter(filter);
    });

    /**
     * Отрисовка дополнительного фильтра
     */
    $("select[name=additionalFilter]").on("change", function() {
        filter = $(this).val();
        rendereAdditionalFilter(filter);
    });

    fillFilterButton();
    renderAdditionalFilter();
    $('#filter-date-range').daterangepicker({
        opens : 'center',
        autoclose : false,
        alwaysShowCalendars : true,
        alwaysOpen : true,
        showCustomRangeLabel : false,
        "minYear": $("input[name=minStatDate]").val(),
        "maxYear": $("input[name=maxStatDate]").val(),
        //"startDate": $("input[name=startDate]").val(),
        //"endDate": $("input[name=endDate]").val(),
        "buttonClasses": "btn btn-sm",
        "applyClass": "btn btn-secondary",
        "cancelClass": "btn",
        ranges : {
            'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Последние 7 дней': [moment().subtract(6, 'days'), moment()],
            'Последние 14 дней': [moment().subtract(13, 'days'), moment()],
            'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
            'Lifetime': [$("input[name=minStatDate]").val(), $("input[name=maxStatDate]").val()],
        },
        "locale": {
            "applyLabel" : 'Применить',
            "cancelLabel" : 'Отменить',
            "daysOfWeek": [
                "Вс",
                "Пн",
                "Вт",
                "Ср",
                "Чт",
                "Пт",
                "Суб"
            ],
            "monthNames": [
                "Январь",
                "Февраль",
                "Март",
                "Апрель",
                "Май",
                "Июнь",
                "Июль",
                "Август",
                "Сентябрь",
                "Октябрь",
                "Ноябрь",
                "Декабрь"
            ],
            "firstDay": 1
        },
    }, function(start, end, label) {
        $(".filter-date-range").text(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
        $("input[name=startDate]").val(start.format('YYYY-MM-DD'));
        $("input[name=endDate]").val(end.format('YYYY-MM-DD'));
    });
});
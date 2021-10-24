/**
 * Заполнит поля формы
 * @param data
 */
function fillGroupSettingForm(data) {
    $("input[name=groupId]").val(data.id);
    $("input[name=groupName]").val(data.name);
    $("input[name=minAcos]").val(data.minAcos);
    $("input[name=minSpend]").val(data.minSpend);
    $("input[name=minSales]").val(data.minSales);
    $("input[name=minConversion]").val(data.minConversion);
    $(".groupProductList").show();
    getProductsByNames();
}

/**
 * Рандерит список товаров в модальном окне
 * @param products
 */
function renderGroupedProductListInModal(products) {
    groupId = $("input[name=groupId]").val();
    let items = '<table class="table table-sm table-bordered table-hover">' +
        '<thead><tr>' +
        '<th scope="col" style=" width: 80%;">Название товара:</th>' +
        '<th scope="col" style=" width: 20%;">Добавить товар:</th></tr></thead>' +
        '<tbody>';
    $.each(products, function(productId, product) {
        items += '<tr><td>' + product.name + '</td></<tr>';
        items += '<td class="text-center"><input type="checkbox" name="ajaxAddProductTooGroup"';
        if (product.groupId != 0 && groupId === product.groupId) {
            items += 'checked';
        }
        items += ' data-productId="'+ productId +'"/></td></<tr>';
    });
    items += "</tbody></table>";
    $(".groupProductList").html(items);
}

/**
 * получит список всех товаров
 */
function getProductsByNames() {
    $.getJSON( "/ajax/getSponsoredProductsByNames.php", function(data) {
        if ("success" === data.status) {
            renderGroupedProductListInModal(data.products)
        } else {
            console.log("getProductsByNames error");
        }
    });
}

/**
 * Получит список выбранных товаров
 */
function getCheckedProducts() {
    let checkedProducts = [];
    let items = $("input[name=groupProduct]");
    for (i = 0; i < items.length; i++) {
        if (items[i].checked) {
            checkedProducts.push(items[i].value);
        }
    }
    return checkedProducts;
}

/**
 * Создаст группу с товарами
 */
function createProductsGroup() {
    $.ajax({
        url     : "/ajax/setProductsGroup.php",
        type    : "post",
        dataType: "json",
        data    : {
            products : getCheckedProducts()
        },
        complete: function() {
            getAjaxNotification("#messages");
        }
    });
}

/**
 * Разгруппирует товары и удалит группу
 */
function removeProductsGroup() {
    groupId = $("input[name=groupId]").val();
    $.ajax({
        url     : "/ajax/removeProductsGroup.php",
        type    : "post",
        dataType: "json",
        data    : {
            groupId : groupId
        },
        complete: function() {
            getAjaxNotification();
        }
    });
}


/**
 * Сохранит параметры товара
 * @param data
 */
function saveGroupSettings(productId = null) {
    if (productId) {
        productId = productId;
    }
    groupId = $("input[name=groupId]").val();
    groupName = $("input[name=groupName]").val();
    minAcos = $("input[name=minAcos]").val();
    minSpend = $("input[name=minSpend]").val();
    minSales = $("input[name=minSales]").val();
    minConversion = $("input[name=minConversion]").val();
    $.ajax({
        url     : "/ajax/updateProductsGroup.php",
        type    : "post",
        dataType: "json",
        data    : {
            "productId" : productId,
            "groupId" : groupId,
            "groupName" : groupName,
            "minAcos" : minAcos,
            "minSpend" : minSpend,
            "minSales" : minSales,
            "minConversion" : minConversion
        },
        complete: function() {
            getAjaxNotification();
        }
    });
}

$(document).ready(function () {
    /**
     * Создаст группу товаров
     */
    $("#groupProduct").on("click", function() {
        createProductsGroup();
    });

    /**
     * Удалит группу товаров
     */
    $("#ungroupProducts").on("click", function() {
        removeProductsGroup();
    });

    /**
     * Обработаем изменение видимости товаров в модальном окне
     */
    $(document).on( "click", "input[name=ajaxAddProductTooGroup]", function() {
        saveGroupSettings($(this).data("productid"));
    });

    /**
     * Обработаем получение параметров для товара
     */
    $(".groupSetting").on("click", function() {
        groupId = $(this).data('group-id');
        $.ajax({
            url     : "/ajax/getGroupSettings.php",
            type    : "post",
            dataType: "json",
            data: {
                "groupId"   : groupId
            },
            success: function(data) {
                fillGroupSettingForm(data.group);
                getAjaxNotification();
            }
        });
    });

    /**
     * Сохранит параметры группы
     */
    $("#groupSettingsSave").on("click", function() {
        saveGroupSettings();
    });
});
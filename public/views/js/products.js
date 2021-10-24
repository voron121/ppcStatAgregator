/**
 * Заполнит поля формы
 * @param data
 */
function fillProductSettingForm(data) {
    if (data.id) {
        $("label[for=productSynonym]").show();
        $("input[name=productSynonym]").show();
        $(".additionalProductSettings").show();
        $(".productList").hide();
        $("input[name=productId]").val(data.id);
        $("input[name=productSynonym]").val(data.name);
        $("input[name=minAcos]").val(data.minAcos);
        $("input[name=minSpend]").val(data.minSpend);
        $("input[name=minSales]").val(data.minSales);
        $("input[name=minConversion]").val(data.minConversion);
        $("input[name=hideProduct]").prop('checked', false);
        if ("yes" === data.hideProduct) {
            $("input[name=hideProduct]").prop('checked', true);
        }
    } else {
        $("label[for=productSynonym]").hide();
        $("input[name=productSynonym]").hide();
        $(".additionalProductSettings").hide();
        $("input[name=productId]").val(0);
        $("input[name=minAcos]").val(data.minAcos);
        $("input[name=minSpend]").val(data.minSpend);
        $("input[name=minSales]").val(data.minSales);
        $("input[name=minConversion]").val(data.minConversion);
        $(".productList").show();
        getProductsByNamesList();
    }
}

/**
 * Рандерит список товаров в модальном окне
 * @param products
 */
function renderProductListInModal(products) {
    let items = '<table class="table table-sm table-bordered table-hover">' +
        '<thead><tr>' +
        '<th scope="col" style=" width: 85%;">Название товара:</th>' +
        '<th scope="col" style=" width: 15%;">Товар скрыт:</th></tr></thead>' +
        '<tbody>';
    $.each(products, function(productId, product) {
        items += '<tr><td>' + product.name + '</td></<tr>';
        items += '<td class="text-center"><input type="checkbox" name="ajaxHideProduct"';
        if ("yes" === product.hideProduct) {
            items += 'checked';
        }
        items += ' data-productId="'+ productId +'"/></td></<tr>';
    });
    items += "</tbody></table>";
    $(".productList").html(items);
}

/**
 * получит список всех товаров
 */
function getProductsByNamesList() {
    $.getJSON( "/ajax/getSponsoredProductsByNames.php", function(data) {
        if ("success" === data.status) {
            renderProductListInModal(data.products)
        } else {
            console.log("getProductsByNames error");
        }
    });
}

/**
 * Сохранит параметры товара
 * @param data
 */
function saveProductSettings(data) {
    url =  "/ajax/setGeneralProductSettings.php";
    if (data.productId != 0) {
        url =  "/ajax/setProductSettings.php";
    }
    $.ajax({
        url : url,
        type : "post",
        dataType : "json",
        data : data,
        complete: function() {
            getAjaxNotification();
        }
    });
}

$(document).ready(function () {
    $('#products-list').DataTable({
        fixedHeader: false,
        fixedColumns: true,
        scrollX: true,
        scrollY: true,
        scrollCollapse: true,
        ordering: false,
        paging: false,
        info: false,
        bPaginate: false,
        search : false,
        fixedColumns: {
            leftColumns: 2,
            heightMatch: 'auto'
        },
        searching: false
    });

    /**
     * Обработаем изменение видимости товаров в модальном окне
     */
    $(document).on( "click", "input[name=ajaxHideProduct]", function() {
        productId = $(this).data("productid");
        hideProduct = "no";
        if ($(this).is(':checked')) {
            hideProduct = "yes";
        }
        saveProductSettings(
            {
                "productId"   : productId,
                "hideProduct" : hideProduct
            }
        );
    });

    /**
     * Обработаем получение параметров для товара
     */
    $(".productSetting").on("click", function() {
        productId = $(this).data('product-id');
        $.ajax({
            url     : "/ajax/getProductSettings.php",
            type    : "post",
            dataType: "json",
            data: {
                "productId"   : productId
            },
            success: function(data) {
                fillProductSettingForm(data.product);
                getAjaxNotification();
            }
        });
    });

    /**
     * Обработаем получение глобальных параметров товара
     */
    $("a[data-param=generalProductSettings]").on("click", function() {
        $.ajax({
            url     : "/ajax/getGeneralProductSettings.php",
            type    : "post",
            dataType: "json",
            data: {},
            success: function(data) {
                fillProductSettingForm(data.product);
                getAjaxNotification();
            }
        });
    });

    /**
     * Сохранит параметры товара
     */
    $("#productSettingsSave").on("click", function() {
        productId = $(".productModal input[name=productId]").val();
        productSynonym = $(".productModal input[name=productSynonym]").val();
        minAcos = $(".productModal input[name=minAcos]").val();
        minSpend = $(".productModal input[name=minSpend]").val();
        minSales = $(".productModal input[name=minSales]").val();
        minConversion = $(".productModal input[name=minConversion]").val();
        hideProduct = "no";
        if ($('input[name=hideProduct]').is(":checked")) {
            hideProduct = "yes";
        }
        saveProductSettings(
            {
                "productId"   : productId,
                "productSynonym"   : productSynonym,
                "minAcos"   : minAcos,
                "minSpend"   : minSpend,
                "minSales"   : minSales,
                "minConversion"   : minConversion,
                "hideProduct" : hideProduct
            }
        );
    });
});
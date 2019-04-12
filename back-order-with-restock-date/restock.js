jQuery(function($){
var _bkOrders = ['notify','yes'];
var manageStock = $('#_manage_stock');
var stockField = $('#_stock');
var selectedStatus = $('#_stock_status');
var backOrdersField = $('#_backorders');
var allowBackOrders = ($.inArray(backOrdersField.children("option:selected").val(), _bkOrders) >= 0 );
var restockDate = $('.restock_date');

    if(manageStock.prop('checked') && (stockField.val() < 1)){
        restockDate.show();
    }
    else{
        if((selectedStatus.children("option:selected").val() === 'onbackorder') && !manageStock.prop('checked')){
            restockDate.show();
        }
        else{
            restockDate.hide();
        }

    }

    $('#inventory_product_data').on('change', '#_manage_stock, #_stock_status, #_backorders, #_stock', function () {
        switch(this.name) {
            case '_manage_stock':
                    if (manageStock.prop('checked') && allowBackOrders) {
                        restockDate.show();
                    }
                    else if((!manageStock.prop('checked'))&&(selectedStatus.children("option:selected").val() === 'onbackorder')){
                        restockDate.show();
                        }
                    else {
                        restockDate.hide();
                        }
                break;
            case '_stock' :
                console.log();
                if ((stockField.val() < 1) && $.inArray(backOrdersField.children("option:selected").val(), _bkOrders)>=0) {
                    restockDate.show();
                }
                else {
                    restockDate.hide();
                }
                break;
            case '_stock_status':
                if (selectedStatus.children("option:selected").val() === 'onbackorder') {
                    restockDate.show();
                }
                else {
                    restockDate.hide();
                }
                break;
            case '_backorders':
                allowBackOrders = ($.inArray(backOrdersField.children("option:selected").val(), _bkOrders) >= 0 );
                if (manageStock.prop('checked') && allowBackOrders) {
                    restockDate.show();
                }
                else {
                    restockDate.hide();
                }
                break;
        }
    });
});

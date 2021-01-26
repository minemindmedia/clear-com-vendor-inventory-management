jQuery(document).ready(function ($) {
    "use strict";
    var base_url = $("#baseUrl").val();
    var home_url = base_url + 'assets/img/loader.gif';
    function removeArrayValue(arr) {
        var what, a = arguments, L = a.length, ax;
        while (L > 1 && arr.length) {
            what = a[--L];
            while ((ax = arr.indexOf(what)) !== -1) {
                arr.splice(ax, 1);
            }
        }
        return arr;

    }
    var selected_ids = new Array();
    $('.po-selected-products').on('click', function () {
        var selected_id = $(this).val();
        if ($(this).is(':checked')) {
            selected_ids.push(selected_id);
            console.log('checkbox');
        } else {
            console.log(selected_ids);
            var valueExsists = selected_ids.includes(selected_id);
            if (valueExsists) {
                removeArrayValue(selected_ids, selected_id);
                console.log(selected_ids);
            }
        }

    });
    $('#generate-po-button').on('click', function (e) {
        e.preventDefault();
   
        $("body").append("<div id='loading' style='width: 100%;height: 100%;top: 0;left: 0;position: fixed;opacity: 0.7; background-color: #fff;z-index: 99;text-align: center;'><img style=' position: absolute;top: 50%;left: 50%;z-index:100 ' width='50' height='50' class='label-spinner' src='" + home_url + "'></div>");
        var purchase_order_data = new Array();
        console.log(selected_ids);
        $.each(selected_ids, function (key, value) {
            purchase_order_data.push({
                selected_id: value,
                product_qty: $("#order-quantity-" + value).val(),
                selected_vendor: $("#row-selected-vendor-" + value).val()
            });
        });
        console.log(purchase_order_data);
//        if (purchase_order_data.length > 0) {
            var data = {
                action: 'generate_po',
                purchase_order_data: purchase_order_data
            };
            var ajaxUrl = generate_po_ajax_object.ajax_url;
            console.log(ajaxUrl);
            jQuery.post(ajaxUrl, data, function (data) {
                var response = jQuery.parseJSON(data);
                window.location.href = response.redirect_url
            });
//        }
    });
    $('.vendor-select').on('keyup change', function (e) {
        e.preventDefault();
        var selected_id = $(this).val();
        var vendor_price = $(this).find('option:selected').data('vendor_price');
        $(this).closest('td').next('td').html(vendor_price);
    });
        
     jQuery('[data-role="datetime"]').datepicker({
        dateFormat : 'yy-mm-dd'
    });

    $('#sync-vendor').on('click', function (e) {
        e.preventDefault();
        $("body").append("<div id='wcvim_spinner' style='width: 100%;height: 100%;top: 0;left: 0;position: fixed;opacity: 0.7; background-color: #fff;z-index: 99;text-align: center;'><img style=' position: absolute;top: 50%;left: 50%;z-index:100 ' width='50' height='50' class='label-spinner' src='" + home_url + "'></div>");
        var data = {
            action: 'updateVendorProductMapping',
        };
        var ajaxUrl = generate_po_ajax_object.ajax_url;
        jQuery.post(ajaxUrl, data, function (data) {
            var response = jQuery.parseJSON(data);
            console.log(response);
            $('#wcvim_spinner').remove();
        });
    });
    $(document).on('click', '[data-role="order-title"]', function(event) {
        var element = $(this);
        var label = element.text();
        element.text(element.attr('data-label'));
        element.attr('data-label', label);
        var target = $('[data-role="order-table"][data-id="' + element.attr('data-id') + '"]');
        if (target.css('display') == 'none') {
            target.slideDown();
        } else {
            target.slideUp();
        }
    });
        $(document).on('click', '#wcvm-delete-all-button', function() {
            console.log('ddd');
        if (confirm('Are you sure?')) {
            $('#wcvm-delete-all-form').submit();
        }
    });
    jQuery(".delete_entire").bind("click", function () {
        var txt;
        var r = confirm("You're about to delete the entire PO. Are you sure you want to continue?");
        if (r == true) {
            jQuery("#" + this.id).submit();
        }
    });
});
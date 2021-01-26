<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 * @var string $show_status
 * @var string $status
 */
?>
<style>
    #TB_ajaxContent {
        text-align: center;
    }
    .bold-center{
        font-weight: bold !important;
        text-align: center !important;
    }
    .wp-list-table.wcvm-orders .manage-column{
        font-size: 10px !important;
    }
    .widefat{
        background-color: #f9f9f9 !important;
    }

</style>
<div class="wrap">
    <?php
    global $wpdb;
    $posts_table = $wpdb->prefix . "posts";
    $posts_table_sql = "SELECT * FROM " . $posts_table . " WHERE post_type = 'wcvm-order' AND post_status = 'auto-draft' ORDER BY wp_posts.post_date DESC ";
//    $orders = $wpdb->get_results($posts_table_sql);
    ?>
    <h1><?= esc_html__('View/Edit Purchase Orders', 'wcvm') ?></h1>
    <?php
        $table = new Vendor_Management_Columns(); ?>
        <?php $table_headers = $table->get_columns_vendors_list(); ?>
    
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=auto-draft') ?>"<?php if (!$status || $status == 'auto-draft'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('New', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=draft') ?>"<?php if ($status == 'draft'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('On order', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=pending') ?>"<?php if ($status == 'pending'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Back order', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=publish') ?>"<?php if ($status == 'publish'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Completed', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=private') ?>"<?php if ($status == 'private'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Canceled', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=returned') ?>"<?php if ($status == 'returned'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Returns Open', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=return_closed') ?>"<?php if ($status == 'return_closed'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Returns Closed', 'wcvm') ?></a>
    |
    <?php /*
      <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=all') ?>"<?php if ($status == 'all'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('All', 'wcvm') ?></a>
      |
     */ ?>
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=trash') ?>"<?php if ($status == 'trash'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Trash', 'wcvm') ?></a>    
    <?php if ($status == 'trash'): ?>
        |
        <button type="submit" id="wcvm-delete-all-button"><?= esc_html__('Delete All', 'wcvm') ?></button>
        <form action="" method="post" id="wcvm-delete-all-form">
            <input type="hidden" name="delete-all" value="all">
        </form>
    <?php endif ?>
    <br><br>
    <div style="float: right;margin-bottom: 20px;">
        <form action="<?php echo get_site_url() . '/wp-admin/admin.php?page=wcvm-epo&status=' . $_GET['status']; ?>" method="get" id="wcvm-delete-all-form">
            <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
            <input type="hidden" name="status" value="<?php echo $_GET['status'] ?>"/>
            <input type="text" name="search_po" id="search_po" value="<?php echo $_GET['search_po'] ?>" placeholder="Search Through PO #">
        </form>
    </div>

    <?php add_thickbox(); ?>
    <div id="my-content-id" style="display:none;">
        <img id="loading_image" src="../wp-content/plugins/woocommerce-vendor-management/templates/loading2.gif"/>
        <div id="vendor_details">

        </div>
    </div>

    <form style="clear: both" id="<?= esc_attr($order->ID) ?>" action="<?= site_url('/wp-admin/admin.php?page=wcvm-epo') ?>" method="post">

        <div style="float: left;width: 200px; padding: 2px;">
            <?php get_print_status($order); ?>
            <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html(1001)) ?>
        </div>
        <div style="float: left;width: 250px; padding: 2px;">
            <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html('vendor')) ?><br>
            <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime(01 / 15 / 2021))) ?>
        </div>
    </form>
    <div style="float: right;padding: 2px;">
        <button type="button" class="button" data-id="<?= esc_attr($order->ID) ?>" data-role="order-title" data-label="<?php
        if ($status == 'publish' || $status == 'private' || $status == 'trash' || $status == 'multiple') {
            echo 'Close';
        } else if ($order->post_status != $show_status && strpos($order->post_status, $show_status) === false) {
            echo 'Close';
        } else {
            echo 'Open';
        }
        ?>"><?php
                    if ($status == 'publish' || $status == 'private' || $status == 'trash' || $status == 'multiple') {
                        echo 'Open';
                    } else if ($order->post_status == $show_status || strpos($order->post_status, $show_status) !== false) {
                        echo 'Close';
                    } else {
                        'Open';
                    }
                    ?></button>
    </div>
    <table class="wp-list-table widefat striped wcvm-orders" style="width:100%; max-width: 1400px; border-collapse: collapse;">

        <thead>
            <tr bgcolor="#e8e8e8" style="font-size:11px;">
                <?php foreach ($table_headers as $header) {
                    ?>
                    <th><?php echo $header; ?></th><?php }
                ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td><a href="">CHRY052</a></td>
                <td style="padding: 5px;"bgcolor="orange">LOW</td>
                <td>$9.00</td>
                <td>RK-CHY-4</td>
                <td>$3.45</td>
                <td>5</td>
                <td>10</td>
                <td><input type="text" value="8" style="width:60px;"></td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <td>5</td>
                <td>0</td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <th><input type="checkbox"></th>

            </tr>
            <tr>
                <td></td>
                <td><a href="">CHRY052</a></td>
                <td bgcolor="orange">LOW</td>
                <td>$9.00</td>
                <td>RK-CHY-4</td>
                <td>$3.45</td>
                <td>5</td>
                <td>10</td>
                <td><input type="text" value="8" style="width:60px;"></td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <td>5</td>
                <td>0</td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <th><input type="checkbox"></th>

            </tr>
        </tbody>
        <tfoot>
            <tr bgcolor="#e8e8e8" style="font-size:11px;">
                <?php foreach ($table_headers as $header) {
                    ?>
                    <th><?php echo $header; ?></th><?php }
                ?>
            </tr>
        </tfoot>

    </table>

    <?php // if ($order->post_status == 'auto-draft' || $order->post_status == 'draft'): ?>
    <div style="padding-top: 5px;width: 300px;float: left">
        <input type="date" name="expected_date" style="width: 100px;" value="<?= esc_attr($order->po_expected_date ? date('Y-m-d', $order->po_expected_date) : '') ?>" placeholder="<?= esc_attr__('YYYY-mm-dd', 'wcvm') ?>" data-role="datetime">
        <button type="submit" name="action" value="update" class="button button-primary"><?= esc_html__($order->post_status == 'auto-draft' ? 'Set Date & Place On Order' : 'Update Order', 'wcvm') ?></button>
    </div>
    <?php // endif ?>
    <div style="padding-top: 5px;float: left">
        <button type="submit" name="print" value="print" class="button button-primary"><?= esc_html__('Print Order', 'wcvm') ?></button>
    </div>
    <?php // if ($order->post_status == 'auto-draft' || $order->post_status == 'draft'): ?>
        <div style="padding-top: 5px;float: right">
            <input type="text" name="_sku" value="" style="height: 26px;" data-role="product-sku" placeholder="<?= esc_html__('SKU', 'wcvm') ?>" data-id="<?= esc_attr($order->ID) ?>">
            <button type="submit" name="action" value="add" class="button"><?= esc_html__('Add Product', 'wcvm') ?></button>
        </div>
    <?php // endif ?>
    <br><br>
    <br><br>
    <form>

        <div style="float: left;width: 200px; padding: 2px;">
            <?php get_print_status($order); ?>
            <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html(1001)) ?>
        </div>
        <div style="float: left;width: 250px; padding: 2px;">
            <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html('vendor')) ?><br>
            <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime(01 / 15 / 2021))) ?>
        </div>
    </form>
    <div style="float: right;padding: 2px;">
        <button type="button" class="button" data-id="<?= esc_attr($order->ID) ?>" data-role="order-title" data-label="<?php
        if ($status == 'publish' || $status == 'private' || $status == 'trash' || $status == 'multiple') {
            echo 'Close';
        } else if ($order->post_status != $show_status && strpos($order->post_status, $show_status) === false) {
            echo 'Close';
        } else {
            echo 'Open';
        }
        ?>"><?php
                    if ($status == 'publish' || $status == 'private' || $status == 'trash' || $status == 'multiple') {
                        echo 'Open';
                    } else if ($order->post_status == $show_status || strpos($order->post_status, $show_status) !== false) {
                        echo 'Close';
                    } else {
                        'Open';
                    }
                    ?></button>
    </div>
    <table class="wp-list-table widefat striped wcvm-orders" style="width:100%; max-width: 1400px; border-collapse: collapse;">

        <thead>
            <tr bgcolor="#e8e8e8" style="font-size:11px;">
                <?php foreach ($table_headers as $header) {
                    ?>
                    <th><?php echo $header; ?></th><?php }
                ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td><a href="">CHRY052</a></td>
                <td style="padding: 5px;"bgcolor="orange">LOW</td>
                <td>$9.00</td>
                <td>RK-CHY-4</td>
                <td>$3.45</td>
                <td>5</td>
                <td>10</td>
                <td><input type="text" value="8" style="width:60px;"></td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <td>5</td>
                <td>0</td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <th><input type="checkbox"></th>

            </tr>
            <tr>
                <td></td>
                <td><a href="">CHRY052</a></td>
                <td bgcolor="orange">LOW</td>
                <td>$9.00</td>
                <td>RK-CHY-4</td>
                <td>$3.45</td>
                <td>5</td>
                <td>10</td>
                <td><input type="text" value="8" style="width:60px;"></td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <td>5</td>
                <td>0</td>
                <td><input type="text" value="10" style="width:60px;"></td>
                <th><input type="checkbox"></th>

            </tr>
        </tbody>
        <tfoot>
            <tr bgcolor="#e8e8e8" style="font-size:11px;">
                <?php foreach ($table_headers as $header) {
                    ?>
                    <th><?php echo $header; ?></th><?php }
                ?>
            </tr>
        </tfoot>

    </table>

    <?php // if ($order->post_status == 'auto-draft' || $order->post_status == 'draft'): ?>
    <div style="padding-top: 5px;width: 300px;float: left">
        <input type="date" name="expected_date" style="width: 100px;" value="<?= esc_attr($order->po_expected_date ? date('Y-m-d', $order->po_expected_date) : '') ?>" placeholder="<?= esc_attr__('YYYY-mm-dd', 'wcvm') ?>" data-role="datetime">
        <button type="submit" name="action" value="update" class="button button-primary"><?= esc_html__($order->post_status == 'auto-draft' ? 'Set Date & Place On Order' : 'Update Order', 'wcvm') ?></button>
    </div>
    <?php // endif ?>
    <div style="padding-top: 5px;float: left">
        <button type="submit" name="print" value="print" class="button button-primary"><?= esc_html__('Print Order', 'wcvm') ?></button>
    </div>
    <?php // if ($order->post_status == 'auto-draft' || $order->post_status == 'draft'): ?>
        <div style="padding-top: 5px;float: right">
            <input type="text" name="_sku" value="" style="height: 26px;" data-role="product-sku" placeholder="<?= esc_html__('SKU', 'wcvm') ?>" data-id="<?= esc_attr($order->ID) ?>">
            <button type="submit" name="action" value="add" class="button"><?= esc_html__('Add Product', 'wcvm') ?></button>
        </div>
    <?php // endif ?>
    <br><br>
    <?php // add_thickbox(); ?>
    <div id="my-content-id" style="display:none;">
        <img id="loading_image" src="../wp-content/plugins/woocommerce-vendor-management/templates/loading2.gif"/>
        <div id="vendor_details">

        </div>
    </div>


    <?php if ($orders): ?>

        <?php foreach ($orders as $order): ?>
            <a name="order<?= esc_attr($order->ID) ?>"></a>
            <?php $vendor = null ?>
            <?php foreach ($vendors as $vendor): ?>
                <?php if ($vendor->ID == $order->post_parent) break ?>
            <?php endforeach ?>
            <form style="clear: both" id="<?= esc_attr($order->ID) ?>" action="" method="post">
                <input type="hidden" name="ID" value="<?= esc_attr($order->ID) ?>">
                <input type="hidden" name="status" value="<?= esc_attr($status) ?>">
                <?php if ($order->expected_date): ?>
                    <div style="float: left;width: 200px; padding: 2px;">
                        <?= esc_html__('Expected Date', 'wcvm') ?>: <?= date('m/d/Y', $order->expected_date) ?>
                    </div>
                <?php elseif ($order->po_expected_date): ?>
                    <div style="float: left;width: 200px; padding: 2px;">
                        <?= esc_html__('Expected Date', 'wcvm') ?>: <?= date('m/d/Y', $order->po_expected_date) ?>
                    </div>
                <?php endif ?>
                <?php if ($order->set_date): ?>
                    <div style="float: left;width: 200px; padding: 2px;">
                        <?= esc_html__('Inventory Set Date', 'wcvm') ?>: <?= date('m/d/Y', $order->set_date) ?>
                    </div>
                <?php endif ?>
                <div style="float: left;width: 200px; padding: 2px;">
                    <?php get_print_status($order); ?>
                    <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->ID)) ?>
                </div>
                <div style="float: left;width: 250px; padding: 2px;">
                    <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html($vendor->post_title)) ?><br>
                    <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->post_date))) ?>
                </div>
                <?php
                if ($status == "returned") {
                    ?>
                    <div style="float: right;padding: 2px;">
                        <button type="button" class="button" onclick="mark_closed('<?php echo $order->ID ?>')">Mark Selected Closed</button>
                    </div>
                    <?php
                }
                ?>


                <div style="clear: both;"></div>
            </form>
            <br><br>
        <?php endforeach ?>
    <?php else: ?>
        <div><?= esc_html__('No orders were found', 'wcvm') ?></div>
    <?php endif ?>
    <div id="ajax-response"></div>
    <br class="clear">
</div>

<script>
    jQuery(".delete_entire").bind("click", function () {
        var txt;
        var r = confirm("You're about to delete the entire PO. Are you sure you want to continue?");
        if (r == true) {
            jQuery("#" + this.id).submit();
        }
    });

    function mark_closed(id)
    {
        var ids_to_delete = "";
        var order_to_process = id;
        jQuery(".deleting").each(function (index) {
            var element_to_play = jQuery(this).parent().parent().parent().parent().parent();
            if (jQuery(this).is(':checked'))
            {
                if (ids_to_delete != "")
                {
                    ids_to_delete += ",";
                }
                ids_to_delete += this.id + "_" + jQuery(element_to_play).attr("id");
            }
        });
        jQuery.ajax({
            type: "POST",
            data: {
                ids_to_delete: ids_to_delete,
                order_to_process: order_to_process
            },
            url: "<?php echo site_url(); ?>/wp-content/plugins/woocommerce-vendor-management/close_selected_returns.php",
            beforeSend: function () {
                // setting a timeout
                //$(placeholder).addClass('loading');
            },
            success: function (data)
            {
                if (data == 1)
                {
                    location.reload();
                }
            }
        });
    }
    jQuery(".delete_selected").bind("click", function () {

        var ids_to_delete = "";
        var order_to_process = this.id;
        jQuery(".deleting").each(function (index) {
            var element_to_play = jQuery(this).parent().parent().parent().parent().parent();
            if (jQuery(this).is(':checked'))
            {
                if (ids_to_delete != "")
                {
                    ids_to_delete += ",";
                }
                ids_to_delete += this.id + "_" + jQuery(element_to_play).attr("id");
            }
        });
        if (ids_to_delete != "")
        {
            jQuery.ajax({
                type: "POST",
                data: {
                    ids_to_delete: ids_to_delete,
                    order_to_process: order_to_process
                },
                url: "<?php echo site_url(); ?>/wp-content/plugins/woocommerce-vendor-management/delete_selected_pos.php",
                beforeSend: function () {
                    // setting a timeout
                    //$(placeholder).addClass('loading');
                },
                success: function (data)
                {
                    if (data == 1)
                    {
                        location.reload();
                    }
                }
            });
        }
    });
    function load_on_order_details(item_id)
    {
        jQuery("#loading_image").css('display', '');
        jQuery("#vendor_details").html('');
        jQuery.ajax({
            type: "POST",
            data: {
                item_id: item_id
            },
            url: "<?php echo site_url(); ?>/wp-content/plugins/woocommerce-vendor-management/load_on_order.php",
            beforeSend: function () {
                // setting a timeout
                //$(placeholder).addClass('loading');
            },
            success: function (data)
            {
                jQuery("#loading_image").css('display', 'none');
                jQuery("#vendor_details").html(data);
            }
        });
    }
    var non_updated_html = '';
    var quick_mode = true;
    function load_quick_edit(on_page_id, item_id)
    {
        if (quick_mode)
        {
            quick_mode = false;
            var row_values = new Array();
            var i = 0;
            jQuery("#product-" + item_id).find('td').each(function () {
                row_values[i] = jQuery(this).html();
                i++;
            });
            var tags_cats = jQuery("#" + on_page_id + "_tags").val().split(",");
            var tags_string = "";
            for (var j = 1; j < tags_cats.length; j++)
            {

                var tag_details = tags_cats[j].split("|");
                if (tags_string != "")
                {
                    tags_string += ",";
                }
                tags_string += "<a href='" + tag_details[0] + "'>" + tag_details[1] + "</a>";
            }
            var edit_form_html = "";
            edit_form_html = "<td colspan='15'>";
            edit_form_html += '<fieldset class="inline-edit-col-left">';
            edit_form_html += '<legend class="inline-edit-legend">Quick View Product : <b> ' + jQuery("#" + on_page_id + "_sku").val() + "-" + jQuery("#" + on_page_id + "_title").val() + '</b> (' + tags_string + ')</legend>';
            edit_form_html += '</fieldset>';
            edit_form_html += '<table style="width: 100%;">';
            edit_form_html += '<tr>';
            edit_form_html += '<td colspan="" rowspan="5"><img style="width:150px;" src="' + jQuery("#" + on_page_id + "_product_image").val() + '" /></td>';
            edit_form_html += '</tr>';
            edit_form_html += '<tr>';
            edit_form_html += '<td colspan="3"><b>CC SKU</b> : <br>' + jQuery("#" + on_page_id + "_sku").val() + '</td>';
            edit_form_html += '<td colspan=""><b>Stock Status</b> : <br>' + row_values[2] + '</td>';
            edit_form_html += '<td colspan="2"><b>Our Price</b> : <br>' + row_values[3] + '</td>';
            edit_form_html += '<td colspan="2"><b>Vendor SKU</b> : <br>' + jQuery("#" + on_page_id + "_vendor_sku").val() + '</td>';
            edit_form_html += '<td colspan="2"><b>Vendor Price</b> : <br>' + row_values[5] + '</td>';
            edit_form_html += '</tr>';
            edit_form_html += '<tr>';
            edit_form_html += '<td colspan="2"><b>QTY On Hand</b> : <br>' + row_values[6] + '</td>';
            edit_form_html += '<td colspan=""><b>Low Thresh</b> : <br>' + jQuery("#" + on_page_id + "_low_thresh").val() + '</td>';
            edit_form_html += '<td colspan=""><b>Reorder Thresh</b> : <br>' + jQuery("#" + on_page_id + "_reorder_thresh").val() + '</td>';
            edit_form_html += '<td colspan=""><b>Reorder QTY</b> : <br>' + jQuery("#" + on_page_id + "_reorder_qty").val() + '</td>';
            edit_form_html += '<td colspan=""><b>On Order</b> : <br>' + row_values[11] + '</td>';
            edit_form_html += '<td colspan=""><b>On Vendor BO</b> : <br>' + row_values[12] + '</td>';
            edit_form_html += '<td colspan="2"><b>Order QTY</b> : <br>' + jQuery("#" + on_page_id + "_reorder_qty").val() + '</td>';
            edit_form_html += '</tr>';
            edit_form_html += '<tr>';
            edit_form_html += '<td colspan="3"><b>Sale Record For</b><br>&nbsp;</td>';
            edit_form_html += '<td colspan=""><b>30 days</b> <br>' + jQuery("#" + on_page_id + "_sale_30").val() + '</td>';
            edit_form_html += '<td colspan="2"><b>60 days</b> <br>' + jQuery("#" + on_page_id + "_sale_60").val() + '</td>';
            edit_form_html += '<td colspan="2"><b>90 days</b> <br>' + jQuery("#" + on_page_id + "_sale_90").val() + '</td>';
            edit_form_html += '<td colspan="2"><b>12 Months</b> <br>' + jQuery("#" + on_page_id + "_sale_365").val() + '</td>';
            edit_form_html += '<td colspan="2"><b>Rare</b> <br>' + jQuery("#" + on_page_id + "_rare").val() + '</td>';
            edit_form_html += '</tr>';
            edit_form_html += '<tr>';
            edit_form_html += '<td style="text-align: right;" colspan="10">\n\
                                <a href="javascript:cancel_quick_edit(&apos;' + item_id + '&apos;)" class="button button-primary">Close</a></td>';
            edit_form_html += '</tr></table>';


            edit_form_html += '</td>';
            non_updated_html = jQuery("#product-" + item_id).html();
            jQuery("#product-" + item_id).html(edit_form_html);

        } else
        {
            alert("Please close the other opened quick view window!");
        }
    }
    function cancel_quick_edit(item_id) {
        document.getElementById("product-" + item_id).innerHTML = non_updated_html;
        jQuery('.quick_edit').css('visibility', 'visible');
        quick_mode = true;
    }
</script>
<?php

function get_print_status($order = FALSE) {
    if ($order->post_status == 'auto-draft') {
        echo 'Status: New<br>';
    } else if ($order->post_status == 'draft') {
        echo 'Status: On order<br>';
    } else if ($order->post_status == 'pending') {
        echo 'Status: Backordered<br>';
    } else if ($order->post_status == 'publish') {
        echo 'Status: Completed<br>';
    } else if ($order->post_status == 'private') {
        echo 'Status: Canceled<br>';
    } else if ($order->post_status == 'returned') {
        echo 'Status: Returned<br>';
    } else if ($order->post_status == 'returned') {
        echo 'Status: Returned<br>';
    } else if ($order->post_status == 'return_closed') {
        echo 'Status: Returns Closed<br>';
    } else if ($order->post_status == 'trash') {
        echo 'Status: Trash<br>';
    } else if (strpos($order->post_status, $_REQUEST['status']) !== FALSE) {
        $status_string = "Status : ";
        if ($_REQUEST['status'] == 'pending') {
            $status_string .= "Backordered";
        } elseif ($_REQUEST['status'] == 'publish') {
            $status_string .= "Completed";
        } elseif ($_REQUEST['status'] == 'private') {
            $status_string .= "Canceled";
        } elseif ($_REQUEST['status'] == 'returned') {
            $status_string .= "Returned";
        } elseif ($_REQUEST['status'] == 'return_closed') {
            $status_string .= "Returns Closed";
        }
        $status_string .= " (Partially)<br>";
        echo $status_string;
    } else if ($_REQUEST['status'] == 'multiple') {
        echo 'Status: Multiple<br>';
    } else {
        echo 'Status: Trash<br>';
    }
}
?>
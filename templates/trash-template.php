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

</style>
<div class="wrap">
    <?php
    global $wpdb;
    $records = false;

//    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'new-order';
    $show_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'new-order';
    ?>
    <h1><?= esc_html__('View/Edit Purchase Orders', 'wcvm') ?></h1>
    <?php
    $table = new Vendor_Management_Columns();
    ?>
    <?php $table_headers = $table->get_on_order_columns_list(); 
                require_once plugin_dir_path(__FILE__) . 'po-status-bar.php';
                ?>

<!--    <a href="<? site_url('/wp-admin/admin.php?page=wcvm-epo&status=new-order') ?>"<?php // if (!$status || $status == 'new-order'): ?> style="font-weight: bold"<?php // endif ?>><? esc_html__('New', 'wcvm') ?></a>
    |
    <a href="<? site_url('/wp-admin/admin.php?page=wcvm-epo&status=on-order') ?>"<?php // if ($status == 'on-order'): ?> style="font-weight: bold"<?php // endif ?>><? esc_html__('On order', 'wcvm') ?></a>
    |
    <a href="<? site_url('/wp-admin/admin.php?page=wcvm-epo&status=back-order') ?>"<?php // if ($status == 'back-order'): ?> style="font-weight: bold"<?php // endif ?>><? esc_html__('Back order', 'wcvm') ?></a>
    |
    <a href="<? site_url('/wp-admin/admin.php?page=wcvm-epo&status=completed') ?>"<?php // if ($status == 'completed'): ?> style="font-weight: bold"<?php // endif ?>><? esc_html__('Completed', 'wcvm') ?></a>
    |
    <a href="<? site_url('/wp-admin/admin.php?page=wcvm-epo&status=canceled') ?>"<?php // if ($status == 'canceled'): ?> style="font-weight: bold"<?php // endif ?>><? esc_html__('Canceled', 'wcvm') ?></a>
    |
    <a href="<? site_url('/wp-admin/admin.php?page=wcvm-epo&status=returned') ?>"<?php // if ($status == 'returned'): ?> style="font-weight: bold"<?php // endif ?>><? esc_html__('Returns Open', 'wcvm') ?></a>
    |
    <a href="<? site_url('/wp-admin/admin.php?page=wcvm-epo&status=return_closed') ?>"<?php // if ($status == 'return_closed'): ?> style="font-weight: bold"<?php // endif ?>><? esc_html__('Returns Closed', 'wcvm') ?></a>
    |
    <a href="<? site_url('/wp-admin/admin.php?page=wcvm-epo&status=trash') ?>"<?php // if ($status == 'trash'): ?> style="font-weight: bold"<?php // endif ?>><? esc_html__('Trash', 'wcvm') ?></a>    -->
    <?php
    if ($orders) {
        ?>
        <button type="submit" id="wcvm-delete-all-button"><?= esc_html__('Delete All', 'wcvm') ?></button>
        <form action="" method="post" id="wcvm-delete-all-form">
            <input type="hidden" name="delete-all" value="all">
        </form>

        <?php
        $get_status = "";
        if (array_key_exists('status', $_GET)) {
            $get_status = $_GET['status'];
        }
        ?>
        <div style="float: right;margin-bottom: 20px;">
            <form action="" method="get" id="wcvm-search-form">
                <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
                <input type="hidden" name="status" value="<?php echo $get_status ?>"/>
                <input type="text" name="search_po" id="search_po" value="<?php // echo $get_status                ?>" placeholder="Search Through PO #">
            </form>
        </div>
        <div style="float: right;margin-bottom: 20px;">
            <form action="<?php echo get_site_url() . '/wp-admin/admin.php?page=wcvm-epo&status=' . $get_status; ?>" method="get" id="wcvm-delete-all-form">
                <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
                <input type="hidden" name="status" value="<?php echo $get_status ?>"/>
            </form>
        </div>
        <?php
        $printed_po_numbers = [];
        $last_order_id = 0;
        $last_expected_date = '';
        foreach ($orders as $order) {
            ?>
            <div style="clear: both;"></div>

            <?php
            if ($last_order_id > 0 && $last_order_id != $order->order_id) {
                $records = true;
                ?>
            </tbody>
            <tfoot>
                <tr bgcolor="#e8e8e8" style="font-size:11px;">
                    <?php
                    foreach ($table_headers as $header) {
                        ?>
                        <th><?php echo $header; ?></th><?php
                    }
                    ?>
                </tr>
            </tfoot>

            </table>

            <div style="padding-top: 5px;float: left">
                <button type="submit" name="print" value="print" class="button button-primary"><?= esc_html__('Print Order', 'wcvm') ?></button>
            </div>
            </div>
            <div style="clear: both;"></div>
            <br><br>
            <br><br>
            </form>


            <?php
        }

        if (!in_array($order->order_id, $printed_po_numbers)) {

            $records = true;
            ?>
            <form style="clear: both" class="purchase-order"  id="<?= esc_attr($order->order_id) ?>" action="<?= site_url('/wp-admin/admin.php?page=wcvm-epo') ?>" method="post">
                <input type="hidden" name="ID" value="<?= esc_attr($order->order_id) ?>">
                <input type="hidden" name="status" value="<?= esc_attr($status) ?>">



                <div style="float: left;width: 200px; padding: 2px;">
                    <?php get_print_status($order); ?>
                    <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->order_id)) ?>
                </div>
                <div style="float: left;width: 250px; padding: 2px;">
                    <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html(get_the_title($order->vendor_id))) ?><br>
                    <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->order_date))) ?>
                </div>

                <div style="float: right;padding: 2px;">
                    <button type="button" class="button" data-id="<?= esc_attr($order->order_id) ?>" data-role="order-title" data-label="<?php
//                    if ($status == 'publish' || $status == 'private' || $status == 'trash' || $status == 'multiple') {
                        echo 'Close';
//                    } else if ($order->post_status != $show_status && strpos($order->post_status, $show_status) === false) {
//                        echo 'Close';
//                    } else {
//                        echo 'Open';
//                    }
                    ?>"><?php
//                                if ($status == 'publish' || $status == 'private' || $status == 'trash' || $status == 'multiple') {
                                    echo 'Open';
//                                } else if ($order->post_status == $show_status || strpos($order->post_status, $show_status) !== false) {
//                                    echo 'Close';
//                                } else {
//                                    'Open';
//                                }
                                ?></button>
                </div>

                <?php // if ($order->post_status == 'trash' || $order->post_status == 'new' || $order->post_status == 'new-order'): ?>
                    <div style="float: right;padding: 2px;">
                        <?php if ($order->post_status == 'trash'): ?>
                            <button type="submit" name="unarchive" value="unarchive" class="button"><?= esc_html__('Restore', 'wcvm') ?></button>
                            <button type="submit" name="delete" value="delete" class="button"><?= esc_html__('Delete', 'wcvm') ?></button>
                    </div>
                <?php endif ?>
                <div<?php
//                $display = "";
//                if (($order->post_status != $show_status && strpos($order->post_status, $show_status) === false) || $status == 'completed' || $status == 'canceled' || $status == 'trash') {
                    $display = "none";
//                }
                ?> style="width:100%;display: <?php echo $display; ?>" data-role="order-table" data-id="<?= esc_attr($order->order_id) ?>" id="<?= esc_attr($order->order_id) ?>">
                    <table class="wp-list-table fixed widefat striped wcvm-orders" style="width:100%; max-width: 1400px; border-collapse: collapse;">

                        <thead>
                            <tr bgcolor="#e8e8e8" style="font-size:11px;">
                                <?php
                                foreach ($table_headers as $header) {
                                    ?>
                                    <th><?php echo $header; ?></th>
                                    <?php
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $printed_po_numbers[] = $order->order_id;
                            $last_order_id = $order->order_id;
                            //$last_expected_date = $order->po_expected_date;
                        }
                        ?>
                        <tr>
        <!--                                <td><?php
//                                    if ($order->rare) {
//                                        echo '&#10004;';
//                                    }
                            ?></td>-->
                            <td><?php echo $order->product_sku; ?></td>
                            <td><?php
                                $stock = $order->product_stock;

                                if ($stock <= 0) {
                                    echo '<span style="background: red;padding: 5px;color: white">' . esc_html__('OUT', 'wcvm') . '</span>';
                                } else {
                                    echo '<span style="background: green;padding: 5px;color: white">' . esc_html__('IN', 'wcvm') . '</span>';
                                }
//                                    elseif ($order->threshold_low && $stock <= 0) {
//                                        echo '<span style="background: red;padding: 5px;color: white">' . esc_html__('OUT', 'wcvm') . '</span>';
//                                    } elseif ($order->threshold_low && $stock <= $order->threshold_low) {
//                                        echo '<span style="background: orange;padding: 5px;">' . esc_html__('LOW', 'wcvm') . '</span>';
//                                    } elseif ($order->threshold_reorder && $stock <= $order->threshold_reorder) {
//                                        echo '<span style="background: yellow;padding: 5px;">' . esc_html__('REORDER', 'wcvm') . '</span>';
//                                    }
                                ?>                        
                                <!--<span style="background: orange;padding: 5px;">LOW</span>-->
                            </td>
                            <td><?php echo wc_price($order->product_price); ?></td>
                            <td><?php echo $order->vendor_sku; ?></td>
                            <td><?php echo wc_price($order->vendor_price_last); ?></td>
                            <td><?php echo $order->product_stock; ?></td>
                            <td><?php echo $order->sale_30_days; ?></td>
        <!--                                <td><input readonly type="text" value="<?php // echo $order->threshold_low;              ?>" style="width:60px;"></td>
                            <td><input readonly type="text" value="<?php // echo $order->threshold_reorder;              ?>" style="width:60px;"></td>
                            <td><input readonly type="text" value="<?php // echo $order->reorder_qty;              ?>" style="width:60px;"></td>-->

                            <td><?php echo $order->on_order_quantity; ?></td>
                            <!--<td>On Vendor Bo</td>-->
                            <?php
                            $order_product_Qty = 0;
//                            if ($status == 'return_closed') {
//                                $order_product_Qty = $order->product_quantity_return_closed;
////                                    $order_Qty = get_post_meta($order->order_id, "wcvmgo_".$order->product_id."_return_closed");
////                                     if ($order_Qty) {
//                                         $order_product_Qty = $order_Qty[0];
//                                     }
//                            } else {
//                                    $order_Qty = get_post_meta($order->order_id, "wcvmgo_" . $order->product_id);
//                                    if ($order_Qty) {
//                                        $order_product_Qty = $order_Qty[0]['product_quantity'];
//                                    }
                                $order_product_Qty = $order->product_ordered_quantity;
//                            }
//                            if ($status != 'trash') {
//                                $inputType = '';
//
//                                if ($status == 'pending') {
//                                    $inputType = 'readonly';
//                                }
//                                if ($_GET['status'] == 'publish') {
//                                    $inputType = 'readonly';
//                                }
//                                
//                            }
                                ?>

                                <td><input readonly="true" type="text" name="<?php echo '__order_qty[' . $order->product_id . ']'; ?>" value="<?php echo $order_product_Qty; ?>" style="width:60px;"></td>

                                <!--<td><input class="deleting" id = "<?php echo $order->product_id; ?>" name="<?php echo '__delete[' . $order->product_id . ']'; ?>" type="checkbox"></td>-->
                                
                        </tr>

                        <?php
//                        }
                    }
                    if ($records) {
                        ?>
                    </tbody>
                    <tfoot>
                        <tr bgcolor="#e8e8e8" style="font-size:11px;">
                            <?php
                            foreach ($table_headers as $header) {
                                ?><th><?php echo $header; ?></th><?php
                            }
                            ?>
                        </tr>
                    </tfoot>

                </table>

                <div style="padding-top: 5px;float: left">
                    <button type="submit" name="print" value="print" class="button button-primary"><?= esc_html__('Print Order', 'wcvm') ?></button>
                </div>
        </form>
        <br><br>
        <br><br>
        <?php
    }
} if (!$records) {
    echo '<br><br>No Orders Found';
}
?>
</div>
<?php

function get_print_status($order = FALSE) {
    if ($order->post_status == 'new-order') {
        echo 'Status: New<br>';
    } else if ($order->post_status == 'on-order') {
        echo 'Status: On order<br>';
    } else if ($order->post_status == 'back-order') {
        echo 'Status: Backordered<br>';
    } else if ($order->post_status == 'completed') {
        echo 'Status: Completed<br>';
    } else if ($order->post_status == 'canceled') {
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
        if ($_REQUEST['status'] == 'back-order') {
            $status_string .= "Backordered";
        } elseif ($_REQUEST['status'] == 'completed') {
            $status_string .= "Completed";
        } elseif ($_REQUEST['status'] == 'canceled') {
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
<script>
//    function mark_closed(id)
//    {
//        var ids_to_delete = "";
//        var order_to_process = id;
//        jQuery(".deleting").each(function (index) {
//            var element_to_play = jQuery(this).parent().parent().parent().parent().parent();
//            if (jQuery(this).is(':checked'))
//            {
//                if (ids_to_delete != "")
//                {
//                    ids_to_delete += ",";
//                }
//                ids_to_delete += this.id + "_" + jQuery(element_to_play).attr("id");
//            }
//        });
//        jQuery.ajax({
//            type: "POST",
//            data: {
//                ids_to_delete: ids_to_delete,
//                order_to_process: order_to_process
//            },
//            url: "<?php echo site_url(); ?>/wp-content/plugins/clear-com-vendor-inventory-management/extras/close_selected_returns.php",
//            beforeSend: function () {
//                // setting a timeout
//                //$(placeholder).addClass('loading');
//            },
//            success: function (data)
//            {
//                if (data == 1)
//                {
//                    location.reload();
//                }
//            }
//        });
//    }
//    jQuery(".delete_selected").bind("click", function () {
//        console.log('s');
//        var ids_to_delete = "";
//        var order_to_process = this.id;
//        console.log(this.id);
//        jQuery(".deleting").each(function (index) {
//            var element_to_play = jQuery(this).parent().parent().parent().parent().parent();
//            if (jQuery(this).is(':checked'))
//            {
//                console.log(jQuery(this).is(':checked'));
//                if (ids_to_delete != "")
//                {
//                    ids_to_delete += ",";
//                }
//                ids_to_delete += this.id + "_" + jQuery(element_to_play).attr("id");
//            }
//        });
//        if (ids_to_delete != "")
//        {
//            console.log(ids_to_delete);
//            console.log(order_to_process);
//            jQuery.ajax({
//                type: "POST",
//                data: {
//                    ids_to_delete: ids_to_delete,
//                    order_to_process: order_to_process
//                },
//                url: "<?php echo site_url(); ?>/wp-content/plugins/clear-com-vendor-inventory-management/delete_selected_pos.php",
//                beforeSend: function () {
//                    // setting a timeout
//                    //$(placeholder).addClass('loading');
//                },
//                success: function (data)
//                {
//                    if (data == 1)
//                    {
//                        location.reload();
//                    }
//                }
//            });
//        }
//    });
//    jQuery(".delete_entire").bind("click", function () {
//        var txt;
//        var r = confirm("You're about to delete the entire PO. Are you sure you want to continue?");
//        if (r == true) {
//            jQuery("#" + this.id).submit();
//        }
//    });
</script>
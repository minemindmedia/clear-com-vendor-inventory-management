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
    
    .wcvm-orders.po-return-closed {
        border: 1px solid #2176ff;
        box-shadow: 0 0 10px #2176ff;
    }

</style>
<div class="wrap">
    <h1><?= esc_html__('View/Edit Purchase Orders', 'wcvm') ?></h1>
<?php
    global $wpdb;
    $records = false;

//    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'new-order';
    $show_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'new-order';
    
    ?>
    
    <?php
    $table = new Vendor_Management_Columns();
    ?>
    <?php
    if ($orders) {
        $get_status = "";
        if (array_key_exists('status', $_GET)) {
            $get_status = $_GET['status'];
        }
        ?>
        <div class="flex">
            <div class="flex-1">
                <?php $table_headers = $table->get_completed_orders_column_list(); 
                    require_once plugin_dir_path(__FILE__) . 'po-status-bar.php';
                ?>
            </div>
            <div class="self-end pb-4">
                <div>
                    <form action="" method="get" id="wcvm-search-form">
                        <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
                        <input type="hidden" name="status" value="<?php echo $get_status ?>"/>
                        <input class="h-9 w-64" type="text" name="search_po" id="search_po" value="<?php // echo $get_status ?>" placeholder="Search Through PO #">
                    </form>
                </div>
                <div>
                    <form action="<?php echo get_site_url() . '/wp-admin/admin.php?page=wcvm-epo&status=' . $get_status; ?>" method="get" id="wcvm-delete-all-form">
                        <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
                        <input type="hidden" name="status" value="<?php echo $get_status ?>"/>
                    </form>
                </div>
            </div>
        </div>

        <?php // add_thickbox();       ?>
        <!--    <div id="my-content-id" style="display:none;">
                <img id="loading_image" src="../wp-content/plugins/woocommerce-vendor-management/templates/loading2.gif"/>
                <div id="vendor_details">
        
                </div>
            </div>-->
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

            <div class="flex space-x-2">
                        <div class="flex-1"></div>
                <div>
                    <button type="submit" name="print" value="print" class="flex block px-2 py-1.5 border-2 border-gray-700 bg-gray-700 hover:bg-gray-900 text-white hover:text-white text-xs rounded m-0">
                    <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="print" class="svg-inline--fa fa-print fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M448 192V77.25c0-8.49-3.37-16.62-9.37-22.63L393.37 9.37c-6-6-14.14-9.37-22.63-9.37H96C78.33 0 64 14.33 64 32v160c-35.35 0-64 28.65-64 64v112c0 8.84 7.16 16 16 16h48v96c0 17.67 14.33 32 32 32h320c17.67 0 32-14.33 32-32v-96h48c8.84 0 16-7.16 16-16V256c0-35.35-28.65-64-64-64zm-64 256H128v-96h256v96zm0-224H128V64h192v48c0 8.84 7.16 16 16 16h48v96zm48 72c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24z"></path></svg>
                        <span class="self-center"><?= esc_html__('Print Order', 'wcvm') ?></span>
                    </button>
                </div>
                </div>
            </div>
            </form>


            <?php
        }
//        }
        if (!in_array($order->order_id, $printed_po_numbers)) {

            $records = true;
            ?>
              <form style="clear: both" class="purchase-order border-2 border-t-8 border-blue-600 p-8 mb-4 bg-gray-50"  id="<?= esc_attr($order->order_id) ?>" action="<?= site_url('/wp-admin/admin.php?page=wcvm-epo') ?>" method="post">
                <input type="hidden" name="ID" value="<?= esc_attr($order->order_id) ?>">
                <input type="hidden" name="status" value="<?= esc_attr($status) ?>">



                <div class="flex space-x-4">
                    <div class="self-center text-base font-semibold">
                        <?php get_print_status($order); ?>
                    </div>
                    <div class="self-center text-base font-semibold">
                        <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->order_id)) ?>
                    </div>
                    <div class="self-center text-base font-semibold">
                        <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html(get_the_title($order->vendor_id))) ?>
                    </div>
                    <div class="self-center text-base font-semibold">
                        <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->order_date))) ?>
                    </div>

                    <div class="flex flex-1">

                    </div>

                    <div class="self-center text-base font-semibold">
                        <button type="button" class="block px-2 py-1.5 border-2 border-gray-700 bg-gray-700 hover:bg-gray-900 text-white hover:text-white text-xs rounded m-0" data-id="<?= esc_attr($order->order_id) ?>" data-role="order-title" data-label="<?php echo '+ Expand'; ?>">
                            <?php echo '- Collapse'; ?>
                        </button>
                    </div>
                </div>

                

                <div<?php
                $display = "";
//                if (($order->post_status != $show_status && strpos($order->post_status, $show_status) === false) || $status == 'completed' || $status == 'canceled' || $status == 'trash') {
//                    $display = "none";
//                }
                ?> style="width:100%;display: <?php echo $display; ?>" data-role="order-table" data-id="<?= esc_attr($order->order_id) ?>" id="<?= esc_attr($order->order_id) ?>">
                <table class="wp-list-table widefat striped wcvm-orders my-6">

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
                        <?php
                        
                        $thumnailID = get_post_thumbnail_id($order->product_id);
                        $product_admin_url = get_edit_post_link($order->product_id);
                        $product_image_src = '';
                        $product_image_src = wc_placeholder_img_src();
                        if ($thumnailID) {
                            $image_src = wp_get_attachment_image_src($thumnailID, 'thumbnail'); // returns product image source
                            $product_image_src = $image_src[0];
                        }
                        $siteUrl = str_replace('wp', '', get_site_url());
                        if ($_SERVER['HTTP_HOST'] == "localhost") {
                            $imagepath = str_replace(get_site_url().'/wp-content', WP_CONTENT_DIR, $product_image_src);
                        } else {
                            $imagepath = str_replace($siteUrl . 'app', WP_CONTENT_DIR, $product_image_src);
                        }
                        if(!file_exists($imagepath)) {
                            $product_image_src = wc_placeholder_img_src();
                        }
            ?>                            
                            <td><a class="sku-thumbnail" href="<?php echo $product_admin_url; ?>" data-image="<?php echo $product_image_src; ?>" target="_blank"><?php echo $order->product_sku ?></a></td>

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
//                                $order_product_Qty = $order->product_ordered_quantity;
                                $order_product_Qty = $order->product_quantity_return_closed;
                                if(!$order_product_Qty){
                                    $order_product_Qty = 0;
                                }                                
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
                            $product_quantity_returned_note = $order->product_quantity_returned_note;
                            if($order_product_Qty && $product_quantity_returned_note != ''){
                                ?>
                                <tr id="product_quantity_returned_note-<?php echo $order->product_id; ?>">
                                    <td colspan="10">
                                        <textarea class="p-2" readonly="true" type="text" name="product_quantity_returned_note[<?php echo $order->product_id; ?>]" placeholder="QTY Returned Notes" data-role="product_quantity_returned_note" value="<?php echo $product_quantity_returned_note; ?>" style="width:100%;padding-left:10px;"><?php echo $product_quantity_returned_note; ?></textarea>
                                    </td>
                                </tr>
                        <?php
                        }
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

                <div style="padding-top: 5px;float: left;">
                    <button type="submit" name="print" value="print" class="button button-primary"><?= esc_html__('Print Order', 'wcvm') ?></button>
                </div>
        </form>
        <br><br>
        <br><br>
        <?php
    }
} if (!$records) { ?>

<?php $table_headers = $table->get_on_order_columns_list(); 
            require_once plugin_dir_path(__FILE__) . 'po-status-bar.php';
        ?>
        <div class="flex border-2 border-t-8 border-blue-600 p-8 mb-4 bg-gray-50 text-lg text-semibold">
            No returns are currently closed.
        </div>
<?php }
?>
</div>
<?php

function get_print_status($order = FALSE) {
    if ($order->post_status == 'new-order') {
        echo 'Status: New<br>';
    } else if ($order->post_status == 'on-order') {
        echo 'Status: On order<br>';
    } else if ($order->post_status == 'pending') {
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
<!--<script>
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
            url: "<?php echo site_url(); ?>/wp-content/plugins/clear-com-vendor-inventory-management/extras/close_selected_returns.php",
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
        console.log('s');
        var ids_to_delete = "";
        var order_to_process = this.id;
        console.log(this.id);
        jQuery(".deleting").each(function (index) {
            var element_to_play = jQuery(this).parent().parent().parent().parent().parent();
            if (jQuery(this).is(':checked'))
            {
                console.log(jQuery(this).is(':checked'));
                if (ids_to_delete != "")
                {
                    ids_to_delete += ",";
                }
                ids_to_delete += this.id + "_" + jQuery(element_to_play).attr("id");
            }
        });
        if (ids_to_delete != "")
        {
            console.log(ids_to_delete);
            console.log(order_to_process);
            jQuery.ajax({
                type: "POST",
                data: {
                    ids_to_delete: ids_to_delete,
                    order_to_process: order_to_process
                },
                url: "<?php echo site_url(); ?>/wp-content/plugins/clear-com-vendor-inventory-management/delete_selected_pos.php",
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
    jQuery(".delete_entire").bind("click", function () {
        var txt;
        var r = confirm("You're about to delete the entire PO. Are you sure you want to continue?");
        if (r == true) {
            jQuery("#" + this.id).submit();
        }
    });
</script>-->
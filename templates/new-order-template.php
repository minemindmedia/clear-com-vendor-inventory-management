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

    .wcvm-orders.po-new {
        border: 1px solid #c1121f;
        box-shadow: 0 0 10px #c1121f;
    }
</style>
<div class="wrap">
<h1><?= esc_html__('View/Edit Purchase Orders', 'wcvm') ?></h1>

    <?php
    global $wpdb;
    $records = false;

//    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'new-order';
//    $show_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'new-order';
    
    ?>
    
    <?php
//    }
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
        <?php $table_headers = $table->get_new_orders_columns_list(); 
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
                <tr>
                    <?php
                    foreach ($table_headers as $header) {
                        ?>
                        <th><?php echo $header; ?></th><?php
                    }
                    ?>
                </tr>
            </tfoot>

            </table>

            <?php if ($order->post_status == 'new-order' || $order->post_status == 'on-order'): ?>
                <div class="flex space-x-2">
                   <div><input class="block py-8" id="calendar" type="text" autocomplete="off" name="expected_date" value="<?= esc_attr($last_expected_date ? date('Y-m-d', $last_expected_date) : '') ?>" placeholder="<?= esc_attr__('dd-mm-yyyy', 'wcvm') ?>" ></div>
                   <div><input type="hidden" name="__expected_date" data-role="date-time" value="<?= esc_attr($last_expected_date ? date('Y-m-d', $last_expected_date) : '') ?>" ></div>
                   <div class="flex-1">
                       <button type="submit" name="action" value="update" class="flex block px-2 py-1.5 border-2 border-gray-700 bg-gray-700 hover:bg-gray-900 text-white hover:text-white text-xs rounded m-0">
                       <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="calendar-star" class="svg-inline--fa fa-calendar-star fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 64h-48V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H160V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H48C21.5 64 0 85.5 0 112v48h448v-48c0-26.5-21.5-48-48-48zM0 464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V192H0v272zm134.1-143.7l54.7-8 24.5-49.6c4.4-9 17.1-8.8 21.5 0l24.5 49.6 54.7 8c9.8 1.4 13.7 13.5 6.6 20.5L281 379.4l9.4 54.6c1.7 9.9-8.7 17.2-17.4 12.6l-49-25.8-48.9 25.8c-8.7 4.6-19.1-2.8-17.4-12.6l9.4-54.6-39.6-38.6c-7.2-7-3.2-19.1 6.6-20.5z"></path></svg>
                            <span class="self-center"><?= esc_html__($order->post_status == 'new-order' ? 'Set Date & Place On Order' : 'Update Order', 'wcvm') ?></span>
                    </button>
                    </div>
            <?php endif ?>
                     <div>
                        <button type="submit" name="print" value="print" class="flex block px-2 py-1.5 border-2 border-gray-700 bg-gray-700 hover:bg-gray-900 text-white hover:text-white text-xs rounded m-0">
                        
                        <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="print" class="svg-inline--fa fa-print fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M448 192V77.25c0-8.49-3.37-16.62-9.37-22.63L393.37 9.37c-6-6-14.14-9.37-22.63-9.37H96C78.33 0 64 14.33 64 32v160c-35.35 0-64 28.65-64 64v112c0 8.84 7.16 16 16 16h48v96c0 17.67 14.33 32 32 32h320c17.67 0 32-14.33 32-32v-96h48c8.84 0 16-7.16 16-16V256c0-35.35-28.65-64-64-64zm-64 256H128v-96h256v96zm0-224H128V64h192v48c0 8.84 7.16 16 16 16h48v96zm48 72c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24z"></path></svg>
                            <span class="self-center"><?= esc_html__('Print Order', 'wcvm') ?></span>
                        </button>
                    </div>
                </div>
            <?php // if ($order->post_status == 'new-order' || $order->post_status == 'draft'):        ?>
            <!-- <div style="padding-top: 5px;float: right">
                <input type="text" name="_sku" value="" style="height: 26px;" data-role="product-sku" placeholder="<?= esc_html__('SKU', 'wcvm') ?>" data-id="<?= esc_attr($last_order_id) ?>">
                <button type="submit" name="action" value="add" class="button"><?= esc_html__('Add Product', 'wcvm') ?></button>
            </div> -->
            <?php // endif         ?>
            </div>
            </form>


            <?php
        }
//        }
        if (!in_array($order->order_id, $printed_po_numbers)) {

            $records = true;
            ?>
            <form style="clear: both" class="purchase-order border-2 border-t-8 border-indigo-600 p-8 mb-4 bg-gray-50"  id="<?= esc_attr($order->order_id) ?>" action="<?= site_url('/wp-admin/admin.php?page=wcvm-epo') ?>" method="post">
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
                        <div class="flex space-x-4">
                            <div>
                                <input type="hidden" name="archive" value="archive" />
                                <a href="javascript:void(0);" id="<?= esc_attr($order->order_id) ?>" class="flex block px-2 py-1.5 border-2 border-red-700 bg-red-700 hover:bg-red-500 text-white hover:text-white text-xs rounded delete_entire"> 
                                <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="trash" class="svg-inline--fa fa-trash fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M432 32H312l-9.4-18.7A24 24 0 0 0 281.1 0H166.8a23.72 23.72 0 0 0-21.4 13.3L136 32H16A16 16 0 0 0 0 48v32a16 16 0 0 0 16 16h416a16 16 0 0 0 16-16V48a16 16 0 0 0-16-16zM53.2 467a48 48 0 0 0 47.9 45h245.8a48 48 0 0 0 47.9-45L416 128H32z"></path></svg>
                                <span class="self-center">Delete Entire PO</span>
                                </a>
                            </div>
                            <div>
                                <a href="javascript:void(0);" id="<?= esc_attr($order->order_id) ?>" class="flex block px-2 py-1.5 border-2 border-red-700 hover:border-red-500 text-red-700 hover:text-red-500 text-xs rounded delete_selected">
                                <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="mouse-pointer" class="svg-inline--fa fa-mouse-pointer fa-w-10" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="currentColor" d="M302.189 329.126H196.105l55.831 135.993c3.889 9.428-.555 19.999-9.444 23.999l-49.165 21.427c-9.165 4-19.443-.571-23.332-9.714l-53.053-129.136-86.664 89.138C18.729 472.71 0 463.554 0 447.977V18.299C0 1.899 19.921-6.096 30.277 5.443l284.412 292.542c11.472 11.179 3.007 31.141-12.5 31.141z"></path></svg>
                                <span class="self-center">Delete Selected Lines</span>
                            </a>
                            </div>
                            <div>
                                <button type="button" class="block px-2 py-1.5 border-2 border-gray-700 bg-gray-700 hover:bg-gray-900 text-white hover:text-white text-xs rounded m-0" data-id="<?= esc_attr($order->order_id) ?>" data-role="order-title" data-label="<?php echo '+ Expand'; ?>">
                                    <?php echo '- Collapse'; ?>
                                </button>
                            </div>
                        </div>
                    </div>

             
                </div>



                <div<?php
                $display = "";
//                if (($order->post_status != $show_status && strpos($order->post_status, $show_status) === false) || $status == 'completed' || $status == 'canceled' || $status == 'trash') {
//                    $display = "none";
//                }
                ?> style="display: <?php echo $display; ?>" data-role="order-table" data-id="<?= esc_attr($order->order_id) ?>" id="<?= esc_attr($order->order_id) ?>">
                    <table class="wp-list-table widefat striped wcvm-orders my-6">

                        <thead>
                            <tr>
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

                                <td><input type="text" name="<?php echo '__order_qty[' . $order->product_id . ']'; ?>" value="<?php echo $order_product_Qty; ?>" style="width:60px;"></td>

                                <td><input class="deleting" id = "<?php echo $order->product_id; ?>" name="<?php echo '__delete[' . $order->product_id . ']'; ?>" type="checkbox"></td>
                                
                        </tr>

                        <?php
//                        }
                    }
                    if ($records) {
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <?php
                            foreach ($table_headers as $header) {
                                ?><th><?php echo $header; ?></th><?php
                            }
                            ?>
                        </tr>
                    </tfoot>

                </table>

                <?php // if ($order->post_status == 'new-order' || $order->post_status == 'on-order'): ?>
                    <div class="flex space-x-2">
                        <div><input class="block py-8" id="calendar" type="text" autocomplete="off" name="expected_date" style="width:150px;" value="<?= esc_attr($last_expected_date ? date('Y-m-d', $last_expected_date) : '') ?>" placeholder="<?= esc_attr__('dd-mm-yyyy', 'wcvm') ?>" ></div>
                        <div><input type="hidden" name="__expected_date" data-role="date-time" value="<?= esc_attr($last_expected_date ? date('Y-m-d', $last_expected_date) : '') ?>" >                    </div>
                        <div class="flex-1">
                            <button type="submit" name="action" value="update" class="flex block px-2 py-1.5 border-2 border-gray-700 bg-gray-700 hover:bg-gray-900 text-white hover:text-white text-xs rounded m-0">
                        <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="calendar-star" class="svg-inline--fa fa-calendar-star fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M400 64h-48V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H160V16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v48H48C21.5 64 0 85.5 0 112v48h448v-48c0-26.5-21.5-48-48-48zM0 464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V192H0v272zm134.1-143.7l54.7-8 24.5-49.6c4.4-9 17.1-8.8 21.5 0l24.5 49.6 54.7 8c9.8 1.4 13.7 13.5 6.6 20.5L281 379.4l9.4 54.6c1.7 9.9-8.7 17.2-17.4 12.6l-49-25.8-48.9 25.8c-8.7 4.6-19.1-2.8-17.4-12.6l9.4-54.6-39.6-38.6c-7.2-7-3.2-19.1 6.6-20.5z"></path></svg>
                        <span class="self-center"><?= esc_html__($order->post_status == 'new-order' ? 'Set Date & Place On Order' : 'Update Order', 'wcvm') ?></span>
                    
                    </button></div>
                    
                <?php // endif ?>
                 <div>
                    <button type="submit" name="print" value="print" class="flex block px-2 py-1.5 border-2 border-gray-700 bg-gray-700 hover:bg-gray-900 text-white hover:text-white text-xs rounded m-0">
                    <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="print" class="svg-inline--fa fa-print fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M448 192V77.25c0-8.49-3.37-16.62-9.37-22.63L393.37 9.37c-6-6-14.14-9.37-22.63-9.37H96C78.33 0 64 14.33 64 32v160c-35.35 0-64 28.65-64 64v112c0 8.84 7.16 16 16 16h48v96c0 17.67 14.33 32 32 32h320c17.67 0 32-14.33 32-32v-96h48c8.84 0 16-7.16 16-16V256c0-35.35-28.65-64-64-64zm-64 256H128v-96h256v96zm0-224H128V64h192v48c0 8.84 7.16 16 16 16h48v96zm48 72c-13.25 0-24-10.75-24-24 0-13.26 10.75-24 24-24s24 10.74 24 24c0 13.25-10.75 24-24 24z"></path></svg>
                        <span class="self-center"><?= esc_html__('Print Order', 'wcvm') ?></span>
                    </button>
                </div>
                </div>
                
        </form>
        <?php
    }
} if (!$records) { ?>

<?php $table_headers = $table->get_on_order_columns_list(); 
            require_once plugin_dir_path(__FILE__) . 'po-status-bar.php';
        ?>
        <div class="flex border-2 border-t-8 border-indigo-600 p-8 mb-4 bg-gray-50 text-lg text-semibold">
            There are no purchase orders created.
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
    jQuery(function() {
        jQuery("#calendar").datepicker({
            dateFormat: 'dd-mm-yy'
        });
    });
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
//                url: "<?php // echo site_url(); ?>/wp-content/plugins/clear-com-vendor-inventory-management/delete_selected_pos.php",                
                url: "<?php echo plugins_url(); ?>/clear-com-vendor-inventory-management/delete_selected_pos.php",
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
</script>
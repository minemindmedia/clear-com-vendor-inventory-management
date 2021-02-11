<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 */
?>
<div class="wrap">
    <?php
    /* start post request */
    // if (isset($_POST['action'])) {
    //     $post_id = $_POST['ID'];
    //     // print_r($_POST);
    //     // die;
    //     // $product_id = $_POST['product_id'];
    //     $product_ids = get_post_meta($post_id, 'wcvmgo_product_id');
    //     foreach($product_ids as $product_id) {
    //         $product_quantity_received = (int) $_POST['product_quantity_received'][$product_id];;
    //         $product_quantity_returned = (int) $_POST['product_quantity_returned'][$product_id];;
    //         $product_quantity_back_order = (int) $_POST['product_quantity_back_order'][$product_id];;
    //         $product_quantity_canceled = (int) $_POST['product_quantity_canceled'][$product_id];;
    //         // $expected_date = $_POST['expected_date'];
    //         $product_expected_date_back_order = $_POST['product_expected_date_back_order'][$product_id];;
    //         $data = get_post_meta($post_id, 'wcvmgo_' . $product_id, true);
    //         $data['product_quantity_received'] = $product_quantity_received;
    //         $data['product_quantity_back_order'] = $product_quantity_back_order;
    //         $data['product_quantity_canceled'] = $product_quantity_canceled;
    //         $data['product_quantity_returned'] = $product_quantity_returned;
    //         $data['product_expected_date_back_order'] = $product_expected_date_back_order;
    //         $redirect = '';
    //         $expected_date = '';
    //         // update received quantity
    //         if($product_quantity_received > 0) {
    //             if ($order->post_status != "") {
    //                 $order->post_status .= "|";
    //             }
    //             $order->post_status = 'publish';
    //             if ($redirect == "") {
    //                 $redirect = "publish";
    //             }
    //             update_post_meta($post_id, 'wcvmgo_' . $product_id . '_received', $product_quantity_received);
    //             $qty_to_update = get_post_meta($post_id, 'wcvmgo_' . $product_id . '_received');
    //             update_post_meta($post_id, 'wcvmgo_' . $product_id . '_qty', $qty_to_update[0] - $product_quantity_received);
    //             update_post_meta($product_id,'_stock_status','instock');
    //             $data['product_quantity'] = $data['product_quantity_received'];
    //         }
    //         // update qty or back order quantity
    //         if($product_quantity_back_order > 0) {
    //             if ($order->post_status != "") {
    //                 $order->post_status .= "|";
    //             }
    //             $order->post_status .= 'pending';
    //             update_post_meta($post_id, 'wcvmgo_' . $product_id . '_qty', $product_quantity_back_order);
    //             if ($product_expected_date_back_order) {
    //                 if (!$expected_date || $expected_date > $product_expected_date_back_order) {
    //                     $expected_date = $product_expected_date_back_order;
    //                 }
    //                 update_post_meta($post_id, 'wcvmgo_' . $product_id . '_date', $product_expected_date_back_order);
    //             } else {
    //                 delete_post_meta($post_id, 'wcvmgo_' . $product_id . '_date');
    //             }
    //             if ($redirect == "") {
    //                 $redirect = "pending";
    //             }
    //         } else {
    //             delete_post_meta($post_id, 'wcvmgo_' . $product_id . '_qty');
    //         }
    //         // update returned quantity
    //         if($product_quantity_returned > 0) {
    //             if ($order->post_status != "") {
    //                 $order->post_status .= "|";
    //             }
    //             $order->post_status .= 'returned';
    //             if ($redirect == "") {
    //                 $redirect = "returned";
    //             }
    //             update_post_meta($post_id, 'wcvmgo_' . $product_id . '_returned', $product_quantity_returned);
    //         }
    //         // update cancelled quantity
    //         if($product_quantity_canceled > 0) {
    //             if ($order->post_status != "") {
    //                 $order->post_status .= "|";
    //             }
    //             $order->post_status .= 'private';
    //             if ($redirect == "") {
    //                 $redirect = "private";
    //             }
    //             update_post_meta($post_id, 'wcvmgo_' . $product_id . '_cancelled', $product_quantity_canceled);
    //         }
    //         // update expected date
    //         if($expected_date) {
    //             update_post_meta($post_id, 'expected_date', $expected_date);
    //         } else {
    //             delete_post_meta($post_id, 'expected_date');
    //         }
    //         // update set date
    //         if(!empty($product_expected_date_back_order)) {
    //             update_post_meta($post_id, 'set_date', time());
    //             // update_post_meta($post_id, 'set_date', strtotime($product_expected_date_back_order));
    //         }
    //         // print_r($data);
    //         update_post_meta($post_id, 'wcvmgo_' . $product_id, $data);
    //     }
    //     // update post status
    //     global $wpdb;
    //     $query = "UPDATE wp_posts SET post_status = '" . $order->post_status . "' WHERE ID = " . $post_id;
    //     $wpdb->query($query);
    //     wp_redirect(site_url('/wp-admin/admin.php?page=wcvm-epo&status=' . $redirect) . '#order' . $post_id);
    //     exit();
    // }
    global $wpdb;
$vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_orders';
        $vendor_purchase_order_items_table = $wpdb->prefix . 'vendor_purchase_orders_items';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        

        $wcvmgo_product_quantity = 0;
        
        $order_details_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
                ."LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
                . " WHERE po.order_id = " . $_POST['ID'];
        $order_details = $wpdb->get_results($order_details_sql);
       
        $order = get_post($_POST['ID']);
//        if ($order && $order->post_type == 'wcvm-order') {

        if ($order_details) {

            $isValid = true;
//            foreach ($order->wcvmgo as $productId) {
            foreach ($order_details as $single_order) {

//                if (get_post_status($productId) == 'trash') {
                if ($single_order->post_status == 'trash') {
                    continue;
                }
//                $data = get_post_meta($order->ID, 'wcvmgo_' . $productId, true);
//                if ($data['product_quantity'] != (int)$_POST['product_quantity_received'][$productId] + (int)$_POST['product_quantity_back_order'][$productId] + (int)$_POST['product_quantity_canceled'][$productId] + (int)$_POST['product_quantity_returned'][$productId]) {

                if ($single_order->product_ordered_quantity != (int) $_POST['product_quantity_received'][$single_order->product_id] + (int) $_POST['product_quantity_back_order'][$single_order->product_id] + (int) $_POST['product_quantity_canceled'][$single_order->product_id] + (int) $_POST['product_quantity_returned'][$single_order->product_id]) {
                    $isValid = false;
                    break;
                }
            }
            if ($isValid) {

//                $order->post_status = get_post_status($order->ID);
                $expectedDate = '';
                $redirect = '';

//                foreach ($order->wcvmgo as $productId) {
                foreach ($order_details as $single_order) {
//                    $order->post_status = "";
//                    $data = get_post_meta($order->ID, 'wcvmgo_' . $productId, true);
                    $data = get_post_meta($single_order->order_id, 'wcvmgo_' . $single_order->product_id, true);

                    $data['product_quantity_received'] = (int) $_POST['product_quantity_received'][$single_order->product_id];
                    $data['product_quantity_back_order'] = (int) $_POST['product_quantity_back_order'][$single_order->product_id];
                    $data['product_quantity_canceled'] = (int) $_POST['product_quantity_canceled'][$single_order->product_id];
                    $data['product_quantity_returned'] = (int) $_POST['product_quantity_returned'][$single_order->product_id];
                    $data['product_expected_date_back_order'] = $_POST['product_expected_date_back_order'][$single_order->product_id];
                    if ($data['product_quantity_back_order'] && $data['product_expected_date_back_order']) {
                        $data['product_expected_date_back_order'] = strtotime($data['product_expected_date_back_order']);
                    } else {
                        $data['product_expected_date_back_order'] = '';
                    }
                    if ($data['product_quantity_received'] > 0) {
                        if ($order->post_status != "") {
                            $order->post_status .= "|";
                        }
                        $order->post_status = 'publish';
                        if ($redirect == "") {
                            $redirect = "publish";
                        }
                        //$data['product_ordered_quantity'] = $data['product_quantity_received'];
                        update_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_received', $data['product_quantity_received']);
                        $qtyToUpdate = get_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_received');

                        update_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_qty', $qtyToUpdate[0] - $data['product_quantity_received']);
                        update_post_meta($single_order->product_id, '_stock_status', 'instock');
                        $wcvmgo_product_quantity = $qtyToUpdate[0] - $data['product_quantity_received'];
                    }
                    if ($data['product_quantity_back_order'] > 0) {
                        $wcvmgo_product_quantity = $data['product_quantity_back_order'];
                        $update_data['product_quantity_back_order'] = $wcvmgo_product_quantity;
                        if ($order->post_status != "") {
                            $order->post_status .= "|";
                        }
                        $order->post_status .= 'pending';
                        update_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_qty', $data['product_quantity_back_order']);
                        if ($data['product_expected_date_back_order']) {
                            if (!$expectedDate || $expectedDate > $data['product_expected_date_back_order']) {
                                $expectedDate = $data['product_expected_date_back_order'];
                            }
                            update_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_date', $data['product_expected_date_back_order']);
                        } else {
                            $update_data['product_expected_date_back_order'] = '';
                            delete_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_date');
                        }
                        if ($redirect == "") {
                            $redirect = "pending";
                        }
                    } else {
                        $update_data['product_quantity_back_order'] = 0;
                        delete_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_qty');
                    }
                    if ($data['product_quantity_canceled'] > 0) {
                        if ($order->post_status != "") {
                            $order->post_status .= "|";
                        }
                        $order->post_status .= 'private';
                        update_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_cancelled', $data['product_quantity_canceled']);
                        if ($redirect == "") {
                            $redirect = "private";
                        }
                    }
                    if ($data['product_quantity_returned'] > 0) {
                        if ($order->post_status != "") {
                            $order->post_status .= "|";
                        }
                        $order->post_status .= 'returned';
                        if ($redirect == "") {
                            $redirect = "returned";
                        }
                        update_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id . '_returned', $data['product_quantity_returned']);
                    }

                    update_post_meta($order->ID, 'wcvmgo_' . $single_order->product_id, $data);

                    if (!empty($origin['product_quantity_received'])) {
//                        $stock = $data['product_quantity_received'] + get_post_meta($single_order->product_id, '_stock', true) - $origin['product_quantity_received'];
                        $stock = $data['product_quantity_received'] + $single_order->product_stock - $origin['product_quantity_received'];
                    } else {
//                        $stock = $data['product_quantity_received'] + get_post_meta($single_order->product_id, '_stock', true);
                        $stock = $data['product_quantity_received'] + $single_order->product_stock;
                    }
                    update_post_meta($single_order->product_id, '_stock', $stock);

                    if ($_POST['action'] == 'archive') {
                        $status = $order->post_status;
                        if ($status != "") {
                            $status .= "|";
                        }
                        $status .= 'trash';
                    }
                    $update_data['post_status'] = $order->post_status;
                    $update_data['product_ordered_quantity'] = $data['product_quantity'];
                    $update_data['product_quantity_received'] = $data['product_quantity_received'];
//                    $update_data['product_quantity_back_order'] = $data['product_quantity_back_order'];
                    $update_data['product_quantity_canceled'] = $data['product_quantity_canceled'];
                    $update_data['product_quantity_returned'] = $data['product_quantity_returned'];
//                    $update_data['product_expected_date_back_order'] = $data['product_expected_date_back_order'];
                    if (!empty($expectedDate)) {
                        $update_data['expected_date'] = $expectedDate;
                    }
                    $update_data['set_date'] = time();
                    $update_data['updated_date'] = date('Y/m/d H:i:s a');
                    $where_data['product_id'] = $single_order->product_id;
                    $where_data['order_id'] = $order->ID;
                    $updated = $wpdb->update($vendor_purchase_order_table, $update_data, $where_data);
                    
                    $quantity_to_deduct_from_on_order = $data['product_quantity_received'] + $data['product_quantity_canceled'] + $data['product_quantity_returned'];
                    $updateOnOrderQuery = "UPDATE wp_vendor_po_lookup SET stock = stock + ".$data['product_quantity_received'].",on_order = on_order - " . $quantity_to_deduct_from_on_order . " WHERE product_id = " . $single_order->product_id . "";
                    $wpdb->query($updateOnOrderQuery);
                }

                if ($expectedDate) {
                    update_post_meta($order->ID, 'expected_date', $expectedDate);
                } else {
                    delete_post_meta($order->ID, 'expected_date');
                }
                update_post_meta($order->ID, 'set_date', time());
                if ($_POST['action'] == 'archive') {
                    update_post_meta($order->ID, 'old_status', $order->post_status);
                    if ($order->post_status != "") {
                        $order->post_status .= "|";
                    }
                    $order->post_status .= 'trash';
                    if ($redirect == "") {
                        $redirect = "trash";
                    }
                }

                $query = "UPDATE wp_posts SET post_status = '" . $order->post_status . "' WHERE ID = " . $order->ID;

                $wpdb->query($query);
                wp_redirect(site_url('/wp-admin/admin.php?page=wcvm-epo&status=' . $redirect) . '#order' . $order->ID);
                exit();
            }
        }
    }
    /* end post request */
    ?>
    <h1><?= esc_html__('Receive Inventory', 'wcvm') ?></h1>
    <?php
    global $wpdb;
    $records = false;
    $status = "";
    if (array_key_exists('status', $_REQUEST)) {
        $status = $_REQUEST['status'];
    }
    $show_status = $status ? $status : 'draft';
    $status = $show_status;
//    $posts_table = $wpdb->prefix . "posts";
//    $posts_table_sql = "SELECT * FROM `" . $posts_table . "` p
//                    LEFT JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND meta_key = 'wcvmgo_product_id' 
//                    LEFT JOIN " . $wpdb->prefix . "vendor_po_lookup wvpl ON wvpl.product_id = pm.meta_value
//                    where p.post_status = '" . $show_status . "' and p.post_type = 'wcvm-order' ORDER BY pm.post_id DESC";
    $purchase_order_table_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
            ."LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
            . " WHERE po.post_status LIKE '" . $show_status . "' ORDER BY po.id DESC";
//    $orders = $wpdb->get_results($posts_table_sql);
    $orders = $wpdb->get_results($purchase_order_table_sql);
    
    if ($orders) {
        $printed_po_numbers = [];
        $last_order_id = 0;
        $product_quantity = 0;
        $product_quantity_received = '';
        $product_quantity_returned = '';
        $product_quantity_back_order = '';
        $product_quantity_canceled = '';
        $product_expected_date_back_order = '';
        foreach ($orders as $order) {
//        print_r($order);die;
            $vendors = explode(',', $order->vendor_name);
            $vendor_ids = explode(',', $order->vendor_id);
            $vendor_prices = explode(',', $order->vendor_price);
            $vendor_skus = explode(',', $order->vendor_sku);
            $wcvmgo = get_post_meta($order->order_id, 'wcvmgo_' . $order->product_id);
            if ($wcvmgo) {
                $product_quantity = $wcvmgo[0]['product_quantity'] ? $wcvmgo[0]['product_quantity'] : '';
                $product_quantity_received = isset($wcvmgo[0]['product_quantity_received']) ? $wcvmgo[0]['product_quantity_received'] : '';
                $product_quantity_returned = isset($wcvmgo[0]['product_quantity_returned']) ? $wcvmgo[0]['product_quantity_returned'] : '';
                $product_quantity_back_order = isset($wcvmgo[0]['product_quantity_back_order']) ? $wcvmgo[0]['product_quantity_back_order'] : '';
                $product_quantity_canceled = isset($wcvmgo[0]['product_quantity_canceled']) ? $wcvmgo[0]['product_quantity_canceled'] : '';
                $product_expected_date_back_order = isset($wcvmgo[0]['product_expected_date_back_order']) ? date('Y-m-d', (int) $wcvmgo[0]['product_expected_date_back_order']) : '';
                // print_r($wcvmgo[0]['product_expected_date_back_order']);
            }
            $product_quantity = $order->product_ordered_quantity;
            $product_quantity_received = $order->product_quantity_received;
            $product_quantity_returned = $order->product_quantity_returned;
            $product_quantity_back_order = $order->product_quantity_back_order;
            $product_quantity_canceled = $order->product_quantity_canceled;
            $product_expected_date_back_order = isset($order->product_expected_date_back_order) ? date('Y-m-d', (int) $order->product_expected_date_back_order) : '';
            // print_r($wcvmgo[0]['product_expected_date_back_order']);

            $vendor_price = 0;
            $vendor_sku = '';
            $i = 0;
            while ($i < count($vendor_ids)) {
                if ($vendor_ids[$i] == $order->vendor_id) {
                    $vendor_price = $vendor_prices[$i];
                    $vendor_sku = $vendor_skus[$i];
                    break;
                }$i++;
            }
            if ($last_order_id > 0 && $last_order_id != $order->order_id) {
                $records = true;
                ?>
            </tbody>
            <tfoot>
                <tr bgcolor="#e8e8e8" style="font-size:11px;">
            <?php foreach ($table_headers as $header) { ?>
                        <th><?php echo $header; ?></th>
                    <?php } ?>
                </tr>
            </tfoot>
            </table>                
            <div style="padding-top: 5px;">
                <button type="submit" name="action" value="update" data-role="receive-inventory" class="button button-primary"><? esc_html__('Set Inventory', 'wcvm') ?></button>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="submit" name="action" value="archive" data-role="receive-inventory" class="button"><?= esc_html__('Set Inventory & Archive', 'wcvm') ?></button>
            </div>
            <br><br>
            </form>
            <br><br>
        <?php } ?>
        <?php
        if (!in_array($order->order_id, $printed_po_numbers)) {
            $printed_po_numbers[] = $order->order_id;
            $last_order_id = $order->order_id;
            $records = true;
            ?>
            <form id="form_<?php echo $order->order_id; ?>" action="" method="post">
                <input type="hidden" name="ID" value="<?= esc_attr($order->order_id) ?>">
                <h4 style="margin-bottom: 5px;">
            <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->order_id)) ?>,
                    <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html($order->vendor_name)) ?>,
                    <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->order_date))) ?>
                </h4>
                    <?php $table = new Vendor_Management_Columns(); ?>
                <?php $table_headers = $table->get_columns_receive_inventory(); ?>
                <table class="wp-list-table widefat striped wcvm-orders" style="width:100%; max-width: 1400px; border-collapse: collapse;">
                    <thead>
                        <tr bgcolor="#e8e8e8" style="font-size:11px;">
            <?php foreach ($table_headers as $header) { ?>
                                <th><?php echo $header; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
        <?php } ?>
                    <tr>
                        <td><a href=""><?php echo $order->product_sku; ?></a></td>
                        <td><?php echo $order->category; ?></td>
                        <td></td>
                        <td><?php echo $order->vendor_name; ?></td>
                        <td></td>
                        <td><?php echo $order->vendor_sku; ?></td>
                        <td><?php echo wc_price($order->vendor_price_last); ?></td>
                        <td id="quantity_<?php echo $order->product_id; ?>" data-quantity="<?php echo $product_quantity; ?>"><?php echo $product_quantity; ?></td>
                        <td><input type="text" name="product_quantity_received[<?php echo $order->product_id; ?>]" data-role="product_quantity_received" value="<?php echo $product_quantity_received; ?>" style="width:60px;"></td>
                        <td><input type="text" name="product_quantity_returned[<?php echo $order->product_id; ?>]" data-role="product_quantity_returned" value="<?php echo $product_quantity_returned; ?>" style="width:60px;"></td>
                        <td><input type="text" name="product_quantity_back_order[<?php echo $order->product_id; ?>]" data-role="product_quantity_back_order" value="<?php echo $product_quantity_back_order; ?>" style="width:60px;"></td>
                        <td><input type="text" name="product_quantity_canceled[<?php echo $order->product_id; ?>]" data-role="product_quantity_canceled" value="<?php echo $product_quantity_canceled; ?>" style="width:60px;"></td>
                        <td><input type="text" name="product_expected_date_back_order[<?php echo $order->product_id; ?>]" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" value="<?php echo $product_expected_date_back_order; ?>"></td>                    
        <!--                    <td><input type="text" value="" style="width:60px;"></td>-->
                        <td></td>
                    </tr>
    <?php } ?>
            </tbody>
            <tfoot>
                <tr bgcolor="#e8e8e8" style="font-size:11px;">
    <?php foreach ($table_headers as $header) { ?>
                        <th><?php echo $header; ?></th>
                    <?php } ?>
                </tr>
            </tfoot>
        </table>                
        <div style="padding-top: 5px;">
                    <button type="submit" name="action" value="update" data-role="receive-inventory" class="button button-primary"><?= esc_html__('Set Inventory', 'wcvm') ?></button>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button type="submit" name="action" value="archive" data-role="receive-inventory" class="button"><?= esc_html__('Set Inventory & Archive', 'wcvm') ?></button>
        </div>
        <br><br>
    </form>
    <br><br>
    <?php
} if (!$records) {
    echo 'No Orders Found';
}
?>
</div>

<script>
    jQuery(document).ready(function ($) {
        "use strict";

        /* start validate receiven inventory inputs */
        $(document).on('click', 'button[data-role="receive-inventory"]', function () {
            var element = $(this);
            var form = element.parents('form:first');
            var isValid = true;
            $.each($('[data-quantity]', form), function (key, item) {

                item = $(item);
                var quantity = parseInt(item.attr('data-quantity'));
                var received = $('input[data-role="product_quantity_received"]', item.parents('tr:first')).val();
                var backOrder = $('input[data-role="product_quantity_back_order"]', item.parents('tr:first')).val();
                var cancel = $('input[data-role="product_quantity_canceled"]', item.parents('tr:first')).val();
                var returned = $('input[data-role="product_quantity_returned"]', item.parents('tr:first')).val();
                received = received ? parseInt(received) : 0;
                backOrder = backOrder ? parseInt(backOrder) : 0;
                cancel = cancel ? parseInt(cancel) : 0;
                returned = returned ? parseInt(returned) : 0;
                if (quantity != (received + backOrder + cancel + returned)) {
                    isValid = false;
                }
            });
            if (!isValid) {
                alert('Sum of "QTY Rcv", "Vnd BO", "Cancel", "Returns Open" should equal "Order QTY"');
            }
            return isValid;
        });
        /* end validate receiven inventory inputs */
    });
</script>
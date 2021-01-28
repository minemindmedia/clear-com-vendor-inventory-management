<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 */
?>
<div class="wrap">
    <?php

    /* start post request */
    if (isset($_POST['action'])) {
        $post_id = $_POST['ID'];
        $porduct_id = $_POST['product_id'];
        $product_quantity_received = $_POST['product_quantity_received'];
        $product_quantity_returned = $_POST['product_quantity_returned'];
        $product_quantity_back_order = $_POST['product_quantity_back_order'];
        $product_quantity_cancelled = $_POST['product_quantity_cancelled'];
        // $expected_date = $_POST['expected_date'];
        $product_expected_date_back_order = strtotime($_POST['product_expected_date_back_order']);

        $redirect = '';
        $expected_date = '';
        // update received quantity
        if($product_quantity_received > 0) {
            if ($order->post_status != "") {
                $order->post_status .= "|";
            }
            $order->post_status = 'publish';
            if ($redirect == "") {
                $redirect = "publish";
            }
            update_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_received', $product_quantity_received);
            $qty_to_update = get_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_received');
            update_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_qty', $qty_to_update[0] - $product_quantity_received);
            update_post_meta($porduct_id,'_stock_status','instock');
        }

        // update qty or back order quantity
        if($product_quantity_back_order > 0) {
            if ($order->post_status != "") {
                $order->post_status .= "|";
            }
            $order->post_status .= 'pending';
            update_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_qty', $product_quantity_back_order);
            if ($product_expected_date_back_order) {
                if (!$expected_date || $expected_date > $product_expected_date_back_order) {
                    $expected_date = $product_expected_date_back_order;
                }
                update_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_date', $product_expected_date_back_order);
            } else {
                delete_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_date');
            }
            if ($redirect == "") {
                $redirect = "pending";
            }
        } else {
            delete_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_qty');
        }

        // update returned quantity
        if($product_quantity_returned > 0) {
            if ($order->post_status != "") {
                $order->post_status .= "|";
            }
            $order->post_status .= 'returned';
            if ($redirect == "") {
                $redirect = "returned";
            }
            update_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_returned', $product_quantity_returned);
        }

        // update cancelled quantity
        if($product_quantity_cancelled > 0) {
            if ($order->post_status != "") {
                $order->post_status .= "|";
            }
            $order->post_status .= 'private';
            if ($redirect == "") {
                $redirect = "private";
            }
            update_post_meta($post_id, 'wcvmgo_' . $porduct_id . '_cancelled', $product_quantity_cancelled);
        }

        // update expected date
        if($expected_date) {
            update_post_meta($post_id, 'expected_date', $expected_date);
        } else {
            delete_post_meta($post_id, 'expected_date');
        }

        // update set date
        if(!empty($product_expected_date_back_order)) {
            update_post_meta($post_id, 'set_date', time());
            // update_post_meta($post_id, 'set_date', strtotime($product_expected_date_back_order));
        }

        // update post status
        global $wpdb;
        $query = "UPDATE wp_posts SET post_status = '" . $order->post_status . "' WHERE ID = " . $post_id;
        $wpdb->query($query);
        wp_redirect(site_url('/wp-admin/admin.php?page=wcvm-epo&status=' . $redirect) . '#order' . $post_id);
        exit();
    }
    /* end post request */

    global $wpdb;
    $status = "";
    if (array_key_exists('status', $_REQUEST)) {
        $status = $_REQUEST['status'];
    }
    $show_status = $status ? $status : 'draft';
    $status = $show_status;
    $posts_table = $wpdb->prefix . "posts";
    $posts_table_sql = "SELECT * 
                    FROM `" . $posts_table . "` p
                    LEFT JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND meta_key = 'wcvmgo_product_id' 
                    LEFT JOIN " . $wpdb->prefix . "vendor_po_lookup wvpl ON wvpl.product_id = pm.meta_value
                    where p.post_status = '" . $show_status . "' and p.post_type = 'wcvm-order' ORDER BY pm.post_id DESC";
    $orders = $wpdb->get_results($posts_table_sql);

    foreach ($orders as $order) {
        $vendors = explode(',', $order->vendor_name);
        $vendor_ids = explode(',', $order->vendor_id);
        $vendor_prices = explode(',', $order->vendor_price);
        $vendor_skus = explode(',', $order->vendor_sku);
        $wcvmgo_qty = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_qty');
        $wcvmgo_received = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_received');
        $wcvmgo_returned = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_returned');
        $wcvmgo_cancelled = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_cancelled');
        $wcvmgo_set_date = get_post_meta($order->ID, 'set_date');
        $wcvmgo_date = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_date');
        $defaul_value = '';
        if($wcvmgo_received || $wcvmgo_returned|| $wcvmgo_cancelled) {
            $defaul_value = 0;
        }

        $vendor_price = 0;
        $vendor_sku = '';
        $i = 0;
        while ($i < count($vendor_ids)) {
            if ($vendor_ids[$i] == $order->primary_vendor_id) {
                $vendor_price = $vendor_prices[$i];
                $vendor_sku = $vendor_skus[$i];
                break;
            }$i++;
        }
        ?>
        <h1><?= esc_html__('Receive Inventory', 'wcvm') ?></h1>
        <form id="form_<?php echo $order->ID; ?>" action="" method="post">
            <input type="hidden" name="ID" value="<?= esc_attr($order->ID) ?>">
            <input type="hidden" name="product_id" value="<?= esc_attr($order->product_id) ?>">
            <input type="hidden" name="expected_date" value="<?= esc_attr($wcvmgo_date ? $wcvmgo_date[0] : '') ?>">
            <h4 style="margin-bottom: 5px;">
                <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->ID)) ?>,
                <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html($order->primary_vendor_name)) ?>,
                <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->post_date))) ?>
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
                    <tr>
                        <td><a href=""><?php echo $order->sku; ?></a></td>
                        <td><?php echo $order->category; ?></td>
                        <td></td>
                        <td><?php echo $order->primary_vendor_name; ?></td>
                        <td></td>
                        <td><?php echo $vendor_sku; ?></td>
                        <td><?php echo wc_price($vendor_price); ?></td>
                        <td id="quantity_<?php echo $order->ID; ?>" data-quantity="<?php echo $wcvmgo_qty[0] ? $wcvmgo_qty[0] : ''; ?>"><?php echo $wcvmgo_qty[0] ? $wcvmgo_qty[0] : ''; ?></td>
                        <td><input type="text" id="product_quantity_received_<?php echo $order->ID; ?>" name="product_quantity_received" value="<?php echo $wcvmgo_received ? $wcvmgo_received[0] : $defaul_value; ?>" style="width:60px;"></td>
                        <td><input type="text" id="product_quantity_returned_<?php echo $order->ID; ?>" name="product_quantity_returned" value="<?php echo $wcvmgo_returned ? $wcvmgo_returned[0] : $defaul_value; ?>" style="width:60px;"></td>
                        <td><input type="text" id="product_quantity_back_order_<?php echo $order->ID; ?>" name="product_quantity_back_order" value="<?php // echo $wcvmgo_product_quantity_back_order ? $wcvmgo_product_quantity_back_order[0] : $defaul_value; ?>" style="width:60px;"></td>
                        <td><input type="text" id="product_quantity_cancelled_<?php echo $order->ID; ?>" name="product_quantity_cancelled" value="<?php echo $wcvmgo_cancelled ? $wcvmgo_cancelled[0] : $defaul_value; ?>" style="width:60px;"></td>
                        <td><input type="text" id="product_expected_date_back_order_<?php echo $order->ID; ?>" name="product_expected_date_back_order" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" value="<?php echo $wcvmgo_set_date ? date('Y-m-d', $wcvmgo_set_date[0]) : ''; ?>"></td>                    
    <!--                    <td><input type="text" value="" style="width:60px;"></td>-->
                        <td></td>
                    </tr>
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
    <?php } ?>
</div>

<script>
    jQuery(document).ready(function ($) {
        "use strict";
        $(document).on('click', 'button[data-role="receive-inventory"]', function () {
            var form = $(this).closest('form')[0];
            var id = form.id.replace('form_', '');
            var isValid = true;
            var quantity = parseInt($('#quantity_' + id).data('quantity'));
            var received = $('#product_quantity_received_' + id).val();
            var backOrder = $('#product_quantity_returned_' + id).val();
            var cancel = $('#product_quantity_back_order_' + id).val();
            var returned = $('#product_quantity_cancelled_' + id).val();
            received = received ? parseInt(received) : 0;
            backOrder = backOrder ? parseInt(backOrder) : 0;
            cancel = cancel ? parseInt(cancel) : 0;
            returned = returned ? parseInt(returned) : 0;
            if (quantity != (received + backOrder + cancel + returned)) {
                isValid = false;
            }
            if (!isValid) {
                alert('Sum of "QTY Rcv", "Vnd BO", "Cancel", "Returns Open" should equal "Order QTY"');
            }
            return isValid;
        });
    });
</script>
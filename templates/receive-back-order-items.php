<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 */

/* start post request */
global $wpdb;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $wcvmgo_product_quantity = 0;
    $vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_order';
    $order = get_post($_POST['ID']);
    if ($order && $order->post_type == 'wcvm-order') {
        $isValid = true;
        foreach ($order->wcvmgo as $productId) {
            $data = get_post_meta($order->ID, 'wcvmgo_' . $productId, true);
            $_POST['product_quantity_received'][$productId] = $data['product_quantity_received'] + $_POST['__product_quantity_received'][$productId];
            $_POST['product_quantity_back_order'][$productId] = $_POST['__product_quantity_back_order'][$productId];
            $_POST['product_quantity_canceled'][$productId] = $_POST['__product_quantity_canceled'][$productId];
            if ($data['product_quantity'] != $_POST['product_quantity_received'][$productId] + $_POST['product_quantity_back_order'][$productId] + $_POST['product_quantity_canceled'][$productId]) {
                $isValid = false;
                break;
            }
        }
        if ($isValid) {
            $order->post_status = 'publish';
            $expectedDate = '';
            foreach ($order->wcvmgo as $productId) {
                $data = get_post_meta($order->ID, 'wcvmgo_' . $productId, true);
                $origin = $data;
                $data['product_quantity_received'] = (int)$_POST['product_quantity_received'][$productId];
                $data['product_quantity_back_order'] = (int)$_POST['product_quantity_back_order'][$productId];
                $data['product_quantity_canceled'] = (int)$_POST['product_quantity_canceled'][$productId];
                $data['product_expected_date_back_order'] = $_POST['product_expected_date_back_order'][$productId];
                if ($data['product_quantity_back_order'] && $data['product_expected_date_back_order']) {
                    $data['product_expected_date_back_order'] = strtotime($data['product_expected_date_back_order']);
                } else {
                    $data['product_expected_date_back_order'] = '';
                }
                update_post_meta($order->ID, 'wcvmgo_' . $productId, $data);
                if ($data['product_quantity_back_order']) {
                    $order->post_status = 'pending';
                    update_post_meta($order->ID, 'wcvmgo_' . $productId . '_qty', $data['product_quantity_back_order']);
                    if ($data['product_expected_date_back_order']) {
                        if (!$expectedDate || $expectedDate > $data['product_expected_date_back_order']) {
                            $expectedDate = $data['product_expected_date_back_order'];
                        }
                        update_post_meta($order->ID, 'wcvmgo_' . $productId . '_date', $data['product_expected_date_back_order']);
                    } else {
                        delete_post_meta($order->ID, 'wcvmgo_' . $productId . '_date');
                    }
                } else {
                    if ($order->post_status == 'publish' && $data['product_quantity_canceled']) {
                        $order->post_status = 'private';
                    }
                    delete_post_meta($order->ID, 'wcvmgo_' . $productId . '_qty');
                    delete_post_meta($order->ID, 'wcvmgo_' . $productId . '_date');
                }
                if (!empty($origin['product_quantity_received'])) {
                    $stock = $data['product_quantity_received'] + get_post_meta($productId, '_stock', true) - $origin['product_quantity_received'];
                } else {
                    $stock = $data['product_quantity_received'] + get_post_meta($productId, '_stock', true);
                }
                update_post_meta($productId, '_stock', $stock);

                $update_data['product_quantity'] = $wcvmgo_product_quantity;
                $update_data['product_quantity_received'] = $data['product_quantity_received'];
                $update_data['product_quantity_back_order'] = $data['product_quantity_back_order'];
                $update_data['product_quantity_canceled'] = $data['product_quantity_canceled'];
                // $update_data['product_quantity_returned'] = $data['product_quantity_returned'];
                $update_data['product_expected_date_back_order'] = $data['product_expected_date_back_order'];
                if(!empty($expectedDate)) {
                    $update_data['expected_date'] = $expectedDate;
                }
                // $update_data['set_date'] = time();
                $update_data['updated_date'] = date('Y/m/d H:i:s a');
                $where_data['product_id'] = $productId;
                $where_data['order_id'] = $order->ID;
                $updated = $wpdb->update($vendor_purchase_order_table, $update_data, $where_data);
                // echo $wpdb->last_query;
            }
            if ($expectedDate) {
                update_post_meta($order->ID, 'expected_date', $expectedDate);
            } else {
                delete_post_meta($order->ID, 'expected_date');
            }
            if ($_POST['action'] == 'archive') {
                update_post_meta($order->ID, 'old_status', $order->post_status);
                $order->post_status = 'trash';
            }
            wp_update_post(array(
                'ID' => $order->ID,
                'post_status' => $order->post_status,
            ));
             // Print last SQL query string
            // echo $wpdb->last_query;

            // // Print last SQL query result
            // echo $wpdb->last_result;

            // Print last SQL query Error
            // echo $wpdb->last_error;
            // print_r('productID ' .$productId . "orderID " . $orderId . "vendorID " . $vendorId);
            // die;
            wp_redirect(site_url('/wp-admin/admin.php?page=wcvm-epo&status=' . $order->post_status) . '#order' . $order->ID);
            exit();
        }
    }
}
/* end post request */


global $wpdb;
$records = false;
$status = "";
if (array_key_exists('status', $_REQUEST)) {
    $status = $_REQUEST['status'];
}
$show_status = $status ? $status : 'pending';
$status = $show_status;
$posts_table = $wpdb->prefix . "posts";
$posts_table_sql = "SELECT * FROM `" . $posts_table . "` p
                LEFT JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND meta_key = 'wcvmgo_product_id' 
                LEFT JOIN " . $wpdb->prefix . "vendor_po_lookup wvpl ON wvpl.product_id = pm.meta_value
                where p.post_status = '" . $show_status . "' and p.post_type = 'wcvm-order' ORDER BY pm.post_id DESC";
$orders = $wpdb->get_results($posts_table_sql);
?>
<div class="wrap">
    <h1><?= esc_html__('Receive Back Order Items', 'wcvm') ?></h1>
    <?php
    if($orders) {
        $printed_po_numbers = [];
        $last_order_id = 0;
        foreach ($orders as $order) {

            $vendors = explode(',', $order->vendor_name);
            $vendor_ids = explode(',', $order->vendor_id);
            $vendor_prices = explode(',', $order->vendor_price);
            $vendor_skus = explode(',', $order->vendor_sku);
            $wcvmgo = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id);
            if($wcvmgo) {
                $product_quantity = $wcvmgo[0]['product_quantity'] ? $wcvmgo[0]['product_quantity'] : '';
                $product_quantity_received = isset($wcvmgo[0]['product_quantity_received']) ? $wcvmgo[0]['product_quantity_received'] : '';
                $product_quantity_returned = isset($wcvmgo[0]['product_quantity_returned']) ? $wcvmgo[0]['product_quantity_returned'] : '';
                $product_quantity_back_order = isset($wcvmgo[0]['product_quantity_back_order']) ? $wcvmgo[0]['product_quantity_back_order'] : '';
                $product_quantity_canceled = isset($wcvmgo[0]['product_quantity_canceled']) ? $wcvmgo[0]['product_quantity_canceled'] : '';
                $product_expected_date_back_order = isset($wcvmgo[0]['product_expected_date_back_order']) ? date('Y-m-d', (int) $wcvmgo[0]['product_expected_date_back_order']) : '';
                // print_r($wcvmgo[0]['product_expected_date_back_order']);
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

            if ($last_order_id > 0 && $last_order_id != $order->ID) {
                $records = true;
    ?>
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
        <div style="padding-top: 5px;">
            <button type="submit" name="action" value="update" class="button button-primary"><?= esc_html__('Set Inventory', 'wcvm') ?></button>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button type="submit" name="action" value="archive" class="button"><?= esc_html__('Set Inventory & Archive', 'wcvm') ?></button>
        </div>
    </form>
    <br><br>
    <?php
    }
    if (!in_array($order->ID, $printed_po_numbers)) {
        $printed_po_numbers[] = $order->ID;
        $last_order_id = $order->ID;
        $records = true;
    ?>
    <form action="" method="post">
        <input type="hidden" name="ID" value="<?= esc_attr($order->ID) ?>">
        <h4 style="margin-bottom: 5px;">
                <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->ID)) ?>,
                <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html($order->primary_vendor_name)) ?>,
                <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->post_date))) ?>
            </h4>
        <?php $table = new Vendor_Management_Columns(); ?>
        <?php $table_headers = $table->get_columns_receive_back_order_items(); ?>
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
            <?php } ?>
                <tr>
                    <td><a href=""><?php echo $order->sku; ?></a></td>
                    <td><a href=""><?php echo $order->category; ?></a></td>
                    <td></td>
                    <td><?php echo $order->primary_vendor_name; ?></td>
                    <td></td>
                    <td><?php echo $vendor_sku; ?></td>
                    <td><?php echo wc_price($vendor_price); ?></td>
                    <td><input type="text" data-role="__product_quantity_received" name="__product_quantity_received[<?php echo $order->product_id; ?>]" value="<?php echo $product_quantity_received; ?>" style="width:60px;"></td>
                    <td><input type="text" data-role="__product_quantity_back_order" name="__product_quantity_back_order[<?php echo $order->product_id; ?>]" value="<?php echo $product_quantity_back_order; ?>" style="width:60px;"></td>
                    <td><input type="text" data-role="__product_quantity_canceled" name="__product_quantity_canceled[<?php echo $order->product_id; ?>]" value="<?php echo $product_quantity_canceled; ?>" style="width:60px;"></td>
                    <td><input type="text" data-role="datetime" name="product_expected_date_back_order[<?php echo $order->product_id; ?>]"  value="<?php echo $product_expected_date_back_order; ?>" style="text-align: center;width: 70px;font-size: 10px;"></td>
                    <td></td>
                </tr>
                <?php } ?>
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
        <div style="padding-top: 5px;">
            <button type="submit" name="action" value="update" class="button button-primary"><?= esc_html__('Set Inventory', 'wcvm') ?></button>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button type="submit" name="action" value="archive" class="button"><?= esc_html__('Set Inventory & Archive', 'wcvm') ?></button>
        </div>
    </form>
    <br><br>
    <?php
    } if(!$records) {
        echo 'No Orders Found';
    }
 ?>
</div>
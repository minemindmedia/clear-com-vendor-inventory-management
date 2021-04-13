<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 */
?>
<div class="wrap">
    <?php
    /* start post request */
    global $wpdb;
    $vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_orders';
    $vendor_purchase_order_items_table = $wpdb->prefix . 'vendor_purchase_orders_items';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $wcvmgo_product_quantity = 0;

        $order_details_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
                . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
                . " WHERE po.order_id = " . $_POST['ID'];
        $order_details = $wpdb->get_results($order_details_sql);

        $order = get_post($_POST['ID']);
        if ($order_details) {

            $isValid = true;
            foreach ($order_details as $single_order) {
                if ($single_order->post_status == 'trash') {
                    continue;
                }
                $_POST['product_quantity_back_order'][$single_order->product_id] = 0;
                $_POST['product_expected_date_back_order'][$single_order->product_id] = '';     
                if ($single_order->product_ordered_quantity != (int) $_POST['product_quantity_received'][$single_order->product_id] + (int) $_POST['product_quantity_back_order'][$single_order->product_id] + (int) $_POST['product_quantity_canceled'][$single_order->product_id] + (int) $_POST['product_quantity_returned'][$single_order->product_id]) {
                    $isValid = false;
                    break;
                }
            }
            if ($isValid) {

                $expectedDate = '';
                $redirect = '';
                $order->post_status = "";
                $returned_date = NULL;
                
                foreach ($order_details as $single_order) {
                        $data['product_quantity_received'] = (int) $_POST['product_quantity_received'][$single_order->product_id];
                        $data['product_quantity_back_order'] = (int) $_POST['product_quantity_back_order'][$single_order->product_id];
                        $data['product_quantity_canceled'] = (int) $_POST['product_quantity_canceled'][$single_order->product_id];
                        $data['product_quantity_returned'] = (int) $_POST['product_quantity_returned'][$single_order->product_id];
                        $data['product_expected_date_back_order'] = $_POST['product_expected_date_back_order'][$single_order->product_id];
                        $data['product_quantity_returned_note'] = $_POST['product_quantity_returned_note'][$single_order->product_id];
                        $data['product_quantity_canceled_note'] = $_POST['product_quantity_canceled_note'][$single_order->product_id];
                        
                        if ($data['product_quantity_back_order'] && $data['product_expected_date_back_order']) {
                            $data['product_expected_date_back_order'] = strtotime($data['product_expected_date_back_order']);
                        } else {
                            $data['product_expected_date_back_order'] = '';
                        }
                        if ($data['product_quantity_received'] > 0) {

                            if ($order->post_status != "") {
                                $order->post_status .= "|";
                            }
                            $order->post_status .= 'completed';
                            if ($redirect == "") {
                                $redirect = "completed";
                            }

                        }
                        if ($data['product_quantity_back_order'] > 0) {
                            if ($order->post_status != "") {
                                $order->post_status .= "|";
                            }
                            $order->post_status .= 'back-order';
                            if ($data['product_expected_date_back_order']) {
                                if (!$expectedDate || $expectedDate > $data['product_expected_date_back_order']) {
                                    $expectedDate = $data['product_expected_date_back_order'];
                                }
                            } else {
                                $update_data['product_expected_date_back_order'] = '';
                            }
                            if ($redirect == "") {
                                $redirect = "back-order";
                            }
                        } else {
                            $update_data['product_quantity_back_order'] = 0;
                        }
                        if ($data['product_quantity_canceled'] > 0) {
                            if ($order->post_status != "") {
                                $order->post_status .= "|";
                            }
                            $order->post_status .= 'canceled';
                            if ($redirect == "") {
                                $redirect = "canceled";
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
                            $returned_date = date('Y/m/d H:i:s a');
                        }

//                        if (!empty($origin['product_quantity_received'])) {
//                            $stock = $data['product_quantity_received'] + $single_order->product_stock - $origin['product_quantity_received'];
//                        } else {
//                            $stock = $data['product_quantity_received'] + $single_order->product_stock;
//                        }
                        $update_data['product_expected_date_back_order'] = $expectedDate;
                        $update_data['product_quantity_received'] = $data['product_quantity_received'];
                        $update_data['product_quantity_back_order'] = $data['product_quantity_back_order'];
                        $update_data['product_quantity_canceled'] = $data['product_quantity_canceled'];
                        $update_data['product_quantity_returned'] = $data['product_quantity_returned'];
                        $update_data['product_quantity_returned_note'] = stripslashes($data['product_quantity_returned_note']);
                        $update_data['product_quantity_canceled_note'] = stripslashes($data['product_quantity_canceled_note']);
                        if (!empty($expectedDate)) {
                            $update_data['product_expected_date'] = $expectedDate;
                        }

                        $update_data['updated_date'] = date('Y/m/d H:i:s a');
                        $where_data['product_id'] = $single_order->product_id;
                        $where_data['vendor_order_idFk'] = $single_order->vendor_order_idFk;
                        $updated = $wpdb->update($vendor_purchase_order_items_table, $update_data, $where_data);
                        
                        
                        $quantity_to_deduct_from_on_order = $data['product_quantity_received'] + $data['product_quantity_canceled'] + $data['product_quantity_returned'] + $data['product_quantity_back_order'];
                        $updateOnOrderQuery = "UPDATE wp_vendor_po_lookup SET on_vendor_bo = on_vendor_bo + ".$data['product_quantity_back_order']." ,stock = stock + " . $data['product_quantity_received'] . ",on_order = on_order - " . $quantity_to_deduct_from_on_order . " WHERE product_id = " . $single_order->product_id . "";
                        $wpdb->query($updateOnOrderQuery);
                        $val = get_post_meta($single_order->product_id ,'_stock');
                        $exsitingStock = $val[0];
                        $updateStock = $exsitingStock + $data['product_quantity_received']; 
                        update_post_meta($single_order->product_id, '_stock', $updateStock);                        
                    }
                        if ($_POST['action'] == 'archive') {
                            if ($order->post_status != "") {
                                $order->post_status .= "|";
                            }
                            $order->post_status .= 'trash';
                        }
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
                        $status = implode('|',array_unique(explode('|', $order->post_status)));                        
                        $updatePOProductData['post_status'] = $status;
                        $updatePOProductData['set_date'] = time();
                        $updatePOProductData['returned_date'] = $returned_date;
                        $updatePOProductData['updated_date'] = date('Y/m/d H:i:s a');
                        $updatePOProductData['updated_by'] = get_current_user_id();
                        $wherePOProductData['order_id'] = $single_order->order_id;
                        $updated = $wpdb->update($vendor_purchase_order_table, $updatePOProductData, $wherePOProductData);
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
        <div class="block mb-4"></div>
        <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/4de99c111d.js" crossorigin="anonymous"></script>
        <?php
        require_once plugin_dir_path(__FILE__) . 'po-status-bar.php';
        global $wpdb;
        $records = false;
        $status = "";
        if (array_key_exists('status', $_REQUEST)) {
            $status = $_REQUEST['status'];
        }
        $show_status = $status ? $status : 'on-order';
        $status = $show_status;
//    $posts_table = $wpdb->prefix . "posts";
//    $posts_table_sql = "SELECT * FROM `" . $posts_table . "` p
//                    LEFT JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND meta_key = 'wcvmgo_product_id' 
//                    LEFT JOIN " . $wpdb->prefix . "vendor_po_lookup wvpl ON wvpl.product_id = pm.meta_value
//                    where p.post_status = '" . $show_status . "' and p.post_type = 'wcvm-order' ORDER BY pm.post_id DESC";
        $purchase_order_table_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
                . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
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
//                $vendors = explode(',', $order->vendor_name);
//                $vendor_ids = explode(',', $order->vendor_id);
//                $vendor_prices = explode(',', $order->vendor_price);
//                $vendor_skus = explode(',', $order->vendor_sku);
//            $wcvmgo = get_post_meta($order->order_id, 'wcvmgo_' . $order->product_id);
//            if ($wcvmgo) {
//                $product_quantity = $wcvmgo[0]['product_quantity'] ? $wcvmgo[0]['product_quantity'] : '';
//                $product_quantity_received = isset($wcvmgo[0]['product_quantity_received']) ? $wcvmgo[0]['product_quantity_received'] : '';
//                $product_quantity_returned = isset($wcvmgo[0]['product_quantity_returned']) ? $wcvmgo[0]['product_quantity_returned'] : '';
//                $product_quantity_back_order = isset($wcvmgo[0]['product_quantity_back_order']) ? $wcvmgo[0]['product_quantity_back_order'] : '';
//                $product_quantity_canceled = isset($wcvmgo[0]['product_quantity_canceled']) ? $wcvmgo[0]['product_quantity_canceled'] : '';
//                $product_expected_date_back_order = isset($wcvmgo[0]['product_expected_date_back_order']) ? date('Y-m-d', (int) $wcvmgo[0]['product_expected_date_back_order']) : '';
//                // print_r($wcvmgo[0]['product_expected_date_back_order']);
//            }
                $product_quantity = $order->product_ordered_quantity;
                $product_quantity_received = $order->product_quantity_received;
                $product_quantity_returned = $order->product_quantity_returned;
                $product_quantity_back_order = $order->product_quantity_back_order;
                $product_quantity_canceled = $order->product_quantity_canceled;
                $product_expected_date_back_order = isset($order->product_expected_date_back_order) ? date('Y-m-d', (int) $order->product_expected_date_back_order) : '';

//                $vendor_price = 0;
//                $vendor_sku = '';
//                $i = 0;
//                while ($i < count($vendor_ids)) {
//                    if ($vendor_ids[$i] == $order->vendor_id) {
//                        $vendor_price = $vendor_prices[$i];
//                        $vendor_sku = $vendor_skus[$i];
//                        break;
//                    }$i++;
//                }
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
                <div class="flex">
                    <div class="flex-1"></div>
                    <div>
                        <div class="flex space-x-4">
                            <div>
                            <button type="submit" name="action" value="update" data-role="receive-inventory" class="flex block px-2 py-1.5 border-2 border-yellow-500 bg-yellow-500 hover:bg-yellow-700 text-white hover:text-white text-xs rounded m-0">
                            <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="layer-plus" class="svg-inline--fa fa-layer-plus fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M304 144h64v64c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16v-64h64c8.84 0 16-7.16 16-16V96c0-8.84-7.16-16-16-16h-64V16c0-8.84-7.16-16-16-16h-32c-8.84 0-16 7.16-16 16v64h-64c-8.84 0-16 7.16-16 16v32c0 8.84 7.16 16 16 16zm195.59 220.1l-58.54-26.53-161.19 73.06c-7.56 3.43-15.59 5.17-23.86 5.17s-16.29-1.74-23.86-5.17L70.95 337.57 12.41 364.1c-16.55 7.5-16.55 32.5 0 40l232.94 105.59c6.8 3.08 14.49 3.08 21.29 0L499.59 404.1c16.55-7.5 16.55-32.5 0-40zM12.41 275.9l232.94 105.59c6.8 3.08 14.49 3.08 21.29 0L448 299.28V280.7c-15.32 4.38-31.27 7.29-48 7.29-88.01 0-160.72-64.67-173.72-149.04L12.41 235.9c-16.55 7.5-16.55 32.5 0 40z"></path></svg>
                            <?= esc_html__('Set Inventory', 'wcvm') ?>
                    </button>
                            </div>
                            <div>
                            <button type="submit" name="action" value="archive" data-role="receive-inventory" class="flex block px-2 py-1.5 border-2 border-yellow-500 bg-yellow-500 hover:bg-yellow-700 text-white hover:text-white text-xs rounded m-0">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="archive" class="inline w-3 h-3 mr-1 self-center" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M32 448c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V160H32v288zm160-212c0-6.6 5.4-12 12-12h104c6.6 0 12 5.4 12 12v8c0 6.6-5.4 12-12 12H204c-6.6 0-12-5.4-12-12v-8zM480 32H32C14.3 32 0 46.3 0 64v48c0 8.8 7.2 16 16 16h480c8.8 0 16-7.2 16-16V64c0-17.7-14.3-32-32-32z"></path></svg>
                        <?= esc_html__('Set Inventory & Archive', 'wcvm') ?>
                    </button>
                            </div>
                        </div>

                    
           
                </div>
            </div>
             
                </form>
           
            <?php } ?>
            <?php
            if (!in_array($order->order_id, $printed_po_numbers)) {
                $printed_po_numbers[] = $order->order_id;
                $last_order_id = $order->order_id;
                $records = true;
                ?>
                <form id="form_<?php echo $order->order_id; ?>" action="" method="post" class="purchase-order border-2 border-t-8 border-yellow-400 p-8 mb-4 bg-gray-50">
                    <input type="hidden" name="ID" value="<?= esc_attr($order->order_id) ?>">
                    <div class="flex space-x-8 text-base font-semibold">
                        <div><?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->order_id)) ?></div>
                        <div><?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->order_date))) ?></div>
                        <div class="flex-1"><?= sprintf(esc_html__('PO Expected Date: %s'), date('m/d/Y', $order->po_expected_date)) ?></div>
                        <div><?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html($order->vendor_name)) ?></div>
                        
                    </div>
                    <?php $table = new Vendor_Management_Columns(); ?>
                    <?php $table_headers = $table->get_columns_receive_inventory(); ?>
                    <table class="wp-list-table widefat striped wcvm-orders my-6">
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
                            <!--<td><a href=""><?php // echo $order->product_sku; ?></a></td>-->
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
                            <td><?php echo $order->product_category; ?></td>
                            <td></td>
                            <td><?php echo $order->vendor_name; ?></td>
                            <td></td>
                            <td><?php echo $order->vendor_sku; ?></td>
                            <td><?php echo wc_price($order->vendor_price_last); ?></td>
                            <td id="quantity_<?php echo $order->product_id; ?>" data-quantity="<?php echo $product_quantity; ?>"><?php echo $product_quantity; ?></td>
                            <td><input type="text" name="product_quantity_received[<?php echo $order->product_id; ?>]" data-role="product_quantity_received" value="<?php echo $product_quantity_received; ?>" style="width:60px;"></td>
                            <td><input type="text" id="<?php echo $order->product_id; ?>" name="product_quantity_returned[<?php echo $order->product_id; ?>]" data-role="product_quantity_returned" value="<?php echo $product_quantity_returned; ?>" style="width:60px;"></td>
                            <!--<td><input type="text" name="product_quantity_back_order[<?php // echo $order->product_id; ?>]" data-role="product_quantity_back_order" value="<?php echo $product_quantity_back_order; ?>" style="width:60px;"></td>-->
                            <td><input type="text" id="<?php echo $order->product_id; ?>" name="product_quantity_canceled[<?php echo $order->product_id; ?>]" data-role="product_quantity_canceled" value="<?php echo $product_quantity_canceled; ?>" style="width:60px;"></td>
                            <!--<td><input type="text" autocomplete="off" name="product_expected_date_back_order[<?php // echo $order->product_id; ?>]" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" value="<?php // echo $product_expected_date_back_order; ?>"></td>-->                    
            <!--                    <td><input type="text" value="" style="width:60px;"></td>-->
                            <!--<td></td>-->
                        </tr>
                        <tr class="hidden" id="product_quantity_returned_note-<?php echo $order->product_id; ?>">
                            <td colspan="11">
                                <textarea class="hidden p-2" type="text" name="product_quantity_returned_note[<?php echo $order->product_id; ?>]" placeholder="Enter notes for QTY Returned:" data-role="product_quantity_returned_note" value="<?php echo $product_quantity_canceled; ?>" style="width:100%;"></textarea>
                            </td>
                        </tr>
                        <tr class="hidden" id="product_quantity_canceled_note-<?php echo $order->product_id; ?>">
                            <td colspan="11">
                                <textarea class="hidden p-2" type="text" name="product_quantity_canceled_note[<?php echo $order->product_id; ?>]" placeholder="Enter notes for QTY Cancelled:" data-role="product_quantity_canceled_note" value="<?php echo $product_quantity_canceled; ?>" style="width:100%;"></textarea>
                            </td>    
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
            <div class="flex">
                    <div class="flex-1"></div>
                    <div>
                        <div class="flex space-x-4">
                            <div>
                            <button type="submit" name="action" value="update" data-role="receive-inventory" class="flex block px-2 py-1.5 border-2 border-yellow-500 bg-yellow-500 hover:bg-yellow-700 text-white hover:text-white text-xs rounded m-0">
                            <svg class="inline w-3 h-3 mr-1 self-center" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="layer-plus" class="svg-inline--fa fa-layer-plus fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M304 144h64v64c0 8.84 7.16 16 16 16h32c8.84 0 16-7.16 16-16v-64h64c8.84 0 16-7.16 16-16V96c0-8.84-7.16-16-16-16h-64V16c0-8.84-7.16-16-16-16h-32c-8.84 0-16 7.16-16 16v64h-64c-8.84 0-16 7.16-16 16v32c0 8.84 7.16 16 16 16zm195.59 220.1l-58.54-26.53-161.19 73.06c-7.56 3.43-15.59 5.17-23.86 5.17s-16.29-1.74-23.86-5.17L70.95 337.57 12.41 364.1c-16.55 7.5-16.55 32.5 0 40l232.94 105.59c6.8 3.08 14.49 3.08 21.29 0L499.59 404.1c16.55-7.5 16.55-32.5 0-40zM12.41 275.9l232.94 105.59c6.8 3.08 14.49 3.08 21.29 0L448 299.28V280.7c-15.32 4.38-31.27 7.29-48 7.29-88.01 0-160.72-64.67-173.72-149.04L12.41 235.9c-16.55 7.5-16.55 32.5 0 40z"></path></svg>
                            <?= esc_html__('Set Inventory', 'wcvm') ?>
                    </button>
                            </div>
                            <div>
                            <button type="submit" name="action" value="archive" data-role="receive-inventory" class="flex block px-2 py-1.5 border-2 border-yellow-500 bg-yellow-500 hover:bg-yellow-700 text-white hover:text-white text-xs rounded m-0">
                            <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="archive" class="inline w-3 h-3 mr-1 self-center" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M32 448c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32V160H32v288zm160-212c0-6.6 5.4-12 12-12h104c6.6 0 12 5.4 12 12v8c0 6.6-5.4 12-12 12H204c-6.6 0-12-5.4-12-12v-8zM480 32H32C14.3 32 0 46.3 0 64v48c0 8.8 7.2 16 16 16h480c8.8 0 16-7.2 16-16V64c0-17.7-14.3-32-32-32z"></path></svg>
                        <?= esc_html__('Set Inventory & Archive', 'wcvm') ?>
                    </button>
                            </div>
                        </div>

                    
           
                </div>
        </form>
        <br><br>
        <?php
    } if (!$records) {
        ?>
        <div class="flex border-2 border-t-8 border-yellow-400 p-8 mb-4 bg-gray-50 text-lg text-semibold">
            There are no orders set to be received.
        </div><?php
    }
    ?>
</div>

<script>
    jQuery(document).ready(function ($) {
        "use strict";

        $(document).on('keyup', 'input', function () {
            var id = $(this).attr('id');
            var input_type = $(this).data('role');
            $('[name ="' + input_type + '_note[' + id + ']"]').addClass('hidden');
            $('#' + input_type + '_note-' + id).addClass('hidden');
            if($(this).val() > 0) {
                $('[name ="' + input_type + '_note[' + id + ']"]').removeClass('hidden');
                $('#' + input_type + '_note-' + id).removeClass('hidden');
            }
         });

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
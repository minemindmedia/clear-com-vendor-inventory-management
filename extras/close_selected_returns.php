<?php
//echo getcwd();die;
define('WP_USE_THEMES', false);
require('../../../../wp-load.php');

$vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_order';
$ids_to_delete = explode(",", $_POST['ids_to_delete']);
$order_to_process = $_POST['order_to_process'];
$post_ids = [];
$deleted = 0;
for ($i = 0; $i < count($ids_to_delete); $i++) {

    $meta_details = explode("_", $ids_to_delete[$i]);

    $orderId = $meta_details[1];
    $productId = $meta_details[0];
    $order_details_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po"
            . " WHERE po.order_id = " .$orderId. " and po.product_id = ".$productId; 
    $order_details = $wpdb->get_results($order_details_sql);
    if ($order_to_process == $orderId) {
        $return_Qty = $order_details[0]->product_quantity_returned;
        $getReturnsData = get_post_meta($orderId, 'wcvmgo_' . $productId . '_returned');
        delete_post_meta($orderId, 'wcvmgo_' . $productId . '_returned');
        delete_post_meta($orderId, 'wcvmgo_' . $productId);
        update_post_meta($orderId, 'wcvmgo_' . $productId . '_return_closed', $getReturnsData[0]);
        
        $update_data['product_quantity_returned'] = 0;
        $update_data['product_quantity_return_closed'] = $return_Qty;
        $update_data['updated_date'] = date('Y/m/d H:i:s a');
        $update_data['updated_by'] = get_current_user_id();
        $where_data['order_id'] = $orderId;
        $where_data['product_id'] = $productId;
        
        $updated = $wpdb->update($vendor_purchase_order_table, $update_data, $where_data);        

        $deleted = 1;
    }
}
$products = get_post_meta($orderId, 'wcvmgo', true);
$status = "";
foreach ($products as $product) {
    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_received')) {
        $status .= "publish";
    }
    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_qty')) {
        if ($status != "") {
            $status.="|";
        }
        $status .= "pending";
    }
    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_cancelled')) {
        if ($status != "") {
            $status.="|";
        }
        $status .= "private";
    }
    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_returned')) {
        if ($status != "") {
            $status.="|";
        }
        $status .= "returned";
    }
    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_return_closed')) {
        if ($status != "") {
            $status.="|";
        }
        $status .= "return_closed";
    }
}

global $wpdb;
$query = "UPDATE wp_posts SET post_status = '" . $status . "' WHERE ID = " . $orderId;
$wpdb->query($query);
$vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_order';
$update_data['post_status'] = $status;
$update_data['updated_date'] = date('Y/m/d H:i:s a');
$update_data['updated_by'] = get_current_user_id();
$where_data['order_id'] = $orderId;
$updated = $wpdb->update($vendor_purchase_order_table, $update_data, $where_data);            

echo $deleted;
?>
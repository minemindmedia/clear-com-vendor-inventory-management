<?php

/** Absolute path to the WordPress directory. */
if ($_SERVER['HTTP_HOST'] == "localhost") {
    require('../../../../wp-load.php');
} else {
    if ( !defined('ABSPATH') )
        define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp/');
    require(ABSPATH . 'wp-load.php');
    }
//echo getcwd();die;
define('WP_USE_THEMES', false);
// require('../../../../wp-load.php');

$vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_orders';
$vendor_purchase_order_items_table = $wpdb->prefix . 'vendor_purchase_orders_items';
$ids_to_delete = explode(",", $_POST['ids_to_delete']);
$order_to_process = $_POST['order_to_process'];
$post_ids = [];
$deleted = 0;
$orderId = 0;
for ($i = 0; $i < count($ids_to_delete); $i++) {

    $meta_details = explode("_", $ids_to_delete[$i]);

    $orderId = $meta_details[1];
    $productId = $meta_details[0];
//    $order_details_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po"
//            . " WHERE po.order_id = " .$orderId. " and po.product_id = ".$productId; 
        $order_details_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
                . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
                . " WHERE po.order_id = " .$orderId.' AND poi.product_id = '.$productId;
    $order_details = $wpdb->get_results($order_details_sql);
    if ($order_to_process == $orderId) {
//        print_r($order_details[0]);die;
        $return_Qty = $order_details[0]->product_quantity_returned;
//        $getReturnsData = get_post_meta($orderId, 'wcvmgo_' . $productId . '_returned');
//        delete_post_meta($orderId, 'wcvmgo_' . $productId . '_returned');
//        delete_post_meta($orderId, 'wcvmgo_' . $productId);
//        update_post_meta($orderId, 'wcvmgo_' . $productId . '_return_closed', $getReturnsData[0]);
        
        $update_data['product_quantity_returned'] = 0;
        $update_data['product_quantity_return_closed'] = $return_Qty;
        $update_data['updated_date'] = date('Y/m/d H:i:s a');
        $update_data['updated_by'] = get_current_user_id();
        $where_data['product_id'] = $order_details[0]->product_id;
        $where_data['vendor_order_idFk'] = $order_details[0]->vendor_order_idFk;        
        $updated = $wpdb->update($vendor_purchase_order_items_table, $update_data, $where_data);        
        $deleted = 1;
    }
}
$order_product_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
        . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
        . " WHERE po.order_id = " .$orderId;
//echo $order_product_sql;die;
    $order_product_details = $wpdb->get_results($order_product_sql);
$status = "";    
//print_r($order_details);die;
foreach($order_product_details as $order){
//    echo $order->product_quantity_received;die;
//    print_r($order);die;
        if($order->product_quantity_received){
            $status .= "completed";
        }
        if($order->product_quantity_back_order){
            if ($status != "") {
                $status.="|";
            }
            $status .= "back-order";
        }
        if($order->product_quantity_canceled){
            if ($status != "") {
                $status.="|";
            }
            $status .= "canceled";
        }
        if($order->product_quantity_returned){
            if ($status != "") {
                $status.="|";
            }
            $status .= "returned";
        }
        if($order->product_quantity_return_closed){
            if ($status != "") {
                $status.="|";
            }
            $status .= "return_closed";
        }
}
//$products = get_post_meta($orderId, 'wcvmgo', true);
//$status = "";
//foreach ($products as $product) {
//    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_received')) {
//        $status .= "completed";
//    }
//    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_qty')) {
//        if ($status != "") {
//            $status.="|";
//        }
//        $status .= "back-order";
//    }
//    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_cancelled')) {
//        if ($status != "") {
//            $status.="|";
//        }
//        $status .= "private";
//    }
//    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_returned')) {
//        if ($status != "") {
//            $status.="|";
//        }
//        $status .= "returned";
//    }
//    if (get_post_meta($orderId, 'wcvmgo_' . $product . '_return_closed')) {
//        if ($status != "") {
//            $status.="|";
//        }
//        $status .= "return_closed";
//    }
//}
//echo $status;die;
global $wpdb;
$postStatus = implode('|',array_unique(explode('|', $status)));                        
$query = "UPDATE wp_posts SET post_status = '" . $postStatus . "' WHERE ID = " . $orderId;
$wpdb->query($query);

$update_po_data['post_status'] = $postStatus;
$update_po_data['updated_date'] = date('Y/m/d H:i:s a');
$update_po_data['updated_by'] = get_current_user_id();
$where_po['order_id'] = $orderId;
//print_r($update_po_data);
//print_r($where_po);die;
$updated = $wpdb->update($vendor_purchase_order_table, $update_po_data, $where_po);            

echo $deleted;
?>
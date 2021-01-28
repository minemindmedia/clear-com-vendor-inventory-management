<?php

define('WP_USE_THEMES', false);
require('../../../wp-load.php');
//require_once( ABSPATH . '/wp-load.php');
$ids_to_delete = explode(",", $_POST['ids_to_delete']);
$order_to_process = $_POST['order_to_process'];
$post_ids = [];
$deleted = 0;
for ($i = 0; $i < count($ids_to_delete); $i++) {

    $meta_details = explode("_", $ids_to_delete[$i]);
    $orderId = $meta_details[1];
    $productId = $meta_details[0];
    if ($order_to_process == $orderId) {
        delete_post_meta($orderId, 'wcvmgo_' . $productId . '_qty');
        delete_post_meta($orderId, 'wcvmgo_' . $productId . '_date');
        delete_post_meta($orderId, 'wcvmgo_' . $productId);
        $products = get_post_meta($orderId, 'wcvmgo', true);
        $index = array_search($productId, $products);
        if ($index !== false) {
            unset($products[$index]);
            $updatedProducts = array_values($products); 
            if ($updatedProducts) {
                update_post_meta($orderId, 'wcvmgo', $updatedProducts);
            } else {
                delete_post_meta($orderId, 'wcvmgo');
                wp_delete_post($orderId);
            }
        }
        $deleted = 1;
    }
}
echo $deleted;
?>
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
    echo $orderId;
    echo ' '.$productId;
    echo ' '.$order_to_process;
    if ($order_to_process == $orderId) {
        delete_post_meta($orderId, 'wcvmgo_' . $productId . '_qty');
        delete_post_meta($orderId, 'wcvmgo_' . $productId . '_date');
        delete_post_meta($orderId, 'wcvmgo_' . $productId);
        $products = get_post_meta($orderId, 'wcvmgo', true);
//        print_r($products);die;
        $index = array_search($productId, $products);
        if ($index !== false) {
            echo 's';
            unset($products[$index]);
            $products = array_values($products);
                    print_r($products);die;

            if ($products) {
                echo 'here 1';die;
                update_post_meta($orderId, 'wcvmgo', $products);
            } else {
                echo 'here';die;
                delete_post_meta($orderId, 'wcvmgo');
                wp_delete_post($orderId);
            }
        }
        echo 'qq';die;
        $deleted = 1;
    }
}
echo $deleted;
?>
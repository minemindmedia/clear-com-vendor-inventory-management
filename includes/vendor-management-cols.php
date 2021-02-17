<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Vendor_Management_Columns {

    public function __construct() {
        
    }

    public function get_columns_receive_inventory() {
        return array(
            '_sku' => __('CC SKU', 'wcvm'),
            //'post_title' => __('Name', 'wcvm'),
            'product_cat' => __('Categories', 'wcvm'),
            'product_tag' => __('Tags', 'wcvm'),
            'wcvm_primary' => __('Vendor Name', 'wcvm'),
            '__wcvm_vendor_code' => __('Vendor Code', 'wcvm'),
            '__wcvm_vendor_sku' => __('Vendor SKU', 'wcvm'),
            '__wcvm_vendor_price_last' => __('Vendor Price', 'wcvm'),
            'product_quantity' => __('Order QTY', 'wcvm'),
            // 'product_expected_date' => __('Expected Order Date', 'wcvm'),
            'product_quantity_received' => __('QTY Rcv', 'wcvm'),
            'product_quantity_returned' => __('QTY Ret', 'wcvm'),
            'product_quantity_back_order' => __('Vnd BO', 'wcvm'),
            'product_quantity_canceled' => __('Cancel', 'wcvm'),
            'product_expected_date_back_order' => __('BO Expected Date', 'wcvm'),
//            'back_orders' => __('Cust BOs', 'wcvm'),
        );
    }

    public function get_columns_receive_back_order_items() {
        return array(
            '_sku' => __('CC SKU', 'wcvm'),
            'post_title' => __('Name', 'wcvm'),
            'wcvm_primary' => __('Vendor Name', 'wcvm'),
            '__wcvm_vendor_code' => __('Vendor Code', 'wcvm'),
            '__wcvm_vendor_sku' => __('Vendor SKU', 'wcvm'),
            'product_quantity' => __('Order QTY', 'wcvm'),
            'product_quantity_received' => __('Curr QTY', 'wcvm'),
            '__product_quantity_received' => __('QTY Rcv', 'wcvm'),
            '__product_quantity_back_order' => __('Vnd BO', 'wcvm'),
            '__product_quantity_canceled' => __('Cancel', 'wcvm'),
            'product_expected_date_back_order' => __('BO Expected Date', 'wcvm'),
            'back_orders' => __('Cust BOs', 'wcvm'),
        );
    }
    public function get_new_orders_columns_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('CC SKU', 'wcvm');
        $columns_array['wcvm_status'] = __('Stock Status', 'wcvm');
        $columns_array['_price'] = __('Our Price', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('Vendor SKU', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('Vendor Price', 'wcvm');
        $columns_array['_stock'] = __('QTY On Hand', 'wcvm');
        $columns_array['_qty_history_30'] = __('30 Days', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('On Order', 'wcvm');
//        if ($status == "" || $status == 'draft' || $status == 'auto-draft') {
        $columns_array['__order_qty'] = __('Order QTY', 'wcvm');
        $columns_array['__delete'] = __('Delete', 'wcvm');

        return $columns_array;
    }
        public function get_on_order_columns_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('CC SKU', 'wcvm');
        $columns_array['wcvm_status'] = __('Stock Status', 'wcvm');
        $columns_array['_price'] = __('Our Price', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('Vendor SKU', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('Vendor Price', 'wcvm');
        $columns_array['_stock'] = __('QTY On Hand', 'wcvm');
        $columns_array['_qty_history_30'] = __('30 Days', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('On Order', 'wcvm');
        $columns_array['__order_qty'] = __('Order QTY', 'wcvm');

        return $columns_array;
    }
        public function get_back_order_columns_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('CC SKU', 'wcvm');
        $columns_array['wcvm_status'] = __('Stock Status', 'wcvm');
        $columns_array['_price'] = __('Our Price', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('Vendor SKU', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('Vendor Price', 'wcvm');
        $columns_array['_stock'] = __('QTY On Hand', 'wcvm');
        $columns_array['_qty_history_30'] = __('30 Days', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('On Order', 'wcvm');
        $columns_array['__order_qty'] = __('BO QTY', 'wcvm');

        return $columns_array;
    }
        public function get_completed_orders_column_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('CC SKU', 'wcvm');
        $columns_array['wcvm_status'] = __('Stock Status', 'wcvm');
        $columns_array['_price'] = __('Our Price', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('Vendor SKU', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('Vendor Price', 'wcvm');
        $columns_array['_stock'] = __('QTY On Hand', 'wcvm');
        $columns_array['_qty_history_30'] = __('30 Days', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('On Order', 'wcvm');
        $columns_array['__order_qty'] = __('Received QTY', 'wcvm');

        return $columns_array;
    }
        public function get_cancelled_orders_column_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('CC SKU', 'wcvm');
        $columns_array['wcvm_status'] = __('Stock Status', 'wcvm');
        $columns_array['_price'] = __('Our Price', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('Vendor SKU', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('Vendor Price', 'wcvm');
        $columns_array['_stock'] = __('QTY On Hand', 'wcvm');
        $columns_array['_qty_history_30'] = __('30 Days', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('On Order', 'wcvm');
        $columns_array['__order_qty'] = __('Cancelled QTY', 'wcvm');

        return $columns_array;
    }
        public function get_returned_orders_column_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('CC SKU', 'wcvm');
        $columns_array['wcvm_status'] = __('Stock Status', 'wcvm');
        $columns_array['_price'] = __('Our Price', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('Vendor SKU', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('Vendor Price', 'wcvm');
        $columns_array['_stock'] = __('QTY On Hand', 'wcvm');
        $columns_array['_qty_history_30'] = __('30 Days', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('On Order', 'wcvm');
        $columns_array['__order_qty'] = __('Returned QTY', 'wcvm');
        $columns_array['__delete'] = __('Mark Close', 'wcvm');        

        return $columns_array;
    }
    public function get_columns_vendors_list() {
        $status = "";
        if (array_key_exists('status', $_REQUEST)) {
            $status = $_REQUEST['status'];
        }
        $columns_array = [];
//        $columns_array['wcvm_rare'] = __('Rare', 'wcvm');
        $columns_array['_sku'] = __('CC SKU', 'wcvm');
        $columns_array['wcvm_status'] = __('Stock Status', 'wcvm');
        $columns_array['_price'] = __('Our Price', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('Vendor SKU', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('Vendor Price', 'wcvm');
        $columns_array['_stock'] = __('QTY On Hand', 'wcvm');
        $columns_array['_qty_history_30'] = __('30 Days', 'wcvm');
//        $columns_array['wcvm_threshold_low'] = __('Low Thresh', 'wcvm');
//        $columns_array['wcvm_threshold_reorder'] = __('Reorder Thresh', 'wcvm');
//        $columns_array['wcvm_reorder_qty'] = __('Reorder QTY', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('On Order', 'wcvm');
//        $columns_array['_wcvm_on_vendor_bo'] = __('On Vendor BO', 'wcvm');
        if ($status == "" || $status == 'draft' || $status == 'auto-draft') {
            $columns_array['__order_qty'] = __('Order QTY', 'wcvm');
        } else if ($status == 'publish') {
            $columns_array['__order_qty'] = __('Received QTY', 'wcvm');
        } else if ($status == 'back-order') {
            $columns_array['__order_qty'] = __('BO QTY', 'wcvm');
        } else if ($status == 'private') {
            $columns_array['__order_qty'] = __('Cancelled QTY', 'wcvm');
        } else if ($status == 'returned') {
            $columns_array['__order_qty'] = __('Returned QTY', 'wcvm');
            $columns_array['__delete'] = __('Mark Close', 'wcvm');
        } else if ($status == 'return_closed') {
            $columns_array['__order_qty'] = __('Returned Closed QTY', 'wcvm');
        }

        if (!array_key_exists('__delete', $columns_array)) {
            $columns_array['__delete'] = __('Delete', 'wcvm');
        }


        return $columns_array;
    }

}

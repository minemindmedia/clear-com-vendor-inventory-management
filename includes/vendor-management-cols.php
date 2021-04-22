<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Vendor_Management_Columns {

    public function __construct() {
        
    }

    public function get_columns_receive_inventory() {
        return array(
            '_sku' => __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm'),
            //'post_title' => __('Name', 'wcvm'),
            'product_cat' => __('<div class="text-sm font-semibold">Categories</div>', 'wcvm'),
            'product_tag' => __('<div class="text-sm font-semibold">Tags</div>', 'wcvm'),
            'wcvm_primary' => __('<div class="text-sm font-semibold">Vendor Name</div>', 'wcvm'),
            '__wcvm_vendor_code' => __('<div class="text-sm font-semibold">Vendor Code</div>', 'wcvm'),
            '__wcvm_vendor_sku' => __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm'),
            '__wcvm_vendor_price_last' => __('<div class="text-sm font-semibold">Vendor Price</div>', 'wcvm'),
            'product_quantity' => __('<div class="text-sm font-semibold">Order QTY</div>', 'wcvm'),
            // 'product_expected_date' => __('Expected Order Date', 'wcvm'),
            'product_quantity_received' => __('<div class="text-sm font-semibold">QTY Rcv</div>', 'wcvm'),
            'product_quantity_returned' => __('<div class="text-sm font-semibold">QTY Ret</div>', 'wcvm'),
//            'product_quantity_back_order' => __('<div class="text-sm font-semibold">Vnd BO</div>', 'wcvm'),
            'product_quantity_canceled' => __('<div class="text-sm font-semibold">Cancel</div>', 'wcvm'),
//            'product_expected_date_back_order' => __('<div class="text-sm font-semibold">BO Expected Date</div>', 'wcvm'),
//            'back_orders' => __('Cust BOs', 'wcvm'),
        );
    }

    public function get_columns_receive_back_order_items() {
        return array(
            '_sku' => __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm'),
            'post_title' => __('Name', 'wcvm'),
            'wcvm_primary' => __('<div class="text-sm font-semibold">Vendor Name</div>', 'wcvm'),
            '__wcvm_vendor_code' => __('<div class="text-sm font-semibold">Vendor Code</div>', 'wcvm'),
            '__wcvm_vendor_sku' => __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm'),
            'product_quantity' => __('<div class="text-sm font-semibold">Order QTY</div>', 'wcvm'),
            'product_quantity_received' => __('<div class="text-sm font-semibold">Curr QTY</div>', 'wcvm'),
            '__product_quantity_received' => __('<div class="text-sm font-semibold">QTY Rcv</div>', 'wcvm'),
            '__product_quantity_back_order' => __('<div class="text-sm font-semibold">Vnd BO</div>', 'wcvm'),
            '__product_quantity_canceled' => __('<div class="text-sm font-semibold">Cancel</div>', 'wcvm'),
            'product_expected_date_back_order' => __('<div class="text-sm font-semibold">BO Expected Date</div>', 'wcvm'),
            'back_orders' => __('Cust BOs', 'wcvm'),
        );
    }
    public function get_new_orders_columns_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm');
        $columns_array['wcvm_status'] = __('<div class="text-sm font-semibold">Stock Status</div>', 'wcvm');
        $columns_array['_price'] = __('<div class="text-sm font-semibold">Our Price</div>', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('<div class="text-sm font-semibold">Vendor Price</div>', 'wcvm');
        $columns_array['_stock'] = __('<div class="text-sm font-semibold">QTY On Hand</div>', 'wcvm');
        $columns_array['_qty_history_30'] = __('<div class="text-sm font-semibold">30 Days</div>', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('<div class="text-sm font-semibold">On Order</div>', 'wcvm');
//        if ($status == "" || $status == 'draft' || $status == 'auto-draft') {
        $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Order QTY</div>', 'wcvm');
        $columns_array['__delete'] = __('<div class="text-sm font-semibold">Delete</div>', 'wcvm');

        return $columns_array;
    }
        public function get_on_order_columns_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm');
        $columns_array['wcvm_status'] = __('<div class="text-sm font-semibold">Stock Status</div>', 'wcvm');
        $columns_array['_price'] = __('<div class="text-sm font-semibold">Our Price</div>', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('<div class="text-sm font-semibold">Vendor Price</div>', 'wcvm');
        $columns_array['_stock'] = __('<div class="text-sm font-semibold">QTY On Hand</div>', 'wcvm');
        $columns_array['_qty_history_30'] = __('<div class="text-sm font-semibold">30 Days</div>', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('<div class="text-sm font-semibold">On Order</div>', 'wcvm');
        $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Order QTY</div>', 'wcvm');
        $columns_array['__delete'] = __('<div class="text-sm font-semibold">Delete</div>', 'wcvm');        

        return $columns_array;
    }
        public function get_back_order_columns_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm');
        $columns_array['wcvm_status'] = __('<div class="text-sm font-semibold">Stock Status</div>', 'wcvm');
        $columns_array['_price'] = __('<div class="text-sm font-semibold">Our Price</div>', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('<div class="text-sm font-semibold">Vendor Price</div>', 'wcvm');
        $columns_array['_stock'] = __('<div class="text-sm font-semibold">QTY On Hand</div>', 'wcvm');
        $columns_array['_qty_history_30'] = __('<div class="text-sm font-semibold">30 Days</div>', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('<div class="text-sm font-semibold">On Order</div>', 'wcvm');
        $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">BO QTY</div>', 'wcvm');

        return $columns_array;
    }
        public function get_completed_orders_column_list() {
        $columns_array = [];
        if (array_key_exists('status', $_REQUEST)) {
            $status = $_REQUEST['status'];
        }
        $columns_array['_sku'] = __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm');
        $columns_array['wcvm_status'] = __('<div class="text-sm font-semibold">Stock Status</div>', 'wcvm');
        $columns_array['_price'] = __('<div class="text-sm font-semibold">Our Price</div>', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('<div class="text-sm font-semibold">Vendor Price</div>', 'wcvm');
        $columns_array['_stock'] = __('<div class="text-sm font-semibold">QTY On Hand</div>', 'wcvm');
        $columns_array['_qty_history_30'] = __('<div class="text-sm font-semibold">30 Days</div>', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('<div class="text-sm font-semibold">On Order</div>', 'wcvm');
        $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Received QTY</div>', 'wcvm');
        if ($status == 'return_closed') {
            $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Returned Closed QTY</div>', 'wcvm');
        }
        return $columns_array;
    }
        public function get_cancelled_orders_column_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm');
        $columns_array['wcvm_status'] = __('<div class="text-sm font-semibold">Stock Status</div>', 'wcvm');
        $columns_array['_price'] = __('<div class="text-sm font-semibold">Our Price</div>', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('<div class="text-sm font-semibold">Vendor Price</div>', 'wcvm');
        $columns_array['_stock'] = __('<div class="text-sm font-semibold">QTY On Hand</div>', 'wcvm');
        $columns_array['_qty_history_30'] = __('<div class="text-sm font-semibold">30 Days</div>', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('<div class="text-sm font-semibold">On Order</div>', 'wcvm');
        $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Cancelled QTY</div>', 'wcvm');

        return $columns_array;
    }
        public function get_returned_orders_column_list() {
        $columns_array = [];
        $columns_array['_sku'] = __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm');
        $columns_array['wcvm_status'] = __('<div class="text-sm font-semibold">Stock Status</div>', 'wcvm');
        $columns_array['_price'] = __('<div class="text-sm font-semibold">Our Price</div>', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('<div class="text-sm font-semibold">Vendor Price</div>', 'wcvm');
        $columns_array['_stock'] = __('<div class="text-sm font-semibold">QTY On Hand</div>', 'wcvm');
        $columns_array['_qty_history_30'] = __('<div class="text-sm font-semibold">30 Days</div>', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('<div class="text-sm font-semibold">On Order</div>', 'wcvm');
        $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Returned QTY</div>', 'wcvm');
        $columns_array['__delete'] = __('<div class="text-sm font-semibold">Mark Close</div>', 'wcvm');        

        return $columns_array;
    }
    public function get_columns_vendors_list() {
        $status = "";
        if (array_key_exists('status', $_REQUEST)) {
            $status = $_REQUEST['status'];
        }
        $columns_array = [];
//        $columns_array['wcvm_rare'] = __('Rare', 'wcvm');
        $columns_array['_sku'] = __('<div class="text-sm font-semibold">CC SKU</div>', 'wcvm');
        $columns_array['wcvm_status'] = __('<div class="text-sm font-semibold">Stock Status</div>', 'wcvm');
        $columns_array['_price'] = __('<div class="text-sm font-semibold">Our Price</div>', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('<div class="text-sm font-semibold">Vendor SKU</div>', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('<div class="text-sm font-semibold">Vendor Price</div>', 'wcvm');
        $columns_array['_stock'] = __('<div class="text-sm font-semibold">QTY On Hand</div>', 'wcvm');
        $columns_array['_qty_history_30'] = __('<div class="text-sm font-semibold">30 Days</div>', 'wcvm');
//        $columns_array['wcvm_threshold_low'] = __('Low Thresh', 'wcvm');
//        $columns_array['wcvm_threshold_reorder'] = __('Reorder Thresh', 'wcvm');
//        $columns_array['wcvm_reorder_qty'] = __('Reorder QTY', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('<div class="text-sm font-semibold">On Order</div>', 'wcvm');
//        $columns_array['_wcvm_on_vendor_bo'] = __('On Vendor BO', 'wcvm');
        if ($status == "" || $status == 'draft' || $status == 'auto-draft') {
            $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Order QTY</div>', 'wcvm');
        } else if ($status == 'publish') {
            $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Received QTY</div>', 'wcvm');
        } else if ($status == 'back-order') {
            $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">BO QTY</div>', 'wcvm');
        } else if ($status == 'private') {
            $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Cancel</div>led QTY', 'wcvm');
        } else if ($status == 'returned') {
            $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Returned QTY</div>', 'wcvm');
            $columns_array['__delete'] = __('<div class="text-sm font-semibold">Mark Close</div>', 'wcvm');
        } else if ($status == 'return_closed') {
            $columns_array['__order_qty'] = __('<div class="text-sm font-semibold">Returned Closed QTY</div>', 'wcvm');
        }

        if (!array_key_exists('__delete', $columns_array)) {
            $columns_array['__delete'] = __('<div class="text-sm font-semibold">Delete</div>', 'wcvm');
        }


        return $columns_array;
    }

}

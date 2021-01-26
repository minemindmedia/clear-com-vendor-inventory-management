<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'Wcvim_Receive_Inventory_List_Table.php';

class Wcvim_Receive_Back_Order_List_Table extends Wcvim_Receive_Inventory_List_Table
{
    public function __construct($args = array())
    {
        $args = $args + array(
            'plural' => 'wcvm-receive-back-orders',
            'singular' => 'wcvm-receive-back-order',
        );
        parent::__construct($args);
    }

    public function get_columns()
    {
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

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'product_quantity_received':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                echo '<span data-quantity="' . esc_attr($data[$column_name]). '">' . esc_html($data[$column_name]) . '</span>';
                break;
            case '__product_quantity_received':
            case '__product_quantity_back_order':
            case '__product_quantity_canceled':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                $data = $data['product_quantity'] - $data['product_quantity_received'];
                if ($data) {
                    echo '<input type="text" style="text-align: right;width: 50px" data-role="' . esc_attr($column_name) . '" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="' . ($column_name == '__product_quantity_received' ? esc_attr($data) : '') . '">';
                }
                break;
            case 'product_expected_date_back_order':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                $data = $data['product_quantity'] - $data['product_quantity_received'];
                if ($data) {
                    $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                    $data = isset($data[$column_name]) && $data[$column_name] ? date('Y-m-d', $data[$column_name]) : '';
                    echo '<input type="text" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="' . esc_attr($data) . '">';
                }
                break;
            default:
                parent::column_default($item, $column_name);
        }
    }
}

<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'Wcvim_Vendors_List_Table.php';

class Wcvim_Receive_Inventory_List_Table extends Wcvim_Vendors_List_Table
{
    public function __construct($args = array())
    {
        $args = $args + array(
            'plural' => 'wcvm-receive-inventory',
            'singular' => 'wcvm-receive-inventory',
        );
        parent::__construct($args);
    }

    public function get_columns()
    {
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
            'back_orders' => __('Cust BOs', 'wcvm'),
        );
    }

    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case '_sku':
                $rand_id = $item->ID . rand(0, 99) . rand(99, 1000);
                $categories = get_the_terms($item->ID, 'product_tag');
                $tags = get_the_terms($item->ID, 'product_cat');
                $string = "";
                for ($i = 0; $i < count($categories); $i++) {
                    if ($string != "") {
                        $string.=" , ";
                    }
                    $string .=get_edit_term_link($categories[$i]->term_id) . "|" . $categories[$i]->name;
                }

                foreach ($tags as $tag) {
                    if ($string != "") {
                        $string.=" , ";
                    }
                    $string .=get_edit_term_link($tag->term_id) . "|" . $tag->name;
                }
                $product_meta = get_post_meta($item->ID);
                $product_image = wp_get_attachment_url($product_meta['_thumbnail_id'][0], 'shop_thumbnail');
                $orderVendorData = get_post_meta($item->order_id, 'wcvmgo_' . $item->ID . '_onorder');
                $vendorSKU = "";
                if ($orderVendorData) {
                    $productVendorData = get_post_meta($item->ID, 'wcvm_' . $orderVendorData[0]['vendor'] . '_sku');
                    if ($productVendorData) {
                        $vendorSKU = $productVendorData[0];
                    }
                }
                $rare = "&nbsp;";
                if($item->wcvm_rare)
                {
                    $rare = "&#10004;";
                }   
                echo '<input type="hidden" id="' . $rand_id . '_sku" value = "' . $item->$column_name . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_title" value = "' . $item->post_title . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_tags" value = "' . $string . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_product_image" value = "' . $product_image . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_vendor_sku" value = "' . $vendorSKU . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_qty_on_hand" value = "' . $item->$column_name . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_low_thresh" value = "' . $product_meta['wcvm_threshold_low'][0] . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_reorder_thresh" value = "' . $product_meta['wcvm_threshold_reorder'][0] . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_reorder_qty" value = "' . $product_meta['wcvm_reorder_qty'][0] . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_sale_30" value = "' . $item->_qty_history_30 . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_sale_60" value = "' . $item->_qty_history_60 . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_sale_90" value = "' . $item->_qty_history_90 . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_sale_365" value = "' . $item->_qty_history_365 . '"/>';
                echo '<input type="hidden" id="' . $rand_id . '_rare" value = "' . $rare . '"/>';
                echo '<a href="javascript:void(0)" class="quick_edit" onclick="load_quick_edit(&apos;' . $rand_id . '&apos;,&apos;' . $item->ID . '&apos;)">' . esc_html($item->$column_name) . '</a>';
                break;
            case 'product_cat':
            case 'product_tag':
                $terms = wp_get_post_terms($item->ID, $column_name, array(
                    'fields' => 'names',
                    'orderby' => 'name',
                    'order' => 'ASC',
                ));
                $terms = array_map('esc_html', $terms);
                echo implode(', ', $terms);
                break;
            case 'product_quantity':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                echo '<span data-quantity="' . esc_attr($data[$column_name]). '">' . esc_html($data[$column_name]) . '</span>';
                break;
            case 'product_expected_date':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                if ($data[$column_name]) {
                    echo date(get_option('date_format'), $data[$column_name]);
                }
                break;
            case 'product_quantity_received':
            case 'product_quantity_back_order':
            case 'product_quantity_returned':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                $data = isset($data[$column_name]) ? $data[$column_name] : '';
                echo '<input type="text" style="text-align: right;width: 50px" data-role="' . esc_attr($column_name) . '" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="' . esc_attr($data) . '">';
                break;
            case 'product_quantity_canceled':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                $data = isset($data[$column_name]) ? $data[$column_name] : '';
                echo '<input type="text" style="text-align: right;width: 50px" data-role="' . esc_attr($column_name) . '" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="' . esc_attr($data) . '">';
                break;
            case 'product_expected_date_back_order':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                $data = isset($data[$column_name]) && $data[$column_name] ? date('Y-m-d', $data[$column_name]) : '';
                echo '<input type="text" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="' . esc_attr($data) . '">';
                break;
            case 'back_orders':
                /** @var wpdb $wpdb */
                global $wpdb;
                $orders = $wpdb->get_results($wpdb->prepare("SELECT
                        orders.ID, backorder.meta_value backorder
                    FROM
                        {$wpdb->prefix}posts orders
                    JOIN
                        {$wpdb->prefix}woocommerce_order_items items
                    ON
                        items.order_id = orders.ID AND items.order_item_type = 'line_item'
                    JOIN
                        {$wpdb->prefix}woocommerce_order_itemmeta backorder
                    ON
                        backorder.order_item_id = items.order_item_id AND backorder.meta_key = 'Backordered'
                    JOIN
                        {$wpdb->prefix}woocommerce_order_itemmeta product
                    ON 
                        product.order_item_id = items.order_item_id AND product.meta_key = '_product_id' AND product.meta_value = %s
                    WHERE
                        orders.post_type = 'shop_order' AND orders.post_status = 'wc-processing'
                    ORDER BY
                        orders.ID DESC
                    ", $item->ID), ARRAY_A
                );
                foreach ($orders as $data) {
                    echo '<a href="' . get_edit_post_link($data['ID']) . '">#' . $data['ID'] . '</a> <small>' . esc_html__('qty', 'wcvm') . ': ' . $data['backorder'] . '</small><br>';
                }
                break;
            default:
                parent::column_default($item, $column_name);
        }
    }
}

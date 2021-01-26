<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class Wcvim_Vendors_List_Table extends WP_List_Table
{

    public function __construct($args = array()) {
        $args = $args + array(
            'plural' => 'wcvm-orders',
            'singular' => 'wcvm-order',
        );
        parent::__construct($args);
    }

    public function get_columns() {
        $status = $_REQUEST['status'];
        $columns_array = [];
        $columns_array['wcvm_rare'] = __('Rare', 'wcvm');
        $columns_array['_sku'] = __('CC SKU', 'wcvm');
        $columns_array['wcvm_status'] = __('Stock Status', 'wcvm');
        $columns_array['_price'] = __('Our Price', 'wcvm');
        $columns_array['__wcvm_vendor_sku'] = __('Vendor SKU', 'wcvm');
        $columns_array['__wcvm_vendor_price_last'] = __('Vendor Price', 'wcvm');
        $columns_array['_stock'] = __('QTY On Hand', 'wcvm');
        $columns_array['_qty_history_30'] = __('30 Days', 'wcvm');
        $columns_array['wcvm_threshold_low'] = __('Low Thresh', 'wcvm');
        $columns_array['wcvm_threshold_reorder'] = __('Reorder Thresh', 'wcvm');
        $columns_array['wcvm_reorder_qty'] = __('Reorder QTY', 'wcvm');
        $columns_array['_wcvm_on_order'] = __('On Order', 'wcvm');
        $columns_array['_wcvm_on_vendor_bo'] = __('On Vendor BO', 'wcvm');
        if ($status == "" || $status == 'draft' || $status == 'auto-draft') {
            $columns_array['__order_qty'] = __('Order QTY', 'wcvm');
        } else if ($status == 'publish') {
            $columns_array['__order_qty'] = __('Received QTY', 'wcvm');
        } else if ($status == 'pending') {
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
        /* return array(
          'wcvm_rare' => __('Rare', 'wcvm'),
          '_sku' => __('CC SKU', 'wcvm'),
          //'post_title' => __('Name', 'wcvm'),
          'wcvm_status' => __('Stock Status', 'wcvm'),
          '_price' => __('Our Price', 'wcvm'),
          // 'wcvm_primary' => __('Pref Vendor', 'wcvm'),
          // '__wcvm_vendor_code' => __('Pref Vendor Code', 'wcvm'),
          '__wcvm_vendor_sku' => __('Vendor SKU', 'wcvm'),
          '__wcvm_vendor_price_last' => __('Vendor Price', 'wcvm'),
          '_stock' => __('QTY On Hand', 'wcvm'),
          '_qty_history_30' => __('30 Days', 'wcvm'),
          'wcvm_threshold_low' => __('Low Thresh', 'wcvm'),
          'wcvm_threshold_reorder' => __('Reorder Thresh', 'wcvm'),
          'wcvm_reorder_qty' => __('Reorder QTY', 'wcvm'),
          '_wcvm_on_order' => __('On Order', 'wcvm'),
          '_wcvm_on_vendor_bo' => __('On Vendor BO', 'wcvm'),
          '__order_qty' => __('Order QTY', 'wcvm'),
          // '__expected_date' => __('Expected Date', 'wcvm'),
          '__delete' => __('Delete', 'wcvm'),
          ); */
    }

    public function prepare_items() {

        global $wpdb;
        $wcvmgo_manual = get_post_meta($this->_args['order']->ID, "wcvmgo");
        $this->_args['order']->wcvmgo = $wcvmgo_manual[0];

        if ($this->_args['order']->wcvmgo) {
            $query = new WP_Query();
            $this->items = $query->query(array(
                'post_type' => 'product',
                'post__in' => $this->_args['order']->wcvmgo,
                'orderby' => 'post_title',
                'order' => 'ASC',
                'nopaging' => true,
                'suppress_filters' => true,
            ));
            $orderDetails = $this->_args['order'];

            $qty30 = array();
            $qty60 = array();
            $qty90 = array();
            $qty365 = array();
            $days_30 = date("Y-m-d", strtotime('-30 days'));
            $days_60 = date("Y-m-d", strtotime('-60 days'));
            $days_90 = date("Y-m-d", strtotime('-90 days'));
            $days_365 = date("Y-m-d", strtotime('-365 days'));
            foreach ($this->items as &$item) {
                $item->order_id = $orderDetails->ID;
            }
            foreach ($this->items as &$item) {
                $item->wcvm_primary = $this->_args['order']->post_parent;

                foreach ($wpdb->get_results("
                SELECT
                    items.meta_value _product_id,
                    SUM(quantity.meta_value) quantity
                FROM
                    {$wpdb->prefix}posts orders
                JOIN
                    {$wpdb->prefix}woocommerce_order_items carts
                ON
                    carts.order_id = orders.id AND carts.order_item_type = 'line_item'
                JOIN
                    {$wpdb->prefix}woocommerce_order_itemmeta items
                ON
                    items.order_item_id = carts.order_item_id AND items.meta_key = '_product_id' AND items.meta_value IN ('" . $item->ID . "')
                JOIN
                    {$wpdb->prefix}woocommerce_order_itemmeta quantity
                ON
                    quantity.order_item_id = items.order_item_id AND quantity.meta_key = '_qty'
                WHERE
                    orders.post_type = 'shop_order' AND orders.post_date > '" . $days_30 . "'
                GROUP BY _product_id
            ") as $row) {
                    $qty30[$row->_product_id] = $row->quantity;
                }
                if (isset($qty30[$item->ID])) {
                    $item->_qty_history_30 = $qty30[$item->ID];
                } else {
                    $item->_qty_history_30 = 0;
                }
            }
            unset($item);
        } else {
            $this->items = array();
        }

        $this->set_pagination_args(array(
            'total_items' => count($this->items),
            'per_page' => count($this->items),
        ));
    }

    protected function display_tablenav($which) {
        if ('top' === $which) {
            wp_nonce_field('bulk-' . $this->_args['plural']);
        }
    }

    protected function column_default($item, $column_name) {
        $status_filter = "";
        if (isset($_REQUEST['status']) && $_REQUEST['status'] != "") {
            $status_filter = $_REQUEST['status'];
        }
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
                if ($item->wcvm_rare) {
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
            case 'wcvm_rare':
                echo '<input type="hidden" data-replace-name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="">';
                //echo '<input type="checkbox" data-replace-name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="1"' . ($item->wcvm_rare ? ' checked="checked"' : '') . '>';
                if ($item->wcvm_rare) {
                    echo "&#10004;";
                }
                break;

            case '__delete':
                if ($status_filter == "returned") {
                    $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID . "_returned", true);
                    echo '<input type="hidden" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="">';
                    if ($data) {
                        echo '<input class="deleting" id="' . esc_attr($item->ID) . '" type="checkbox" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="1">';
                    }
                } else if ($status_filter == "return_closed") {
                    
                } else {
                    echo '<input type="hidden" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="">';
                    echo '<input class="deleting" id="' . esc_attr($item->ID) . '" type="checkbox" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="1">';
                }
                break;
            case 'wcvm_primary':
                echo '<input type="hidden" name="wcvm_primary[' . esc_attr($item->ID) . ']" value="' . esc_attr($item->$column_name) . '">';
                foreach ($this->_args['vendors'] as $vendor) {
                    if (!in_array($vendor->ID, $item->wcvm)) {
                        continue;
                    }
                    if ($vendor->ID != $item->$column_name) {
                        continue;
                    }
                    echo esc_html($vendor->title_short);
                }
                echo '</select>';
                break;
            case '_qty_history_30':
                echo number_format($item->_qty_history_30, 0);
                break;
            case '_stock':
                echo number_format($item->_stock, 0);
                break;
            case '_wcvm_on_order':
                global $wpdb;
                $results = $wpdb->get_results("SELECT SUM(`meta_value`) as on_order FROM `wp_postmeta` WHERE `meta_key` = 'wcvmgo_" . $item->ID . "_qty'", OBJECT);
                if ($results) {
                    if ($results[0]->on_order > 0) {
                        echo '<a href="#TB_inline?width=600&height=250&inlineId=my-content-id" onclick="load_on_order_details(&apos;' . $item->ID . '&apos;)" class="thickbox" title="Currently On Order">' . $results[0]->on_order . '</a>';
                    }
                }
                break;
            case '__order_qty':
                if ($status_filter == "pending") {
                    $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID . "_qty", true);
                } else if ($status_filter == "publish") {
                    $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID . "_received", true);
                } else if ($status_filter == "private") {
                    $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID . "_cancelled", true);
                } else if ($status_filter == "returned") {
                    $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID . "_returned", true);
                } else if ($status_filter == "return_closed") {
                    $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID . "_return_closed", true);
                    $string = $data;
                } else {
                    $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                    $string = '<input type="text" style="text-align: right;width: 50px" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="' . esc_attr($data['product_quantity']) . '">';
                }
                if ($string == "") {
                    if ($data) {
                        echo '<input readonly type="text" style="text-align: right;width: 50px" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="' . esc_attr($data) . '">';
                    } else {
                        echo 0;
                    }
                } else {
                    echo $string;
                }
                break;
            case '__expected_date':
                $data = get_post_meta($this->_args['order']->ID, 'wcvmgo_' . $item->ID, true);
                $stamp = $data['product_expected_date'];
                if ($stamp) {
                    $stamp = date('Y-m-d', $stamp);
                } else {
                    $stamp = '';
                }
                echo '<input type="text" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" name="' . esc_attr($column_name) . '[' . esc_attr($item->ID) . ']" value="' . esc_attr($stamp) . '">';
                break;
            default:
                parent::column_default($item, $column_name);
        }
    }
}

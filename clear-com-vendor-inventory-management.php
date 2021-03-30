<?php
/* Plugin Name: Woocommerce Vendor Inventory Managemnet
 * Plugin URI: #
 * Description: Vendor management Updated!.
 * Author:
 * Author URI: #
 * Version: 1.0.1
 */

class WC_Clear_Com_Vendor_Inventory_Management
{
    public function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScript'));
        add_action('admin_menu', array($this, 'wcvimActionAdminMenu'));
        add_action('admin_menu', array($this, 'wcvimSaveAdminMenu'));
        add_action('admin_menu', array($this, 'wcvimPo'));
        // add_action('admin_menu', array($this, 'wcvimReceiveInventory'));
        add_action('admin_menu', array($this, 'wcvimReceiveInventoryRemove'));
        // hide backorder menu
        // add_action('admin_menu', array($this, 'wcvimReceiveBackOrderItems'));
        add_action('admin_menu', array($this, 'wcvimgeneratePo'));

        add_action("wp_ajax_generate_po", array($this, "generate_po"));
        add_action("wp_ajax_updateVendorProductMapping", array($this, "updateVendorProductMapping"));
        add_action("wp_ajax_sync_vendor_product_mapping_table", array($this, "sync_vendor_product_mapping_table"));
        add_action("wp_ajax_sync_vendor_po_lookup_table", array($this, "sync_vendor_po_lookup_table"));
        add_action("wp_ajax_update_vendor_po_lookup", array($this, "update_vendor_po_lookup"));
        add_action('plugins_loaded', array($this, 'wcvmcvoActionPluginsLoaded'));

        add_filter('woocommerce_product_data_tabs', array($this, 'wcvmcpFilterWcProductDataTabs'));
        add_action('woocommerce_product_data_panels', array($this, 'wcvmcpActionWcProductDataPanels'));
        add_action('save_post_product', array($this, 'wcvmcpActionSavePostProduct'));
    }

    public function wcvmcpFilterWcProductDataTabs($data)
    {
        $data['wcvm-product'] = array(
            'label' => __('Vendor Management', 'wcvm'),
            'target' => 'wcvmcpAdminProduct',
        );
        return $data;
    }

    public function wcvmcpActionWcProductDataPanels()
    {
        $product = get_post();
        $query = new WP_Query();
        $vendors = $query->query(array(
            'post_type' => 'wcvm-vendor',
            'suppress_filters' => true,
            'orderby' => 'post_title',
            'order' => 'asc',
            'nopaging' => true,
        ));
        include plugin_dir_path(__FILE__) . '/templates/admin-product.php';
    }

    public function wcvmcpActionSavePostProduct($productId)
    {
        if (empty($_POST['ID'])) {
            return false;
        }
        if ($_POST['ID'] != $productId) {
            return false;
        }
        if (!isset($_POST['wcvm'])) {
            return false;
        }

        $query = new WP_Query();
        $vendorIds = $query->query(array(
            'post_type' => 'wcvm-vendor',
            'orderby' => 'post_title',
            'order' => 'ASC',
            'suppress_filters' => true,
            'nopaging' => true,
            'fields' => 'ids'
        ));

        $_POST['wcvm'] = array_filter($_POST['wcvm']);
        $removeIds = get_post_meta($productId, 'wcvm', true);
        $vendorIds = array_intersect($vendorIds, $_POST['wcvm']);
        $removeIds = array_diff($removeIds, $vendorIds);
        $_POST['wcvm_primary'] = in_array($_POST['wcvm_primary'], $vendorIds) ? $_POST['wcvm_primary'] : '';
        if ($_POST['wcvm_primary']) {
            array_unshift($vendorIds, $_POST['wcvm_primary']);
            $vendorIds = array_unique($vendorIds);
        }
        if ($vendorIds) {
            update_post_meta($productId, 'wcvm', $vendorIds);
        } else {
            delete_post_meta($productId, 'wcvm');
        }
        update_post_meta($productId, 'wcvm_rare', $_POST['wcvm_rare']);
        update_post_meta($productId, 'wcvm_discontinued', $_POST['wcvm_discontinued']);
        if ($_POST['wcvm_discontinued']) {
            update_post_meta($productId, 'wcvm_discontinued_date', date("m-d-Y"));
        } else {
            delete_post_meta($productId, 'wcvm_discontinued_date');
        }
        //    update_post_meta($productId, 'wcvm_threshold_low', $_POST['wcvm_threshold_low']);
        //    update_post_meta($productId, 'wcvm_threshold_reorder', $_POST['wcvm_threshold_reorder']);
        //    update_post_meta($productId, 'wcvm_reorder_qty', $_POST['wcvm_reorder_qty']);
        update_post_meta($productId, 'wcvm_primary', $_POST['wcvm_primary']);
        foreach ($vendorIds as $vendorId) {
            update_post_meta($productId, 'wcvm_' . $vendorId . '_sku', $_POST['wcvm_' . $vendorId . '_sku']);
            update_post_meta($productId, 'wcvm_' . $vendorId . '_link', $_POST['wcvm_' . $vendorId . '_link']);
            update_post_meta($productId, 'wcvm_' . $vendorId . '_price_last', str_replace("$", "", $_POST['wcvm_' . $vendorId . '_price_last']));
            update_post_meta($productId, 'wcvm_' . $vendorId . '_freight_in', str_replace("$", "", $_POST['wcvm_' . $vendorId . '_freight_in']));
            update_post_meta($productId, 'wcvm_' . $vendorId . '_price_bulk', $_POST['wcvm_' . $vendorId . '_price_bulk']);
            update_post_meta($productId, 'wcvm_' . $vendorId . '_price_notes', $_POST['wcvm_' . $vendorId . '_price_notes']);
        }
        foreach ($removeIds as $vendorId) {
            delete_post_meta($productId, 'wcvm_' . $vendorId . '_sku');
            delete_post_meta($productId, 'wcvm_' . $vendorId . '_link');
            delete_post_meta($productId, 'wcvm_' . $vendorId . '_price_last');
            delete_post_meta($productId, 'wcvm_' . $vendorId . '_freight_in');
            delete_post_meta($productId, 'wcvm_' . $vendorId . '_price_bulk');
            delete_post_meta($productId, 'wcvm_' . $vendorId . '_price_notes');
        }

        return true;
    }

    public function create_vendor_product_mapping()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vendor_product_mapping';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `vpm_id` int(11) NOT NULL AUTO_INCREMENT,
            `post_id` int(11) NOT NULL,
            `vendor_id` int(11) DEFAULT NULL,
            `vendor_sku` varchar(15) DEFAULT NULL,
            `vendor_price` decimal(10,2) DEFAULT NULL,
            PRIMARY KEY (`vpm_id`)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_vendor_po_lookup()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vendor_po_lookup';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE `wp_vendor_po_lookup` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) DEFAULT NULL,
        `product_title` text,
        `sku` text,
        `regular_price` decimal(10,2) DEFAULT NULL,
        `stock_status` text,
        `stock` int(11) DEFAULT NULL,
        `threshold_low` double DEFAULT NULL,
        `threshold_reorder` int(11) DEFAULT NULL,
        `reorder_qty` int(11) DEFAULT NULL,
        `rare` int(11) DEFAULT NULL,
        `category` text,
        `vendor_id` text,
        `vendor_name` text,
        `vendor_sku` text,
        `vendor_link` text,
        `vendor_price_bulk` int(11) DEFAULT NULL,
        `vendor_price_notes` text,
        `vendor_price` text,
        `primary_vendor_id` int(11) DEFAULT NULL,
        `primary_vendor_name` text,
        `on_order` int(11) DEFAULT NULL,
        `sale_30_days` int(11) DEFAULT NULL,
        `stock_30_days_sale_percent` decimal(10,2) DEFAULT NULL,
        `order_qty` int(11) DEFAULT NULL,
        `on_vendor_bo` int(11) DEFAULT NULL,
        `new` int(11) DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_vendor_purchase_order()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vendor_purchase_orders';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `vendor_id` int(11) DEFAULT NULL,
        `order_id` int(11) DEFAULT NULL,
        `post_status` varchar(200) DEFAULT NULL,
        `post_old_status` varchar(20) DEFAULT NULL,
        `order_date` datetime DEFAULT NULL,
        `po_expected_date` int(11) DEFAULT NULL,
        `expected_date` int(11) DEFAULT NULL,
        `set_date` int(11) DEFAULT NULL,
        `created_date` datetime DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `updated_date` datetime DEFAULT NULL,
        `updated_by` int(11) DEFAULT NULL,
        PRIMARY KEY (`id`)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_vendor_purchase_order_items()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vendor_purchase_orders_items';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        `id`  int(11) NOT NULL AUTO_INCREMENT,
        `product_id` int(11) NOT NULL,
        `vendor_order_idFk` int(11) NOT NULL,
        `product_title` text NOT NULL,
        `product_sku` text NOT NULL,
        `product_price` decimal(10,2) NOT NULL,
        `product_ordered_quantity` int(11) NOT NULL,
        `product_category` text DEFAULT NULL,
        `product_expected_date` int(11) DEFAULT NULL,
        `product_rare` tinyint(1) DEFAULT NULL,
        `product_threshold_low` int(11) DEFAULT NULL,
        `product_threshold_reorder` int(11) DEFAULT NULL,
        `product_reorder_qty` int(11) DEFAULT NULL,
        `vendor_sku` text DEFAULT NULL,
        `vendor_name` text DEFAULT NULL,
        `vendor_link` text DEFAULT NULL,
        `vendor_price_last` decimal(10,2) DEFAULT NULL,
        `vendor_price_bulk` decimal(10,2) DEFAULT NULL,
        `vendor_price_notes` text DEFAULT NULL,
        `product_quantity_received` int(11) DEFAULT NULL,
        `product_quantity_back_order` int(11) DEFAULT NULL,
        `product_quantity_canceled` int(11) DEFAULT NULL,
        `product_quantity_returned` int(11) DEFAULT NULL,
        `product_quantity_return_closed` int(11) DEFAULT NULL,
        `product_expected_date_back_order` int(11) DEFAULT NULL,
        `on_order_quantity` int(11) NOT NULL,
        `sale_30_days` int(11) NOT NULL,
        `product_stock` int(11) NOT NULL,
        `product_quantity_returned_note` text DEFAULT NULL,
        `product_quantity_canceled_note` text DEFAULT NULL,
        `created_date` datetime DEFAULT NULL,
        `created_by` int(11) DEFAULT NULL,
        `updated_date` datetime DEFAULT NULL,
        `updated_by` int(11) DEFAULT NULL,
        PRIMARY KEY id (`id`)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function activate_plugin()
    {
        flush_rewrite_rules();
        $this->create_vendor_product_mapping();
        $this->create_vendor_po_lookup();
        $this->create_vendor_purchase_order();
        $this->create_vendor_purchase_order_items();
    }

    public function enqueueScript()
    {
?>
        <style>
            .product-items-table th {
                background-color: white !important;
                font-size: 10px !important;
                padding: 14px !important;
            }

            .wp-list-table.wcvm-orders th {
                background-color: white !important;
                font-size: 10px !important;
                padding: 14px !important;
            }

            .wp-list-table.wcvm-orders .manage-column {
                font-size: 10px !important;
            }

            .widefat {
                background-color: #f9f9f9 !important;
            }

            .widefat {
                vertical-align: middle !important;
                word-wrap: break-word !important;
            }

            .wp-list-table.widefat.fixed {
                table-layout: fixed !important;
            }
        </style>
        <?php
        wp_enqueue_style('jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('generate-po-script', plugin_dir_url(__FILE__) . 'assets/vendors.js', array('jquery'), '1.0.0', true);
        wp_localize_script('generate-po-script', 'generate_po_ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }

    public function wcvimActionAdminMenu()
    {
        add_menu_page(__('Vendors Management', 'wcvim'), __('Vendors Management', 'wcvim'), 'manage_options', 'vendor-management', array($this, 'vendorManagemetMenuPage'));
    }
    public function wcvimReceiveInventoryRemove()
    {
        add_menu_page(__('Receive Inventory', 'wcvim'), __('Receive Inventory', 'wcvim'), 'manage_options', 'wcvm-ri', array($this, 'wcvimReceiveInventoryPage'));
        remove_menu_page('wcvm-ri');
    }

    public function wcvimgeneratePo()
    {
        add_submenu_page('vendor-management', __('Generate Purchase Order', 'wcvim'), __('Generate Purchase Order', 'wcvim'), 'manage_options', 'generate-purchase-order', array($this, 'generatePurchaseOrder'), 1);
    }

    public function wcvimPo()
    {
        add_submenu_page('vendor-management', __('View/Edit Purchase Order', 'wcvim'), __('View/Edit Purchase Order', 'wcvim'), 'manage_options', 'wcvm-epo', array($this, 'wcvimPurchaseOrderPage'));
    }

    public function wcvimReceiveInventory()
    {
        add_submenu_page('vendor-management', __('Receive Inventory', 'wcvim'), __('Receive Inventory', 'wcvim'), 'manage_options', 'wcvm-ri', array($this, 'wcvimReceiveInventoryPage'));
    }

    public function wcvimReceiveBackOrderItems()
    {
        add_submenu_page('vendor-management', __('Receive Back Order Items', 'wcvim'), __('Receive Back Order Items', 'wcvim'), 'manage_options', 'wcvm-rboi', array($this, 'wcvimReceiveBackOrderItemsPage'));
    }

    public function wcvmcvoActionPluginsLoaded()
    {
        require_once plugin_dir_path(__FILE__) . 'includes/vendor-management-cols.php';
    }

    public function wcvimPurchaseOrderPage()
    {
        global $wpdb;
        $vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_orders';
        $vendor_purchase_order_items_table = $wpdb->prefix . 'vendor_purchase_orders_items';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['unarchive'])) {

            $update_data['post_status'] = 'new-order';
            $update_data['updated_date'] = date('Y/m/d H:i:s a');
            $update_data['updated_by'] = get_current_user_id();
            $where_data['order_id'] = $_POST['ID'];
            $updated = $wpdb->update($vendor_purchase_order_table, $update_data, $where_data);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['archive']) && empty($_POST['print']) && empty($_POST['action'])) {
            $order = get_post($_POST['ID']);
            $update_data['post_status'] = 'trash';
            $update_data['updated_date'] = date('Y/m/d H:i:s a');
            $update_data['updated_by'] = get_current_user_id();
            $where_data['order_id'] = $_POST['ID'];
            $updated = $wpdb->update($vendor_purchase_order_table, $update_data, $where_data);
            $order->post_status = 'trash';
            wp_update_post($order);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['print'])) {
            //            header('Location:' . site_url('/wp-content/plugins/clear-com-vendor-inventory-management/templates/print-template-page.php?po=' . $_POST['ID']));
            header('Location:' . plugin_dir_url(__FILE__) . 'templates/print-template-page.php?po=' . $_POST['ID'] . '&status=' . $_POST['status']);
            exit();
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['delete'])) {
            $wpdb->delete($vendor_purchase_order_table, array('order_id' => $_POST['ID']));
            wp_delete_post($_POST['ID']);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['delete-all'])) {
            $query = new WP_Query();
            foreach ($query->query(array(
                'post_type' => 'wcvm-order',
                'suppress_filters' => true,
                'post_status' => 'trash',
                'fields' => 'ids',
            )) as $id) {
                wp_delete_post($id);
            }
            $wpdb->delete($vendor_purchase_order_table, array('post_status' => 'trash'));
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action'])) {

            if ($_POST['action'] == 'new-return') {
                $orderId = $_POST['ID'];
                $valid = true;
                foreach ($_POST['__order_qty'] as $productId => $_) {
                    $poStatus = $_POST['status'];
                    $getPOLineItemDetails = $wpdb->get_results(""
                            . "SELECT * FROM " . $vendor_purchase_order_table . " po "
                            . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
                            . "WHERE order_id = " . $orderId . " AND product_id = " . $productId . " AND post_status LIKE '%" . $poStatus . "%'");
                    if (!$getPOLineItemDetails) {
                        
                    } else {
                        if ($_POST['product_quantity_returned'][$productId] <= $_POST['__order_qty'][$productId]) {
                            $updatePOProductData['product_quantity_returned'] = $getPOLineItemDetails[0]->product_quantity_returned + $_POST['product_quantity_returned'][$productId];
                            $updatePOProductData['product_quantity_received'] = $getPOLineItemDetails[0]->product_quantity_received - $_POST['product_quantity_returned'][$productId];
                        }
                        else{
                            $valid = false;
                        }
                        if ($valid) {
                            $updatePOProductData['updated_date'] = date('Y/m/d H:i:s a');
                            $updatePOProductData['updated_by'] = get_current_user_id();
                            $wherePOProductData['product_id'] = $productId;
                            $wherePOProductData['vendor_order_idFk'] = $getPOLineItemDetails[0]->vendor_order_idFk;
                            //                        $wherePOProductData['order_id'] = $orderId;
                            $updated = $wpdb->update($vendor_purchase_order_items_table, $updatePOProductData, $wherePOProductData);
                            $order_product_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
                                    . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
                                    . " WHERE po.order_id = " . $orderId;

                            $order_product_details = $wpdb->get_results($order_product_sql);
                            $status = "";
                            foreach ($order_product_details as $order) {
                                if ($order->product_quantity_received) {
                                    $status .= "completed";
                                }
                                if ($order->product_quantity_back_order) {
                                    if ($status != "") {
                                        $status .= "|";
                                    }
                                    $status .= "back-order";
                                }
                                if ($order->product_quantity_canceled) {
                                    if ($status != "") {
                                        $status .= "|";
                                    }
                                    $status .= "canceled";
                                }
                                if ($order->product_quantity_returned) {
                                    if ($status != "") {
                                        $status .= "|";
                                    }
                                    $status .= "returned";
                                }
                                if ($order->product_quantity_return_closed) {
                                    if ($status != "") {
                                        $status .= "|";
                                    }
                                    $status .= "return_closed";
                                }
                            }

                            global $wpdb;
                            $postStatus = implode('|', array_unique(explode('|', $status)));

                            $update_po_data['post_status'] = $postStatus;
                            $update_po_data['updated_date'] = date('Y/m/d H:i:s a');
                            $update_po_data['updated_by'] = get_current_user_id();
                            $where_po['order_id'] = $orderId;
                            $updated = $wpdb->update($vendor_purchase_order_table, $update_po_data, $where_po);
                            wp_redirect(site_url('/wp-admin/admin.php?page=wcvm-epo&status=returned'));                                                        
                        }
                    }
                }
            } else {
            $order = get_post($_POST['ID']);
            $order->post_status = $order->old_status ? $order->old_status : 'on-order';

            if (!empty($_POST['__order_qty'])) {
                $vendorId = get_post_field('post_parent', $_POST['ID'], 'raw');

                if ($_POST['action'] == 'update') {
                    $orderId = $_POST['ID'];
                }

                foreach ($_POST['__order_qty'] as $productId => $_) {
                    $poStatus = $_POST['status'];
                    $getPOLineItemDetails = $wpdb->get_results(""
                        . "SELECT * FROM " . $vendor_purchase_order_table . " po "
                        . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
                        . "WHERE order_id = " . $orderId . " AND product_id = " . $productId . " AND post_status = '" . $poStatus . "'");
                    if (!$getPOLineItemDetails) {
                    } else {
                        $quantityToUpdateinLookup = $_POST['__order_qty'][$productId];
                        if ($getPOLineItemDetails[0]->post_status == 'on-order') {
                            if ($getPOLineItemDetails[0]->product_ordered_quantity != $_POST['__order_qty'][$productId]) {
                                $quantityToUpdateinLookup = $_POST['__order_qty'][$productId] - $getPOLineItemDetails[0]->product_ordered_quantity;
                            }
                        }

                        $updateOnOrderQuery = "UPDATE wp_vendor_po_lookup SET on_order = on_order + " . $quantityToUpdateinLookup . " WHERE product_id = " . $productId . "";

                        $wpdb->query($updateOnOrderQuery);
                        $updatePOProductData['product_ordered_quantity'] = $_POST['__order_qty'][$productId];
                        $updatePOProductData['updated_date'] = date('Y/m/d H:i:s a');
                        $updatePOProductData['updated_by'] = get_current_user_id();
                        $wherePOProductData['product_id'] = $productId;
                        $wherePOProductData['vendor_order_idFk'] = $getPOLineItemDetails[0]->vendor_order_idFk;
                        $updated = $wpdb->update($vendor_purchase_order_items_table, $updatePOProductData, $wherePOProductData);
                    }
                }
                if (!empty($_POST['expected_date'])) {
                    $vpo_UpdateData['po_expected_date'] = strtotime($_POST['expected_date']);
                    $vpo_UpdateData['post_status'] = 'on-order';
                }
                $vpo_UpdateData['updated_date'] = date('Y/m/d H:i:s a');
                $vpo_UpdateData['updated_by'] = get_current_user_id();
                $where_vpo['order_id'] = $orderId;
                $insertedPOId = $wpdb->update($vendor_purchase_order_table, $vpo_UpdateData, $where_vpo);
                if (!empty($_POST['expected_date'])) {
                    $order = get_post($_POST['ID']);
                    $order->post_status = 'on-order';
                    wp_update_post($order);
                    wp_redirect(site_url('/wp-admin/admin.php?page=wcvm-epo&status=' . $order->post_status) . '#order' . $order->ID);
                }
            }
        }
        }
        $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'new-order';
        $show_status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'new-order';
        if ($show_status == "on-order" || $show_status == "new-order") {
            $query_status = "='" . $show_status . "'";
        } else {
            $query_status = "LIKE '%" . $show_status . "%'";
        }
        $status = $show_status;
        $posts_table = $wpdb->prefix . "posts";
        $postmeta_table = $wpdb->prefix . "postmeta";
        $vendor_po_lookup_table = $wpdb->prefix . "vendor_po_lookup";
        $vendor_purchase_order_table = $wpdb->prefix . "vendor_purchase_orders";
        $vendor_purchase_order_items_table = $wpdb->prefix . "vendor_purchase_orders_items";

        //    $posts_table_sql = "SELECT * FROM `" . $posts_table . "` p
        //    JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND meta_key = 'wcvmgo_product_id'
        //    LEFT JOIN " . $wpdb->prefix . "vendor_po_lookup wvpl ON wvpl.product_id = pm.meta_value
        //    WHERE 1=1 AND p.post_status " . $query_status . " AND p.post_type = 'wcvm-order' ORDER BY p.ID DESC";

        $purchase_order_table_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po"
            . " LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk"
            . " WHERE po.post_status " . $query_status . " ORDER BY po.order_id DESC";
        $orders = $wpdb->get_results($purchase_order_table_sql);

        if ($status == 'new-order') {
            require_once plugin_dir_path(__FILE__) . 'templates/new-order-template.php';
        } elseif ($status == 'on-order') {
            require_once plugin_dir_path(__FILE__) . 'templates/on-order-template.php';
        } elseif ($status == 'back-order') {
            require_once plugin_dir_path(__FILE__) . 'templates/back-order-template.php';
        } elseif ($status == 'completed') {
            require_once plugin_dir_path(__FILE__) . 'templates/completed-orders-template.php';
        } elseif ($status == 'canceled') {
            require_once plugin_dir_path(__FILE__) . 'templates/cancelled-orders-template.php';
        } elseif ($status == 'returned') {
            require_once plugin_dir_path(__FILE__) . 'templates/returned-order-template.php';
        } elseif ($status == 'return_closed') {
            require_once plugin_dir_path(__FILE__) . 'templates/return-closed-order-template.php';
        } elseif ($status == 'trash') {
            require_once plugin_dir_path(__FILE__) . 'templates/trash-template.php';
        }
        //        require_once plugin_dir_path(__FILE__) . 'templates/purchase_order_page.php';
    }

    public function wcvimReceiveInventoryPage()
    {
        require_once plugin_dir_path(__FILE__) . 'templates/receive-inventory.php';
    }

    public function wcvimReceiveBackOrderItemsPage()
    {
        require_once plugin_dir_path(__FILE__) . 'templates/receive-back-order-items.php';
    }

    public function wcvimSaveAdminMenu()
    {
        add_submenu_page(
            null,
            __('Update Vendor Product Mapping', 'wcvim'),
            __('Update Vendor Product Mapping', 'wcvim'),
            'manage_options',
            'update-vendor-product-mapping',
            array($this, 'updateVendorProductMapping')
        );
        //        add_menu_page(__('Update Vendor Product Mapping', 'wcvim'), __('Update Vendor Product Mapping', 'wcvim'), 'manage_options', 'uasortpdate-vendor-product-mapping', array($this, 'updateVendorProductMapping'));
    }

    public function extra_tablenav($which)
    {
        if ($which == 'top') {
            $product_update_last_date = get_option('_product_update_last_date');
            $vendor_management_last_date = get_option('_vendor_management_last_date'); ?>
            <div style="padding-bottom: 10px;clear: both;">
                <input type="hidden" name="baseUrl" id="baseUrl" value="<?php echo plugin_dir_url(__FILE__); ?>">
                <?php
                echo '<input type="submit" id="generate-po-button" name="wcvm_save" class="button button-primary" value="' . esc_html__('Generate') . '">';
                if ($product_update_last_date >= $vendor_management_last_date) {
                ?>
                    <!--<a style="" href="#" class="button button-primary sync-vendor-details sync-vendor-product-mapping"><?= esc_attr__('Sync Vendor Product', 'wcvm') ?></a>-->
                    <a style="" href="#" class="button button-primary sync-vendor-details sync-vendor-po"><?= esc_attr__('Sync Vendor PO', 'wcvm') ?></a>
                    <!--<a style="" href="#" class="button button-primary sync-vendor-details update-vendor-po"><?= esc_attr__('Update Vendor PO', 'wcvm') ?></a>-->
                    <div style="margin-top:10px;" class="text-danger">
                        <span style="padding:5px; font-size:12px"> Product Update Last Date: <?php echo $product_update_last_date; ?> Vendor Management Last Date: <?php echo $vendor_management_last_date; ?></span>
                    </div>
                <?php
                } ?>
            </div>
        <?php
        } else {
            echo '<input onclick="jQuery(&apos;#save_button_hit&apos;).val(1)" type="submit" name="wcvm_save" class="button button-primary" value="' . esc_html__('Generate') . '">';
        }
    }

    public function generate_po()
    {
        global $wpdb;
        $vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_orders';
        $vendor_purchase_order_item_table = $wpdb->prefix . 'vendor_purchase_orders_items';
        $ajaxResponse['message'] = 'no post data';
        $ajaxResponse['purchase_order'] = false;
        $product_IDs = [];
        if (isset($_POST)) {
            $purchase_orders_post_data = $_POST['purchase_order_data'];
            $created_purchase_orders_ids = [];
            if ($purchase_orders_post_data) {
                foreach ($purchase_orders_post_data as $purchase_orders_post_data_single) {
                    if (!array_key_exists($purchase_orders_post_data_single['selected_vendor'], $created_purchase_orders_ids)) {
                        $data = array(
                            'post_type' => 'wcvm-order',
                            'post_status' => 'new-order',
                            'post_parent' => $purchase_orders_post_data_single['selected_vendor']
                        );
                        $created_purchase_orders_ids[$purchase_orders_post_data_single['selected_vendor']] = wp_insert_post($data);
                    }

                    $sql = "SELECT * FROM `{$wpdb->prefix}vendor_po_lookup` WHERE id = '" . $purchase_orders_post_data_single['selected_id'] . "'";

                    $productDetails = $wpdb->get_results($sql);
                    $productVendorLookupData = json_decode(json_encode($productDetails[0]), true);
                    $productVendorIds = explode(",", $productVendorLookupData['vendor_id']);
                    $productVendorNames = explode(",", $productVendorLookupData['vendor_name']);
                    $productVendorPrices = explode(",", $productVendorLookupData['vendor_price']);
                    $productVendorSkus = explode(",", $productVendorLookupData['vendor_sku']);
                    $producyVendorMapping = [];
                    if ($productVendorIds) {
                        $i = 0;
                        while ($i < count($productVendorIds)) {
                            $producyVendorMapping[$productVendorIds[$i]]['name'] = $productVendorNames[$i];
                            $producyVendorMapping[$productVendorIds[$i]]['prices'] = $productVendorPrices[$i];
                            $producyVendorMapping[$productVendorIds[$i]]['sku'] = $productVendorSkus[$i];
                            $i++;
                        }
                    }
                    $productID = $productVendorLookupData['product_id'];
                    $productQty = $purchase_orders_post_data_single['product_qty'];
                    $vendor_product_ids_data[$purchase_orders_post_data_single['selected_vendor']][] = $productID;
                    $vendor_product_orders_data[$purchase_orders_post_data_single['selected_vendor']][$productID] = array(
                        'product_id' => $productVendorLookupData['product_id'],
                        'product_title' => $productVendorLookupData['product_title'],
                        'product_sku' => $productVendorLookupData['sku'],
                        'product_price' => $productVendorLookupData['regular_price'],
                        'product_quantity' => $productQty,
                        'product_rare' => $productVendorLookupData['rare'],
                        'vendor_sku' => $producyVendorMapping[$purchase_orders_post_data_single['selected_vendor']]['sku'],
                        'vendor_name' => $producyVendorMapping[$purchase_orders_post_data_single['selected_vendor']]['name'],
                        'vendor_price_last' => $producyVendorMapping[$purchase_orders_post_data_single['selected_vendor']]['prices'],
                        'vendor_link' => $productVendorLookupData['vendor_link'],
                        'vendor_price_bulk' => $productVendorLookupData['vendor_price_bulk'],
                        'vendor_price_notes' => $productVendorLookupData['vendor_price_notes'],
                    );
                    $vendor_product_on_orders_data[$purchase_orders_post_data_single['selected_vendor']][$productID] = array(
                        'vendor' => $purchase_orders_post_data_single['selected_vendor'],
                        'qty' => $productQty,
                        'order' => $created_purchase_orders_ids[$purchase_orders_post_data_single['selected_vendor']],
                        'order_date' => date('Y/m/d H:i:s a'),
                        'expected_date' => ''
                    );


                    $vendor_product_quantities[$purchase_orders_post_data_single['selected_vendor']][$productID] = $productQty;
                }
            }


            $uniquer_vendor_ids = array_keys($created_purchase_orders_ids);

            if ($uniquer_vendor_ids) {
                foreach ($uniquer_vendor_ids as $uniquer_vendor_id) {
                    $vpo_insertData['vendor_id'] = $uniquer_vendor_id;
                    $vpo_insertData['order_id'] = $created_purchase_orders_ids[$uniquer_vendor_id];
                    $vpo_insertData['post_status'] = 'new-order';
                    $vpo_insertData['order_date'] = date('Y/m/d H:i:s a');
                    $vpo_insertData['created_date'] = date('Y/m/d H:i:s a');
                    $vpo_insertData['created_by'] = get_current_user_id();
                    $insertedPOId = $wpdb->insert($vendor_purchase_order_table, $vpo_insertData);

                    if ($wpdb->insert_id) {
                        $createdOrderId = $wpdb->insert_id;
                        foreach ($vendor_product_ids_data[$uniquer_vendor_id] as $vendor_single_product) {
                            $productIDs = '';
                            $productIDs = $vendor_single_product;
                            $sql = "SELECT * FROM " . $wpdb->prefix . "vendor_po_lookup vpol WHERE vpol.product_id = " . $productIDs;
                            $orderDetails = $wpdb->get_results($sql);
                            $insertPOProductData['vendor_order_idFk'] = $createdOrderId;
                            $insertPOProductData['product_id'] = $productIDs;
                            $insertPOProductData['product_title'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['product_title'];
                            $insertPOProductData['product_sku'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['product_sku'];
                            $insertPOProductData['product_price'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['product_price'];
                            $insertPOProductData['product_rare'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['product_rare'];
                            $insertPOProductData['product_ordered_quantity'] = $vendor_product_on_orders_data[$uniquer_vendor_id][$vendor_single_product]['qty'];
                            $insertPOProductData['product_category'] = $orderDetails[0]->category;
                            $insertPOProductData['vendor_sku'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['vendor_sku'];
                            $insertPOProductData['vendor_name'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['vendor_name'];
                            $insertPOProductData['vendor_price_last'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['vendor_price_last'];
                            $insertPOProductData['vendor_link'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['vendor_link'];
                            $insertPOProductData['vendor_price_bulk'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['vendor_price_bulk'];
                            $insertPOProductData['vendor_price_notes'] = $vendor_product_orders_data[$uniquer_vendor_id][$vendor_single_product]['vendor_price_notes'];
                            $insertPOProductData['on_order_quantity'] = $orderDetails[0]->on_order;
                            $insertPOProductData['sale_30_days'] = $orderDetails[0]->sale_30_days;
                            $insertPOProductData['product_stock'] = $orderDetails[0]->stock;
                            $insertPOProductData['created_date'] = date('Y/m/d H:i:s a');
                            $insertPOProductData['created_by'] = get_current_user_id();
                            $insertedPOLineItem = $wpdb->insert($vendor_purchase_order_item_table, $insertPOProductData);

                            if ($insertedPOLineItem) {
                                $ajaxResponse['purchase_order'] = true;
                            }
                            if ($productIDs == '') {
                                $productIDs .= $vendor_single_product;
                            } else {
                                $productIDs .= ',' . $vendor_single_product;
                            }
                            //                            add_post_meta($created_purchase_orders_ids[$uniquer_vendor_id], 'wcvmgo_product_id', $productIDs);
                        }
                    }
                }
                $ajaxResponse['redirect_url'] = admin_url() . 'admin.php?page=wcvm-epo&status=new-order';
            }
        }
        exit(json_encode($ajaxResponse));
    }

    public function generatePurchaseOrder()
    {
        $link = admin_url('admin-ajax.php?action=generatePO&post_id=');
        global $wpdb;
        $vendor_po_lookup_table = $wpdb->prefix . "vendor_po_lookup";
        $vendor_purchase_order_table = $wpdb->prefix . "vendor_purchase_orders";
        // $order_details_table_sql = "SELECT *,
        // 			CASE
        // 				WHEN stock IS NULL THEN 'OUT'
        // 				WHEN CAST(stock as signed) <= 0 THEN 'OUT'
        // 				WHEN CAST(stock as signed) <= threshold_low THEN 'LOW'
        // 				WHEN CAST(stock as signed) <= threshold_reorder THEN 'REORDER'
        // 				ELSE 'OK'
        // 			END stock_status
        // 			FROM " . $order_details_table . "";
        // $orderDetails = $wpdb->get_results($order_details_table_sql);
        $order_by = [];
        $selected_days = '';
        $selected_qty = '';
        $thirty_days_filter = '';
        $qty_on_hand_filter = '';
        $percentage_filter = '';
        $vendors_selected = '';
        $status_selected = '';
        $purchase_orders_post_data = [];
        $selected_status = [];
        // print_r($_GET);
        if (array_key_exists('30_days', $_GET) || array_key_exists('qty_on_hand', $_GET) || array_key_exists('percentage', $_GET)) {
            $thirty_days_filter = $_GET['30_days'];
            $qty_on_hand_filter = $_GET['qty_on_hand'];
            $percentage_filter = $_GET['percentage'];
            if (!empty($thirty_days_filter)) {
                $order_by[] = "v.sale_30_days " . $thirty_days_filter;
            }
            if (!empty($qty_on_hand_filter)) {
                $order_by[] = "v.stock " . $qty_on_hand_filter;
            }
            if (!empty($percentage_filter)) {
                $order_by[] = "v.stock_30_days_sale_percent " . $percentage_filter;
            }
        } else {
            $percentage_filter = 'asc';
            $order_by[] = "v.stock_30_days_sale_percent " . $percentage_filter;
        }
        $where = '';
        //$where = " WHERE v.stock_status IN ('outofstock','instock')";
        if (array_key_exists('selected_vendors', $_GET)) {
            if ($_GET['selected_vendors'] != "") {
                $vendors_selected = $_GET['selected_vendors'];
                $purchase_orders_post_data = explode("|", $_GET['selected_vendors']);
                $where = " WHERE v.primary_vendor_id IN (" . implode(",", $purchase_orders_post_data) . ")";
            }
        }
        /* if (array_key_exists('selected_status', $_GET)) {
          if ($_GET['selected_status'] != "") {
          $status_selected = $_GET['selected_status'];
          $selected_status = explode("|", $_GET['selected_status']);
          if ($where) {
          $where .= " AND ";
          } else {
          $where = " WHERE ";
          }
          $where .= " v.stock_status IN (" . implode(",", $selected_status) . ")";
          }
          } */
        $order_details_table_sql = "SELECT v.id,v.stock_30_days_sale_percent, v.product_id, v.product_title, v.sku, v.regular_price, v.stock_status, 
        v.stock, v.threshold_low, v.threshold_reorder, v.reorder_qty, v.new , v.rare, v.category, v.vendor_id, v.vendor_name, 
        v.vendor_sku, v.vendor_link, v.vendor_price_bulk, v.vendor_price_notes, v.vendor_price, v.primary_vendor_id, 
        v.primary_vendor_name, v.on_order, v.sale_30_days, v.order_qty,v.on_vendor_bo, v.stock_status, 
        CASE WHEN v.stock IS NULL THEN 'OUT' 
        WHEN CAST(v.stock as signed) <= 0 THEN 'OUT' 
        ELSE 'IN' END product_stock_status
        FROM " . $vendor_po_lookup_table . " v
         " . $where . "
        group by v.id,v.stock_30_days_sale_percent, v.product_id, v.product_title, v.sku, v.regular_price, v.stock_status, v.stock, v.threshold_low, 
        v.threshold_reorder, v.reorder_qty, v.new , v.rare, v.category, v.vendor_id, v.vendor_name, v.vendor_sku, v.vendor_link, 
        v.vendor_price_bulk, v.vendor_price_notes, v.vendor_price, v.primary_vendor_id, v.primary_vendor_name, v.on_order, 
        v.sale_30_days, v.order_qty,v.on_vendor_bo, v.stock_status ";


        $sql = $order_details_table_sql;
        if (count($order_by) > 0) {
            $sql .= " ORDER BY " . implode(', ', $order_by);
        }
        $orderDetails = $wpdb->get_results($sql);
        ?>
        <style>
            /*thead{
                position: fixed;
                background: white;
            }
            tbody:before {
            content: "-";
            display: block;
            line-height: 4em;
            color: transparent;
        }*/
            .dropdown-menu>.active>a,
            .dropdown-menu>.active>a:hover,
            .dropdown-menu>.active>a:focus {
                color: #fff !important;
                text-decoration: none !important;
                background-color: #428bca !important;
                outline: 0 !important;
            }

            .scrollable {
                overflow: auto !important;
                width: 70px !important;
                /* adjust this width depending to amount of text to display */
                height: 80px !important;
                /* adjust height depending on number of options to display */
                border: 1px silver solid !important;
            }

            .scrollable select {
                border: none !important;
            }

            .dropdown-menu>li>a {
                display: block;
                padding: 3px 20px;
                clear: both;
                font-weight: normal;
                line-height: 1.428571429;
                color: #333;
                white-space: nowrap;
            }

            .dropdown-menu {
                font-size: 14px !important;
                font-family: inherit !important;
            }

            .multiselect-container>li {
                margin-bottom: 0;

            }

            .multiselect-container>li>a>label {
                padding: 3px 1px 3px 10px !important;
                font-family: sans-serif;
            }

            .btn {
                font-size: 14px !important;
                font-weight: normal !important;
            }

            th {
                text-align: inherit;
                padding: 8px !important;
                font-size: 10px;
            }

            td {
                text-align: inherit;
                font-size: 10px;
            }

            #wpbody-content {
                background: #f1f1f1;
            }

            #tags_categories {
                width: 100px;
            }

            input[readonly] {
                background: transparent;
                border: none;
                box-shadow: none;
            }

            input[type=checkbox] {
                /*                background: white;*/
                border: 1px solid #cbc8c8;
            }

            .center {
                text-align: center;
            }

            .first-cell {
                width: 40px;
            }

            .third-cell {
                width: 56px;
            }

            .fourth-cell {
                width: 200px;
            }

            .fifth-cell {
                width: 200px;
            }

            .sixth-cell {
                width: 85px;
                /*width: 65px;*/
            }

            .seventh-cell {
                width: 65px;
                /*width: 55px;*/
            }

            .eighth-cell {
                width: 55px;
                padding-top: 5px;
                padding-bottom: 5px;
            }

            .tenth-cell {
                width: 75px;
            }

            .eleventh-cell {
                width: 85px;
            }

            .even {
                background-color: #f9f9f9;
            }

            .vendor-select {
                font-size: 11px !important;
                padding: 0 15px 0 2px !important;
                background-size: 8px 8px !important;
                min-height: 25px !important;
                width: 120px !important;
            }

            table.dataTable thead th,
            table.dataTable thead td {
                padding: 0px 4px !important;
            }

            .dropdown-toggle {
                background-color: white;
                border-color: #7e8993 !important;
                padding: .160rem .70rem !important;
            }

            .sort-arrow {
                border: solid black;
                border-width: 0 3px 3px 0;
                display: inline-block;
                padding: 3px;
            }

            .right {
                transform: rotate(-45deg);
                -webkit-transform: rotate(-45deg);
            }

            .left {
                transform: rotate(135deg);
                -webkit-transform: rotate(135deg);
            }

            .up {
                transform: rotate(-135deg);
                -webkit-transform: rotate(-135deg);
            }

            .down {
                transform: rotate(45deg);
                -webkit-transform: rotate(45deg);
            }

            * {
                -moz-box-sizing: border-box;
                -o-box-sizing: border-box;
                -webkit-box-sizing: border-box;
                box-sizing: border-box;
            }

            body {
                background-color: #f1f1f1 !important;
                color: #333;
                font-size: 1em;
            }

            a:link,
            a:visited,
            a:hover,
            a:active {
                /*color: #000;*/
                color: #0073aa;
                text-decoration: none;
            }

            .container {
                margin: 50px auto;
                padding: 0 50px;
                max-width: 960px;
            }

            table {
                background: #FFF;
                border-collapse: collapse;
                width: 100%;
            }

            td,
            th {
                padding: 4px;
                border: 1px solid #CCC;
                overflow: hidden;
                text-align: left;
                vertical-align: middle;
            }

            th {
                background-color: #DDD;
                font-weight: 400;
            }

            th a,
            td a {
                color: #007bff;
                display: block;
                width: 100%;
            }

            th a.sort-by {
                padding-right: 18px;
                position: relative;
            }

            a.sort-by:before,
            a.sort-by:after {
                border: 4px solid transparent;
                content: "";
                display: block;
                height: 0;
                right: 5px;
                top: 50%;
                position: absolute;
                width: 0;
            }

            a.sort-by:before {
                border-bottom-color: #666;
                margin-top: -9px;
            }

            a.sort-by:after {
                border-top-color: #666;
                margin-top: 1px;
            }

            a.sort-by.hiddenafter:after {
                display: none;
            }

            a.sort-by.hiddenbefore:before {
                display: none;
            }
        </style>
        <div id='page-loader' style='width: 100%;height: 100%;top: 0;left: 0;position: fixed;opacity: 0.7; background-color: #fff;z-index: 99;text-align: center;'>
            <img style=' position: absolute;top: 50%;left: 50%;z-index:100 ' width='50' height='50' class='label-spinner' src="<?php echo plugin_dir_url(__FILE__) . 'assets/img/loader.gif' ?>">
        </div>
        <div class="wrap wm-vm-go">
            <h1><?= esc_html__('Generate Purchase Orders', 'wcvm') ?></h1>
            <form action="" method="post">
                <!-- <input type="hidden" name="new_item" id="wcvm_new_item" value="">
                <input type="hidden" name="rare_item" id="wcvm_rare_item" value=""> -->
                <?php $this->extra_tablenav('top'); ?>
            </form>

            <!-- start filter section -->

            <div style="float: left;vertical-align: top">

                <!--                <select name="stock_status_filter" class="vendor_details" id="stock_status_filter" multiple="multiple">
                                                                                                                    <option <?php
                                                                                                                            // if (in_array('out', $selected_status)) {
                                                                                                                            //                    echo 'selected';
                                                                                                                            //                }
                                                                                                                            ?> value="out"><?= esc_html__('OUT', 'wcvm') ?></option>
                                                                                                                    <option <?php
                                                                                                                            // if (in_array('low', $selected_status)) {
                                                                                                                            //                    echo 'selected';
                                                                                                                            //                }
                                                                                                                            ?> value="low"><?= esc_html__('LOW', 'wcvm') ?></option>
                                                                                                                    <option <?php
                                                                                                                            // if (in_array('reorder', $selected_status)) {
                                                                                                                            //                    echo 'selected';
                                                                                                                            //                }
                                                                                                                            ?> value="reorder"><?= esc_html__('REORDER', 'wcvm') ?></option>
                                                                                                                    <option <?php
                                                                                                                            // if (in_array('ok', $selected_status)) {
                                                                                                                            //                echo 'selected';
                                                                                                                            //            }
                                                                                                                            ?> value="ok"><?= esc_html__('OK', 'wcvm') ?></option>
                                                                                                                </select>-->
                <select name="primary_vendor_filter" class="vendor_details scrollable" id="primary_vendor_filter" multiple="multiple" style="display:none">
                    <?php
                    global $wpdb;
                    $posts_table = $wpdb->prefix . "posts";
                    $posts_table_sql = ""
                        . "SELECT p.ID,p.post_title, pm.meta_value as title_short
                                FROM " . $wpdb->prefix . "posts p
                                LEFT JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND pm.meta_key = 'title_short'
                                WHERE post_type = 'wcvm-vendor' OR post_type = 'wcvm-vendors' AND post_status = 'publish' ORDER BY p.post_title";
                    $posts = $wpdb->get_results($posts_table_sql);
                    foreach ($posts as $vendor) :
                        $selected = "";
                        if (in_array($vendor->ID, $purchase_orders_post_data)) {
                            $selected = 'selected';
                        } ?>
                        <option <?php echo $selected; ?> value="<?= esc_attr($vendor->ID) ?>"><?= esc_html($vendor->post_title) ?> (<?= esc_html($vendor->title_short) ?>)</option>
                    <?php endforeach ?>
                </select>
            </div>
            <form id="sort-form" action="" method="get">
                <input type="hidden" name="page" value="generate-purchase-order">
                <input type="hidden" name="selected_vendors" id="selected_vendors" value="<?php echo $vendors_selected; ?>" />
                <!--<input type="hidden" name="selected_status" id="selected_status" value="<?php // echo $status_selected;
                                                                                            ?>"/>-->
                <input type="hidden" name="30_days" id="30_days" value="<?php echo $thirty_days_filter; ?>" />
                <input type="hidden" name="qty_on_hand" id="qty_on_hand" value="<?php echo $qty_on_hand_filter; ?>" />
                <input type="hidden" name="percentage" id="percentage_filter" value="<?php echo $percentage_filter; ?>" />

                <input type="submit" name="filter_action" class="btn btn-primary button" id="filter-vendor" value="<?= esc_attr__('Filter', 'wcvm') ?>" style="min-height:29px !important;margin-left:20px;display:none">
            </form>
            <!-- end filter section -->

            <div id="my-content-id" style="">
                <img id="loading_image" src="<?php plugin_dir_path(__FILE__) . '/assets/img/loader.gif' ?>" />
                <div id="vendor_details">

                </div>
            </div>
            <div id="ajax-response"></div>
            <br class="clear">
        </div>
        <table id="po-items-table" class="product-items-table" style="margin-top: 25px;">
            <thead>
                <tr>
                    <!--                    <th class="center first-cell">New</th>
                    <th class="center first-cell">Rare</th>-->
                    <th class="center third-cell">CC SKU</th>
                    <th class="center fourth-cell">Vendor SKU</th>
                    <th class="center fifth-cell">Category/Tags</th>
                    <th class="center sixth-cell">Stock<br />Status</th>
                    <th class="center seventh-cell">Our<br />Price</th>
                    <th class="center eighth-cell">Select<br>Vendor</th>
                    <th class="center seventh-cell">Vendor<br>Price</th>
                    <th class="center tenth-cell">
                        <?php
                        $extra_class_30_days = "";
                        $extra_class_qty = "";
                        $extra_class_percentage = "";

                        if ($thirty_days_filter == 'asc') {
                            $extra_class_30_days = "hiddenafter";
                        } elseif ($thirty_days_filter == 'desc') {
                            $extra_class_30_days = "hiddenbefore";
                        }
                        if ($qty_on_hand_filter == 'asc') {
                            $extra_class_qty = "hiddenafter";
                        } elseif ($qty_on_hand_filter == 'desc') {
                            $extra_class_qty = "hiddenbefore";
                        }
                        if ($percentage_filter == 'asc') {
                            $extra_class_percentage = "hiddenafter";
                        } elseif ($percentage_filter == 'desc') {
                            $extra_class_percentage = "hiddenbefore";
                        } ?>
                        <a id="qty" href="#" class="sort-by <?php echo $qty_on_hand_filter . " " . $extra_class_qty; ?>">QTY On Hand</a>
                    </th>
                    <th class="center eleventh-cell">
                        <a href="#" class="sort-by <?php echo $thirty_days_filter . " " . $extra_class_30_days; ?>" id='30-days'>30<br> Days</a>
                    </th>
                    <th class="center eleventh-cell">
                        <a href="#" class="sort-by <?php echo $percentage_filter . " " . $extra_class_percentage; ?>" id='percentage'>QTY On Hand %</a>
                    </th>
                    <!--                    <th class="center seventh-cell">Low<br>Thresh</th>
                    <th class="center seventh-cell">Reorder<br>Thresh</th>
                    <th class="center seventh-cell">Reorder<br>QTY</th>-->
                    <th class="center seventh-cell">On<br>Order</th>
                    <th class="center seventh-cell">On<br>Vendor<br>BO</th>
                    <th class="center seventh-cell">Order<br>QTY</th>
                    <th class="center seventh-cell">Add<br>To<br>PO</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_even_odd = array('even', 'odd');
                $even_odd_counter = 0;
                foreach ($orderDetails as $orderDetail) {
                    $vendors = explode(',', $orderDetail->vendor_name);
                    $vendor_ids = explode(',', $orderDetail->vendor_id);
                    $vendor_prices = explode(',', $orderDetail->vendor_price);
                    $row_classes = "generate-po-row " . $row_even_odd[$even_odd_counter % 2];
                    
                    //                    if ($orderDetail->rare) {
                    //                        $row_classes .= " rare_item";
                    //                    } else {
                    //                        $row_classes .= " non_rare_item";
                    //                    }
                    //
                    //                    if ($orderDetail->new) {
                    //                        $row_classes .= " new_item";
                    //                    } else {
                    //                        $row_classes .= " non_new_item";
                    //                    }
                    if ($orderDetail->sale_30_days) {
                        $row_classes .= " 30_days";
                    }
                    if ($orderDetail->stock) {
                        $row_classes .= " stock";
                    }
                    $row_classes .= " " . strtolower($orderDetail->product_stock_status) . " " . "primary_vendor_" . $orderDetail->primary_vendor_id; ?>
                    <tr class="<?php echo $row_classes; ?>" id='row-<?php echo $orderDetail->id ?>'>
                        <!--<td class="center first-cell"><?php // echo ($orderDetail->new) ? "&#10004;" : "";
                                                            ?></td>-->
                        <!--<td class="center first-cell"><?php // echo ($orderDetail->rare) ? "&#10004;" : "";
                                                            ?></td>-->
                        <?php
                        $thumnailID = get_post_thumbnail_id($orderDetail->product_id);
                        //          $product_url = get_permalink($orderDetail->product_id);
                        $product_admin_url = get_edit_post_link($orderDetail->product_id);
                        $product_image_src = '';
                        $product_image_src = wc_placeholder_img_src();
                        if ($thumnailID) {
                            $image_src = wp_get_attachment_image_src($thumnailID, 'thumbnail'); // returns product image source
                            //$image = woocommerce_get_product_thumbnail(); // returns product image
                            //$image = wp_get_attachment_image($thumnailID,'thumbnail'); //returns product image
                            $product_image_src = $image_src[0];
                        }
                        $siteUrl = str_replace('wp', '', get_site_url());
                        if ($_SERVER['HTTP_HOST'] == "localhost") {
                            $imagepath = str_replace(get_site_url() . '/wp-content', WP_CONTENT_DIR, $product_image_src);
                        } else {
                            $imagepath = str_replace($siteUrl . 'app', WP_CONTENT_DIR, $product_image_src);
                        }
                        if (!file_exists($imagepath)) {
                            $product_image_src = wc_placeholder_img_src();
                        }
                        ?>
                        <td class="center third-cell">
                            <!--<a class="sku-thumbnail" href="<?php // echo $product_url;
                                                                ?>" data-image="http://localhost/wordpress-14/wp-content/uploads/2016/09/Honda-FOB-11-150x150.jpg"><?php // echo $orderDetail->sku
                                                                                                                                                                    ?></a>-->
                            <a class="sku-thumbnail" href="<?php echo $product_admin_url; ?>" data-image="<?php echo $product_image_src; ?>" target="_blank"><?php echo $orderDetail->sku ?></a>

                        </td>
                        <td class="center fourth-cell"><?php echo $orderDetail->vendor_sku ?></td>
                        <td class="center fifth-cell"><?php echo ($orderDetail->category) ?></td>
                        <td class="center sixth-cell">
                            <?php
                            if ($orderDetail->product_stock_status == 'OUT') {
                                echo '<span style="background: red;padding: 5px;color: white">' . esc_html__('OUT', 'wcvm') . '</span>';
                            } else {
                                echo $orderDetail->product_stock_status;
                            } ?>
                        </td>
                        <td class="center seventh-cell"><?php echo wc_price($orderDetail->regular_price) ?></td>
                        <?php $purchase_orders_post_data_single_price = 0; ?>
                        <td class="eighth-cell">
                            <select id="row-selected-vendor-<?php echo $orderDetail->id ?>" class="vendor-select">
                                <?php
                                for ($i = 0; $i < count($vendors); $i++) {
                                    $selected = '';
                                    if ($vendor_ids[$i] == $orderDetail->primary_vendor_id) {
                                        $selected = 'selected';
                                    }
                                    if ($selected == 'selected') {
                                        $purchase_orders_post_data_single_price = $vendor_prices[$i];
                                    }
                                    if ($i == 0 && $selected == '') {
                                        $purchase_orders_post_data_single_price = $vendor_prices[$i];
                                    }
                                ?>
                                    <option <?php echo $selected; ?> data-vendor_price="<?php echo get_woocommerce_currency_symbol() . trim(number_format($vendor_prices[$i], 2)); ?>" value="<?php echo $vendor_ids[$i]; ?>"><?php echo $vendors[$i]; ?></option>
                                <?php
                                } ?>
                            </select>
                        </td>
                        <td class="center seventh-cell"><?php echo wc_price($purchase_orders_post_data_single_price); ?></td>
                        <td class="center tenth-cell"><?php echo $orderDetail->stock ?></td>
                        <td class="center eleventh-cell"><?php echo $orderDetail->sale_30_days ?></td>
                        <td class="center eleventh-cell"><?php echo $orderDetail->stock_30_days_sale_percent. '%' ?></td>
                        <!--<td class="center seventh-cell"><?php // echo $orderDetail->threshold_low
                                                            ?></td>-->
                        <!--<td class="center seventh-cell"><?php // echo $orderDetail->threshold_reorder
                                                            ?></td>-->
                        <!--<td class="center seventh-cell"><?php // echo $orderDetail->reorder_qty
                                                            ?></td>-->
                        <td class="center seventh-cell"><?php echo $orderDetail->on_order ? $orderDetail->on_order : 0 ?></td>
                        <td class="center seventh-cell"><?php echo $orderDetail->on_vendor_bo; ?></td>
                        <td class="center seventh-cell"><input id='order-quantity-<?php echo $orderDetail->id ?>' tabindex="<?php echo $even_odd_counter + 1; ?>" type="text" style="width:50px" value="0"></td>
                        <td class="center seventh-cell"><input type="checkbox" class='po-selected-products' value="<?php echo $orderDetail->id ?>"></td>
                    </tr>
                <?php
                    $even_odd_counter++;
                } ?>
            </tbody>
        </table>
        <!-- stylesheet -->

        <!--        <link rel=" stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">-->
        <!--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">-->

        <!--        <link rel=" stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">-->
        <link rel=" stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'assets/extras/bootstrap.min.css'; ?>">
        <!--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">-->
        <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'assets/extras/bootstrap-multiselect.css'; ?>">
        <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'assets/extras/tailwind.css'; ?>">


        <!-- script -->
        <!--        <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>-->
        <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>-->
        <script src="<?php echo plugin_dir_url(__FILE__) . 'assets/extras/popper.min.js'; ?>"></script>
        <!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>-->
        <!--        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>-->
        <script src="<?php echo plugin_dir_url(__FILE__) . 'assets/extras/bootstrap.min.js'; ?>"></script>
        <!--        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>-->
        <script src="<?php echo plugin_dir_url(__FILE__) . 'assets/extras/bootstrap-multiselect.min.js'; ?>"></script>



        <!--  -->

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                "use strict";
                // $('.wm-vm-go input[name="filter_action"]').on('click', function() {
                // 	$(this.form).attr('method', 'get');
                // });
                //                $(".sku-thumbnail").mouseenter(function() {
                //                    var image_name = $(this).data('image');
                //                    var imageTag = '<div class="image" style="position:absolute;">' + '<img src="' + image_name + '" alt="image" height="100" />' + '</div>';
                //                    $(this).parent('td').append(imageTag);
                //                });
                //                $(".sku-thumbnail").mouseleave(function() {
                //                    $(this).parent('td').children('div.image').remove();
                //                });
                var sorted_by_days = false;
                var sorted_by_qty = false;
                $(".sort-by").each(function() {
                    if ($(this).hasClass('asc') || $(this).hasClass('desc')) {
                        if ($(this).attr('id') == '30-days') {
                            sorted_by_days = true;
                        } else if ($(this).attr('id') == 'qty') {
                            sorted_by_qty = true;
                        } else if ($(this).attr('id') == 'percentage') {
                            sorted_by_qty = true;
                        }
                    }
                });
                $(".sort-by").mouseover(function() {
                    //hiddenafter means that up arrow is visible and down arrow is hidden
                    if ($(this).hasClass('asc')) {
                        $(this).removeClass('hiddenafter');
                        $(this).addClass('hiddenbefore');
                    } else if ($(this).hasClass('desc')) {
                        $(this).removeClass('hiddenbefore');
                        $(this).addClass('hiddenafter');
                    } else {
                        $(this).removeClass('hiddenbefore');
                        $(this).addClass('hiddenafter');
                    }
                });
                $(".sort-by").mouseout(function() {
                    if (!$(this).hasClass('permanent')) {
                        if ($(this).attr('id') == '30-days') {
                            if (sorted_by_days) {
                                if ($(this).hasClass('hiddenafter')) {
                                    $(this).removeClass('hiddenafter');
                                    $(this).addClass('hiddenbefore');
                                } else if ($(this).hasClass('hiddenbefore')) {
                                    $(this).removeClass('hiddenbefore');
                                    $(this).addClass('hiddenafter');
                                }
                            } else {
                                $(this).removeClass('hiddenbefore');
                                $(this).removeClass('hiddenafter');
                            }
                        } else if ($(this).attr('id') == 'qty') {
                            if (sorted_by_qty) {
                                if ($(this).hasClass('hiddenafter')) {
                                    $(this).removeClass('hiddenafter');
                                    $(this).addClass('hiddenbefore');
                                } else if ($(this).hasClass('hiddenbefore')) {
                                    $(this).removeClass('hiddenbefore');
                                    $(this).addClass('hiddenafter');
                                }
                            } else {
                                $(this).removeClass('hiddenbefore');
                                $(this).removeClass('hiddenafter');
                            }
                        } else if ($(this).attr('id') == 'percentage') {
                            if (sorted_by_qty) {
                                if ($(this).hasClass('hiddenafter')) {
                                    $(this).removeClass('hiddenafter');
                                    $(this).addClass('hiddenbefore');
                                } else if ($(this).hasClass('hiddenbefore')) {
                                    $(this).removeClass('hiddenbefore');
                                    $(this).addClass('hiddenafter');
                                }
                            } else {
                                $(this).removeClass('hiddenbefore');
                                $(this).removeClass('hiddenafter');
                            }
                        }
                    }
                });
                $(".sort-by").on('click', function(e) {
                    e.preventDefault();
                    $(this).addClass('permanent');
                    if ($(this).attr('id') == '30-days') {
                        if ($(this).hasClass('asc')) {
                            $("#30_days").val('desc');
                        } else if ($(this).hasClass('desc')) {
                            $("#30_days").val('asc');
                        } else {
                            $("#30_days").val('asc');
                        }
                        $("#qty_on_hand").val("");
                        $("#percentage_filter").val("");
                    } else if ($(this).attr('id') == 'qty') {
                        if ($(this).hasClass('asc')) {
                            $("#qty_on_hand").val('desc');
                        } else if ($(this).hasClass('desc')) {
                            $("#qty_on_hand").val('asc');
                        } else {
                            $("#qty_on_hand").val('asc');
                        }
                        $("#30_days").val("");
                        $("#percentage_filter").val("");
                    } else if ($(this).attr('id') == 'percentage') {
                        if ($(this).hasClass('asc')) {
                            $("#percentage_filter").val('desc');
                        } else if ($(this).hasClass('desc')) {
                            $("#percentage_filter").val('asc');
                        } else {
                            $("#percentage_filter").val('asc');
                        }
                        $("#30_days").val("");
                        $("#qty_on_hand").val("");
                    }

                    var vendors_selected;
                    if ($("#primary_vendor_filter").val()) {
                        vendors_selected = $("#primary_vendor_filter").val().join('|');
                    }
                    $("#selected_vendors").val(vendors_selected);
                    //                    var status_selected = $("#stock_status_filter").val().join('|');
                    //                    $("#selected_status").val(status_selected);
                    $("#page-loader").show();
                    console.log("entered if2");
                    $("#sort-form").submit();
                });
                var unselect_stock_status = '';

                //stock status multiselect
                //                $('#stock_status_filter').multiselect({
                //                    buttonWidth: '240px',
                //                    nonSelectedText: 'Select Stock Statuses',
                //                });
                //vendor multiselect
                $('#primary_vendor_filter').multiselect({
                    buttonWidth: '440px',
                    includeSelectAllOption: true,
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true,
                    maxHeight: 350,
                    filterPlaceholder: 'Search for Vendor',
                    nonSelectedText: 'Select Vendors',
                });
                // document.getElementsByClassName("btn dropdown-toggle btn-default")[0].style.borderColor = "red";
                $('#filter-vendor').on('click', function(e) {
                    e.preventDefault();
                    var show_all = false;
                    var rare_item_filter = '';
                    var selected_statuses = new Array();
                    var selected_vendors = $("#primary_vendor_filter").val();
                    if (!selected_vendors) {
                        show_all = true;
                    }
                    //console.log("selected vendors are "+selected_vendors);
                    //                    if ($("#new_item_filter").val() == 1) {
                    //                        new_item_filter = "new_item";
                    //                    } else if ($("#new_item_filter").val() == 0) {
                    //                        new_item_filter = "non_new_item";
                    //                    }
                    //                    if ($("#rare_item_filter").val() == 1) {
                    //                        rare_item_filter = "rare_item";
                    //                    } else if ($("#rare_item_filter").val() == 0 && $("#rare_item_filter").val() != "") {
                    //                        rare_item_filter = "non_rare_item";
                    //                    }
                    //                    if ($("#stock_status_filter").val().length) {
                    //                        selected_statuses = $("#stock_status_filter").val();
                    //                    }
                    console.log(show_all);
                    $(".generate-po-row").each(function() {
                        var show_row = true;
                        var form_submit = false;
                        var pre_selected_vendors = $("#selected_vendors").val().split("|");
                        // if (selected_statuses.length) {
                        //     var status_class_found = 0;
                        //     for (var status_counter = 0; status_counter < selected_statuses.length; status_counter++) {
                        //         if ($(this).hasClass(selected_statuses[status_counter])) {
                        //             status_class_found = 1;
                        //         }
                        //     }
                        //     if (!status_class_found) {
                        //         show_row = false;
                        //     }
                        // }
                        if (!show_all) {
                            var selected_vendor_class_found = 0;
                            for (var vendor_counter = 0; vendor_counter < selected_vendors.length; vendor_counter++) {
                                if ($(this).hasClass("primary_vendor_" + selected_vendors[vendor_counter])) {
                                    selected_vendor_class_found = 1;
                                }
                                if (pre_selected_vendors.length && pre_selected_vendors[0] != '') {
                                    if (jQuery.inArray(selected_vendors[vendor_counter], pre_selected_vendors) != -1) {} else {
                                        form_submit = true;
                                    }
                                }
                            }
                            if (!selected_vendor_class_found) {
                                show_row = false;
                            }
                        } else {
                            console.log(pre_selected_vendors);
                            if (pre_selected_vendors.length && pre_selected_vendors[0] != '' && show_all) {
                                form_submit = true;
                            }
                            show_row = true;
                        }
                        if (form_submit == true) {
                            var vendors_selected = $("#primary_vendor_filter").val();
                            $("#selected_vendors").val("");
                            if (vendors_selected) {
                                $("#selected_vendors").val(vendors_selected.join('|'));
                            }
                            $("#page-loader").show();
                            $("#sort-form").submit();
                        } else {


                            if (!show_row) {
                                $(this).hide();
                            } else {
                                $(this).show();
                            }
                        }
                    });
                });

            });
        </script>
    <?php
    }

    // sync vendor product mapping table
    public function sync_vendor_product_mapping_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'vendor_product_mapping';
        $ajaxResponse = [];
        $ajaxResponse['success'] = false;
        $truncate = "TRUNCATE TABLE " . $table;
        $wpdb->query($truncate);

        $sql = "SELECT * FROM " . $wpdb->prefix . "postmeta pm join {$wpdb->prefix}posts p on p.id = pm.post_id WHERE meta_key LIKE 'wcvm'";
        $results = $wpdb->get_results($sql);
        foreach ($results as $result) {
            $metaValues = unserialize($result->meta_value);
            foreach ($metaValues as $metaValue) {
                //$primaryVendor = get_post_meta($result->post_id, 'wcvm_primary', TRUE);
                //if ($metaValue == $primaryVendor) {
                $insert_data['post_id'] = $result->post_id;
                $insert_data['vendor_id'] = $metaValue;
                $insert_data['vendor_sku'] = get_post_meta($insert_data['post_id'], 'wcvm_' . $metaValue . '_sku', true);

                $insert_data['vendor_price'] = get_post_meta($insert_data['post_id'], 'wcvm_' . $metaValue . '_price_last', true);
                $format = array('%d', '%d', '%s', '%d');
                $insert = $wpdb->insert($table, $insert_data, $format);
                //}
            }
        }
        if ($insert) {
            $total_rows = $wpdb->get_results("SELECT count(*) AS total_rows FROM " . $table);
            $ajaxResponse['success'] = true;
            $ajaxResponse['inserted_rows'] = $total_rows[0]->total_rows;
        }
        exit(json_encode($ajaxResponse));
    }

    // sync vendor po lookup table
    public function sync_vendor_po_lookup_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'vendor_po_lookup';
        $ajaxResponse = [];
        $ajaxResponse['success'] = false;
        $truncate = "TRUNCATE TABLE " . $table;
        $wpdb->query($truncate);

        //        $sql = "INSERT INTO `{$wpdb->prefix}vendor_po_lookup`(`product_id`, `sku`, `regular_price`, `stock_status`, `stock`, `threshold_low`, `threshold_reorder`, `reorder_qty`, `rare`, `category`, `vendor_id`, `vendor_name`, `vendor_sku`, `vendor_price`)
        //				SELECT distinct p.id productid,  pm.meta_value as sku, r.meta_value as regular_price,
        //				ss.meta_value as stock_status,s.meta_value as stock,tl.meta_value as threshold_low,tr.meta_value as threshhold_reorder,tq.meta_value as reorder_qty
        //				,rr.meta_value as rare,n.name as Cat,
        //				v.vendor_id,v.vendor_name,v.vendor_sku,v.vendor_price
        //				FROM {$wpdb->prefix}posts p
        //				 join {$wpdb->prefix}postmeta pm on pm.post_id = p.id and pm.meta_key = '_sku'
        //				 join {$wpdb->prefix}postmeta r on r.post_id = p.id and r.meta_key = '_regular_price'
        //
        //				 join {$wpdb->prefix}postmeta ss on ss.post_id = p.id and ss.meta_key = '_stock_status'
        //				 join {$wpdb->prefix}postmeta s on s.post_id = p.id and s.meta_key = '_stock'
        //				 join {$wpdb->prefix}postmeta tl on tl.post_id = p.id and tl.meta_key = 'wcvm_threshold_low'
        //				 join {$wpdb->prefix}postmeta tr on tr.post_id = p.id and tr.meta_key = 'wcvm_threshold_reorder'
        //				 join {$wpdb->prefix}postmeta tq on tq.post_id = p.id and tq.meta_key = 'wcvm_reorder_qty'
        //				 join {$wpdb->prefix}postmeta rr on rr.post_id = p.id and rr.meta_key = 'wcvm_rare'
        //				 join (
        //					select tr.object_id as postid,GROUP_CONCAT(te.name) as name
        //				from {$wpdb->prefix}term_relationships tr
        //				join {$wpdb->prefix}term_taxonomy t on t.term_taxonomy_id = tr.term_taxonomy_id and t.taxonomy in ('product_cat','product_tag')
        //				join {$wpdb->prefix}terms te on te.term_id = t.term_id
        //
        //
        //				group by tr.object_id
        //				) n on n.postid = p.id
        //				join (
        //				select post_id,GROUP_CONCAT(vendor_id) as vendor_id,GROUP_CONCAT(vendor_sku) as vendor_sku,GROUP_CONCAT(vendor_price) as vendor_price,GROUP_CONCAT(p.post_title) as vendor_name
        //				from {$wpdb->prefix}vendor_product_mapping  vp
        //				join {$wpdb->prefix}posts p on p.ID = vp.vendor_id and p.post_type = 'wcvm-vendor'
        //				group by post_id
        //				) v on v.post_id = p.id
        //				where p.post_type = 'product'";
        /*$sql = "INSERT INTO `{$wpdb->prefix}vendor_po_lookup`(`product_id`, `product_title`, `sku`, `regular_price`, `stock_status`, `stock`, `threshold_low`, `threshold_reorder`, `reorder_qty`, `rare`, `category`, `vendor_id`, `vendor_name`, `vendor_sku`, `vendor_price`, `primary_vendor_id`, `primary_vendor_name`)
        SELECT distinct p.id productid, p.post_title as product_title,pm.meta_value as sku, r.meta_value as regular_price,
        ss.meta_value as stock_status,s.meta_value as stock,tl.meta_value as threshold_low,tr.meta_value as threshhold_reorder,tq.meta_value as reorder_qty
        ,rr.meta_value as rare,n.name as Cat,
        v.vendor_id,v.vendor_name,v.vendor_sku,v.vendor_price
        ,z.Primary_vendor_id,z.primary_vendor_name
        FROM {$wpdb->prefix}posts p
        join {$wpdb->prefix}postmeta pm on pm.post_id = p.id and pm.meta_key = '_sku'
        join {$wpdb->prefix}postmeta r on r.post_id = p.id and r.meta_key = '_regular_price'

        join {$wpdb->prefix}postmeta ss on ss.post_id = p.id and ss.meta_key = '_stock_status'
        join {$wpdb->prefix}postmeta s on s.post_id = p.id and s.meta_key = '_stock'
        join {$wpdb->prefix}postmeta tl on tl.post_id = p.id and tl.meta_key = 'wcvm_threshold_low'
        join {$wpdb->prefix}postmeta tr on tr.post_id = p.id and tr.meta_key = 'wcvm_threshold_reorder'
        join {$wpdb->prefix}postmeta tq on tq.post_id = p.id and tq.meta_key = 'wcvm_reorder_qty'
        join {$wpdb->prefix}postmeta rr on rr.post_id = p.id and rr.meta_key = 'wcvm_rare'
        join (
        select tr.object_id as postid,GROUP_CONCAT(te.name) as name
        from {$wpdb->prefix}term_relationships tr
        join {$wpdb->prefix}term_taxonomy t on t.term_taxonomy_id = tr.term_taxonomy_id and t.taxonomy in ('product_cat','product_tag')
        join {$wpdb->prefix}terms te on te.term_id = t.term_id

        group by tr.object_id
        ) n on n.postid = p.id
        join (
        select post_id,GROUP_CONCAT(vendor_id) as vendor_id,GROUP_CONCAT(vendor_sku) as vendor_sku,GROUP_CONCAT(vendor_price) as vendor_price,GROUP_CONCAT(p.post_title) as vendor_name
        from {$wpdb->prefix}vendor_product_mapping vp
        join {$wpdb->prefix}posts p on p.ID = vp.vendor_id and p.post_type = 'wcvm-vendor'
        group by post_id
        ) v on v.post_id = p.id
        join (
        select p.ID as postid, pm.meta_value as Primary_vendor_id,pp.post_title as primary_vendor_name
        from {$wpdb->prefix}posts p
        join {$wpdb->prefix}postmeta pm on p.ID = pm.post_id and pm.meta_key = 'wcvm_primary'
        join {$wpdb->prefix}posts pp on pp.ID = pm.meta_value and pp.post_type= 'wcvm-vendor'
        where p.post_type = 'product'
        ) z on z.postid = p.ID
        where p.post_type = 'product'";
        $insert = $wpdb->query($sql);*/
        global $wpdb;
        $table = $wpdb->prefix . 'vendor_po_lookup';
        $args = array(
            'post_status' => 'publish',
            'post_type' => 'product',
            'posts_per_page' => -1
        );
        $loop = new WP_Query($args);

        $counter = 0;
        $truncate = "TRUNCATE TABLE " . $table;
        $wpdb->query($truncate);

        foreach ($loop->posts as $singleItem) {
            $counter++;
            $product = wc_get_product($singleItem->ID);
            // Get Product ID
            $id = $product->get_id();
            // Get Product General Info
            $type = $product->get_type();
            $name = $product->get_name();
            $slug = $product->get_slug();
            $date_created = $product->get_date_created();
            $date_modified = $product->get_date_modified();
            $status = $product->get_status();
            $featured = $product->get_featured();
            $catalog_visibility = $product->get_catalog_visibility();
            $description = $product->get_description();
            $short_description = $product->get_short_description();
            $sku = $product->get_sku();
            $menu_order = $product->get_menu_order();
            $virtual = $product->get_virtual();
            $link = get_permalink($product->get_id());
            // Get Product Prices
            $price = $product->get_price();
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $date_on_sale_from = $product->get_date_on_sale_from();
            $date_on_sale_to = $product->get_date_on_sale_to();
            $total_sales = $product->get_total_sales();
            // Get Product Tax, Shipping & Stock
            $product->get_tax_status();
            $product->get_tax_class();
            $product->get_manage_stock();
            $stock = $product->get_stock_quantity();
            $stock_status = $product->get_stock_status();
            $product->get_backorders();
            $product->get_sold_individually();
            $product->get_purchase_note();
            $product->get_shipping_class_id();
            // Get Product Dimensions
            $product->get_weight();
            $product->get_length();
            $product->get_width();
            $product->get_height();
            // $product->get_dimensions();
            // Get Linked Products
            $product->get_upsell_ids();
            $product->get_cross_sell_ids();
            $product->get_parent_id();
            // Get Product Variations and Attributes
            $product->get_children(); // get variations
            $product->get_attributes();
            $product->get_default_attributes();
            $product->get_attribute('attributeid'); //get specific attribute value
            // Get Product Taxonomies
            // $product->get_categories();
            $product->get_category_ids();
            $product->get_tag_ids();
            // $price = $product->get_price_html();
            if (empty($stock)) {
                $stock = 0;
            }
            $cat_names = wp_get_post_terms($id, 'product_cat', ['fields' => 'names']);
            $tagsCats = implode(', ', $cat_names);

            $tag_names = wp_get_post_terms($product->get_id(), 'product_tag', ['fields' => 'names']);
            if ($tag_names) {
                if ($tagsCats) {
                    $tagsCats .= ",";
                }
                $tagsCats .= implode(', ', $tag_names);
            }
            $vendor_ids = (get_post_meta($id, 'wcvm', true));
            if ($vendor_ids) {
                $vendor_id_array = [];
                $vendor_names = [];
                $vendor_skus = [];
                $vendor_price = [];
                $vendor_price_bulk = [];
                $vendor_price_notes = [];
                $vendor_price_link = [];
                if ($vendor_ids) {
                    foreach ($vendor_ids as  $vendor_id) {
                        $vendor_id_array[] = $vendor_id;
                        $vendor_names[] = get_the_title($vendor_id);
                        $vendor_skus[] = get_post_meta($id, 'wcvm_' . $vendor_id . '_sku', true);
                        $vendor_price[] = get_post_meta($id, 'wcvm_' . $vendor_id . '_price_last', true);
                        $vendor_price_bulk[] = get_post_meta($id, 'wcvm_' . $vendor_id . '_price_bulk', true);
                        $vendor_price_notes[] = get_post_meta($id, 'wcvm_' . $vendor_id . '_price_notes', true);
                        $vendor_price_link[] = get_post_meta($id, 'wcvm_' . $vendor_id . '_link', true);
                    }
                }

                $insert_data['product_id'] = $id;
                $insert_data['product_title'] = $name;
                $insert_data['sku'] =  $sku;
                $insert_data['regular_price'] = $regular_price;
                $insert_data['stock_status'] = $stock_status;
                $insert_data['stock'] = $stock;
                $insert_data['threshold_low'] = 0;
                $insert_data['threshold_reorder'] = 0;
                $insert_data['reorder_qty'] = 0;
                $insert_data['rare'] = 0;
                $insert_data['category'] = $tagsCats;
                $insert_data['vendor_id'] = implode(',', $vendor_id_array);
                $insert_data['vendor_name'] = implode(',', $vendor_names);
                $insert_data['vendor_sku'] =  implode(',', $vendor_skus);
                $insert_data['vendor_link'] = implode(',', $vendor_price_link);
                $insert_data['vendor_price_bulk'] = implode(',', $vendor_price_bulk);
                $insert_data['vendor_price_notes'] = implode(', ', $vendor_price_notes);
                $insert_data['vendor_price'] = implode(', ', $vendor_price);
                $insert_data['primary_vendor_id'] = get_post_meta($id, 'wcvm_primary', true);
                $insert_data['primary_vendor_name'] = get_the_title($insert_data['primary_vendor_id']);
                $insert_data['on_order'] = 0;
                $insert_data['sale_30_days'] = 0;
                $insert_data['order_qty'] = 0;
                $insert_data['on_vendor_bo'] = 0;
                $insert_data['new'] = 0;
                $insert = $wpdb->insert($table, $insert_data);
            }
        }
        wp_reset_postdata();
        //        $productOrderData = $wpdb->get_results("select product_id,group_concat(order_status) as order_status,group_concat(order_quantity) as order_quantity
        //        from
        //        (
        //        SELECT product_id,o.post_status as order_status ,sum(product_ordered_quantity) as order_quantity
        //        FROM `wp_vendor_purchase_orders_items` p
        //        join wp_vendor_purchase_orders o on o.id = p.vendor_order_idfk
        //        where o.post_status = 'on-order' OR o.post_status LIKE '%back-order%'
        //        group by product_id,o.post_status
        //        )p
        //        group by product_id");
        $productOrderData = $wpdb->get_results("select product_id,group_concat(order_status) as order_status,group_concat(order_quantity) as order_quantity
        from
        (
        SELECT product_id,o.post_status as order_status , case when o.post_status = 'on-order'
        then sum(product_ordered_quantity) when o.post_status like '%back-order%' then sum(product_quantity_back_order) end as order_quantity
        FROM `wp_vendor_purchase_orders_items` p
        join wp_vendor_purchase_orders o on o.id = p.vendor_order_idfk
        where o.post_status = 'on-order' OR o.post_status LIKE '%back-order%'
        group by product_id,o.post_status
        )p
        group by product_id");

        if ($productOrderData) {
            foreach ($productOrderData as $singleRow) {
                $backOrders = 0;
                $onOrders = 0;
                $explodedStatusData = explode(",", $singleRow->order_status);
                $explodedCountData = explode(",", $singleRow->order_quantity);
                for ($i = 0; $i < count($explodedStatusData); $i++) {
                    if (strpos($explodedStatusData[$i], "back-order") !== false) {
                        $backOrders = (int)$backOrders + (int)$explodedCountData[$i];
                    }
                    if (strpos($explodedStatusData[$i], "on-order") !== false) {
                        $onOrders = (int)$onOrders + (int)$explodedCountData[$i];
                    }
                }
                $updateData['on_order'] = $onOrders;
                $updateData['on_vendor_bo'] = $backOrders;
                $where['product_id'] = $singleRow->product_id;
                $wpdb->update('wp_vendor_po_lookup', $updateData, $where);
            }
        }
        $sql = "SELECT
                    items.meta_value product_id,
                    SUM(quantity.meta_value) quantity
                FROM
                    wp_posts orders
                JOIN
                    wp_woocommerce_order_items carts
                ON
                    carts.order_id = orders.id AND carts.order_item_type = 'line_item'
                JOIN
                    wp_woocommerce_order_itemmeta items
                ON
                    items.order_item_id = carts.order_item_id AND items.meta_key = '_product_id'
                JOIN
                    wp_woocommerce_order_itemmeta quantity
                ON
                    quantity.order_item_id = items.order_item_id AND quantity.meta_key = '_qty'
                WHERE
                    orders.post_type = 'shop_order' AND orders.post_date > cast(DATE_SUB( now(), INTERVAL 300 DAY ) as date)
                GROUP BY product_id";
        $data = $wpdb->get_results($sql);
        if ($data) {
            foreach ($data as $single_row) {
                $updateSaleData['sale_30_days'] = $single_row->quantity;
                $where['product_id'] = $single_row->product_id;
                $wpdb->update('wp_vendor_po_lookup', $updateSaleData, $where);
            }
        }
        $sql = "select p.id as product_id
        from wp_posts p 
        join wp_postmeta m on m.post_id = p.ID and m.meta_key = 'wcvm_new'
        where p.post_type = 'product' and m.meta_value = 1";
        $data = $wpdb->get_results($sql);
        if ($data) {
            foreach ($data as $single_row) {
                $updateNewData['new'] = 1;
                $where['product_id'] = $single_row->product_id;
                $wpdb->update('wp_vendor_po_lookup', $updateNewData, $where);
            }
            update_option('_vendor_management_last_date', date('m-d-Y H:i:s'));
            $ajaxResponse['success'] = true;
        }

        $sql = "select * from wp_vendor_po_lookup";
        $data = $wpdb->get_results($sql);
        if($data) {
            $percent_value = '';
            foreach ($data as $single_row) {
                $thirty_days_sale = $single_row->sale_30_days;
                    if($thirty_days_sale == 0) {
                        $thirty_days_sale = 1;
                    }
                $percent_value = $single_row->stock / $thirty_days_sale * 100;
                $updateNewData['stock_30_days_sale_percent'] = $percent_value;
                $where['product_id'] = $single_row->product_id;
                $wpdb->update('wp_vendor_po_lookup', $updateNewData, $where);
            }  
        }
        $deleteQuery = "DELETE FROM wp_vendor_po_lookup WHERE id  NOT IN (
            SELECT id 
            FROM `wp_vendor_po_lookup` 
            WHERE (stock =1 and sale_30_days=1) or (stock =2 and sale_30_days=2) or (stock =2 and sale_30_days=3)
            or (stock =3 and sale_30_days=4) or (stock =3 and sale_30_days=5) or (stock =4 and sale_30_days=6)
            or (stock =4 and sale_30_days=7) or (stock =5 and sale_30_days=8) or (stock =5 and sale_30_days=9)
            or (stock =6 and sale_30_days=11) 
            
            UNION
            
            SELECT id 
            FROM `wp_vendor_po_lookup` 
            WHERE (`stock` <= (`sale_30_days`/2))
                )
                                                      ";
        $wpdb->query($deleteQuery);

        if ($insert) {
            $total_rows = $wpdb->get_results("SELECT count(*) AS total_rows FROM " . $table);
            $ajaxResponse['success'] = true;
            $ajaxResponse['inserted_rows'] = $total_rows[0]->total_rows;
        }
        exit(json_encode($ajaxResponse));
    }

    // update vendor product mapping table
    public function update_vendor_po_lookup()
    {
        global $wpdb;
        $ajaxResponse = [];
        $ajaxResponse['success'] = false;
        $sql = "SELECT
                    items.meta_value product_id,
                    SUM(quantity.meta_value) quantity
                FROM
                    wp_posts orders
                JOIN
                    wp_woocommerce_order_items carts
                ON
                    carts.order_id = orders.id AND carts.order_item_type = 'line_item'
                JOIN
                    wp_woocommerce_order_itemmeta items
                ON
                    items.order_item_id = carts.order_item_id AND items.meta_key = '_product_id'
                JOIN
                    wp_woocommerce_order_itemmeta quantity
                ON
                    quantity.order_item_id = items.order_item_id AND quantity.meta_key = '_qty'
                WHERE
                    orders.post_type = 'shop_order' AND orders.post_date > cast(DATE_SUB( now(), INTERVAL 300 DAY ) as date)
                GROUP BY product_id";
        $data = $wpdb->get_results($sql);
        if ($data) {
            foreach ($data as $single_row) {
                $updateData['sale_30_days'] = $single_row->quantity;
                $where['product_id'] = $single_row->product_id;
                $wpdb->update('wp_vendor_po_lookup', $updateData, $where);
            }
        }

        //        $sql = 'SELECT substring(quantity.meta_key,8,POSITION("_qty" IN quantity.meta_key)-8) as product_id, orders.post_status, SUM(quantity.meta_value) as quantity
        //                FROM
        //                    wp_posts orders
        //                JOIN
        //                    wp_postmeta quantity
        //                ON
        //                    quantity.post_id = orders.ID AND quantity.meta_key like "wcvmgo_%_qty"
        //                WHERE
        //                    orders.post_type = "wcvm-order" AND orders.post_status IN ("on-order", "back-order")
        //                GROUP BY
        //                    quantity.meta_key, orders.post_status';
        //        $data = $wpdb->get_results($sql);
        //        if ($data) {
        //            foreach ($data as $single_row) {
        //                $updateOnOrderData = [];
        //                if ($single_row->post_status == 'on-order') {
        //                    $updateOnOrderData['on_order'] = $single_row->quantity;
        //                } else if ($single_row->post_status == 'back-order') {
        //                    $updateOnOrderData['on_vendor_bo'] = $single_row->quantity;
        //                }
        //
        //                $where['product_id'] = $single_row->product_id;
        //                $wpdb->update('wp_vendor_po_lookup', $updateOnOrderData, $where);
        //            }
        //        }
        $sql = "select p.id as product_id
                from wp_posts p 
                join wp_postmeta m on m.post_id = p.ID and m.meta_key = 'wcvm_new'
                where p.post_type = 'product' and m.meta_value = 1";
        $data = $wpdb->get_results($sql);
        if ($data) {
            foreach ($data as $single_row) {
                $updateNewData['new'] = 1;
                $where['product_id'] = $single_row->product_id;
                $wpdb->update('wp_vendor_po_lookup', $updateNewData, $where);
            }
            update_option('_vendor_management_last_date', date('m-d-Y H:i:s'));
            $ajaxResponse['success'] = true;
        }
        exit(json_encode($ajaxResponse));
    }

    public function saveVendorPage()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_wcvim_vendor_save']) && wp_verify_nonce($_POST['_wcvim_vendor_save'], 'save')) {
            $_POST = array_map('trim', $_POST);
            $data = array(
                'post_type' => 'wcvm-vendor',
                'post_status' => 'publish',
                'post_title' => $_POST['post_title'],
                'post_content' => $_POST['post_content'],
            );
            if (empty($_POST['ID'])) {
                $data['ID'] = wp_insert_post($data);
            } else {
                $data['ID'] = $_POST['ID'];
                $data['ID'] = wp_update_post($data);
            }
            if ($_POST['website'] && !preg_match('$https?://$i', $_POST['website'])) {
                $_POST['website'] = 'http://' . $_POST['website'];
            }
            update_post_meta($data['ID'], 'code', $_POST['code']);
            update_post_meta($data['ID'], 'title_short', $_POST['title_short']);
            update_post_meta($data['ID'], 'post_title', $_POST['post_title']);
            update_post_meta($data['ID'], 'website', $_POST['website']);
            update_post_meta($data['ID'], 'phone', $_POST['phone']);
            update_post_meta($data['ID'], 'contact_name', $_POST['contact_name']);
            update_post_meta($data['ID'], 'contact_email', $_POST['contact_email']);
            update_post_meta($data['ID'], 'contact_phone', $_POST['contact_phone']);
            update_post_meta($data['ID'], 'post_content', $_POST['post_content']);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk-wcvm-vendors') && isset($_POST['action']) && $_POST['action'] == 'delete') {
            foreach ($_POST['post'] as $id) {
                wp_delete_post($id);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'bulk-wcvm-vendors') && isset($_POST['action2']) && $_POST['action2'] == 'delete') {
            foreach ($_POST['post'] as $id) {
                wp_delete_post($id);
            }
        }
    }

    public function vendorManagemetMenuPage()
    {
    ?>
        <div class="wrap">

            <h1><?= esc_html__('Vendors', 'wcvim') ?> <a href="#edit-form" class="page-title-action" data-action="wcvim-edit"><?= esc_html__('Add Vendor') ?></a></h1>
            <div id="wcvim-save" style="display: none;">

                <form action="<?php echo admin_url() . 'admin.php?page=vendor-management'; ?>" method="post" style="width: 100%" data-name="wcvim-save">
                    <table style="width: 100%">
                        <tr>
                            <td width="20%"><label for="code"><?= esc_html__('Code', 'wcvim') ?></label> <span style="color: red">*</span></td>
                            <td width="80%"><input type="text" id="code" name="code" value="" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td><label for="title_short"><?= esc_html__('Short Name', 'wcvim') ?></label> <span style="color: red">*</span></td>
                            <td><input type="text" id="title_short" name="title_short" value="" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td><label for="post_title"><?= esc_html__('Name', 'wcvim') ?></label> <span style="color: red">*</span></td>
                            <td><input type="text" id="post_title" name="post_title" value="" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td><label for="website"><?= esc_html__('Website', 'wcvim') ?></label></td>
                            <td><input type="text" id="website" name="website" value="" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td><label for="phone"><?= esc_html__('Phone', 'wcvim') ?></label></td>
                            <td><input type="text" id="phone" name="phone" value="" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td><label for="contact_name"><?= esc_html__('Contact Name', 'wcvim') ?></label></td>
                            <td><input type="text" id="contact_name" name="contact_name" value="" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td><label for="contact_email"><?= esc_html__('Contact Email', 'wcvim') ?></label></td>
                            <td><input type="text" id="contact_email" name="contact_email" value="" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td><label for="contact_phone"><?= esc_html__('Contact Phone', 'wcvim') ?></label></td>
                            <td><input type="text" id="contact_phone" name="contact_phone" value="" style="width: 100%;"></td>
                        </tr>
                        <tr>
                            <td><label for="post_content"><?= esc_html__('Notes', 'wcvim') ?></label></td>
                            <td><textarea id="post_content" name="post_content" style="width: 100%;height:100px;"></textarea></td>
                        </tr>
                    </table>
                    <?php submit_button(__('Save Vendor', 'wcvim')) ?>
                    <div><span style="color: red">*</span> - <?= esc_html__('required field', 'wcvim') ?></div>
                    <input type="hidden" name="ID" value="">
                    <?php wp_nonce_field('save', '_wcvim_vendor_save') ?>
                </form>
            </div>
            <form action="" method="post">
                <?php
                $this->saveVendorPage(); ?>
            </form>
            <div id="ajax-response"></div>
            <br class="clear">
            <?php
            global $wpdb;
            $query = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = 'wcvm-vendor' AND post_status = 'publish'";
            $data = $wpdb->get_results($query);
            if ($data) {
            ?>
                <style>
                    .wp-list-table.wcvm-vendors .table-view-list td {
                        vertical-align: middle;
                    }

                    .wp-list-table.wcvm-vendors .table-view-list.manage-column {
                        font-size: 10px;
                    }
                </style>
                <table id="vendors-list" class="wp-list-table widefat fixed striped pages table-view-list wcvm-vendors">
                    <thead style="font-weight:bold">
                        <tr>
                            <th scope="col" class="manage-column column-author">Code</th>
                            <th scope="col" class="manage-column column-author">Name</th>
                            <th scope="col" class="manage-column column-author">Contact</th>
                            <th scope="col" class="manage-column column-author">Notes</th>
                            <!--<th scope="col" class="manage-column column-author">Actions</th>-->
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        foreach ($data as $single_row) {
                            $code = get_post_meta($single_row->ID, 'code', true);
                            $contact_name = get_post_meta($single_row->ID, 'contact_name', true);
                            $phone = get_post_meta($single_row->ID, 'phone', true);
                            $title_short = get_post_meta($single_row->ID, 'title_short', true);
                            $post_title = get_post_meta($single_row->ID, 'post_title', true);
                            $website = get_post_meta($single_row->ID, 'website', true);
                            $contact_email = get_post_meta($single_row->ID, 'contact_email', true);
                            $contact_phone = get_post_meta($single_row->ID, 'contact_phone', true);
                            $post_content = get_post_meta($single_row->ID, 'post_content', true); ?>
                            <tr>
                                <td><?php echo $code . ' / ' . $title_short . "<br>"; ?>
                                    <?php echo '<a href="#edit-record" data-action="wcvim-edit" data-code="' . $code . '" data-contact-name="' . $contact_name . '" data-contact-phone="' . $contact_phone . '" data-post-title="' . $post_title . '"  data-title-short="' . $title_short . '"data-phone="' . $phone . '"data-email="' . $contact_email . '"data-website="' . $website . '" data-record="' . esc_attr(json_encode($single_row)) . '">' . esc_html__('Edit', 'wcvim') . '</a>' ?>
                                </td>
                                <td><?php echo $post_title . "<br>" . $phone . "<br>" . $website; ?></td>
                                <td><?php
                                    if ($contact_name) {
                                        echo $contact_name . "<br>";
                                    }
                                    if ($contact_email) {
                                        echo $contact_email . "<br>";
                                    }
                                    if ($contact_phone) {
                                        echo $contact_phone . "<br>";
                                    } ?></td>
                                <td><?php echo $post_content; ?></td>
                                <!--<td></td>-->
                            </tr>
                        <?php
                        } ?>
                    </tbody>
                </table>
                <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
                <!--                <script src="https://code.jquery.com/jquery-3.5.1.js"></script>-->
                <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
                <script>
                    jQuery(document).ready(function() {
                        jQuery('#vendors-list').DataTable({
                            "pageLength": 25
                        });
                    });
                </script>
                <style>
                    table.dataTable tr.odd {
                        background-color: white;
                        border: 1px lightgrey;
                    }

                    table.dataTable tr.even {
                        background-color: #F1F1F1;
                        border: 1px lightgrey;
                    }
                </style>
            <?php
            } ?>
        </div>
        <?php add_thickbox(); ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                "use strict";
                jQuery("a[data-action=wcvim-edit]").click(function() {
                    var element = jQuery(this);
                    tb_show("Add / Edit Vendor", "#TB_inline?inlineId=wcvim-save&height=450", false);
                    var data = element.attr("data-record");
                    var phone = element.attr("data-phone");
                    var code = element.attr("data-code");
                    var contact_name = element.attr("data-contact-name");
                    var contact_email = element.attr("data-email");
                    var website = element.attr("data-website");
                    var title_short = element.attr("data-title-short");
                    var title_post = element.attr("data-post-title");
                    var contact_phone = element.attr("data-contact-phone");
                    console.log(data);
                    jQuery("#code").val(code);
                    jQuery("#title_short").val(title_short);
                    jQuery("#post_title").val(title_post);
                    jQuery("#website").val(website);
                    jQuery("#phone").val(phone);
                    jQuery("#contact_name").val(contact_name);
                    jQuery("#contact_email").val(contact_email);
                    jQuery("#contact_phone").val(contact_phone);
                    var container = jQuery("#TB_ajaxContent");
                    if (data) {
                        jQuery.each(JSON.parse(data), function(key, value) {
                            jQuery("[name=" + key + "]", container).val(value);
                        });
                    } else {
                        jQuery('[name!=_wcvim_vendor_save][name!=""][type!=submit]', container).val('');
                    }
                    return false;
                });
                jQuery(document).on('submit', 'form[data-name=wcvim-save]', function() {
                    var form = jQuery(this);
                    var isValid = true;
                    var inputPostName = jQuery('input[name=code]', form);
                    if (inputPostName.val().trim()) {
                        inputPostName.css({
                            border: ''
                        });
                    } else {
                        inputPostName.css({
                            border: '1px solid red'
                        });
                        isValid = false;
                    }
                    var inputTitleShort = jQuery('input[name=title_short]', form);
                    if (inputTitleShort.val().trim()) {
                        inputTitleShort.css({
                            border: ''
                        });
                    } else {
                        inputTitleShort.css({
                            border: '1px solid red'
                        });
                        isValid = false;
                    }
                    var inputPostTitle = jQuery('input[name=post_title]', form);
                    if (inputPostTitle.val().trim()) {
                        inputPostTitle.css({
                            border: ''
                        });
                    } else {
                        inputPostTitle.css({
                            border: '1px solid red'
                        });
                        isValid = false;
                    }
                    return isValid;
                });
            });
        </script>
<?php
    }
}

// define the woocommerce_update_product callback
function wcvimCustomWoocommerceUpdateProduct($product_get_id)
{
    update_option('_product_update_last_date', date('m-d-Y H:i:s'));
}

add_action('woocommerce_update_product', 'wcvimCustomWoocommerceUpdateProduct', 10, 1);

return new WC_Clear_Com_Vendor_Inventory_Management();

<?php
/* Plugin Name: Woocommerce Vendor Inventory Managemnet
 * Plugin URI: #
 * Description: Vendor management Updated!.
 * Author: 
 * Author URI: #
 * Version: 1.0.1
 */

class WC_Clear_Com_Vendor_Inventory_Management {

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScript'));
        add_action('admin_menu', array($this, 'wcvimActionAdminMenu'));
        add_action('admin_menu', array($this, 'wcvimSaveAdminMenu'));
        add_action('admin_menu', array($this, 'wcvimPo'));
        add_action('admin_menu', array($this, 'wcvimReceiveInventory'));
        add_action('admin_menu', array($this, 'wcvimReceiveBackOrderItems'));
        add_action('admin_menu', array($this, 'wcvimgeneratePo'));
        add_action("wp_ajax_generate_po", array($this, "generate_po"));
        add_action("wp_ajax_updateVendorProductMapping", array($this, "updateVendorProductMapping"));
        add_action('plugins_loaded', array($this, 'wcvmcvoActionPluginsLoaded'));
    }

    public function create_vendor_product_mapping() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vendor_product_mapping';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    `product_mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                    `post_id` int(11) NOT NULL,
                    `vendor_id` int(11) NOT NULL,
                    `vendor_sku` varchar(11) DEFAULT NULL,
                    `vendor_price` decimal(11,2) DEFAULT NULL,
                    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                    PRIMARY KEY id (product_mapping_id)
    ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    public function create_vendor_po_lookup() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vendor_po_lookup';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `product_title` varchar(100) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `regular_price` int(11) NOT NULL,
  `stock_status` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL,
  `threshold_low` double NOT NULL,
  `threshold_reorder` int(11) NOT NULL,
  `reorder_qty` int(11) NOT NULL,
  `rare` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `vendor_id` varchar(100) NOT NULL,
  `vendor_name` varchar(100) NOT NULL,
  `vendor_sku` varchar(50) NOT NULL,
  `vendor_link` varchar(100) NOT NULL,
  `vendor_price_bulk` int(11) NOT NULL,
  `vendor_price_notes` varchar(50) NOT NULL,
  `vendor_price` varchar(100) NOT NULL,
  `primary_vendor_id` int(11) NOT NULL,
  `primary_vendor_name` varchar(100) NOT NULL,
    `on_order` int(11) NOT NULL,
  `sale_30_days` int(11) NOT NULL,
  `order_qty` int(11) NOT NULL,
  `on_vendor_bo` int(11) NOT NULL,
  `new` INT(11) NULL,
  PRIMARY KEY id (id)
    ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }

    public function activate_plugin() {
        flush_rewrite_rules();
        $this->create_vendor_product_mapping();
        $this->create_vendor_po_lookup();
    }

    public function enqueueScript() {
        ?>
        <style>
            th{ 
                background-color: white !important;
                font-size: 10px !important;
                padding: 14px !important;
            }
            .wp-list-table.wcvm-orders .manage-column{
                font-size: 10px !important;
            }
            .widefat{
                background-color: #f9f9f9 !important;
            }
            .widefat {
                vertical-align: middle !important;
                word-wrap: break-word !important;    
            }
            table.fixed{
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
//        wp_localize_script('generate-po-script', 'wcvm_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    public function wcvimActionAdminMenu() {
        add_menu_page(__('Vendors Management', 'wcvim'), __('Vendors Management', 'wcvim'), 'manage_options', 'vendor-management', array($this, 'vendorManagemetMenuPage'));
    }

    public function wcvimgeneratePo() {

        add_submenu_page('vendor-management', __('Generate Purchase Order', 'wcvim'), __('Generate Purchase Order', 'wcvim'), 'manage_options', 'generate-purchase-order', array($this, 'generatePurchaseOrder'), 1);
    }

    public function wcvimPo() {

        add_submenu_page('vendor-management', __('View/Edit Purchase Order', 'wcvim'), __('View/Edit Purchase Order', 'wcvim'), 'manage_options', 'wcvm-epo', array($this, 'wcvimPurchaseOrderPage'));
    }

    public function wcvimReceiveInventory() {

        add_submenu_page('vendor-management', __('Receive Inventory', 'wcvim'), __('Receive Inventory', 'wcvim'), 'manage_options', 'wcvm-ri', array($this, 'wcvimReceiveInventoryPage'));
    }

    public function wcvimReceiveBackOrderItems() {

        add_submenu_page('vendor-management', __('Receive Back Order Items', 'wcvim'), __('Receive Back Order Items', 'wcvim'), 'manage_options', 'wcvm-rboi', array($this, 'wcvimReceiveBackOrderItemsPage'));
    }

//    public function wcvimPOPageSlug() {
//        add_submenu_page(
//                null,
//                __('Purchase Order', 'wcvim'),
//                __('Purchase Order', 'wcvim'),
//                'manage_options',
//                'purchase-order',
//                array($this, 'wcvimPurchaseOrderPage')
//        );
//        add_menu_page(__('Update Vendor Product Mapping', 'wcvim'), __('Update Vendor Product Mapping', 'wcvim'), 'manage_options', 'uasortpdate-vendor-product-mapping', array($this, 'updateVendorProductMapping'));
//    }
    public function wcvmcvoActionPluginsLoaded() {
        require_once plugin_dir_path(__FILE__) . 'includes/vendor-management-cols.php';
    }

    public function wcvimPurchaseOrderPage() {
        global $wpdb;
//        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
//            print_r($_POST);die;
//        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['unarchive'])) {
            $order = get_post($_POST['ID']);
            $order->post_status = $order->old_status ? $order->old_status : 'draft';
            wp_update_post($order);
            delete_post_meta($order->ID, 'old_status');
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['archive']) && empty($_POST['print']) && empty($_POST['action'])) {
            $order = get_post($_POST['ID']);
            update_post_meta($order->ID, 'old_status', $order->post_status);
            $order->post_status = 'trash';
            wp_update_post($order);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['print'])) {
//        $order = get_post($_POST['ID']);
//        $wcvmgo_manual = get_post_meta($_POST['ID'], "wcvmgo");
//        $order->wcvmgo = $wcvmgo_manual[0];
//        $vendor = get_post($order->post_parent);
        wp_redirect(site_url('/wp-content/plugins/clear-com-vendor-inventory-management/templates/print-template-page.php?po='.$order->ID));
//        include plugin_dir_u(__FILE__) .'/templates/print-template-page.php?po=1';
        exit();
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['delete'])) {
            wp_delete_post($_POST['ID']);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['delete-all'])) {
//             $sql = "DELETE FROM `wp_posts` WHERE post_type = 'wcvm-order' AND `post_status` = 'trash'";
//                     $wpdb->get_results($sql);

            $query = new WP_Query();
            foreach ($query->query(array(
                'post_type' => 'wcvm-order',
                'suppress_filters' => true,
                'post_status' => 'trash',
                'fields' => 'ids',
            )) as $id) {
                wp_delete_post($id);
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
//            print_r($_POST);die;
            $order = get_post($_POST['ID']);
            $order->post_status = $order->old_status ? $order->old_status : 'draft';
            /* foreach ($_POST['wcvm_threshold_low'] as $productId => $value) {
              update_post_meta($productId, 'wcvm_threshold_low', $value ? $value : '');
              }
              foreach ($_POST['wcvm_threshold_reorder'] as $productId => $value) {
              update_post_meta($productId, 'wcvm_threshold_reorder', $value ? $value : '');
              }
              foreach ($_POST['wcvm_reorder_qty'] as $productId => $value) {
              update_post_meta($productId, 'wcvm_reorder_qty', $value ? $value : '');
              } */
            if (!empty($_POST['__order_qty'])) {
                $vendorId = get_post_field('post_parent', $_POST['ID'], 'raw');
                if ($_POST['action'] == 'update' || $_POST['action'] == 'add') {
                    $orderId = $_POST['ID'];
                } else {
                    $orderId = wp_insert_post(array(
                        'post_type' => 'wcvm-order',
                        'post_status' => 'draft',
                        'post_parent' => $vendorId,
                    ));
                    update_post_meta($orderId, 'wcvmgo', get_post_meta($_POST['ID'], 'wcvmgo', true));
                }
                foreach ($_POST['__order_qty'] as $productId => $_) {
                    update_post_meta($orderId, 'wcvmgo_' . $productId . '_qty', $_POST['__order_qty'][$productId]);
                    $stamp = strtotime($_POST['__expected_date'][$productId]);
                    if (!$stamp && !empty($_POST['expected_date'])) {
                        $stamp = strtotime($_POST['expected_date']);
                    }
                    if ($stamp) {
                        update_post_meta($orderId, 'wcvmgo_' . $productId . '_date', $stamp);
                    }
                    update_post_meta($orderId, 'wcvmgo_' . $productId, array(
                        'product_id' => $productId,
                        'product_title' => get_post_field('post_title', $productId),
                        'product_sku' => get_post_meta($productId, '_sku', true),
                        'product_price' => get_post_meta($productId, '_price', true),
                        'product_quantity' => $_POST['__order_qty'][$productId],
                        'product_expected_date' => $stamp,
                        'product_rare' => get_post_meta($productId, 'wcvm_rare', true),
                        'product_threshold_low' => get_post_meta($productId, 'wcvm_threshold_low', true),
                        'product_threshold_reorder' => get_post_meta($productId, 'wcvm_threshold_reorder', true),
                        'product_reorder_qty' => get_post_meta($productId, 'wcvm_reorder_qty', true),
                        'vendor_sku' => get_post_meta($productId, 'wcvm_' . $vendorId . '_sku', true),
                        'vendor_link' => get_post_meta($productId, 'wcvm_' . $vendorId . '_link', true),
                        'vendor_price_last' => get_post_meta($productId, 'wcvm_' . $vendorId . '_price_last', true),
                        'vendor_price_bulk' => get_post_meta($productId, 'wcvm_' . $vendorId . '_price_bulk', true),
                        'vendor_price_notes' => get_post_meta($productId, 'wcvm_' . $vendorId . '_price_notes', true),
                    ));
                }
                if (!empty($_POST['expected_date'])) {
                    $order = get_post($_POST['ID']);
                    $order->post_status = 'draft';
                    update_post_meta($orderId, 'po_expected_date', strtotime($_POST['expected_date']));
                    wp_update_post($order);
                    wp_redirect(site_url('/wp-admin/admin.php?page=wcvm-epo&status=' . $order->post_status) . '#order' . $order->ID);
                }
            }
        }
        require_once plugin_dir_path(__FILE__) . 'templates/purchase_order_page.php';
    }

    public function wcvimReceiveInventoryPage() {
        require_once plugin_dir_path(__FILE__) . 'templates/receive-inventory.php';
    }

    public function wcvimReceiveBackOrderItemsPage() {
        require_once plugin_dir_path(__FILE__) . 'templates/receive-back-order-items.php';
    }

    public function wcvimSaveAdminMenu() {
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

    public function extra_tablenav($which) {
        if ($which == 'top') {
            $product_update_last_date = get_option('_product_update_last_date');
            $vendor_management_last_date = get_option('_vendor_management_last_date');
            ?>
            <div style="padding-bottom: 10px;clear: both;">
                <?php
                //echo '<input type="submit" name="wcvm_save" class="button button-primary" value="' . esc_html__('Update') . '">';
                //echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                ?>
                <input type="hidden" name="baseUrl" id="baseUrl" value="<?php echo plugin_dir_url(__FILE__); ?>">
                <?php
                echo '<input type="submit" id="generate-po-button" name="wcvm_save" class="button button-primary" value="' . esc_html__('Generate') . '">';
                if ($product_update_last_date >= $vendor_management_last_date || 1) {
                    ?>
                    <a href="#" class="button button-primary" id="sync-vendor"><?= esc_attr__('Sync Data', 'wcvm') ?></a>
                    <div style="margin-top:10px;" class="text-danger">
                        <span style="padding:5px; font-size:12px"> Product Update Last Date: <?php echo $product_update_last_date; ?> Vendor Management Last Date: <?php echo $vendor_management_last_date; ?></span>
                    </div>
                <?php } ?>
            </div>
            <div style="float: left;vertical-align: top">
                <select name="new_item_filter" class="vendor_details" id="new_item_filter">
                    <option value="">- <?= esc_html__('New Item', 'wcvm') ?> -</option>
                    <option value="1"><?= esc_html__('Yes', 'wcvm') ?></option>
                    <option value="0"><?= esc_html__('No', 'wcvm') ?></option>
                </select>
                <select name="rare_item_filter" class="vendor_details" id="rare_item_filter">
                    <option value="">- <?= esc_html__('Rare Item', 'wcvm') ?> -</option>
                    <option value="1"><?= esc_html__('Yes', 'wcvm') ?></option>
                    <option value="0"><?= esc_html__('No', 'wcvm') ?></option>
                </select>
                <select name="stock_status_filter" class="vendor_details" id="stock_status_filter" multiple="multiple">
                    <option value="out"><?= esc_html__('OUT', 'wcvm') ?></option>
                    <option value="low"><?= esc_html__('LOW', 'wcvm') ?></option>
                    <option value="reorder"><?= esc_html__('REORDER', 'wcvm') ?></option>
                    <option value="ok"><?= esc_html__('OK', 'wcvm') ?></option>
                </select>
                <select name="primary_vendor_filter" class="vendor_details scrollable" id="primary_vendor_filter" multiple="multiple">
                    <?php
                    global $wpdb;
                    $posts_table = $wpdb->prefix . "posts";
                    $posts_table_sql = ""
                            . "SELECT p.ID,p.post_title, pm.meta_value as title_short
                                FROM " . $wpdb->prefix . "posts p
                                LEFT JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND pm.meta_key = 'title_short'
                                WHERE post_type = 'wcvm-vendor' OR post_type = 'wcvm-vendors' AND post_status = 'publish' ORDER BY p.post_title";
                    $posts = $wpdb->get_results($posts_table_sql);
                    foreach ($posts as $vendor):
                        ?>
                        <option value="<?= esc_attr($vendor->ID) ?>"><?= esc_html($vendor->post_title) ?> (<?= esc_html($vendor->title_short) ?>)</option>
                    <?php endforeach ?>
                </select>
                            <!--<input type="submit" name="filter_action" if="filter_action" class="button" value="<?= esc_attr__('Filter', 'wcvm') ?>">-->
                <a href="#" class="btn btn-primary button" id="filter-vendor"><?= esc_attr__('Filter', 'wcvm') ?></a>
            </div>
            <?php
        } else {
            //echo '<input type="submit" name="wcvm_save" class="button button-primary" value="' . esc_html__('Update') . '">';
            //echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            echo '<input onclick="jQuery(&apos;#save_button_hit&apos;).val(1)" type="submit" name="wcvm_save" class="button button-primary" value="' . esc_html__('Generate') . '">';
        }
    }

    public function generate_po() {
        global $wpdb;

        $ajaxResponse['message'] = 'no post data';
//        print_r($_POST);die;
        $product_IDs = [];
        if (isset($_POST)) {
            $selected_vendors = $_POST['purchase_order_data'];
            $vendor_post_ids = [];
            if ($selected_vendors) {
                foreach ($selected_vendors as $selected_vendor) {
                    if (!array_key_exists($selected_vendor['selected_vendor'], $vendor_post_ids)) {
                        $data = array(
                            'post_type' => 'wcvm-order',
                            'post_status' => 'auto-draft',
                            'post_parent' => $selected_vendor['selected_vendor']
                        );
                        $vendor_post_ids[$selected_vendor['selected_vendor']] = wp_insert_post($data);
                    }
                    $sql = "SELECT * FROM `{$wpdb->prefix}vendor_po_lookup` WHERE id = '" . $selected_vendor['selected_id'] . "'";
                    $productDetails = $wpdb->get_results($sql);
                    $productData = json_decode(json_encode($productDetails[0]), true);
                    $productID = $productData['product_id'];
                    $productQty = $selected_vendor['product_qty'];
                    $vendor_product_ids_data[$selected_vendor['selected_vendor']][] = $productID;
                    $vendor_product_orders_data[$selected_vendor['selected_vendor']][$productID] = array(
                        'product_id' => $productData['product_id'],
                        'product_title' => $productData['product_title'],
                        'product_sku' => $productData['sku'],
                        'product_price' => $productData['regular_price'],
                        'product_quantity' => $productQty,
                        'product_rare' => $productData['rare'],
                        'product_threshold_low' => $productData['threshold_low'],
                        'product_threshold_reorder' => $productData['threshold_reorder'],
                        'product_reorder_qty' => $productData['reorder_qty'],
                        'vendor_sku' => $productData['vendor_sku'],
                        'vendor_price_last' => $productData['vendor_price'],
                        'vendor_link' => $productData['vendor_link'],
                        'vendor_price_bulk' => $productData['vendor_price_bulk'],
                        'vendor_price_notes' => $productData['vendor_price_notes'],
                    );
                    $vendor_product_on_orders_data[$selected_vendor['selected_vendor']][$productID] = array(
                        'vendor' => $selected_vendor['selected_vendor'],
                        'qty' => $productQty,
                        'order' => $vendor_post_ids[$selected_vendor['selected_vendor']],
                        'order_date' => date('Y/m/d H:i:s a'),
                        'expected_date' => ''
                    );
                    $vendor_product_quantities[$selected_vendor['selected_vendor']][$productID] = $productQty;
                }
            }

            $uniquer_vendor_ids = array_keys($vendor_post_ids);

            if ($uniquer_vendor_ids) {
                foreach ($uniquer_vendor_ids as $uniquer_vendor_id) {
                    update_post_meta($vendor_post_ids[$uniquer_vendor_id], 'wcvmgo', $vendor_product_ids_data[$uniquer_vendor_id]);
                    foreach ($vendor_product_ids_data[$uniquer_vendor_id] as $vendor_single_product) {
                        $productIDs = '';
                        update_post_meta($vendor_post_ids[$uniquer_vendor_id], 'wcvmgo_' . $vendor_single_product . '_qty', $vendor_product_quantities[$uniquer_vendor_id][$vendor_single_product]);
                        update_post_meta($vendor_post_ids[$uniquer_vendor_id], 'wcvmgo_' . $vendor_single_product . '_onorder', serialize($vendor_product_on_orders_data[$uniquer_vendor_id][$vendor_single_product]));
                        $productIDs = $vendor_single_product;
//                        if ($productIDs == '') {
//                            $productIDs .= $vendor_single_product;
//                        } else {
//                            $productIDs .= ',' . $vendor_single_product;
//                        }
                        add_post_meta($vendor_post_ids[$uniquer_vendor_id], 'wcvmgo_product_id', $productIDs);
                    }
                }
                $ajaxResponse['redirect_url'] = admin_url() . 'admin.php?page=wcvm-epo&status=auto-draft';
            }
        }
        exit(json_encode($ajaxResponse));
    }

    public function generatePurchaseOrder() {
        $link = admin_url('admin-ajax.php?action=generatePO&post_id=');
        global $wpdb;
        $order_details_table = $wpdb->prefix . "vendor_po_lookup";
        $order_details_table_sql = "SELECT *,
					CASE
						WHEN stock IS NULL THEN 'OUT'
						WHEN CAST(stock as signed) <= 0 THEN 'OUT'
						WHEN CAST(stock as signed) <= threshold_low THEN 'LOW'
						WHEN CAST(stock as signed) <= threshold_reorder THEN 'REORDER'
						ELSE 'OK'
					END stock_status
					FROM " . $order_details_table . "";
        $orderDetails = $wpdb->get_results($order_details_table_sql);
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
            .dropdown-menu>.active>a, .dropdown-menu>.active>a:hover, .dropdown-menu>.active>a:focus {
                color: #fff !important;
                text-decoration: none !important;
                background-color: #428bca !important;
                outline: 0 !important;
            }
            .scrollable{
                overflow: auto !important;
                width: 70px !important; /* adjust this width depending to amount of text to display */
                height: 80px !important; /* adjust height depending on number of options to display */
                border: 1px silver solid !important;
            }
            .scrollable select{
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
            .dropdown-menu{
                font-size: 14px !important;
                font-family: inherit !important;
            }
            .multiselect-container>li {
                margin-bottom: 0;

            }
            .multiselect-container>li>a>label{
                padding: 3px 1px 3px 10px !important;
                font-family: sans-serif;
            }
            .btn{
                font-size: 14px !important;
                font-weight: normal !important;
            }

            th{
                text-align: inherit;
                padding: 8px !important;
                font-size: 10px;
            }
            td{
                text-align: inherit;
                font-size: 10px;
            }
            #wpbody-content{
                background: #f1f1f1;
            }
            #tags_categories{
                width: 100px;
            }
            input[readonly]
            {
                background: transparent;
                border: none;
                box-shadow: none;
            }
            input[type=checkbox]{
                /*                background: white;*/
                border: 1px solid #cbc8c8;
            }
            .center{
                text-align: center;
            }
            .first-cell{
                width: 40px;
            }
            .third-cell{
                width: 75px;
            }
            .fourth-cell{
                width: 200px;
            }
            .fifth-cell{
                width: 200px;
            }
            .sixth-cell{
                width: 65px;
            }
            .seventh-cell{
                width: 55px;
            }
            .eighth-cell{
                width: 55px;    
                padding-top: 5px;
                padding-bottom: 5px;
            }
            .tenth-cell{
                width: 45px;
            }
            .eleventh-cell{
                width: 45px;
            }
            .even{
                background-color: #f9f9f9;
            }
            .vendor-select{
                font-size: 11px !important;
                padding: 0 15px 0 2px !important;
                background-size: 8px 8px !important;
                min-height: 25px !important;
                width: 85px !important;
            }
            table.dataTable thead th, table.dataTable thead td {
                padding: 0px 4px !important;
            }
            .dropdown-toggle {
                background-color: white;
                border-color: #7e8993 !important;
                padding: .160rem .70rem !important;
            }
        </style>
        <div class="wrap wm-vm-go">
            <h1><?= esc_html__('Generate Purchase Orders', 'wcvm') ?></h1>
            <form action="" method="post">
                <!-- <input type="hidden" name="new_item" id="wcvm_new_item" value="">
                <input type="hidden" name="rare_item" id="wcvm_rare_item" value=""> -->
                <?php $this->extra_tablenav('top'); ?>
            </form>
            <div id="my-content-id" style="display:none;">
                <img id="loading_image" src="<?php plugin_dir_path(__FILE__) . '/assets/img/loader.gif' ?>"/>
                <div id="vendor_details">

                </div>
            </div>
            <div id="ajax-response"></div>
            <br class="clear">
        </div>
        <table id="po-items-table" class="" style="margin-top: 25px;">
            <thead>  
                <tr>
                    <th class="center first-cell">New</th>
                    <th class="center first-cell">Rare</th>
                    <th class="center third-cell">CC SKU</th>
                    <th class="center fourth-cell">Vendor SKU</th>
                    <th class="center fifth-cell">Category/Tags</th>
                    <th class="center sixth-cell">Stock<br/>Status</th>
                    <th class="center seventh-cell">Our<br/>Price</th>
                    <th class="center eighth-cell">Select<br>Vendor</th>
                    <th class="center seventh-cell">Vendor<br>Price</th>
                    <th class="center tenth-cell">QTY<br>On<br>Hand</th>
                    <th class="center eleventh-cell">30<br>Days</th>
                    <th class="center seventh-cell">Low<br>Thresh</th>
                    <th class="center seventh-cell">Reorder<br>Thresh</th>
                    <th class="center seventh-cell">Reorder<br>QTY</th>
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
                    if ($orderDetail->rare) {
                        $row_classes .= " rare_item";
                    } else {
                        $row_classes .= " non_rare_item";
                    }

                    if ($orderDetail->new) {
                        $row_classes .= " new_item";
                    } else {
                        $row_classes .= " non_new_item";
                    }
                    $row_classes .= " " . strtolower($orderDetail->stock_status) . " " . "primary_vendor_" . $orderDetail->primary_vendor_id;
                    ?>
                    <tr class="<?php echo $row_classes; ?>" id='row-<?php echo $orderDetail->id ?>'>
                        <td class="center first-cell"><?php echo ($orderDetail->new) ? "&#10004;" : ""; ?></td>
                        <td class="center first-cell"><?php echo ($orderDetail->rare) ? "&#10004;" : ""; ?></td>
                        <td class="center third-cell"><?php echo $orderDetail->sku ?></td>
                        <td class="center fourth-cell"><?php echo $orderDetail->vendor_sku ?></td>
                        <td class="center fifth-cell"><?php echo ($orderDetail->category) ?></td>
                        <td class="center sixth-cell"><?php echo $orderDetail->stock_status ?></td>
                        <td class="center seventh-cell"><?php echo wc_price($orderDetail->regular_price) ?></td>
                        <td class="eighth-cell">
                            <select id="row-selected-vendor-<?php echo $orderDetail->id ?>" class="vendor-select">
                                <?php
                                for ($i = 0; $i < count($vendors); $i++) {
                                    $selected = '';
                                    if ($vendor_ids[$i] == $orderDetail->primary_vendor_id) {
                                        $selected = 'selected';
                                    }
                                    if ($selected = 'selected') {
                                        $selected_vendor_price = $vendor_prices[$i];
                                    }
                                    ?>
                                    <option <?php echo $selected; ?> data-vendor_price="<?php echo get_woocommerce_currency_symbol() . $vendor_prices[$i]; ?>" value="<?php echo $vendor_ids[$i]; ?>"><?php echo $vendors[$i]; ?></option>
                                <?php } ?>
                            </select>
                        </td>
                        <td class="center seventh-cell"><?php echo wc_price($selected_vendor_price); ?></td>
                        <td class="center tenth-cell"><?php echo $orderDetail->stock ?></td>
                        <td class="center eleventh-cell"><?php echo $orderDetail->sale_30_days ?></td>
                        <td class="center seventh-cell"><?php echo $orderDetail->threshold_low ?></td>
                        <td class="center seventh-cell"><?php echo $orderDetail->threshold_reorder ?></td>
                        <td class="center seventh-cell"><?php echo $orderDetail->reorder_qty ?></td>
                        <td class="center seventh-cell"><?php echo $orderDetail->on_order ?></td>
                        <td class="center seventh-cell"><?php echo 'ask' ?></td>
                        <td class="center seventh-cell"><input id='order-quantity-<?php echo $orderDetail->id ?>' type="text" style="width:30px" value="<?php echo $orderDetail->reorder_qty; ?>"></td>
                        <td class="center seventh-cell"><input type="checkbox" class='po-selected-products' value="<?php echo $orderDetail->id ?>"></td>
                    </tr>
                    <?php
                    $even_odd_counter++;
                }
                ?>
            </tbody>
        </table>

        <!-- stylesheet -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css" type="text/css" />
        <!-- <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.6/css/responsive.dataTables.min.css" type="text/css" /> -->
        <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css" type="text/css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">

        <!-- script -->
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
        <!-- <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.6/js/dataTables.responsive.min.js"></script> -->
        <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.7/js/dataTables.fixedHeader.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>

        <!--  -->

        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                "use strict";
                // $('.wm-vm-go input[name="filter_action"]').on('click', function() {
                // 	$(this.form).attr('method', 'get');
                // });

                var unselect_stock_status = '';
                //displays datatable with sticky header
                /*var table = $('#po-items-table').DataTable({
                 responsive: true,
                 paging: false,
                 "searching": false,
                 columnDefs: [
                 { orderable: false, targets: 0 },
                 { orderable: false, targets: 3 },
                 { orderable: false, targets: 4 },
                 { orderable: false, targets: 5 },
                 { orderable: false, targets: 7 },
                 { orderable: false, targets: 8 },
                 { orderable: false, targets: 10 },
                 { orderable: false, targets: 14 },
                 { orderable: false, targets: 15 },
                 { orderable: false, targets: 16 }
                 ],
                 order: [[2, 'asc']]
                 });
                 new $.fn.dataTable.FixedHeader( table );*/

                //stock status multiselect
                $('#stock_status_filter').multiselect({
                    buttonWidth: '400px'
                });
                //vendor multiselect
                $('#primary_vendor_filter').multiselect({
                    buttonWidth: '400px',
                    includeSelectAllOption: true,
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true,
                    maxHeight: 350,
                    filterPlaceholder: 'Search for Vendor',
                });
                // document.getElementsByClassName("btn dropdown-toggle btn-default")[0].style.borderColor = "red";
                $('#filter-vendor').on('click', function (e) {
                    var new_item_filter = '';
                    var rare_item_filter = '';
                    var selected_statuses = new Array();
                    var selected_vendors = new Array();
                    if ($("#new_item_filter").val() == 1) {
                        new_item_filter = "new_item";
                    } else if ($("#new_item_filter").val() == 0) {
                        new_item_filter = "non_new_item";
                    }
                    if ($("#rare_item_filter").val() == 1) {
                        rare_item_filter = "rare_item";
                    } else if ($("#rare_item_filter").val() == 0 && $("#rare_item_filter").val() != "") {
                        rare_item_filter = "non_rare_item";
                    }
                    if ($("#stock_status_filter").val().length) {
                        selected_statuses = $("#stock_status_filter").val();
                    }
                    if ($("#primary_vendor_filter").val().length) {
                        selected_vendors = $("#primary_vendor_filter").val();
                    }

                    e.preventDefault();
                    $(".generate-po-row").each(function () {
                        var show_row = true;
                        if (new_item_filter != "" && !$(this).hasClass(new_item_filter)) {
                            show_row = false;
                        }
                        if (rare_item_filter != "" && !$(this).hasClass(rare_item_filter)) {
                            show_row = false;
                        }

                        if (selected_statuses.length) {
                            var status_class_found = 0;
                            for (var status_counter = 0; status_counter < selected_statuses.length; status_counter++) {
                                if ($(this).hasClass(selected_statuses[status_counter])) {
                                    status_class_found = 1;
                                }
                            }
                            if (!status_class_found) {
                                show_row = false;
                            }
                        }
                        if (selected_vendors.length) {
                            var selected_vendor_class_found = 0;
                            for (var vendor_counter = 0; vendor_counter < selected_vendors.length; vendor_counter++) {
                                if ($(this).hasClass("primary_vendor_" + selected_vendors[vendor_counter])) {
                                    selected_vendor_class_found = 1;
                                }
                            }
                            if (!selected_vendor_class_found) {
                                show_row = false;
                            }
                        }





                        if (!show_row) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    });
                    /*$('.vendor_row').show();
                     $('#filter-by-wcvm_status option').each(function() {
                     if(!$(this).prop('selected')) {
                     unselect_stock_status = $(this).val();
                     console.log(unselect_stock_status.toLowerCase())
                     $('.stock_status_'+unselect_stock_status.toLowerCase()).hide();
                     }
                     });*/
                });
                //                $('#generate-po-button').on('click', function (e) {
                //                    e.preventDefault();
                //                    var purchase_order_data = new Array();
                //                    $(".po-selected-products").each(function () {
                //                        var selected_id = $(this).val();
                //                        console.log($(this).is(':checked'));
                //                        if ($(this).is(':checked')) {
                //                            purchase_order_data.push({
                //                                selected_id: selected_id,
                //                                product_qty: $("#order-quantity-" + selected_id).val(),
                //                                selected_vendor: $("#row-selected-vendor-" + selected_id).val()
                //                            });
                //                        }
                //                        $.ajax({
                //                            type: "post",
                //                            dataType: "json",
                //                            url: "<?php echo admin_url('admin-ajax.php') ?>",
                //                            data : {
                //                                action: "generatePO",
                //                                post_id : selected_id
                //                            },
                //                            success: function (msg) {
                //                                console.log(msg);
                //                            }
                //                        });
                //                    });
                //                    console.log(purchase_order_data);
                //                });
            });
        </script>
        <?php
    }

    public function updateVendorProductMapping() {
        global $wpdb;
        $ajaxResponse = [];
        $ajaxResponse['success'] = false;
        $truncate = "TRUNCATE TABLE {$wpdb->prefix}vendor_product_mapping";
        $wpdb->query($truncate);

        $sql = "SELECT * FROM " . $wpdb->prefix . "postmeta pm join {$wpdb->prefix}posts p on p.id = pm.post_id WHERE meta_key LIKE 'wcvm'";

        $results = $wpdb->get_results($sql);
        $table = $wpdb->prefix . 'vendor_product_mapping';
        $count = 0;
        // $delete = $wpdb->query("TRUNCATE TABLE `".$table."`");
        // die;

        foreach ($results as $result) {
            # code...
            $metaValues = unserialize($result->meta_value);
            foreach ($metaValues as $metaValue) {
                # code...
                // print_r("Post ID " . $result->post_id . "-----" . "vendor id " . $metaValue);
                //   	$data = array('post_id' => $result->post_id, 'vendor_id' => $metaValue);
                // $format = array('%d','%d');
                // $wpdb->insert($table,$data,$format);
                // $count++;
                $skuKey = "wcvm_" . $metaValue . "_sku";
                $sql = "SELECT * FROM {$wpdb->prefix}postmeta pm WHERE meta_key = '" . $skuKey . "' and post_id = " . $result->post_id;
                $vendorSku = $wpdb->get_results($sql);

                $priceKey = "wcvm_" . $metaValue . "_price_last";
                $sql = "SELECT * FROM {$wpdb->prefix}postmeta pm WHERE meta_key = '" . $priceKey . "' and post_id = " . $result->post_id;
                $vendorPrice = $wpdb->get_results($sql);

                //print_r("Post ID " . $result->post_id . "-----" . "vendor id " . $metaValue . "-------" . "SKU" . $vendorSku[0]->meta_value . "-------" . "Price" . $vendorPrice[0]->meta_value);

                $data = array('post_id' => $result->post_id, 'vendor_id' => $metaValue, 'vendor_sku' => $vendorSku[0]->meta_value, 'vendor_price' => $vendorPrice[0]->meta_value);
                $format = array('%d', '%d', '%s', '%d');
                $wpdb->insert($table, $data, $format);
            }
            // print_r("<br/>");
        }

        $truncate = "TRUNCATE TABLE {$wpdb->prefix}vendor_po_lookup";
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
        $sql = "INSERT INTO `{$wpdb->prefix}vendor_po_lookup`(`product_id`, `product_title`, `sku`, `regular_price`, `stock_status`, `stock`, `threshold_low`, `threshold_reorder`, `reorder_qty`, `rare`, `category`, `vendor_id`, `vendor_name`, `vendor_sku`, `vendor_price`, `primary_vendor_id`, `primary_vendor_name`)
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
        $sync_data = $wpdb->query($sql);
        if ($sync_data) {
            $ajaxResponse['success'] = true;
            update_option('_vendor_management_last_date', date('m-d-Y H:i:s'));
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
                $updateData['sale_30_days'] = $single_row->quantity;
                $where['product_id'] = $single_row->product_id;
                $wpdb->update('wp_vendor_po_lookup', $updateData, $where);
            }
        }
        $sql = 'SELECT substring(quantity.meta_key,8,POSITION("_qty" IN quantity.meta_key)-8) as product_id, orders.post_status, SUM(quantity.meta_value) as quantity
                FROM
                    wp_posts orders
                JOIN
                    wp_postmeta quantity
                ON
                    quantity.post_id = orders.ID AND quantity.meta_key like "wcvmgo_%_qty"
                WHERE
                    orders.post_type = "wcvm-order" AND orders.post_status IN ("draft", "pending")
                GROUP BY
                    quantity.meta_key, orders.post_status';
        $data = $wpdb->get_results($sql);
        if ($data) {
            foreach ($data as $single_row) {
                $updateOnOrderData = [];
                if ($single_row->post_status == 'draft') {
                    $updateOnOrderData['on_order'] = $single_row->quantity;
                } else if ($single_row->post_status == 'pending') {
                    $updateOnOrderData['on_vendor_bo'] = $single_row->quantity;
                }

                $where['product_id'] = $single_row->product_id;
                $wpdb->update('wp_vendor_po_lookup', $updateOnOrderData, $where);
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
        }

        exit(json_encode($ajaxResponse));
    }

    public function saveVendorPage() {
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

    public function vendorManagemetMenuPage() {
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
                $this->saveVendorPage();
                ?>
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
                    .wp-list-table.wcvm-vendors td {
                        vertical-align: middle;
                    }                .wp-list-table.wcvm-vendors .manage-column {
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
                            $code = get_post_meta($single_row->ID, 'code', TRUE);
                            $contact_name = get_post_meta($single_row->ID, 'contact_name', TRUE);
                            $phone = get_post_meta($single_row->ID, 'phone', TRUE);
                            $title_short = get_post_meta($single_row->ID, 'title_short', TRUE);
                            $post_title = get_post_meta($single_row->ID, 'post_title', TRUE);
                            $website = get_post_meta($single_row->ID, 'website', TRUE);
                            $contact_email = get_post_meta($single_row->ID, 'contact_email', TRUE);
                            $contact_phone = get_post_meta($single_row->ID, 'contact_phone', TRUE);
                            $post_content = get_post_meta($single_row->ID, 'post_content', TRUE);
                            ?>
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
                                    }
                                    ?></td>
                                <td><?php echo $post_content; ?></td>
                                <!--<td></td>-->
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"/>
                <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
                <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
                <script>
                $(document).ready(function () {
                    $('#vendors-list').DataTable({
                        "pageLength": 25
                    });
                });
                </script>
                <style >
                    table.dataTable tr.odd { background-color: white;  border:1px lightgrey;}
                    table.dataTable tr.even{ background-color: #F1F1F1; border:1px lightgrey; }
                </style>					
                <?php
            }
            ?>
        </div>
        <?php add_thickbox(); ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                "use strict";
                jQuery("a[data-action=wcvim-edit]").click(function () {
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
                        jQuery.each(JSON.parse(data), function (key, value) {
                            jQuery("[name=" + key + "]", container).val(value);
                        });
                    } else {
                        jQuery('[name!=_wcvim_vendor_save][name!=""][type!=submit]', container).val('');
                    }
                    return false;
                });
                jQuery(document).on('submit', 'form[data-name=wcvim-save]', function () {
                    var form = jQuery(this);
                    var isValid = true;
                    var inputPostName = jQuery('input[name=code]', form);
                    if (inputPostName.val().trim()) {
                        inputPostName.css({border: ''});
                    } else {
                        inputPostName.css({border: '1px solid red'});
                        isValid = false;
                    }
                    var inputTitleShort = jQuery('input[name=title_short]', form);
                    if (inputTitleShort.val().trim()) {
                        inputTitleShort.css({border: ''});
                    } else {
                        inputTitleShort.css({border: '1px solid red'});
                        isValid = false;
                    }
                    var inputPostTitle = jQuery('input[name=post_title]', form);
                    if (inputPostTitle.val().trim()) {
                        inputPostTitle.css({border: ''});
                    } else {
                        inputPostTitle.css({border: '1px solid red'});
                        isValid = false;
                    }
                    return isValid;
                });
            })
                    ;

        </script>
        <?php
    }

}

// define the woocommerce_update_product callback 
function wcvimCustomWoocommerceUpdateProduct($product_get_id) {
    update_option('_product_update_last_date', date('m-d-Y H:i:s'));
}

add_action('woocommerce_update_product', 'wcvimCustomWoocommerceUpdateProduct', 10, 1);

return new WC_Clear_Com_Vendor_Inventory_Management();

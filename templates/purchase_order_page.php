<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 * @var string $show_status
 * @var string $status
 */
?>
<style>
    #TB_ajaxContent {
        text-align: center;
    }
    .bold-center{
        font-weight: bold !important;
        text-align: center !important;
    }

</style>
<div class="wrap">
    <?php
    $status = "";
    if (array_key_exists('status', $_REQUEST)) {
        $status = $_REQUEST['status'];
    }
    $show_status = $status ? $status : 'draft';

    $status = $show_status;
    global $wpdb;
    $posts_table = $wpdb->prefix . "posts";
    $posts_table_sql = "SELECT * 
                        FROM `" . $posts_table . "` p
                        LEFT JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND meta_key = 'wcvmgo_product_id' 
                        LEFT JOIN " . $wpdb->prefix . "vendor_po_lookup wvpl ON wvpl.product_id = pm.meta_value
                        where p.post_status = '" . $show_status . "' and p.post_type = 'wcvm-order'";
    $orders = $wpdb->get_results($posts_table_sql);
    
    ?>
    <h1><?= esc_html__('View/Edit Purchase Orders', 'wcvm') ?></h1>
    <?php $table = new Vendor_Management_Columns(); ?>
    <?php $table_headers = $table->get_columns_vendors_list(); ?>


    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=auto-draft') ?>"<?php if (!$status || $status == 'auto-draft'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('New', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=draft') ?>"<?php if ($status == 'draft'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('On order', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=pending') ?>"<?php if ($status == 'pending'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Back order', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=publish') ?>"<?php if ($status == 'publish'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Completed', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=private') ?>"<?php if ($status == 'private'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Canceled', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=returned') ?>"<?php if ($status == 'returned'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Returns Open', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=return_closed') ?>"<?php if ($status == 'return_closed'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Returns Closed', 'wcvm') ?></a>
    |
    <?php /*
      <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=all') ?>"<?php if ($status == 'all'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('All', 'wcvm') ?></a>
      |
     */ ?>
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=trash') ?>"<?php if ($status == 'trash'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Trash', 'wcvm') ?></a>    
    <?php if ($status == 'trash'): ?>
        |
        <button type="submit" id="wcvm-delete-all-button"><?= esc_html__('Delete All', 'wcvm') ?></button>
        <form action="" method="post" id="wcvm-delete-all-form">
            <input type="hidden" name="delete-all" value="all">
        </form>
    <?php endif ?>
    <br><br>
    <?php if ($orders) { ?>
        <div style="float: right;margin-bottom: 20px;">
            <form action="<?php echo get_site_url() . '/wp-admin/admin.php?page=wcvm-epo&status=' . $_GET['status']; ?>" method="get" id="wcvm-delete-all-form">
                <input type="hidden" name="page" value="<?php echo $_GET['page'] ?>"/>
                <input type="hidden" name="status" value="<?php echo $_GET['status'] ?>"/>
                <input type="text" name="search_po" id="search_po" value="<?php echo $_GET['search_po'] ?>" placeholder="Search Through PO #">
            </form>
        </div>

        <?php // add_thickbox();   ?>
        <!--    <div id="my-content-id" style="display:none;">
                <img id="loading_image" src="../wp-content/plugins/woocommerce-vendor-management/templates/loading2.gif"/>
                <div id="vendor_details">
        
                </div>
            </div>-->
        <?php
        $printed_po_numbers = [];
        $last_order_id = 0;
        $last_expected_date = '';
        foreach ($orders as $order) {

            $vendor_price = 0;
            $vendor_sku = '';
//            print_r($order);die;
            $vendors = explode(',', $order->vendor_name);
            $vendor_Prices = explode(',', $order->vendor_price);
            $vendor_Skus = explode(',', $order->vendor_sku);
            $i = 0;
            while ($i <= count($vendors)) {

                if ($vendors[$i] == $order->primary_vendor_name) {
                    $vendor_price = $vendor_Prices[$i];
                    $vendor_sku = $vendor_Skus[$i];
                    break;
                }$i++;
            }
            ?>
            <div style="clear: both;"></div>

            <?php
            if ($last_order_id > 0 && $last_order_id != $order->ID) {
                ?>
            </tbody>
            <tfoot>
                <tr bgcolor="#e8e8e8" style="font-size:11px;">
                    <?php foreach ($table_headers as $header) {
                        ?>
                        <th><?php echo $header; ?></th><?php }
                    ?>
                </tr>
            </tfoot>

            </table>
            <?php // if ($order->post_status == 'auto-draft' || $order->post_status == 'draft'):    ?>
            <div style="padding-top: 5px;width: 300px;float: left">
                <input type="date" name="expected_date" style="width: 100px;" value="<?= esc_attr($last_expected_date ? date('Y-m-d', $last_expected_date) : '') ?>" placeholder="<?= esc_attr__('YYYY-mm-dd', 'wcvm') ?>" >
                <button type="submit" name="action" value="update" class="button button-primary"><?= esc_html__($order->post_status == 'auto-draft' ? 'Set Date & Place On Order' : 'Update Order', 'wcvm') ?></button>
            </div>
            <?php // endif    ?>
            <div style="padding-top: 5px;float: left">
                <button type="submit" name="print" value="print" class="button button-primary"><?= esc_html__('Print Order', 'wcvm') ?></button>
            </div>
            <?php // if ($order->post_status == 'auto-draft' || $order->post_status == 'draft'):   ?>
            <div style="padding-top: 5px;float: right">
                <input type="text" name="_sku" value="" style="height: 26px;" data-role="product-sku" placeholder="<?= esc_html__('SKU', 'wcvm') ?>" data-id="<?= esc_attr($last_order_id) ?>">
                <button type="submit" name="action" value="add" class="button"><?= esc_html__('Add Product', 'wcvm') ?></button>
            </div>
            <?php // endif    ?>
            </div>
            <div style="clear: both;"></div>
            <br><br>
            <br><br>

            <?php
        }
        if (!in_array($order->ID, $printed_po_numbers)) {
            ?>
            <form style="clear: both" id="<?= esc_attr($order->ID) ?>" action="<?= site_url('/wp-admin/admin.php?page=wcvm-epo') ?>" method="post">

                <div style="float: left;width: 200px; padding: 2px;">
                    <?php get_print_status($order); ?>
                    <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->ID)) ?>
                </div>
                <div style="float: left;width: 250px; padding: 2px;">
                    <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html($order->primary_vendor_name)) ?><br>
                    <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->post_date))) ?>
                </div>

                <div style="float: right;padding: 2px;">
                    <button type="button" class="button" data-id="<?= esc_attr($order->ID) ?>" data-role="order-title" data-label="<?php
                    if ($status == 'publish' || $status == 'private' || $status == 'trash' || $status == 'multiple') {
                        echo 'Close';
                    } else if ($order->post_status != $show_status && strpos($order->post_status, $show_status) === false) {
                        echo 'Close';
                    } else {
                        echo 'Open';
                    }
                    ?>"><?php
                                if ($status == 'publish' || $status == 'private' || $status == 'trash' || $status == 'multiple') {
                                    echo 'Open';
                                } else if ($order->post_status == $show_status || strpos($order->post_status, $show_status) !== false) {
                                    echo 'Close';
                                } else {
                                    'Open';
                                }
                                ?></button>
                </div>

                <?php if ($order->post_status == 'trash' || $order->post_status == 'new' || $order->post_status == 'auto-draft'): ?>
                    <div style="float: right;padding: 2px;">
                        <?php if ($order->post_status == 'trash'): ?>
                            <button type="submit" name="unarchive" value="unarchive" class="button"><?= esc_html__('Restore', 'wcvm') ?></button>
                            <button type="submit" name="delete" value="delete" class="button"><?= esc_html__('Delete', 'wcvm') ?></button>
                        <?php else: ?>
                            <input type="hidden" name="archive" value="archive" />
                    <!--                            <button style="display: none;" type="submit" name="archive" id="order_<?= esc_attr($order->ID) ?>" value="archive" class="button"><?= esc_html__('Delete Entire PO', 'wcvm') ?></button>-->
                            <a href="javascript:void(0);" id="<?= esc_attr($order->ID) ?>" class="button delete_entire">Delete Entire PO</a>
                            <a href="javascript:void(0);" id="<?= esc_attr($order->ID) ?>" class="button delete_selected">Delete Selected Lines</a>
                        <?php endif ?>
                    </div>
                <?php endif ?>
                <div<?php
                $display = "";
                if (($order->post_status != $show_status && strpos($order->post_status, $show_status) === false) || $status == 'publish' || $status == 'private' || $status == 'trash') {
                    $display = "none";
                }
                ?> style="display: <?php echo $display; ?>" data-role="order-table" data-id="<?= esc_attr($order->ID) ?>" id="<?= esc_attr($order->ID) ?>">
                    <table class="wp-list-table fixed widefat striped wcvm-orders" style="width:100%; max-width: 1400px; border-collapse: collapse;">

                        <thead>
                            <tr bgcolor="#e8e8e8" style="font-size:11px;">
                                <?php foreach ($table_headers as $header) {
                                    ?>
                                    <th><?php echo $header; ?></th><?php }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $printed_po_numbers[] = $order->ID;
                            $last_order_id = $order->ID;
                            //$last_expected_date = $order->po_expected_date;
                        }
                        ?>
                        <tr>
                            <td><?php if($order->rare) { echo '&#10004;'; } ?></td>
                            <td><?php echo $order->sku; ?></td>
                            <td><?php
                                $stock = $order->stock;

                                if (!$stock) {
                                    echo '<span style="background: red;padding: 5px;color: white">' . esc_html__('OUT', 'wcvm') . '</span>';
                                } elseif ($order->threshold_low && $stock <= 0) {
                                    echo '<span style="background: red;padding: 5px;color: white">' . esc_html__('OUT', 'wcvm') . '</span>';
                                } elseif ($order->threshold_low && $stock <= $order->threshold_low) {
                                    echo '<span style="background: orange;padding: 5px;">' . esc_html__('LOW', 'wcvm') . '</span>';
                                } elseif ($order->threshold_reorder && $stock <= $order->threshold_reorder) {
                                    echo '<span style="background: yellow;padding: 5px;">' . esc_html__('REORDER', 'wcvm') . '</span>';
                                } else {
                                    echo '<span style="background: green;padding: 5px;color: white">' . esc_html__('OK', 'wcvm') . '</span>';
                                }
                                ?>                        
                                <!--<span style="background: orange;padding: 5px;">LOW</span>-->
                            </td>
                            <td><?php echo wc_price($order->regular_price); ?></td>
                            <td><?php echo $vendor_sku; ?></td>
                            <td><?php echo wc_price($vendor_price); ?></td>
                            <td><?php echo $order->stock; ?></td>
                            <td><?php echo $order->sale_30_days; ?></td>
                            <td><input readonly type="text" value="<?php echo $order->threshold_low; ?>" style="width:60px;"></td>
                            <td><input readonly type="text" value="<?php echo $order->threshold_reorder; ?>" style="width:60px;"></td>
                            <td><input readonly type="text" value="<?php echo $order->reorder_qty; ?>" style="width:60px;"></td>
                            
                            <td><?php echo $order->on_order; ?></td>

                            <td>On Vendor Bo</td>
                            <?php
                            $order_Qty = get_post_meta($order->ID, "wcvmgo_" . $order->product_id . "_qty");
                            $order_product_Qty = $order_Qty[0];
                            $inputType = '';

                            if ($status == 'pending') {
                                $inputType = 'readonly';
                            }
                            ?>
                            <td><input <?php echo $inputType . ' '; ?>type="text" value="<?php echo $order_product_Qty; ?>" style="width:60px;"></td>
                            <td><input type="checkbox"></td>

                        </tr>

                        <?php
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr bgcolor="#e8e8e8" style="font-size:11px;">
                        <?php foreach ($table_headers as $header) {
                            ?>
                            <th><?php echo $header; ?></th><?php }
                        ?>
                    </tr>
                </tfoot>

            </table>

            <?php // if ($order->post_status == 'auto-draft' || $order->post_status == 'draft'):    ?>
            <div style="padding-top: 5px;width: 300px;float: left">
                <input type="date" name="expected_date" style="width: 100px;" value="<?= esc_attr($last_expected_date ? date('Y-m-d', $last_expected_date) : '') ?>" placeholder="<?= esc_attr__('YYYY-mm-dd', 'wcvm') ?>" >
                <button type="submit" name="action" value="update" class="button button-primary"><?= esc_html__($order->post_status == 'auto-draft' ? 'Set Date & Place On Order' : 'Update Order', 'wcvm') ?></button>
            </div>
            <?php // endif    ?>
            <div style="padding-top: 5px;float: left">
                <button type="submit" name="print" value="print" class="button button-primary"><?= esc_html__('Print Order', 'wcvm') ?></button>
            </div>
            <?php // if ($order->post_status == 'auto-draft' || $order->post_status == 'draft'):   ?>
            <div style="padding-top: 5px;float: right">
                <input type="text" name="_sku" value="" style="height: 26px;" data-role="product-sku" placeholder="<?= esc_html__('SKU', 'wcvm') ?>" data-id="<?= esc_attr($last_order_id) ?>">
                <button type="submit" name="action" value="add" class="button"><?= esc_html__('Add Product', 'wcvm') ?></button>
            </div>
            <?php // endif    ?>
    </form>
    <br><br>
    <br><br>
    <?php
} else {
    echo 'No Orders Found';
}
?>
</div>
<?php

function get_print_status($order = FALSE) {
    if ($order->post_status == 'auto-draft') {
        echo 'Status: New<br>';
    } else if ($order->post_status == 'draft') {
        echo 'Status: On order<br>';
    } else if ($order->post_status == 'pending') {
        echo 'Status: Backordered<br>';
    } else if ($order->post_status == 'publish') {
        echo 'Status: Completed<br>';
    } else if ($order->post_status == 'private') {
        echo 'Status: Canceled<br>';
    } else if ($order->post_status == 'returned') {
        echo 'Status: Returned<br>';
    } else if ($order->post_status == 'returned') {
        echo 'Status: Returned<br>';
    } else if ($order->post_status == 'return_closed') {
        echo 'Status: Returns Closed<br>';
    } else if ($order->post_status == 'trash') {
        echo 'Status: Trash<br>';
    } else if (strpos($order->post_status, $_REQUEST['status']) !== FALSE) {
        $status_string = "Status : ";
        if ($_REQUEST['status'] == 'pending') {
            $status_string .= "Backordered";
        } elseif ($_REQUEST['status'] == 'publish') {
            $status_string .= "Completed";
        } elseif ($_REQUEST['status'] == 'private') {
            $status_string .= "Canceled";
        } elseif ($_REQUEST['status'] == 'returned') {
            $status_string .= "Returned";
        } elseif ($_REQUEST['status'] == 'return_closed') {
            $status_string .= "Returns Closed";
        }
        $status_string .= " (Partially)<br>";
        echo $status_string;
    } else if ($_REQUEST['status'] == 'multiple') {
        echo 'Status: Multiple<br>';
    } else {
        echo 'Status: Trash<br>';
    }
}
?>

<?php
/**
 * @var WP_Post $order
 * @var WP_Post $vendor
 * @var WP_Post[] $products
 */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp/');

require(ABSPATH . 'wp-load.php');
// require('../../../../wp-load.php');
$total = 0;
$vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_orders';
$vendor_purchase_order_items_table = $wpdb->prefix . 'vendor_purchase_orders_items';
if (array_key_exists('po', $_REQUEST)) {
    $orderID = $_REQUEST['po'];
    $order = get_post($orderID);
//    $wcvmgo_manual = get_post_meta($orderID, "wcvmgo");
//    $purchaseOrderDetails = $wpdb->get_results('SELECT * FROM wp_vendor_purchase_order WHERE order_id = ' . $orderID);
    $order_details_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
            . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
            . " WHERE po.order_id = " . $orderID;
    $order_details = $wpdb->get_results($order_details_sql);
//    $order->wcvmgo = $wcvmgo_manual[0];
    $vendor = get_post($order->post_parent);
}
?>
<body class="email" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
    <div id="wrapper" dir="ltr">
        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
            <tbody><tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="800" id="template_container">
                            <tbody><tr id="logo">
                                    <td valign="top" width="50%">
                                        <a href="https://drivetimekeys.com">
                                            <img src="https://drivetimekeys.com/app/uploads/2017/02/dtkemaillogo.png" alt="DriveTimeKeys.com" style="margin:0;">
                                        </a>
                                    </td>
                                    <td width="50%">
                                        <p>Your source for automotive replacement keys and fobs.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" valign="top" colspan="2">
                                        <!-- Header -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="800" id="template_header">
                                            <tbody><tr>
                                                    <td id="header_wrapper">
                                                        <h1>Purchase Order <?php echo $order->ID . ',' . $vendor->post_title . ',' . date('m/d/Y', strtotime($order->post_date)) ?></h1>
                                                    </td>
                                                </tr>
                                            </tbody></table>
                                        <!-- End Header -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" valign="top" colspan="2">
                                        <!-- Body -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="800" id="template_body">
                                            <tbody><tr>
                                                    <td valign="top" id="body_content">
                                                        <!-- Content -->
                                                        <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                            <tbody><tr>
                                                                    <td valign="top">
                                                                        <div id="body_content_inner">

                                                                            <table style="border-spacing: 5px;border-collapse: separate">
                                                                                <tbody><tr>
                                                                                        <th style="width: 33%;text-align: left">Vendor Name</th>
                                                                                        <th style="width: 33%;text-align: left">Billing Address</th>
                                                                                        <th style="width: 33%;text-align: left">Ship to Address</th>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <td style="width: 33%;text-align: left;vertical-align: top">
                                                                                            <?php echo $vendor->post_title; ?><br>
                                                                                            <?= esc_html($vendor->contact_name) ?><br>
                                                                                            <?= esc_html($vendor->contact_phone) ?><br>
                                                                                            <?= esc_html($vendor->contact_email) ?>

                                                                                            <br>
                                                                                            <br>
                                                                                        </td>
                                                                                        <td style="width: 33%;text-align: left;vertical-align: top">
                                                                                            ClearCom Technologies Inc<br>
                                                                                            Wenda Crabb<br>
                                                                                            435-759-2495<br>
                                                                                            wenda@clearcomtech.com<br>
                                                                                            PO BOX 307<br>
                                                                                            Kanosh, UT 84637
                                                                                        </td>
                                                                                        <td style="width: 33%;text-align: left;vertical-align: top">
                                                                                            45 N 200 W<br>
                                                                                            Kanosh, UT 84637
                                                                                        </td>
                                                                                    </tr>
                                                                                </tbody></table>
                                                                            <br><br>
                                                                            <table style="width: 100%;border-spacing: 5px;border-collapse: separate">
                                                                                <tbody><tr>
                                                                                        <th style="text-align: right">Quantity</th>
                                                                                        <th style="text-align: left">Vendor SKU</th>
                                                                                        <th style="text-align: left">Ccom SKU</th>
                                                                                        <th style="text-align: left">Product description</th>
                                                                                        <th style="text-align: right">Price</th>
                                                                                        <th style="text-align: right">Extended$</th>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <?php
//                                                                                        foreach ($purchaseOrderDetails as $singleLineItem):
                                                                                        foreach ($order_details as $singleLineItem):
                                                                                            ?>
                                                                                            <td style="text-align: center"><?php echo $singleLineItem->product_ordered_quantity; ?></td>
                                                                                            <td style="text-align: left"><?php echo $singleLineItem->vendor_sku; ?>   </td>
                                                                                            <td style="text-align: left"><?php echo $singleLineItem->product_sku; ?></td>
                                                                                            <td style="text-align: left"><?php echo $singleLineItem->product_title; ?></td>
                                                                                            <td style="text-align: right"><?php echo wc_price($singleLineItem->vendor_price_last); ?></td>
                                                                                            <td style="text-align: right"><?php echo wc_price(number_format((float) $singleLineItem->vendor_price_last * (float) $singleLineItem->product_ordered_quantity,2)); ?></td>
                                                                                        </tr>
                                                                                        <?php $total += (float) $singleLineItem->vendor_price_last * (float) $singleLineItem->product_ordered_quantity ?>  
                                                                                                                                                                            
                                                                                    <?php endforeach ?>                                                                                    
                                                                                    <tr>
                                                                                        <td colspan="5" style="text-align: right">Total</td>
                                                                                        <td style="text-align: right"><?php echo wc_price(number_format($total,2)); ?></td>
                                                                                    </tr>
                                                                                </tbody></table>
                                                                            <br><br>
                                                                            <h4>Shipping Requested and PO Notes</h4>
                                                                            <div style="border: 1px solid black;width: 100%;height: 200px;"></div>

                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                        <!-- End Content -->
                                                    </td>
                                                </tr>
                                            </tbody></table>
                                        <!-- End Body -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" valign="top" colspan="2">
                                        <!-- Footer -->
                                        <table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
                                            <tbody><tr>
                                                    <td valign="top">
                                                        <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                            <tbody><tr>
                                                                    <td colspan="2" valign="middle" id="clearcom">
                                                                        <img src="https://drivetimekeys.com/app/uploads/2017/02/cclogo-white.png" alt="ClearCom Technologies">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2" valign="middle" id="help">
                                                                        If you have any questions or need assistance in any way, please email:<br>
                                                                        <a href="mailto:rogerw@clearcomtech.com">rogerw@clearcomtech.com</a>
                                                                    </td>
                                                                </tr>
                                                            </tbody></table>
                                                    </td>
                                                </tr>
                                            </tbody></table>
                                        <!-- End Footer -->
                                    </td>
                                </tr>
                            </tbody></table>
                    </td>
                </tr>
            </tbody></table>
    </div>



    <script>
        window.print();
    </script>
</body>

<?php
/**
 * @var WP_Post $order
 * @var WP_Post $vendor
 * @var WP_Post[] $products
 */
//require('wp-load.php');
require('../../../../wp-load.php');
$total = 0;
if (array_key_exists('po', $_REQUEST)) {
    $orderID = $_REQUEST['po'];
    $order = get_post($orderID);
    $wcvmgo_manual = get_post_meta($orderID, "wcvmgo");
    $order->wcvmgo = $wcvmgo_manual[0];
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
                                                                                        foreach ($order->wcvmgo as $productId):
                                                                                            $skuKey = "wcvm_" . $productId . "_qty";
                                                                                            $sql = "SELECT * FROM `wp_vendor_po_lookup` WHERE `product_id` = " . $productId;
                                                                                            $data = $wpdb->get_results($sql);
                                                                                            $poData = $data[0];
                                                                                            $sql = "SELECT meta_value FROM `wp_postmeta` WHERE `post_id` = " . $orderID . " AND `meta_key` LIKE 'wcvmgo_" . $productId . "_qty'";
                                                                                            $vendorSku = $wpdb->get_results($sql);
                                                                                            $data = $vendorSku[0];
                                                                                            if ($data->meta_value):
                                                                                                $vendors = explode(',', $poData->vendor_name);
                                                                                                $vendor_Prices = explode(',', $poData->vendor_price);
                                                                                                $vendor_Skus = explode(',', $poData->vendor_sku);
                                                                                                $i = 0;
                                                                                                while ($i < count($vendors)) {

                                                                                                    if ($vendors[$i] == $poData->primary_vendor_name) {
                                                                                                        $vendor_price = $vendor_Prices[$i];
                                                                                                        $vendor_sku = $vendor_Skus[$i];
                                                                                                        break;
                                                                                                    }$i++;
                                                                                                }
                                                                                                ?>
                                                                                                <td style="text-align: right"><?php echo $data->meta_value; ?></td>
                                                                                                <td style="text-align: left"><?php echo $vendor_sku; ?>   </td>
                                                                                                <td style="text-align: left"><?php echo $poData->sku; ?></td>
                                                                                                <td style="text-align: left"><?php echo $poData->product_title; ?></td>
                                                                                                <td style="text-align: right"><?php echo $vendor_price; ?></td>
                                                                                                <td style="text-align: right"><?php echo $vendor_price * (float) $data->meta_value; ?></td>
                                                                                            </tr>
                                                                                            <?php $total += (float) $poData->vendor_price * (float) $data->meta_value ?>  
                                                                                        <?php endif ?>                                                                                    
                                                                                    <?php endforeach ?>                                                                                    
                                                                                    <tr>
                                                                                        <td colspan="5" style="text-align: right">Total</td>
                                                                                        <td style="text-align: right"><?php echo $total; ?></td>
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

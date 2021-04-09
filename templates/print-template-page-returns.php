<?php

/**
 * @var WP_Post $order
 * @var WP_Post $vendor
 * @var WP_Post[] $products
 */

/** Absolute path to the WordPress directory. */
if ($_SERVER['HTTP_HOST'] == "localhost") {
    define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');
    require(ABSPATH . 'wp-load.php');
} else {
    if (!defined('ABSPATH'))
        define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp/');
    require(ABSPATH . 'wp-load.php');
}

$total = 0;
$vendor_purchase_order_table = $wpdb->prefix . 'vendor_purchase_orders';
$vendor_purchase_order_items_table = $wpdb->prefix . 'vendor_purchase_orders_items';
if (array_key_exists('po', $_REQUEST)) {
    $orderID = $_REQUEST['po'];
    $order = get_post($orderID);
    $order_details_sql = "SELECT * FROM `" . $vendor_purchase_order_table . "` po "
        . "LEFT JOIN " . $vendor_purchase_order_items_table . " poi ON po.id = poi.vendor_order_idFk "
        . " WHERE po.order_id = " . $orderID;
    $order_details = $wpdb->get_results($order_details_sql);
    $vendor = get_post($order->post_parent);

    $cancelled_note = false;
    $returned_note = false;
    if (array_key_exists('status', $_GET)) {
        if ($_GET['status'] == 'canceled') {
            $cancelled_note = true;
        } else if ($_GET['status'] == 'returned') {
            $returned_note = true;
        }
    }

?>

    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/4de99c111d.js" crossorigin="anonymous"></script>

    <div class="flex flex-col divide-y-2 divide-gray-300 divide-solid w-full p-16 mx-auto">
        <div class="flex flex-wrap items-center pb-8">
            <div class="flex-1">
                <img src="https://clearcomkeys.com/app/themes/clearcomkeys/dist/images/clearcomlogo.png" alt="">
            </div>
            <div class="font-bold text-base">Your source for automotive replacement keys and fobs</div>
        </div>
        <div class="flex py-8">
            <div class="w-1/4">
                <div class="flex flex-col p-8">
                    <div class="font-bold text-xl">Vendor Name:</div>
                    <div><?= esc_html($vendor->post_title) ?></div>
                    <div><?= esc_html($vendor->address) ?></div>
                    <div><?= esc_html(($vendor->city ? $vendor->city . ', ' : '') . ($vendor->state ? $vendor->state . ', ' : '') . ($vendor->zip ? $vendor->zip : '')) ?></div>
                    <div><?= esc_html($vendor->contact_phone) ?></div>
                </div>
            </div>
            <div class="w-1/4">
                <div class="flex flex-col p-8">
                    <div class="font-bold text-xl">Billing Address:</div>
                    <div>ClearCom Technologies Inc</div>
                    <div>435-759-2495</div>
                    <div>PO BOX 307</div>
                    <div>Kanosh, UT 84637</div>
                </div>
            </div>
            <div class="w-1/4">
                <div class="flex flex-col p-8">
                    <div class="font-bold text-xl">Ship to Address:</div>
                    <div>ClearCom Technologies Inc</div>
                    <div>435-759-2495</div>
                    <div>45 N 200 W</div>
                    <div>Kanosh, UT 84637</div>
                </div>
            </div>
            <div class="w-1/4">
                <div class="flex flex-col border border-gray-500 p-8">
                    <div class="font-bold text-xl">Order Information:</div>
                    <div><span class="font-semibold">PO #:</span> <?= esc_html($order->ID) ?></div>
                    <div><span class="font-semibold">PO Date:</span> <?= esc_html(date('m/d/Y', strtotime($order->post_date))) ?></div>
                    <div><span class="font-semibold">PO Expected Date:</span> <?= esc_html(date('m/d/Y', $order_details[0]->po_expected_date)) ?></div>
                    <div><span class="font-semibold">Return Date:</span> <?= esc_html(date('m/d/Y', strtotime($order_details[0]->returned_date))) ?></div>
                </div>
            </div>
        </div>
        <table class="table w-full py-8">
            <thead>
                <tr class="text-left">
                    <th class="p-4 border-b border-gray-500 font-bold text-lg">Quantity:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-lg">QTY Returned:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-lg">Vendor SKU:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-lg">ClearCom SKU:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-lg">Product Description:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-lg">Price:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-lg">Refund Total:</th>
                </tr>
            </thead>
            <?php
            $itemQty = 0;
            $itemExtendedPrice = 0;
            $itemTotalPrice = 0;
            foreach ($order_details as $singleLineItem) {
                $itemQty = 0;
                $itemExtendedPrice = (float) $singleLineItem->vendor_price_last * $itemQty;
                $itemTotalPrice = (float) $singleLineItem->vendor_price_last * $itemQty;
                if ($returned_note) {
                    if ($singleLineItem->product_quantity_returned > 0) {
                        $itemQty = $singleLineItem->product_quantity_returned;
                        $itemExtendedPrice = (float) $singleLineItem->vendor_price_last * $itemQty;
                        $itemTotalPrice = (float) $singleLineItem->vendor_price_last * $itemQty;
                    }
                }
            ?>
                <tbody>
                    <tr>
                        <td class="p-4 border-b border-gray-300">
                            <?php echo $singleLineItem->product_ordered_quantity; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300">
                            <?php echo $itemQty; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300">
                            <?php echo $singleLineItem->vendor_sku; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300">
                            <?php echo $singleLineItem->product_sku; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300">
                            <?php echo $singleLineItem->product_title; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300">
                            <?php echo wc_price($singleLineItem->vendor_price_last); ?>
                        </td>
                        <td class="p-4 border-b border-gray-300">
                            <?php echo wc_price($itemTotalPrice); ?>
                        </td>
                        <?php $total += $itemTotalPrice; ?>
                    </tr>
                    <?php 
                    if($singleLineItem->product_quantity_canceled_note || $singleLineItem->product_quantity_returned_note){
                        ?>
                    <tr>
                        <td colspan="7" class="p-4 border-l-8 border-b-2 border-gray-300">
                            <div class="block"><span class="font-bold">Cancellation Note:</span> <?php echo $singleLineItem->product_quantity_canceled_note; ?></div>
                            <div class="block"><span class="font-bold">Return Note:</span> <?php echo $singleLineItem->product_quantity_returned_note; ?></div>
                        </td>
                    </tr>
                    <?php 
                    }
                    ?>
                </tbody>
            <?php } ?>
            <tfoot>
                <tr>
                    <td class="table-cell"></td>
                    <td class="table-cell"></td>
                    <td class="table-cell"></td>
                    <td class="table-cell"></td>
                    <td class="table-cell"></td>
                    <td class="p-4 border-b border-l border-gray-300 font-bold text-lg">
                        Grand Refund Total:
                    </td>
                    <td class="p-4 border-b border-gray-300 aligh-right">
                        <?php echo wc_price($total); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="h-96"></div>

    <body class="email" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
        <div style="display:none;" id="wrapper" dir="ltr">
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tbody>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="800" id="template_container">
                                <tbody>
                                    <tr id="logo">
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
                                                <tbody>
                                                    <tr>
                                                        <td id="header_wrapper">
                                                            <h1>Purchase Order <?php echo $order->ID . ',' . $vendor->post_title . ',' . date('m/d/Y', strtotime($order->post_date)) ?></h1>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <!-- End Header -->
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" valign="top" colspan="2">
                                            <!-- Body -->
                                            <table border="0" cellpadding="0" cellspacing="0" width="800" id="template_body">
                                                <tbody>
                                                    <tr>
                                                        <td valign="top" id="body_content">
                                                            <!-- Content -->
                                                            <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                                <tbody>
                                                                    <tr>
                                                                        <td valign="top">
                                                                            <div id="body_content_inner">

                                                                                <table style="border-spacing: 5px;border-collapse: separate">
                                                                                    <tbody>
                                                                                        <tr>
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
                                                                                    </tbody>
                                                                                </table>
                                                                                <br><br>
                                                                                <table style="width: 100%;border-spacing: 5px;border-collapse: separate">
                                                                                    <tbody>
                                                                                        <tr>
                                                                                            <th style="text-align: right">Quantity</th>
                                                                                            <th style="text-align: left">Vendor SKU</th>
                                                                                            <th style="text-align: left">Ccom SKU</th>
                                                                                            <th style="text-align: left">Product description</th>
                                                                                            <th style="text-align: right">Price</th>
                                                                                            <th style="text-align: right">Extended$</th>
                                                                                            <?php if ($cancelled_note) { ?>
                                                                                                <th style="text-align: right">Cancelled Amount</th>
                                                                                                <th style="text-align: center">Cancelled Note</th>
                                                                                            <?php } else if ($returned_note) { ?>
                                                                                                <th style="text-align: right">Returned Amount</th>
                                                                                                <th style="text-align: center">Returned Note</th>
                                                                                            <?php } ?>
                                                                                        </tr>
                                                                                        <tr>
                                                                                            <?php
                                                                                            $itemQty = '';
                                                                                            $itemExtendedPrice = '';
                                                                                            $itemTotalPrice = '';
                                                                                            //                                                                                        foreach ($purchaseOrderDetails as $singleLineItem):
                                                                                            foreach ($order_details as $singleLineItem) :
                                                                                                $itemQty = $singleLineItem->product_ordered_quantity;
                                                                                                $itemExtendedPrice = (float) $singleLineItem->vendor_price_last * $singleLineItem->product_ordered_quantity;
                                                                                                $itemTotalPrice = (float) $singleLineItem->vendor_price_last * $singleLineItem->product_ordered_quantity;
                                                                                                if ($returned_note) {
                                                                                                    if ($singleLineItem->product_quantity_returned > 0) {
                                                                                                        $itemQty = $singleLineItem->product_quantity_returned;
                                                                                                        $itemExtendedPrice = (float) $singleLineItem->vendor_price_last * $singleLineItem->product_quantity_returned;
                                                                                                        $itemTotalPrice = (float) $singleLineItem->vendor_price_last * $singleLineItem->product_quantity_returned;
                                                                                                    }
                                                                                                }
                                                                                            ?>
                                                                                                <td style="text-align: center"><?php echo $itemQty; ?></td>
                                                                                                <td style="text-align: left"><?php echo $singleLineItem->vendor_sku; ?> </td>
                                                                                                <td style="text-align: left"><?php echo $singleLineItem->product_sku; ?></td>
                                                                                                <td style="text-align: left"><?php echo $singleLineItem->product_title; ?></td>
                                                                                                <td style="text-align: right"><?php echo wc_price($singleLineItem->vendor_price_last); ?></td>
                                                                                                <td style="text-align: right"><?php echo wc_price($itemExtendedPrice); ?></td>
                                                                                                <?php if ($cancelled_note) { ?>
                                                                                                    <td style="text-align: right"><?php echo wc_price($singleLineItem->product_price * $singleLineItem->product_quantity_canceled); ?></td>
                                                                                                    <td style="text-align: left"><?php echo $singleLineItem->product_quantity_canceled_note; ?></td>
                                                                                                <?php } else if ($returned_note) { ?>
                                                                                                    <td style="text-align: right"><?php echo wc_price($singleLineItem->product_price * $singleLineItem->product_quantity_returned); ?></td>
                                                                                                    <td style="text-align: left"><?php echo $singleLineItem->product_quantity_returned_note; ?></td>
                                                                                                <?php } ?>
                                                                                        </tr>
                                                                                        <?php $total += $itemTotalPrice; ?>

                                                                                    <?php endforeach ?>
                                                                                    <tr>
                                                                                        <td colspan="5" style="text-align: right">Total</td>
                                                                                        <td style="text-align: right"><?php echo wc_price($total); ?></td>
                                                                                    </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                                <br><br>
                                                                                <h4>Shipping Requested and PO Notes</h4>
                                                                                <div style="border: 1px solid black;width: 100%;height: 200px;"></div>

                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                            <!-- End Content -->
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <!-- End Body -->
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" valign="top" colspan="2">
                                            <!-- Footer -->
                                            <table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
                                                <tbody>
                                                    <tr>
                                                        <td valign="top">
                                                            <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                                <tbody>
                                                                    <tr>
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
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <!-- End Footer -->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <script>
            //window.print();
        </script>
    </body>
<?php } ?>
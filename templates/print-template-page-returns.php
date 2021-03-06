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

    <div class="flex flex-col divide-y-2 divide-gray-300 divide-solid w-full mx-auto">
    <div class="flex flex-wrap items-center pb-8">
            <div class="flex-1 pr-8">
                <img src="https://clearcomkeys.com/app/themes/clearcomkeys/dist/images/clearcomlogo.png" alt="" class="w-48">
                <p class="text-sm">Your source for automotive replacement keys and fobs</p>
            </div>
            <div class="">
                <div class="flex flex-col border border-gray-500 p-2">
                    <div class="font-bold text-sm">Order Information:</div>
                    <div><span class="font-semibold text-sm">PO #:</span> <?= esc_html($order->ID) ?></div>
                    <div><span class="font-semibold text-sm">PO Date:</span> <?= esc_html(date('m/d/Y', strtotime($order->post_date))) ?></div>
                    <div><span class="font-semibold text-sm">PO Expected Date:</span> <?= esc_html($po_expected_date) ?></div>
                    <div><span class="font-semibold text-sm">Return Date:</span> <?= esc_html(date('m/d/Y', strtotime($order_details[0]->returned_date))) ?></div>
                </div>
            </div>
        </div>
        <div class="flex">
            <div class="w-1/3">
                <div class="flex flex-col py-2 text-sm">
                    <div class="font-bold text-sm whitespace-nowrap">Vendor Name:</div>
                    <div class="text-sm"><?= esc_html($vendor->post_title) ?></div>
                    <div class="text-sm"><?= esc_html($vendor->address) ?></div>
                    <div class="text-sm"><?= esc_html(($vendor->city ? $vendor->city . ', ' : '') . ($vendor->state ? $vendor->state . ', ' : '') . ($vendor->zip ? $vendor->zip : '')) ?></div>
                    <div class="text-sm"><?= esc_html($vendor->contact_phone) ?></div>
                </div>
            </div>
            <div class="w-1/3">
                <div class="flex flex-col py-2">
                    <div class="font-bold text-sm">Billing Address:</div>
                    <div class="text-sm">ClearCom Tech</div>
                    <div class="text-sm">435-759-2495</div>
                    <div class="text-sm">PO BOX 307</div>
                    <div class="text-sm">Kanosh, UT 84637</div>
                </div>
            </div>
            <div class="w-1/3">
                <div class="flex flex-col py-2">
                    <div class="font-bold text-sm">Ship to Address:</div>
                    <div class="text-sm">ClearCom Tech</div>
                    <div class="text-sm">435-759-2495</div>
                    <div class="text-sm">45 N 200 W</div>
                    <div class="text-sm">Kanosh, UT 84637</div>
                </div>
            </div>

        </div>
        <table class="table w-full py-8">
            <thead>
                <tr class="text-left">
                    <th class="p-4 border-b border-gray-500 font-bold text-sm">Quantity:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-sm">QTY Returned:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-sm">Vendor SKU:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-sm">ClearCom SKU:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-sm">Product Description:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-sm">Price:</th>
                    <th class="p-4 border-b border-gray-500 font-bold text-sm">Refund Total:</th>
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
                        <td class="p-4 border-b border-gray-300 text-sm">
                            <?php echo $singleLineItem->product_ordered_quantity; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300 text-sm">
                            <?php echo $itemQty; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300 text-sm">
                            <?php echo $singleLineItem->vendor_sku; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300 text-sm">
                            <?php echo $singleLineItem->product_sku; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300 text-sm">
                            <?php echo $singleLineItem->product_title; ?>
                        </td>
                        <td class="p-4 border-b border-gray-300 text-sm">
                            <?php echo wc_price($singleLineItem->vendor_price_last); ?>
                        </td>
                        <td class="p-4 border-b border-gray-300 text-sm">
                            <?php echo wc_price($itemTotalPrice); ?>
                        </td>
                        <?php $total += $itemTotalPrice; ?>
                    </tr>
                    <?php 
                    if($singleLineItem->product_quantity_canceled_note || $singleLineItem->product_quantity_returned_note){
                        ?>
                    <tr>
                        <td colspan="7" class="p-4 border-l-8 border-b-2 border-gray-300">
                            <?php if($singleLineItem->product_quantity_canceled_note) { ?>
                                <div class="block"><span class="font-bold">Cancellation Note:</span> <?php echo $singleLineItem->product_quantity_canceled_note; ?></div>
                            <?php } if($singleLineItem->product_quantity_returned_note) { ?>
                                <div class="block"><span class="font-bold">Return Note:</span> <?php echo $singleLineItem->product_quantity_returned_note; ?></div>
                            <?php } ?>
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
                    <td class="p-4 border-b border-l border-gray-300 font-bold text-sm">
                        Grand Refund Total:
                    </td>
                    <td class="p-4 border-b border-gray-300 aligh-right text-sm">
                        <?php echo wc_price($total); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="h-96"></div>

<?php } ?>

<script type="text/javascript">
      window.onload = function() { window.print(); }
 </script>
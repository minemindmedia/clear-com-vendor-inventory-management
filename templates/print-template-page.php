<?php
/**
 * @var WP_Post $order
 * @var WP_Post $vendor
 * @var WP_Post[] $products
 */

require('../../../../wp-load.php');
    if (array_key_exists('po', $_REQUEST)) {
        $orderID = $_REQUEST['po'];
        $order = get_post($orderID);
        $wcvmgo_manual = get_post_meta($orderID, "wcvmgo");
        $order->wcvmgo = $wcvmgo_manual[0];
        $vendor = get_post($order->post_parent);
//        echo $order;die;
    }
//    if (array_key_exists('vendor', $_REQUEST)) {
//        echo $vendor = $_REQUEST['vendor'];
//    }
//    if (array_key_exists('date', $_REQUEST)) {
//        echo $order_post_date = $_REQUEST['date'];
//    }
/**
 * @var WP_Post $order
 * @var WP_Post $vendor
 * @var WP_Post[] $products
 */
?>

<?php do_action('woocommerce_email_header', 'Purchase Order ' . esc_html($order->ID) . ', ' . esc_html($vendor->title_short) . ', ' . date('m/d/Y', strtotime($order->post_date)), '') ?>

<table style="border-spacing: 5px;border-collapse: separate">
    <tr>
        <th style="width: 33%;text-align: left">Vendor Name</th>
        <th style="width: 33%;text-align: left">Billing Address</th>
        <th style="width: 33%;text-align: left">Ship to Address</th>
    </tr>
    <tr>
        <td style="width: 33%;text-align: left;vertical-align: top">
            <?= esc_html($vendor->post_title) ?><br>
            <?= esc_html($vendor->contact_name) ?><br>
            <?= esc_html($vendor->contact_phone) ?><br>
            <?= esc_html($vendor->contact_email) ?>
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
</table>
<br><br>
<table style="width: 100%;border-spacing: 5px;border-collapse: separate">
    <tr>
        <th style="text-align: right">Quantity</th>
        <th style="text-align: left">Vendor SKU</th>
        <th style="text-align: left">Ccom SKU</th>
        <th style="text-align: left">Product description</th>
        <th style="text-align: right">Price</th>
        <th style="text-align: right">Extended$</th>
    </tr>
    <?php $total = 0 ?>
    <?php // foreach ($order->wcvmgo as $productId): ?>
        <?php // $data = get_field('wcvmgo_' . $productId, $order) ?>
        <?php // if ($data['product_quantity']): ?>
            <tr>
<!--                <td style="text-align: right"><? number_format_i18n($data['product_quantity'], 0) ?></td>
                <td style="text-align: left"><? esc_html($data['vendor_sku']) ?></td>
                <td style="text-align: left"><? esc_html($data['product_sku']) ?></td>
                <td style="text-align: left"><? esc_html($data['product_title']) ?></td>
                <td style="text-align: right"><? number_format_i18n($data['vendor_price_last'], 2) ?></td>
                <td style="text-align: right"><? number_format_i18n($data['vendor_price_last'] * $data['product_quantity'], 2) ?></td>-->
            </tr>
            <?php // $total += $data['vendor_price_last'] * $data['product_quantity'] ?>
        <?php // endif ?>
    <?php // endforeach ?>
    <tr>
        <td colspan="5" style="text-align: right">Total</td>
        <td style="text-align: right"><?= number_format_i18n($total, 2) ?></td>
    </tr>
</table>
<br><br>
<h4>Shipping Requested and PO Notes</h4>
<div style="border: 1px solid black;width: 100%;height: 200px;"></div>

<?php do_action('woocommerce_email_footer', '', '') ?>

<script>
window.print();
</script>

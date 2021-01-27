<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 */

?>
<div class="wrap">
<?php
if(isset($_POST['update'])) {
    print_r($_POST);
    $post_id = $_POST['ID'];
    $porduct_id = $_POST['product_id'];
    $received_qty = $_POST['qty_received'];
    $return_qty= $_POST['qty_ret'];
    $bo_qty = $_POST['qty_vind_bo'];
    $cancel_qty = $_POST['qty_cancel'];
    $expected_date = $_POST['expected_date'];
    $set_date = $_POST['qty_expected_date'];
    update_post_meta( $post_id, 'wcvmgo_' .  $porduct_id . '_received', $received_qty);
    update_post_meta( $post_id, 'wcvmgo_' .  $porduct_id . '_qty', $bo_qty);
    update_post_meta( $post_id, 'wcvmgo_' .  $porduct_id . '_returned', $return_qty);
    update_post_meta( $post_id, 'wcvmgo_' .  $porduct_id . '_qty_vind_bo', $bo_qty);
    update_post_meta( $post_id, 'wcvmgo_' .  $porduct_id . '_cancelled', $cancel_qty);
    update_post_meta( $post_id, 'expected_date', $expected_date);
    update_post_meta( $post_id, 'set_date', strtotime($set_date));
}

global $wpdb;
$status = $_REQUEST['status'];
$show_status = $status ? $status : 'draft';
$status = $show_status;
$posts_table = $wpdb->prefix . "posts";
$posts_table_sql = "SELECT * 
                    FROM `" . $posts_table . "` p
                    LEFT JOIN " . $wpdb->prefix . "postmeta pm ON pm.post_id = p.ID AND meta_key = 'wcvmgo_product_id' 
                    LEFT JOIN " . $wpdb->prefix . "vendor_po_lookup wvpl ON wvpl.product_id = pm.meta_value
                    where p.post_status = '" . $show_status . "' and p.post_type = 'wcvm-order' ORDER BY pm.post_id DESC";
$orders = $wpdb->get_results($posts_table_sql);

    foreach ($orders as $order) {
        $vendors = explode(',', $order->vendor_name);
        $vendor_ids = explode(',', $order->vendor_id);
        $vendor_prices = explode(',', $order->vendor_price);
        $wcvmgo_received = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_received');
        $wcvmgo_returned = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_returned');
        $wcvmgo_qty_vind_bo = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_qty_vind_bo');
        $wcvmgo_cancelled = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_cancelled');
        $wcvmgo_set_date = get_post_meta($order->ID, 'set_date');
        $wcvmgo_qty = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_qty');
        $wcvmgo_date = get_post_meta($order->ID, 'wcvmgo_' . $order->product_id . '_date');
    ?>
    <h1><?= esc_html__('Receive Inventory', 'wcvm') ?></h1>
    <form id="form_<?php echo $order->ID; ?>" action="" method="post">
        <input type="hidden" name="ID" value="<?= esc_attr($order->ID) ?>">
        <input type="hidden" name="product_id" value="<?= esc_attr($order->product_id) ?>">
        <input type="hidden" name="expected_date" value="<?= esc_attr($wcvmgo_date ? $wcvmgo_date[0] : '') ?>">
        <h4 style="margin-bottom: 5px;">
            <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html($order->ID)) ?>,
            <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html($order->primary_vendor_name)) ?>,
            <?= sprintf(esc_html__('PO Date: %s'), date('m/d/Y', strtotime($order->post_date))) ?>
        </h4>
        <?php $table = new Vendor_Management_Columns(); ?>
        <?php $table_headers = $table->get_columns_receive_inventory(); ?>
        <table class="wp-list-table widefat striped wcvm-orders" style="width:100%; max-width: 1400px; border-collapse: collapse;">

            <thead>
                <tr bgcolor="#e8e8e8" style="font-size:11px;">
                    <?php foreach ($table_headers as $header) {
                        ?>
                        <th><?php echo $header; ?></th><?php }
                    ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href=""><?php echo $order->sku; ?></a></td>
                    <td><?php echo $order->category; ?></td>
                    <td></td>
                    <td><?php echo $order->primary_vendor_name; ?></td>
                    <td></td>
                    <td><?php echo $order->vendor_sku; ?></td>
                    <td><?php echo $order->vendor_price; ?></td>
                    <td id="quantity_<?php echo $order->ID; ?>" data-quantity="<?php echo $wcvmgo_qty[0] ? $wcvmgo_qty[0] : ''; ?>"><?php echo  $wcvmgo_qty[0] ? $wcvmgo_qty[0] : ''; ?></td>
                    <td><input type="text" id="qty_received_<?php echo $order->ID; ?>" name="qty_received" value="<?php echo $wcvmgo_received ? $wcvmgo_received[0] : ''; ?>" style="width:60px;"></td>
                    <td><input type="text" id="qty_ret_<?php echo $order->ID; ?>" name="qty_ret" value="<?php echo $wcvmgo_returned ? $wcvmgo_returned[0] : ''; ?>" style="width:60px;"></td>
                    <td><input type="text" id="qty_vind_bo_<?php echo $order->ID; ?>" name="qty_vind_bo" value="<?php echo $wcvmgo_qty_vind_bo ? $wcvmgo_qty_vind_bo[0] : ''; ?>" style="width:60px;"></td>
                    <td><input type="text" id="qty_cancel_<?php echo $order->ID; ?>" name="qty_cancel" value="<?php echo $wcvmgo_cancelled ? $wcvmgo_cancelled[0] : ''; ?>" style="width:60px;"></td>
                    <td><input type="text" id="qty_expected_date_<?php echo $order->ID; ?>" name="qty_expected_date" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" value="<?php echo $wcvmgo_set_date ? date('Y-m-d', $wcvmgo_set_date[0]) : ''; ?>"></td>                    
<!--                    <td><input type="text" value="" style="width:60px;"></td>-->
                    <td></td>
                </tr>
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
        <div style="padding-top: 5px;">
            <button type="submit" name="update" value="update" data-role="receive-inventory" class="button button-primary"><?= esc_html__('Set Inventory', 'wcvm') ?></button>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button type="submit" name="archive" value="archive" data-role="receive-inventory" class="button"><?= esc_html__('Set Inventory & Archive', 'wcvm') ?></button>
        </div>
        <br><br>
        <!-- <a href="#" class="qty_role">QTY Role</a> -->
        </form>
    <br><br>
    <?php } ?>
    </div>
   
<script>
jQuery(document).ready(function($) {
    "use strict";
    $(document).on('click', 'button[data-role="receive-inventory"]', function() {
        // e.preventDefault();
        var form = $(this).closest('form')[0];
        var id = form.id.replace('form_', '');
        var isValid = true;
        var quantity = parseInt($('#quantity_' + id).data('quantity'));
        var received = $('#qty_received_' + id).val();
        var backOrder = $('#qty_ret_' + id).val();
        var cancel = $('#qty_vind_bo_' + id).val();
        var returned = $('#qty_cancel_' + id).val();
        received = received ? parseInt(received) : 0;
        backOrder = backOrder ? parseInt(backOrder) : 0;
        cancel = cancel ? parseInt(cancel) : 0;
        returned = returned ? parseInt(returned) : 0;
        if (quantity != (received + backOrder + cancel + returned)) {
            isValid = false;
        }
        if (!isValid) {
            alert('Sum of "QTY Rcv", "Vnd BO", "Cancel", "Returns Open" should equal "Order QTY"');
        }
        console.log(id);
        console.log(quantity);
        console.log(received);
        console.log(backOrder);
        console.log(cancel);
        console.log(returned);
        return isValid;
    });
});
</script>
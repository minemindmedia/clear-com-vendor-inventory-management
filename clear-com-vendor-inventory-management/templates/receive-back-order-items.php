<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 */
?>
<div class="wrap">
    <h1><?= esc_html__('Receive Back Order Items', 'wcvm') ?></h1>
    <form action="" method="post">
        <input type="hidden" name="ID" value="<?= esc_attr($order->ID) ?>">
        <h4 style="margin-bottom: 5px;">
            <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html(10001)) ?>,
            <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html('vendor')) ?>,
            <?= sprintf(esc_html__('PO Date: %s'), date(get_option('date_format'), strtotime(01 / 22 / 2021))) ?>
        </h4>
        <?php $table = new Vendor_Management_Columns(); ?>
        <?php $table_headers = $table->get_columns_receive_back_order_items(); ?>
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
                    <td><a href="">CHRY052</a></td>
                    <td><a href="">Chrysler Remote Head 3 Button L,U,Px</a></td>
                    <td>Kigo</td>
                    <td>C</td>
                    <td>RK-CHY-4</td>
                    <td>100</td>
                    <td>100</td>
                    <td><input type="text" value="8" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" name="" value=""></td>
                    <td>0</td>

                </tr>
                <tr>
                    <td><a href="">CHRY052</a></td>
                    <td><a href="">Chrysler Remote Head 3 Button L,U,Px</a></td>
                    <td>Kigo</td>
                    <td>C</td>
                    <td>RK-CHY-4</td>
                    <td>100</td>
                    <td>100</td>
                    <td><input type="text" value="8" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" name="" value=""></td>
                    <td>0</td>

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
            <button type="submit" name="action" value="update" class="button button-primary"><?= esc_html__('Set Inventory', 'wcvm') ?></button>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button type="submit" name="action" value="archive" class="button"><?= esc_html__('Set Inventory & Archive', 'wcvm') ?></button>
        </div>
    </form>
    <br><br>
</div>
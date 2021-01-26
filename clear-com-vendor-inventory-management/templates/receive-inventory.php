<?php
/**
 * @var WP_Post[] $orders
 * @var WP_Post[] $vendors
 */
?>
<div class="wrap">
    <h1><?= esc_html__('Receive Inventory', 'wcvm') ?></h1>
    <form action="" method="post">
        <input type="hidden" name="ID" value="<?= esc_attr($order->ID) ?>">
        <h4 style="margin-bottom: 5px;">
            <?= sprintf(esc_html__('PO #: %s', 'wcvm'), esc_html(1001)) ?>,
            <?= sprintf(esc_html__('Vendor: %s', 'wcvm'), esc_html("vendor")) ?>,
            <?= sprintf(esc_html__('PO Date: %s'), date(get_option('date_format'), strtotime(01 / 22 / 2021))) ?>
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
                    <td><a href="">CC SKU</a></td>
                    <td>1I, 5B, PRX</td>
                    <td>FCC:ACJ932HK1310A</td>
                    <td>North Coast</td>
                    <td>X</td>
                    <td>72147-T2G-A21</td>
                    <td>$79.94</td>
                    <td>1</td>
                    <td><input type="text" value="8" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" name="" value=""></td>                    
<!--                    <td><input type="text" value="" style="width:60px;"></td>-->
                    <td></td>

                </tr>
                <tr>
                    <td><a href="">CC SKU</a></td>
                    <td>1I, 5B, PRX</td>
                    <td>FCC:ACJ932HK1310A</td>
                    <td>North Coast</td>
                    <td>X</td>
                    <td>72147-T2G-A21</td>
                    <td>$79.94</td>
                    <td>1</td>
                    <td><input type="text" value="8" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <td><input type="text" value="" style="width:60px;"></td>
                    <!--<td><input type="text" value="" style="width:60px;"></td>-->
                    <td><input type="text" style="text-align: center;width: 70px;font-size: 10px;" data-role="datetime" name="" value=""></td>                    
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
            <button type="submit" name="action" value="update" data-role="receive-inventory" class="button button-primary"><?= esc_html__('Set Inventory', 'wcvm') ?></button>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <button type="submit" name="action" value="archive" data-role="receive-inventory" class="button"><?= esc_html__('Set Inventory & Archive', 'wcvm') ?></button>
        </div>
        <br><br>
    </form>
    <br><br>
    </div>
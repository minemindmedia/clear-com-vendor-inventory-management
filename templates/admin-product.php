<?php
/**
 * @var WP_Post $product
 * @var WP_Post[] $vendors
 */
?>
<div id="wcvmcpAdminProduct" class="panel woocommerce_options_panel">
    <div class="options_group">
        <div style="width: 50%;float: left;">
            <p class="form-field">
                <label><?= esc_html__('Stock Information For', 'wcvm') ?></label>
                <?= esc_html($product->_sku) ?>
            </p>
        </div>
        <div style="width: 50%;float: left;">
            <p class="form-field">
                <label><?= esc_html__('Stock Status', 'wcvm') ?></label>
                <?php $_stock = (float) $product->_stock ?>
                <?php if (!$_stock): ?>
                    <span style="background: red;padding: 5px;color: white"><?= esc_html__('OUT', 'wcvm') ?></span>
                <?php elseif ($product->wcvm_threshold_low && $_stock <= 0): ?>
                    <span style="background: red;padding: 5px;color: white"><?= esc_html__('OUT', 'wcvm') ?></span>
                <?php elseif ($product->wcvm_threshold_low && $_stock <= $product->wcvm_threshold_low): ?>
                    <span style="background: orange;padding: 5px;"><?= esc_html__('LOW', 'wcvm') ?></span>
                <?php elseif ($product->wcvm_threshold_reorder && $_stock <= $product->wcvm_threshold_reorder): ?>
                    <span style="background: yellow;padding: 5px;"><?= esc_html__('REORDER', 'wcvm') ?></span>
                <?php else: ?>
                    <span style="background: green;padding: 5px;color: white"><?= esc_html__('OK', 'wcvm') ?></span>
                <?php endif ?>
            </p>
        </div>
        <div style="clear: both"></div>
        <div style="width: 100%">
            <p class="form-field">
                <label for="wcvm_rare"><?= esc_html__('Rare Product?', 'wcvm') ?></label>
                <input type="hidden" name="wcvm_rare" value="">
                <input type="checkbox" class="checkbox" name="wcvm_rare" id="wcvm_rare" value="1"<?php if ($product->wcvm_rare): ?> checked="checked"<?php endif ?>>
            </p>
            <p class="form-field">
                <label for="wcvm_discontinued"><?= esc_html__('Discontinued Product?', 'wcvm') ?></label>
                <input type="hidden" name="wcvm_discontinued" value="">
                <input type="checkbox" class="checkbox" name="wcvm_discontinued" id="wcvm_discontinued" value="1"<?php if ($product->wcvm_discontinued): ?> checked="checked"<?php endif ?>>
            </p>
            <?php
            if ($product->wcvm_discontinued) {
                ?>
                <p class="form-field">
                    <label for="wcvm_discontinued"><?= esc_html__('Discontinued Date', 'wcvm') ?></label>
                    <?php echo get_post_meta($product->ID, 'wcvm_discontinued_date',TRUE); ?>
                </p>
                <?php
            }
            ?>
        </div>
    </div>

    <div class="options_group">
        <div style="width: 50%;float: left;">
            <p class="form-field">
                <label><?= esc_html__('QTY On Hand', 'wcvm') ?></label>
                <?= esc_html($product->_stock) ?>
            </p>
            <p class="form-field">
                <label><?= esc_html__('30 Day Sales History', 'wcvm') ?></label>
                <?= esc_html($product->_qty_history_30) ?>
            </p>
        </div>
        <div style="width: 50%;float: left;">
            <p class="form-field">
                <label><?= esc_html__('Amount On Order', 'wcvm') ?></label>
                <?= esc_html($product->_wcvm_on_order) ?>
                <?php $exptected = $product->_wcvm_on_order_date ?>
                <?php if ($exptected): ?>
                    <span class="description"><small>(<?= date(get_option('date_format'), $exptected) ?>)</small></span>
                <?php endif ?>
            </p>
            <p class="form-field">
                <label><?= esc_html__('Amount On Vendor BO', 'wcvm') ?></label>
                <?= esc_html($product->_wcvm_on_vendor_bo) ?>
                <?php $exptected = $product->_wcvm_on_vendor_bo_date ?>
                <?php if ($exptected): ?>
                    <span class="description"><small>(<?= date(get_option('date_format'), $exptected) ?>)</small></span>
                <?php endif ?>
            </p>
        </div>
        <div style="clear: both"></div>
    </div>

    <div class="options_group">
        <div style="width: 50%;float: left;">
            <p class="form-field">
                <label for="wcvm_threshold_low"><?= esc_html__('Low Threshold', 'wcvm') ?></label>
                <input type="text" id="wcvm_threshold_low" name="wcvm_threshold_low" value="<?= esc_attr($product->wcvm_threshold_low) ?>" style="width: 100%">
            </p>
            <p class="form-field">
                <label for="wcvm_threshold_reorder"><?= esc_html__('Reorder Threshold', 'wcvm') ?></label>
                <input type="text" id="wcvm_threshold_reorder" name="wcvm_threshold_reorder" value="<?= esc_attr($product->wcvm_threshold_reorder) ?>" style="width: 100%">
            </p>
            <p class="form-field">
                <label for="wcvm_reorder_qty"><?= esc_html__('Reorder QTY', 'wcvm') ?></label>
                <input type="text" id="wcvm_reorder_qty" name="wcvm_reorder_qty" value="<?= esc_attr($product->wcvm_reorder_qty) ?>" style="width: 100%">
            </p>
        </div>
        <div style="clear: both"></div>
    </div>

    <input type="hidden" name="wcvm_primary" value="">
    <?php $selectedVendors = $product->wcvm ?>
    <?php $selectedVendors = $selectedVendors ? array_merge(array(':ID:'), $selectedVendors) : array(':ID:') ?>
    <?php foreach ($selectedVendors as $vendorId): ?>
        <?php $prototypeId = $vendorId == ':ID:' ? '' : $vendorId ?>
        <div class="options_group"<?php if (!$prototypeId): ?> data-role="prototype" style="display: none"<?php endif ?>>
            <input type="hidden" name="wcvm[]" value="<?= esc_attr($prototypeId) ?>">
            <p class="form-field">
                <label><?= esc_html__('Vendor', 'wcvm') ?></label>
                <span data-role="title"><?php foreach ($vendors as $vendor) : ?><?php if ($vendor->ID == $prototypeId): ?><?= esc_html($vendor->post_title) ?><?php endif ?><?php endforeach ?></span>
            </p>
            <p class="form-field">
                <label for="wcvm_<?= esc_attr($vendorId) ?>_sku"><?= esc_html__('Vendor SKU', 'wcvm') ?></label>
                <input type="text" id="wcvm_<?= esc_attr($vendorId) ?>_sku" name="wcvm_<?= esc_attr($vendorId) ?>_sku" value="<?= esc_attr(get_post_meta($product->ID, 'wcvm_' . $vendorId . '_sku', true)) ?>">
                <span class="description"><a href="" data-role="copy" data-id="wcvm_<?= esc_attr($vendorId) ?>_sku" data-success="<?= esc_attr__('copied', 'wcvm') ?>" data-text="<?= esc_attr__('copy link', 'wcvm') ?>"><?= esc_html__('copy link', 'wcvm') ?></a></span>
            </p>
            <p class="form-field">
                <label for="wcvm_<?= esc_attr($vendorId) ?>_link"><?= esc_html__('Product Link', 'wcvm') ?></label>
                <input type="text" id="wcvm_<?= esc_attr($vendorId) ?>_link" name="wcvm_<?= esc_attr($vendorId) ?>_link" value="<?= esc_attr(get_post_meta($product->ID, 'wcvm_' . $vendorId . '_link', true)) ?>">
                <span class="description"><a href="" data-role="copy" data-id="wcvm_<?= esc_attr($vendorId) ?>_link" data-success="<?= esc_attr__('copied', 'wcvm') ?>" data-text="<?= esc_attr__('copy link', 'wcvm') ?>"><?= esc_html__('copy link', 'wcvm') ?></a></span>
            </p>
            <p class="form-field">
            <table>
                <tr>
                    <?php
					$total_price = 0;
                    $last_price = esc_attr(get_post_meta($product->ID, 'wcvm_' . $vendorId . '_price_last', true));
                    $freight_in = esc_attr(get_post_meta($product->ID, 'wcvm_' . $vendorId . '_freight_in', true));
					if(!is_numeric($last_price)){
						$last_price = 0;
					}
					if(!is_numeric($freight_in)){
						$freight_in = 0;
					}
					$total_price = $last_price + $freight_in;
                    ?>
                    <td><?= esc_html__('Last Price', 'wcvm') ?></td><td><input onkeyup="update_total('<?= esc_attr($vendorId) ?>')" onkeydown="update_total('<?= esc_attr($vendorId) ?>')" type="text" id="wcvm_<?= esc_attr($vendorId) ?>_price_last" name="wcvm_<?= esc_attr($vendorId) ?>_price_last" value="<?php echo $last_price; ?>"></td>
                    <td><?= esc_html__('Freight In', 'wcvm') ?></td><td><input onkeyup="update_total('<?= esc_attr($vendorId) ?>')" onkeydown="update_total('<?= esc_attr($vendorId) ?>')" type="text" id="wcvm_<?= esc_attr($vendorId) ?>_freight_in" name="wcvm_<?= esc_attr($vendorId) ?>_freight_in" value="<?php echo $freight_in; ?>"></td>
                    <td>Total Cost</td><td id="<?php echo $vendorId ?>_total_price"><b>$<?php echo $total_price; ?></b></td>
                </tr>

            </table>
    <!--                <label for="wcvm_<?= esc_attr($vendorId) ?>_price_last"><?= esc_html__('Last Price', 'wcvm') ?></label>
                <input type="text" id="wcvm_<?= esc_attr($vendorId) ?>_price_last" name="wcvm_<?= esc_attr($vendorId) ?>_price_last" value="<?= esc_attr(get_post_meta($product->ID, 'wcvm_' . $vendorId . '_price_last', true)) ?>">
                <label for="wcvm_<?= esc_attr($vendorId) ?>_freight_in"><?= esc_html__('Freight In', 'wcvm') ?></label>
                <input type="text" id="wcvm_<?= esc_attr($vendorId) ?>_freight_in" name="wcvm_<?= esc_attr($vendorId) ?>_freight_in" value="<?= esc_attr(get_post_meta($product->ID, 'wcvm_' . $vendorId . '_freight_in', true)) ?>">-->

            </p>
            <p class="form-field">
                <label for="wcvm_<?= esc_attr($vendorId) ?>_price_bulk"><?= esc_html__('Bulk Price', 'wcvm') ?></label>
                <input type="text" id="wcvm_<?= esc_attr($vendorId) ?>_price_bulk" name="wcvm_<?= esc_attr($vendorId) ?>_price_bulk" value="<?= esc_attr(get_post_meta($product->ID, 'wcvm_' . $vendorId . '_price_bulk', true)) ?>">
            </p>
            <p class="form-field">
                <label for="wcvm_<?= esc_attr($vendorId) ?>_price_notes"><?= esc_html__('Notes', 'wcvm') ?></label>
                <textarea rows="2" cols="20" id="wcvm_<?= esc_attr($vendorId) ?>_price_notes" name="wcvm_<?= esc_attr($vendorId) ?>_price_notes"><?= esc_html(get_post_meta($product->ID, 'wcvm_' . $vendorId . '_price_notes', true)) ?></textarea>
            </p>
            <p class="form-field">
                <label for="wcvm_<?= esc_attr($vendorId) ?>_primary"><?= esc_html__('Primary Vendor', 'wcvm') ?></label>
                <input type="checkbox" class="checkbox" id="wcvm_<?= esc_attr($vendorId) ?>_primary" data-role="primary" name="wcvm_primary" value="<?= esc_attr($prototypeId) ?>"<?php if ($prototypeId && $prototypeId == $product->wcvm_primary): ?> checked="checked"<?php endif ?>>
            </p>
            <p class="form-field">
                <label>&nbsp;</label>
                <button type="button" class="button" data-role="delete" data-id="<?= esc_attr($prototypeId) ?>"><?= esc_html__('Delete', 'wcvm') ?></button>
            </p>
        </div>
    <?php endforeach ?>

    <div class="options_group" data-role="border">
        <p class="form-field">
            <label for="wcvm_add"><?= esc_html__('Choose Vendor', 'wcvm') ?></label>
            <?php if ($vendors): ?>
                <select class="select" id="wcvm_add" name="wcvm_add">
                    <option value=""></option>
                    <?php foreach ($vendors as $vendor): ?>
                        <option value="<?= esc_attr($vendor->ID) ?>"<?php if (in_array($vendor->ID, $selectedVendors)): ?> disabled="disabled"<?php endif ?>><?= esc_html($vendor->post_title) ?></option>
                    <?php endforeach ?>
                </select>
                <span class="description">
                    <button type="button" class="button button-primary" data-role="add"><?= esc_html__('Add Vendor', 'wcvm') ?></button>
                </span>
            <?php else: ?>
                <?= esc_html__('Please add vendors under Vendor Management section', 'wcvm') ?>
            <?php endif ?>
        </p>
    </div>
</div>
<script>
    function update_total(vendor_id)
    {
        var last_price = jQuery("#wcvm_" + vendor_id + "_price_last").val().replace("$", "");
        var freight_in = jQuery("#wcvm_" + vendor_id + "_freight_in").val().replace("$", "");
        if (last_price == "")
        {
            last_price = parseFloat(0);
        }
        if (freight_in == "")
        {
            freight_in = parseFloat(0);
        }
        var total_price = parseFloat(last_price) + parseFloat(freight_in);
        if (isNaN(total_price))
        {
            total_price = "Invalid Input";
        }
        else
        {
            total_price = "$" + total_price.toFixed(2);
        }
        jQuery("#" + vendor_id + "_total_price").html("<b>" + total_price + "</b>");
    }
</script>

    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=new-order') ?>"<?php if (!$status || $status == 'new-order'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('New', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=on-order') ?>"<?php if ($status == 'on-order'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('On order', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=back-order') ?>"<?php if ($status == 'back-order'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Back order', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=completed') ?>"<?php if ($status == 'completed'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Completed', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=canceled') ?>"<?php if ($status == 'canceled'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Canceled', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=returned') ?>"<?php if ($status == 'returned'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Returns Open', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=return_closed') ?>"<?php if ($status == 'return_closed'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Returns Closed', 'wcvm') ?></a>
    |
    <a href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=trash') ?>"<?php if ($status == 'trash'): ?> style="font-weight: bold"<?php endif ?>><?= esc_html__('Trash', 'wcvm') ?></a>    

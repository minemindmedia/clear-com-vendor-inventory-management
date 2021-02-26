<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .po-status {
            font-size: 15px;
        }

        .po-status.po-new {
            color: #c1121f;
        }
        .po-status.po-on-order {
            color: #fe5f55;
        }
        .po-status.po-back-order {
            color: #f8961e;
        }
        .po-status.po-completed {
            color: #c9a227;
        }
        .po-status.po-canceled {
            color: #38b000;
        }
        .po-status.po-returned {
            color: #43aa8b;
        }
        .po-status.po-return-closed {
            color: #2176ff;
        }
        .po-status.po-trash {
            color: #1b263b;
        }
    </style>
    <a class="p-2 bg-indigo-600 hover:bg-indigo-300 text-white text-base" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=new-order') ?>" <?php if (!$status || $status == 'new-order') : ?> style="font-weight: bold" <?php endif ?>><?= esc_html__('New orders', 'wcvm') ?></a>
    |
    <a class="po-status po-on-order" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=on-order') ?>" <?php if ($status == 'on-order') : ?> style="font-weight: bold" <?php endif ?>><?= esc_html__('On order', 'wcvm') ?></a>
    |
    <a class="po-status po-back-order" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=back-order') ?>" <?php if ($status == 'back-order') : ?> style="font-weight: bold" <?php endif ?>><?= esc_html__('Back order', 'wcvm') ?></a>
    |
    <a class="po-status po-completed" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=completed') ?>" <?php if ($status == 'completed') : ?> style="font-weight: bold" <?php endif ?>><?= esc_html__('Completed', 'wcvm') ?></a>
    |
    <a class="po-status po-canceled" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=canceled') ?>" <?php if ($status == 'canceled') : ?> style="font-weight: bold" <?php endif ?>><?= esc_html__('Canceled', 'wcvm') ?></a>
    |
    <a class="po-status po-returned" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=returned') ?>" <?php if ($status == 'returned') : ?> style="font-weight: bold" <?php endif ?>><?= esc_html__('Returns Open', 'wcvm') ?></a>
    |
    <a class="po-status po-return-closed" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=return_closed') ?>" <?php if ($status == 'return_closed') : ?> style="font-weight: bold" <?php endif ?>><?= esc_html__('Returns Closed', 'wcvm') ?></a>
    |
    <a class="po-status po-trash" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=trash') ?>" <?php if ($status == 'trash') : ?> style="font-weight: bold" <?php endif ?>><?= esc_html__('Trash', 'wcvm') ?></a>
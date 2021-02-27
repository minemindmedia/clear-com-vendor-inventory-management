<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
<script src="https://kit.fontawesome.com/4de99c111d.js" crossorigin="anonymous"></script>

<h1><?= esc_html__('View/Edit Purchase Orders', 'wcvm') ?></h1>

<div class="flex space-x-4 my-4">
    <a class="relative p-2 bg-indigo-600 text-white hover:text-white text-md rounded hover:underline focus:text-white focus:outline-none" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=new-order') ?>">
        <?= esc_html__('New orders', 'wcvm') ?>
        <?php if (!$status || $status == 'new-order') : ?> 
            <svg class="absolute right-0 mr-2 transform rotate-45 text-indigo-600 w-4 h-4 mx-auto z-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="triangle" class="svg-inline--fa fa-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M329.6 24c-18.4-32-64.7-32-83.2 0L6.5 440c-18.4 31.9 4.6 72 41.6 72H528c36.9 0 60-40 41.6-72l-240-416z"></path></svg>
        <?php endif ?>
    </a>


    <a class="relative p-2 bg-green-600  text-white hover:text-white text-md rounded hover:underline focus:text-white focus:outline-none" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=on-order') ?>">
    <?= esc_html__('On order', 'wcvm') ?>
        <?php if ($status == 'on-order') : ?>
            <svg class="absolute right-0 mr-2 transform rotate-45 text-green-600 w-4 h-4 mx-auto z-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="triangle" class="svg-inline--fa fa-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M329.6 24c-18.4-32-64.7-32-83.2 0L6.5 440c-18.4 31.9 4.6 72 41.6 72H528c36.9 0 60-40 41.6-72l-240-416z"></path></svg>
        <?php endif ?>
        </a>

    <a class="relative p-2 bg-pink-600  text-white hover:text-white text-md rounded hover:underline focus:text-white focus:outline-none" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=back-order') ?>">
    <?= esc_html__('Back order', 'wcvm') ?>
    <?php if ($status == 'back-order') : ?> 
        <svg class="absolute right-0 mr-2 transform rotate-45 text-pink-600 w-4 h-4 mx-auto z-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="triangle" class="svg-inline--fa fa-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M329.6 24c-18.4-32-64.7-32-83.2 0L6.5 440c-18.4 31.9 4.6 72 41.6 72H528c36.9 0 60-40 41.6-72l-240-416z"></path></svg>
    <?php endif ?>
    </a>

    <a class="relative p-2 bg-red-600  text-white hover:text-white text-md rounded hover:underline focus:text-white focus:outline-none" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=completed') ?>">
    <?= esc_html__('Completed', 'wcvm') ?>
    <?php if ($status == 'completed') : ?>
        <svg class="absolute right-0 mr-2 transform rotate-45 text-red-600 w-4 h-4 mx-auto z-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="triangle" class="svg-inline--fa fa-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M329.6 24c-18.4-32-64.7-32-83.2 0L6.5 440c-18.4 31.9 4.6 72 41.6 72H528c36.9 0 60-40 41.6-72l-240-416z"></path></svg>
    <?php endif ?>
    </a>

    <a class="relative p-2 bg-yellow-600  text-white hover:text-white text-md rounded hover:underline focus:text-white focus:outline-none" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=canceled') ?>">
    <?= esc_html__('Canceled', 'wcvm') ?>
    <?php if ($status == 'canceled') : ?>
        <svg class="absolute right-0 mr-2 transform rotate-45 text-yellow-600 w-4 h-4 mx-auto z-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="triangle" class="svg-inline--fa fa-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M329.6 24c-18.4-32-64.7-32-83.2 0L6.5 440c-18.4 31.9 4.6 72 41.6 72H528c36.9 0 60-40 41.6-72l-240-416z"></path></svg>
    <?php endif ?>
    </a>
    
    <a class="relative p-2 bg-purple-600  text-white hover:text-white text-md rounded hover:underline focus:text-white focus:outline-none" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=returned') ?>">
    <?= esc_html__('Returns Open', 'wcvm') ?>
    <?php if ($status == 'returned') : ?>
        <svg class="absolute right-0 mr-2 transform rotate-45 text-purple-600 w-4 h-4 mx-auto z-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="triangle" class="svg-inline--fa fa-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M329.6 24c-18.4-32-64.7-32-83.2 0L6.5 440c-18.4 31.9 4.6 72 41.6 72H528c36.9 0 60-40 41.6-72l-240-416z"></path></svg>
    <?php endif ?>
    </a>

    <a class="relative p-2 bg-blue-600  text-white hover:text-white text-md rounded hover:underline focus:text-white focus:outline-none" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=return_closed') ?>">
    <?= esc_html__('Returns Closed', 'wcvm') ?>
    <?php if ($status == 'return_closed') : ?>
        <svg class="absolute right-0 mr-2 transform rotate-45 text-blue-600 w-4 h-4 mx-auto z-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="triangle" class="svg-inline--fa fa-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M329.6 24c-18.4-32-64.7-32-83.2 0L6.5 440c-18.4 31.9 4.6 72 41.6 72H528c36.9 0 60-40 41.6-72l-240-416z"></path></svg>
    <?php endif ?>
    </a>

    <a class="relative p-2 bg-gray-600  text-white hover:text-white text-md rounded hover:underline focus:text-white focus:outline-none" href="<?= site_url('/wp-admin/admin.php?page=wcvm-epo&status=trash') ?>">
    <?= esc_html__('Trash', 'wcvm') ?>
    <?php if ($status == 'trash') : ?>
        <svg class="absolute right-0 mr-2 transform rotate-45 text-gray-600 w-4 h-4 mx-auto z-0" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="triangle" class="svg-inline--fa fa-triangle fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M329.6 24c-18.4-32-64.7-32-83.2 0L6.5 440c-18.4 31.9 4.6 72 41.6 72H528c36.9 0 60-40 41.6-72l-240-416z"></path></svg>
    <?php endif ?>
    </a>
</div>
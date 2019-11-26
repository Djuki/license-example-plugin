<?php

function lc_store_premium_values()
{
    if (!isset($_GET['action'])) return;
    if ($_GET['action'] !== 'lc_store_premium_values') return;

    echo 'Run cron task here.';
    exit;
}
add_action('init', 'lc_store_premium_values');
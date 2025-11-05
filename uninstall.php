<?php
// uninstall.php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clear all cron events
wp_clear_scheduled_hook('check_active_advertisements_cron');
wp_clear_scheduled_hook('check_inactive_advertisements_cron');
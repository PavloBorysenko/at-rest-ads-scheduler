<?php
/**
 * Plugin Name: AtRest Ads Scheduler
 * Description: A plugin to schedule advertisements to be shown on the website.
 * Author: Na-Gora
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// New function CR29.

// Please add to Advertisement post type acf fields:
// - show_from: Date picker
// - show_to: Date picker
// - is_scheduled: True/False field

define('AT_REST_ADS_SCHEDULER_DIR', plugin_dir_path(__FILE__));
define('AT_REST_ADS_SCHEDULER_URL', plugin_dir_url(__FILE__));

require_once AT_REST_ADS_SCHEDULER_DIR . 'src/Data/AdsPosts.php';
require_once AT_REST_ADS_SCHEDULER_DIR . 'src/AdsAutoUpdate.php';

// Auto-update is_active based on dates when saving advertisement post
new Supernova\AtRestScheduler\AdsAutoUpdate\AdsAutoUpdate();

add_action('acf/input/admin_footer', function() {
    if (!isset($_GET['post_type']) || 'advertisement' !== $_GET['post_type']) {
        return;
    }
    ?>
    <script src="<?php echo esc_url(AT_REST_ADS_SCHEDULER_URL . 'assets/js/datepicker.js'); ?>"></script>
    <?php
}, 9999999);
  
register_activation_hook(__FILE__, function() {
    if (!wp_next_scheduled('check_active_advertisements_cron')) {
        wp_schedule_event(time(), 'twicedaily', 'check_active_advertisements_cron');
    }

    if (!wp_next_scheduled('check_inactive_advertisements_cron')) {
        wp_schedule_event(time(), 'twicedaily', 'check_inactive_advertisements_cron');
    }
});

register_deactivation_hook(__FILE__, function() {
    $timestamp = wp_next_scheduled('check_active_advertisements_cron');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'check_active_advertisements_cron');
    }

    $timestamp = wp_next_scheduled('check_inactive_advertisements_cron');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'check_inactive_advertisements_cron');
    }
});


add_action('check_active_advertisements_cron', function() {
    $adsPosts = new Supernova\AtRestScheduler\Data\AdsPosts('advertisement');
    $post_ids = $adsPosts->getAdvertisementsToDeactivate();
    if (empty($post_ids)) return;
    foreach ($post_ids as $post_id) {
        update_field('is_active', false, $post_id);
    }
});
  
add_action('check_inactive_advertisements_cron', function() {
    $adsPosts = new Supernova\AtRestScheduler\Data\AdsPosts('advertisement');
    $post_ids = $adsPosts->getAdvertisementsToActivate();
    if (empty($post_ids)) return;
    foreach ($post_ids as $post_id) {
        update_field('is_active', true, $post_id);
    }  
});

  


  
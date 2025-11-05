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


add_action('acf/input/admin_footer', function() {
    ?>
    <script>
    (function($){
        acf.add_filter('date_picker_args', function( args, field ){
            console.log(field[0].dataset.name)
            console.log(field.data('name'))
  
            if(field.data('name') === 'show_from' || field.data('name') === 'show_to') {
                args.maxDate = +10000;
                args.minDate = -10;
            }            
            return args;
        });
    })(jQuery);
    </script>
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
    $post_ids = get_advertisements_to_deactivate();
    if (empty($post_ids)) return;
    foreach ($post_ids as $post_id) {
        update_field('is_active', false, $post_id);
    }
});
  
add_action('check_inactive_advertisements_cron', function() {
    $post_ids = get_advertisements_to_activate();
    if (empty($post_ids)) return;
    foreach ($post_ids as $post_id) {
        update_field('is_active', true, $post_id);
    }  
});
  
function get_advertisements_to_deactivate() : array {
    $now = current_time('Ymd');
    $args = [
        'post_type' => 'advertisement',
        'post_status' => 'publish',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'is_active',
                'value' => "1",
                'compare' => '='
            ],
            [
                'relation' => 'OR',
                [
                    'key' => 'show_to',
                    'value' => $now,
                    'compare' => '<',
                    'type' => 'NUMERIC'
                ],
                [
                    'key' => 'show_from',
                    'value' => $now,
                    'compare' => '>',
                    'type' => 'NUMERIC'
                ],
            ],
        ],
        'fields' => 'ids',
    ];
    $query = new WP_Query($args);
  
    if ($query->have_posts()) {
      return $query->posts;
    }
    return [];
}
function get_advertisements_to_activate() : array {
    $now = current_time('Ymd');
    $args = [
        'post_type' => 'advertisement',
        'post_status' => 'publish',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'is_active',
                'value' => "0",
                'compare' => '='
            ],
            [
                'key' => 'show_from',
                'value' => $now,
                'compare' => '<=',
                'type' => 'NUMERIC'
            ],
            [
                'key' => 'show_to',
                'value' => $now,
                'compare' => '>',
                'type' => 'NUMERIC'
            ],
        ],
        'fields' => 'ids',
    ];
    $query = new WP_Query($args);
  
    if ($query->have_posts()) {
      return $query->posts;
    }
    return [];
}
  
// Auto-update is_active based on dates when saving advertisement post
add_action('acf/save_post', function($post_id) {
    if (get_post_type($post_id) !== 'advertisement') return;
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;
    
    $post_status = get_post_status($post_id);
    if ($post_status !== 'publish') return;
    
    $now = current_time('Ymd');
    
    
    $show_from = get_field('show_from', $post_id, false);
    $show_to = get_field('show_to', $post_id, false);
    
    // Don't auto-update is_active if either date is missing
    if (empty($show_from) || empty($show_to)) return;
    
    // This handles any Return Format setting in ACF (d/m/Y, Y-m-d, Ymd, etc.)
    $show_from_date = null;
    if ($show_from) {
        $timestamp = strtotime($show_from);
        if ($timestamp !== false) {
            $show_from_date = date('Ymd', $timestamp);
        }
    }
    
    $show_to_date = null;
    if ($show_to) {
      $timestamp = strtotime($show_to);
      if ($timestamp !== false) {
        $show_to_date = date('Ymd', $timestamp);
      }
    }
    
  
    if (!$show_from_date || !$show_to_date) return;
    
    $should_be_active = true;
    
    // Deactivate if current date is before show_from (not started yet)
    if ($show_from_date && $now < $show_from_date) {
        $should_be_active = false;
    }
    
    // Deactivate if current date is after show_to (already ended)
    if ($show_to_date && $now > $show_to_date) {
        $should_be_active = false;
    }
    
    update_field('is_active', $should_be_active, $post_id);
}, 20);
  
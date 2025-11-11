<?php

namespace Supernova\AtRestScheduler\AdsAutoUpdate;

class AdsAutoUpdate {
    public function __construct() {
        add_action('acf/save_post', [$this, 'updateAdsScheduler'], 20);
    }

    public function updateAdsScheduler($post_id) {

        if (!$this->isNeedToUpdate($post_id)) return;
        
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
    }

    private function isNeedToUpdate($post_id) : bool {

        if (get_post_type($post_id) !== 'advertisement') return false;
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return false;
        if (wp_is_post_revision($post_id)) return false;
        
        $post_status = get_post_status($post_id);
        if ($post_status !== 'publish') return false;

        return true;
    }
}
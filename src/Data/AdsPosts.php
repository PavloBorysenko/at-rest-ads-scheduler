<?php

namespace Supernova\AtRestScheduler\Data;

class AdsPosts {

    private string $postType;
    public function __construct($postType) {
        $this->postType = $postType;
    }

    public function getAdvertisementsToDeactivate() : array {
        $args = $this->getBaseQueryArgs();
        $args['meta_query'] = $this->getMetaQueryToDeactivate();

        $query = new \WP_Query($args);
      
        if ($query->have_posts()) {
          return $query->posts;
        }
        return [];
    }
    public function getAdvertisementsToActivate() : array {
        $args = $this->getBaseQueryArgs();
        $args['meta_query'] = $this->getMetaQueryToActivate();

        $query = new \WP_Query($args);
      
        if ($query->have_posts()) {
          return $query->posts;
        }
        return [];
    }

    private function getBaseQueryArgs() : array {
        return [
            'post_type' => $this->postType,
            'post_status' => 'publish',
            'fields' => 'ids',
        ];
    }
    private function getMetaQueryToDeactivate() : array {
        $now = current_time('Ymd');
        return [
            'relation' => 'AND',
            [
                'key' => 'is_active',
                'value' => "1",
                'compare' => '='
            ],
            [
                    'key' => 'is_scheduled',
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
            ]
        ];
    }

    private function getMetaQueryToActivate() : array {
        $now = current_time('Ymd');
        return [
            'relation' => 'AND',
            [
                'key' => 'is_active',
                'value' => "0",
                'compare' => '='
            ],
            [
                'key' => 'is_scheduled',
                'value' => "1",
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
                'compare' => '>=',
                'type' => 'NUMERIC'
            ],
        ];
    }
}
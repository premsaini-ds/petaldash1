<?php

namespace HelpieFaq\Includes\Tracking;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Tracking\Events')) {
    class Events
    {
        public function get_events()
        {
            $faqs_count = $this->get_faqs_count();
            $qna_count = $this->get_qna_count();
            $faq_groups_count = $this->get_faq_groups_taxonomy_count();
            $faq_tags_count = $this->get_faq_tags_taxonomy_count();

            $events = array(
                "faq_counts" => $faqs_count,
                "qna_counts" => $qna_count,
                "faq_groups_counts" => $faq_groups_count,
                "faq_tags_counts" => $faq_tags_count,
            );
            return $events;
        }

        public function get_faqs_count()
        {
            $args = array(
                'post_type' => 'helpie_faq',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => 'question_types',
                        'value' => 'qna',
                        'compare' => 'NOT LIKE',
                    ),
                    array(
                        'key' => 'question_types',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            );

            $query = new \WP_Query($args);
            $count = $query->found_posts;

            return $count;
        }

        public function get_qna_count()
        {
            $args = array(
                'post_type' => 'helpie_faq',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => 'question_types',
                        'value' => 'qna',
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key' => 'question_types',
                        'compare' => 'EXISTS',
                    ),
                ),
            );

            $query = new \WP_Query($args);
            $count = $query->found_posts;

            return $count;
        }

        public function get_faq_groups_taxonomy_count()
        {
            $terms = get_terms(array(
                'taxonomy' => 'helpie_faq_group',
                'hide_empty' => false,
            ));

            $count = count($terms);

            return $count;
        }

        public function get_faq_tags_taxonomy_count()
        {
            $tags = get_tags(array(
                'taxonomy' => 'helpie_faq_tag',
                'orderby' => 'name',
                'hide_empty' => false,
            ));
            $count = is_wp_error($tags) ? 0 : count($tags);
            return $count;
        }
    }
}

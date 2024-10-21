<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Integration')) {
    class Integration
    {

        public function get_post_ids_from_group_settings($post_id)
        {
            $post = get_post($post_id);
            $group_settings = $this->get_all_group_settings();

            $matched_group_ids_by_post = $this->get_matched_group_ids_by_post($group_settings, $post);
            $matched_group_ids_by_terms = $this->get_matched_group_ids_by_terms($group_settings, $post);

            $group_ids = array_unique(array_merge($matched_group_ids_by_post, $matched_group_ids_by_terms));
            $post_ids = $this->get_all_group_faq_items($group_ids);
            return $post_ids;
        }

        private function get_all_group_settings()
        {

            $terms = get_terms(array(
                'taxonomy' => 'helpie_faq_group',
                'hide_empty' => false,
            ));

            $all_group_settings = [];

            if (is_wp_error($terms) || empty($terms)) {
                return [];
            }

            foreach ($terms as $term_id => $term) {
                $term_id = $term->term_id;
                $term_meta = get_term_meta($term_id, 'faq_group_settings', true);
                $settings = isset($term_meta['fields']) ? $term_meta['fields'] : [];
                if (empty($settings)) {
                    continue;
                }
                $all_group_settings[$term_id] = $settings;
            }

            return $all_group_settings;
        }

        public function get_matched_group_ids_by_post($all_group_settings, $post)
        {
            $matched_group_ids = [];
            foreach ($all_group_settings as $term_id => $single_settings) {

                foreach ($single_settings as $key => $field) {

                    $is_post_field = strpos($key, "post__") === 0;
                    if (!$is_post_field || !isset($field['value'])) {
                        continue;
                    }

                    $post_found = in_array($post->ID, $field['value']);

                    if ($post_found) {
                        array_push($matched_group_ids, $term_id);
                    }
                }
            }

            return $matched_group_ids;
        }

        public function get_matched_group_ids_by_terms($all_group_settings, $post)
        {

            $taxonomies = get_object_taxonomies($post->post_type, 'objects');
            if (is_wp_error($taxonomies) || empty($taxonomies)) {
                return [];
            }

            $matched_group_ids = [];

            foreach ($taxonomies as $taxonomy_name => $taxonomy) {

                foreach ($all_group_settings as $group_id => $single_settings) {

                    foreach ($single_settings as $key => $field) {

                        $is_taxonomy_field = strpos($key, "taxonomy__") === 0;
                        if (!$is_taxonomy_field || !isset($field['value'])) {
                            continue;
                        }

                        $taxonomy_configured = ($taxonomy_name == $field['value']);

                        if (!$taxonomy_configured) {
                            continue;
                        }

                        $split_keys = explode("__", $key);

                        $field_position = end($split_keys);

                        $terms_field_key_name = "terms__" . $field_position;
                        $configured_term_values = isset($single_settings[$terms_field_key_name]['value']) ? $single_settings[$terms_field_key_name]['value'] : [];

                        if (empty($configured_term_values)) {
                            continue;
                        }

                        $term_ids = $this->get_all_term_ids_by_post($post, $taxonomy_name);

                        $terms_congifured = array_intersect($term_ids, $configured_term_values);

                        if (count($terms_congifured) > 0) {
                            $matched_group_ids[] = $group_id;
                        }
                    }
                }
            }

            $matched_group_ids = array_unique($matched_group_ids);

            return $matched_group_ids;
        }

        private function get_all_group_faq_items($group_ids)
        {
            $post_ids = [];

            foreach ($group_ids as $group_id) {
                $term_meta = get_term_meta($group_id, 'helpie_faq_group_items');
                $faq_group_items = isset($term_meta[0]['faq_groups']) ? $term_meta[0]['faq_groups'] : [];

                foreach ($faq_group_items as $key => $faq) {
                    array_push($post_ids, $faq['faq_item']['post_id']);
                }
            }

            return $post_ids;
        }

        public function get_posts_by_ids($post_ids)
        {
            if (empty($post_ids)) {
                return [];
            }
            $args = array(
                'post__in' => $post_ids,
                'post_type' => 'helpie_faq',
                'nopaging' => true,
            );

            $posts = get_posts($args);
            if (is_wp_error($posts)) {
                return [];
            }

            return $posts;
        }
        private function get_all_term_ids_by_post($post, $taxonomy_name)
        {
            $terms = get_the_terms($post->ID, $taxonomy_name);
            if (is_wp_error($terms) || empty($terms)) {
                return [];
            }
            $term_ids = [];

            foreach ($terms as $term) {
                $term_ids[] = $term->term_id;

                if (!empty($term->parent)) {
                    $children_ids = $this->get_all_childrens($term->parent, $term->taxonomy);
                    $term_ids = array_merge($term_ids, $children_ids);
                }
            }
            $term_ids = array_unique($term_ids);
            return $term_ids;
        }

        public function get_all_childrens($term_id, $taxonomy)
        {
            $term_ids = array();
            $parent_category = get_term_by('id', $term_id, $taxonomy);
            if (!empty($parent_category->parent)) {
                $term_ids = $this->get_all_childrens($parent_category->parent, $parent_category->taxonomy);
            }
            $term_ids[] = $term_id;
            return $term_ids;
        }
    }
}

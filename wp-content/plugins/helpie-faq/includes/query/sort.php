<?php

namespace HelpieFaq\Includes\Query;

if (!class_exists('\HelpieFaq\Includes\Query\Sort')) {
    class Sort
    {
        public function get_sort_args(array $args)
        {
            $wp_query_args = array();
            if (!isset($args['sortby'])) {
                return $wp_query_args;
            }
            switch ($args['sortby']) {
                case "alphabetical":
                    $wp_query_args['orderby'] = 'title';
                    break;
                case "updated":
                    $wp_query_args['orderby'] = 'modified';
                    break;
                case "publish":
                    $wp_query_args['orderby'] = 'publish_date';
                    break;
                case "user_engagement":
                    $meta_key_exists = $this->click_counter_meta_key_exists();
                    $meta_args = $this->get_user_engagement_meta_query_args($meta_key_exists);

                    $wp_query_args['orderby'] = 'click_counter';
                    $wp_query_args['order'] = 'DESC';
                    $wp_query_args['meta_query'] = $meta_args;
                    break;
                case "menu_order":
                    $wp_query_args['orderby'] = 'menu_order';
                    // $wp_query_args['order'] = 'DESC';
                    break;
                case "post__in":
                    $wp_query_args['orderby'] = 'post__in';
                    $wp_query_args['order'] = 'ASC';
                    break;
                default:
                    $wp_query_args['orderby'] = 'include';
                    break;
            }
            return $wp_query_args;
        }

        /**
         * Use of Method to check user_engagement meta_key "click_counter" has in postmeta table
         *
         * @return boolean
         */
        public function click_counter_meta_key_exists()
        {
            global $wpdb;
            $click_counter_exists = false;
            $table = $wpdb->prefix . 'postmeta';
            $query = 'select count(meta_key) as no_of_meta_keys from ' . $table . ' where meta_key="click_counter";';
            $result = $wpdb->get_row($wpdb->prepare('SELECT COUNT(meta_key) as no_of_meta_keys FROM ' . $table . ' WHERE meta_key = %s', 'click_counter'), 'ARRAY_A');
            if (!empty($result['no_of_meta_keys'])) {
                $click_counter_exists = true;
            }
            return $click_counter_exists;
        }

        /** Use of Method to get user engagement meta query args  */
        public function get_user_engagement_meta_query_args($click_counter_exists)
        {
            $query_args = [
                'key' => 'click_counter',
                'compare' => 'NOT EXISTS',
            ];
            if ($click_counter_exists == true) {
                $query_args['compare'] = 'EXISTS';
            }
            return array($query_args);
        }
    }
}

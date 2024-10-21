<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Third_Party_Filters_Control')) {
    class Third_Party_Filters_Control
    {

        public function remove_filters()
        {
            if (has_action('the_content')) {
                remove_action('the_content', 'related_post_display_auto');

            }

            if (is_plugin_active('yet-another-related-posts-plugin/yarpp.php')) {
                global $yarpp;
                remove_filter('the_content', [$yarpp, 'the_content'], 1200);
            }

        }

        public function add_filters()
        {
            if (!function_exists('is_plugin_active')) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if (is_plugin_active('related-post/related-post.php')) {
                add_action('the_content', 'related_post_display_auto');
            }

            if (is_plugin_active('yet-another-related-posts-plugin/yarpp.php')) {
                global $yarpp;
                // $yarpp = new \YARPP();
                add_filter('the_content', [$yarpp, 'the_content']);
            }
        }

    }

}

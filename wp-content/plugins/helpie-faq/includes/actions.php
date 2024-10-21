<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Actions')) {
    class Actions
    {
        public $faq_category_taxonomy_name = '';
        public $plugin_domain = '';
        public $version = '';

        public function __construct()
        {
            $this->plugin_domain = HELPIE_FAQ_DOMAIN;
            $this->version = HELPIE_FAQ_VERSION;
            $this->faq_category_taxonomy_name = HELPIE_FAQ_CATEGORY_TAXONOMY;
        }

        public function init()
        {
            $this->init_faq_category_actions();

        }

        public function init_faq_category_actions()
        {
            // action for showing custom fields in category table (quick edit)
            add_action('quick_edit_custom_box', array($this, 'add_category_taxonomy_custom_fields'), 10, 3);

            add_action('edited_' . $this->faq_category_taxonomy_name, array($this, 'save_category_taxonomy_custom_fields'), 10, 2);
        }

        public function add_category_taxonomy_custom_fields($column_name, $screen)
        {

            $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field(wp_unslash($_GET['taxonomy'])) : '';
            $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
            $is_faq_category = ($taxonomy == HELPIE_FAQ_CATEGORY_TAXONOMY && $post_type == HELPIE_FAQ_POST_TYPE);
            if (!$is_faq_category) {
                return;
            }
            if ($column_name != 'order' && $screen != 'edit-tags') {
                return;
            }

            $content = '<fieldset>';
            $content .= '<div class="inline-edit-col">';
            $content .= '<label>';
            $content .= '<span class="title">Order</span>';
            $content .= '<span class="input-text-wrap">';
            $content .= '<input type="number" name="order" value="0" />';
            $content .= '</span>';
            $content .= '</label>';
            $content .= '</div>';
            $content .= '</fieldset>';

            hfaq_safe_echo($content);
        }

        public function save_category_taxonomy_custom_fields($term_id)
        {
            if (isset($_POST['order']) && !empty($_POST['order'])) {
                $order = sanitize_text_field(wp_unslash($_POST['order']));
                update_term_meta($term_id, 'order', $order);
            }
        }

        // public function register_frontend_scripts()
        // {
        //     wp_register_script($this->plugin_domain . '-bundle', HELPIE_FAQ_URL . 'assets/bundles/main.app.js', array('jquery'), $this->version, 'all');
        // }

        /* Handle frontend assets */
        public function handle_frontend_assets($location = '')
        {
            wp_enqueue_script($this->plugin_domain . '-bundle', HELPIE_FAQ_URL . 'assets/bundles/main.app.js', array('jquery'), $this->version, 'all');
            wp_enqueue_style($this->plugin_domain . '-bundle-styles', HELPIE_FAQ_URL . 'assets/bundles/main.app.css', array(), $this->version, 'all');
        }

        public function handle_helpie_menu_assets($location = '', $shortcode_params = [])
        {
            // error_log('handle_helpie_menu_assets');
            wp_enqueue_script($this->plugin_domain . '-menu-bundle', HELPIE_FAQ_URL . 'assets/bundles/menu.app.js', array('jquery'), $this->version, 'all');
            wp_enqueue_style($this->plugin_domain . '-menu-bundle-styles', HELPIE_FAQ_URL . 'assets/bundles/menu.app.css', array(), $this->version, 'all');

            /* Load Settings and Data */

            $menu_id = isset($shortcode_params['menu_id']) ? $shortcode_params['menu_id'] : 0;

            if ($menu_id == 0) {
                return;
            }

            $menu_data = $this->get_menu_data($menu_id);

            // error_log('menu_data: ' . print_r($menu_data, true));

            wp_localize_script($this->plugin_domain . '-menu-bundle', 'helpie_menu_data', $menu_data);

        }

        public function get_menu_data($menu_id)
        {
            $title = get_the_title($menu_id);
            $settings = get_post_meta($menu_id, 'helpie_menu_settings', true);

            // error_log('settings: ' . print_r($settings, true));

            $show_children = isset($settings['show_children']) && isset($settings['show_children']['value']) ? $settings['show_children']['value'] : 'yes';
            $show_children = ($show_children == 'yes') ? true : false;

            $show_count = isset($settings['show_count']) && isset($settings['show_count']['value']) ? $settings['show_count']['value'] : 'yes';
            $show_count = ($show_count == 'yes') ? true : false;

            $order_by = isset($settings['order_by']) && isset($settings['order_by']['value']) ? $settings['order_by']['value'] : 'menu_order';
            $order = isset($settings['order']) && isset($settings['order']['value']) ? $settings['order']['value'] : 'ASC';

            $menu = array();
            $menu['title'] = $title;
            $menu['menu_id'] = $menu_id;
            $menu['settings'] = $settings;

            // error_log('menu_source: ' . print_r($settings['menu_source'], true));
            if ($settings['menu_source'] && is_array($settings['menu_source'])) {
                $taxonomy = $settings['menu_source']['value'];
            } else {
                $taxonomy = 'category';
            }

            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'parent' => 0,
                'orderby' => $order_by,
                'order' => $order,

            ));

            $menu['items'] = [];

            foreach ($terms as $term) {
                $item = [
                    'id' => $term->term_id,
                    'title' => $term->name,
                    'slug' => $term->slug,
                    'parent' => $term->parent,
                    'order' => $term->order,
                    'children' => [],
                    'url' => get_term_link($term->term_id),
                ];

                if ($show_children == true) {
                    $child_terms = get_terms(array(
                        'taxonomy' => $taxonomy,
                        'hide_empty' => false,
                        'parent' => $term->term_id,
                        'orderby' => $order_by,
                        'order' => $order,

                    ));

                    foreach ($child_terms as $child_term) {
                        $child_item = [
                            'id' => $child_term->term_id,
                            'title' => $child_term->name,
                            'slug' => $child_term->slug,
                            'parent' => $child_term->parent,
                            'order' => $child_term->order,
                            'url' => get_term_link($child_term->term_id),
                        ];

                        $item['children'][] = $child_item;
                    }
                }

                $menu['items'][] = $item;

            }

            return $menu;
        }

    } // end of class
}

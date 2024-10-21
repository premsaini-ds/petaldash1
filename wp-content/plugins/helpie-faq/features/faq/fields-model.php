<?php

namespace HelpieFaq\Features\Faq;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\HelpieFaq\\Features\\Faq\\Fields_Model' ) ) {
    class Fields_Model {
        public $repo;

        public function __construct() {
            $this->repo = new \HelpieFaq\Includes\Repos\Faq_Repo();
        }

        public function get_fields() {
            $fields = array(
                'title'                        => $this->get_title_field(),
                'title_tag'                    => $this->get_title_tag_field(),
                'categories'                   => $this->get_categories_field(),
                'theme'                        => $this->get_theme_field(),
                'display_mode'                 => $this->get_display_mode(),
                'display_mode_group_by'        => $this->get_display_mode_group_by(),
                'display_mode_group_container' => $this->get_display_mode_group_container(),
                'toggle'                       => $this->get_toggle_field(),
                'open_by_default'              => $this->get_open_by_default_field(),
                'faq_url_attribute'            => $this->get_faq_url_attribute_field(),
                'sortby'                       => $this->get_sortby_field(),
                'order'                        => $this->get_order_field(),
                'limit'                        => $this->get_limit_field(),
                'enable_wpautop'               => $this->get_enable_wpautop_field(),
                'show_search'                  => $this->get_show_search_field(),
                'search_placeholder'           => $this->get_search_placeholder(),
                'product_only'                 => $this->get_product_only(),
            );
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
            /* NOTE: Check for Helpie KB Plugin */
            if ( \is_plugin_active( 'helpie/helpie.php' ) ) {
                $fields['kb_categories'] = $this->get_kb_categories_field();
            }
            /* NOTE: Check for Woocommerce Plugin */
            $woo_integrator = new \HelpieFaq\Includes\Woo_Integrator();
            $is_admin_request = $this->is_admin_request();
            // error_log('is_admin_request: ' . $is_admin_request);
            // $is_helpie_edit_page = $this->is_edit_page();
            if ( $woo_integrator->is_woocommerce_activated() && is_admin() ) {
                // $fields['products'] = $this->get_products_field();
            }
            return $fields;
        }

        public function is_admin_request() {
            /**
             * Get current URL.
             *
             * @link https://wordpress.stackexchange.com/a/126534
             */
            $current_url = home_url( add_query_arg( null, null ) );
            /**
             * Get admin URL and referrer.
             *
             * @link https://core.trac.wordpress.org/browser/tags/4.8/src/wp-includes/pluggable.php#L1076
             */
            $admin_url = strtolower( admin_url() );
            $referrer = strtolower( wp_get_referer() );
            /**
             * Check if this is a admin request. If true, it
             * could also be a AJAX request from the frontend.
             */
            if ( 0 === strpos( $current_url, $admin_url ) ) {
                /**
                 * Check if the user comes from a admin page.
                 */
                if ( 0 === strpos( $referrer, $admin_url ) ) {
                    return true;
                } else {
                    /**
                     * Check for AJAX requests.
                     *
                     * @link https://gist.github.com/zitrusblau/58124d4b2c56d06b070573a99f33b9ed#file-lazy-load-responsive-images-php-L193
                     */
                    if ( function_exists( 'wp_doing_ajax' ) ) {
                        return !wp_doing_ajax();
                    } else {
                        return !(defined( 'DOING_AJAX' ) && DOING_AJAX);
                    }
                }
            } else {
                return false;
            }
        }

        // public function is_edit_page()
        // {
        //     if (!function_exists('get_current_screen')) {
        //         require_once ABSPATH . '/wp-admin/includes/screen.php';
        //     }
        //     $screen = get_current_screen();
        //     $is_helpie_edit_pages = false;
        //     if ($screen->parent_base == 'edit' || $screen->post_type == 'helpie_faq') {
        //         $is_helpie_edit_pages = true;
        //     }
        //     return $is_helpie_edit_pages;
        // }
        public function get_default_args() {
            $args = array();
            // Get Default Values from GET - FIELDS
            $fields = $this->get_fields();
            foreach ( $fields as $key => $field ) {
                $args[$key] = $field['default'];
            }
            return $args;
        }

        protected function get_display_mode() {
            return array(
                'name'    => 'display_mode',
                'label'   => __( 'Display Mode', 'helpie-faq' ),
                'default' => 'simple_accordion',
                'options' => array(
                    'simple_accordion' => __( 'Simple Accordion', 'helpie-faq' ),
                    'faq_list'         => __( 'FAQ List', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        protected function get_display_mode_group_by() {
            return array(
                'name'    => 'display_mode_group_by',
                'label'   => __( 'Display Mode Group By', 'helpie-faq' ),
                'default' => 'none',
                'options' => array(
                    'none'     => __( 'None', 'helpie-faq' ),
                    'category' => __( 'Category', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        protected function get_display_mode_group_container() {
            return array(
                'name'       => 'display_mode_group_container',
                'label'      => __( 'Group Container', 'helpie-faq' ),
                'default'    => 'simple_section_with_header',
                'options'    => array(
                    'simple_section_with_header' => __( 'Simple Section With Header', 'helpie-faq' ),
                    'accordion'                  => __( 'Accordion', 'helpie-faq' ),
                ),
                'type'       => 'select',
                'conditions' => array(
                    'terms' => array(array(
                        'name'     => 'display_mode',
                        'operator' => '==',
                        'value'    => 'simple_accordion',
                    ), array(
                        'name'     => 'display_mode_group_by',
                        'operator' => '!=',
                        'value'    => 'none',
                    )),
                ),
            );
        }

        // FIELDS
        protected function get_title_field() {
            return array(
                'name'    => 'title',
                'label'   => __( 'Title', 'helpie-faq' ),
                'default' => '',
                'type'    => 'text',
            );
        }

        protected function get_kb_categories_field() {
            $options = $this->repo->get_options( 'kb-categories' );
            return array(
                'name'    => 'kb_categories',
                'label'   => __( 'KB Categories', 'helpie-faq' ),
                'default' => 'all',
                'options' => $options,
                'type'    => 'multi-select',
            );
        }

        public function get_toggle_field() {
            return array(
                'name'    => 'toggle',
                'label'   => __( 'Toggle', 'helpie-faq' ),
                'default' => 'on',
                'options' => array(
                    'on'  => __( 'On', 'helpie-faq' ),
                    'off' => __( 'Off', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        public function get_enable_wpautop_field() {
            return array(
                'name'    => 'enable_wpautop',
                'label'   => __( 'Enable wpautop', 'helpie-faq' ),
                'default' => 'off',
                'options' => array(
                    'on'  => __( 'On', 'helpie-faq' ),
                    'off' => __( 'Off', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        public function get_show_search_field() {
            return array(
                'name'    => 'show_search',
                'label'   => __( 'Show Search', 'helpie-faq' ),
                'default' => 'off',
                'options' => array(
                    'on'  => __( 'On', 'helpie-faq' ),
                    'off' => __( 'Off', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        public function get_theme_field() {
            return array(
                'name'    => 'theme',
                'label'   => __( 'Theme', 'helpie-faq' ),
                'default' => 'light',
                'options' => array(
                    'light' => __( 'Light', 'helpie-faq' ),
                    'dark'  => __( 'Dark', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        public function get_open_first_field() {
            return array(
                'name'    => 'open_first',
                'label'   => __( 'Open First FAQ Item', 'helpie-faq' ),
                'default' => 'off',
                'options' => array(
                    'on'  => __( 'On', 'helpie-faq' ),
                    'off' => __( 'Off', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        protected function get_products_field() {
            // error_log('get_products_field()');
            $options = $this->repo->get_options( 'woo-products' );
            return array(
                'name'    => 'products',
                'label'   => __( 'Woo Products', 'helpie-faq' ),
                'default' => 'all',
                'options' => $options,
                'type'    => 'multi-select',
            );
        }

        protected function get_categories_field() {
            $options = $this->repo->get_options( 'categories' );
            return array(
                'name'    => 'categories',
                'label'   => __( 'Categories', 'helpie-faq' ),
                'default' => 'all',
                'options' => $options,
                'type'    => 'multi-select',
            );
        }

        protected function get_tags_field() {
            return array(
                'name'    => 'tags',
                'label'   => __( 'Tags', 'helpie-faq' ),
                'default' => '',
                'type'    => 'text',
            );
        }

        protected function get_sortby_field() {
            $sortby = array(
                'name'    => 'sortby',
                'label'   => __( 'Sort By', 'helpie-faq' ),
                'default' => __( 'Publish Date', 'helpie-faq' ),
                'options' => array(
                    'publish'      => __( 'Publish Date', 'helpie-faq' ),
                    'updated'      => __( 'Updated Date', 'helpie-faq' ),
                    'alphabetical' => __( 'Alphabetical', 'helpie-faq' ),
                    'menu_order'   => __( 'Menu Order', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
            return $sortby;
        }

        protected function get_order_field() {
            return array(
                'name'    => 'order',
                'label'   => __( 'Order', 'helpie-faq' ),
                'default' => 'desc',
                'options' => array(
                    'asc'  => __( 'Ascending', 'helpie-faq' ),
                    'desc' => __( 'Descending', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        protected function get_limit_field() {
            return array(
                'name'    => 'limit',
                'label'   => __( 'Limit', 'helpie-faq' ),
                'default' => 10,
                'type'    => 'number',
            );
        }

        protected function get_search_placeholder() {
            return array(
                'name'      => 'search_placeholder',
                'label'     => __( 'Search Placeholder', 'helpie-faq' ),
                'default'   => __( 'Search FAQ', 'helpie-faq' ),
                'type'      => 'text',
                'condition' => array(
                    'show_search' => 'on',
                ),
            );
        }

        protected function get_submission() {
            return array(
                'name'    => 'show_submission',
                'label'   => __( 'Show Submission', 'helpie-faq' ),
                'default' => 'on',
                'options' => array(
                    'on'  => __( 'On', 'helpie-faq' ),
                    'off' => __( 'Off', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        public function get_faq_url_attribute_field() {
            return array(
                'name'    => 'faq_url_attribute',
                'label'   => __( 'Add FAQ Url Attribute', 'helpie-faq' ),
                'default' => 'on',
                'options' => array(
                    'on'  => __( 'On', 'helpie-faq' ),
                    'off' => __( 'Off', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        public function get_open_by_default_field() {
            return array(
                'name'    => 'open_by_default',
                'label'   => __( 'FAQ Open By Default', 'helpie-faq' ),
                'default' => 'open_first',
                'options' => array(
                    'none'          => __( 'None', 'helpie-faq' ),
                    'open_first'    => __( 'Open First FAQ', 'helpie-faq' ),
                    'open_all_faqs' => __( 'All FAQs', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        public function get_product_only() {
            return array(
                'name'    => 'product_only',
                'label'   => __( 'FAQs Shows Products Only', 'helpie-faq' ),
                'default' => 'off',
                'options' => array(
                    'on'  => __( 'On', 'helpie-faq' ),
                    'off' => __( 'Off', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
        }

        public function get_title_tag_field() {
            $setting_defaults = new \HelpieFaq\Includes\Settings\Option_Values();
            $allowed_title_tags = $setting_defaults->get_allowed_title_tags();
            return array(
                'name'    => 'title_tag',
                'type'    => 'select',
                'label'   => __( 'Select FAQ Title Tag', 'helpie-faq' ),
                'options' => $allowed_title_tags,
                'default' => 'h3',
            );
        }

        // OTHER
    }

    // END CLASS
}
<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Woo_Integrator')) {

    class Woo_Integrator
    {
        private $options;

        public function __construct($callback = '')
        {
            global $helpie_woocommerce_faq_loaded;
            // error_log('woo_integrator...');
            $this->options = $this->get_default_args();

            // error_log('woo_integration_switcher: ' . $this->options['woo_integration_switcher']);
            // error_log('helpie_woocommerce_faq_loaded: ' . $helpie_woocommerce_faq_loaded);

            if ($this->options['woo_integration_switcher'] && false == $helpie_woocommerce_faq_loaded) {

                // $hook_name = $this->options['woo_integration_location'];

                $hook_name = isset($this->options['woo_integration_location']) && $this->options['woo_integration_location'] != '' ? $this->options['woo_integration_location'] : 'woocommerce_product_tabs';

                if ($hook_name == 'woocommerce_product_tabs') {
                    add_filter('woocommerce_product_tabs', [$this, 'woo_new_product_tab']);
                } else {
                    add_action($hook_name, [$this, 'woo_new_product_tab_content'], 10);
                }
                // add_filter('woocommerce_product_tabs', [$this, 'woo_new_product_tab']);
                // add_action('woocommerce_before_add_to_cart_form', [$this, 'woo_new_product_tab_content'], 10);
                // add_action('woocommerce_after_single_product_summary', [$this, 'woo_new_product_tab_content'], 10);
                // add_action('woocommerce_after_add_to_cart_button', [$this, 'woo_new_product_tab_content'], 10);
                // add_action('woocommerce_after_single_product', [$this, 'woo_new_product_tab_content'], 10);

                // add_action('woocommerce_before_add_to_cart_button', [$this, 'woo_new_product_tab_content'], 10);

                // add_filter('woocommerce_product_after_tabs', [$this, 'woo_new_product_tab'], 20);

                $helpie_woocommerce_faq_loaded = true;
            }
        }

        public function woo_new_product_tab($tabs)
        {

            // error_log('woo_new_product_tab...');
            // Adds the new tab
            $tabs['desc_tab'] = array(
                'title' => $this->options['tab_title'],
                'priority' => 50,
                'callback' => array($this, 'woo_new_product_tab_content'),
            );
            return $tabs;
        }

        public function woo_new_product_tab_content()
        {
            global $product;
            //get current product ID
            $product_id = $product->get_ID();

            $Faq = new \HelpieFaq\Features\Faq\Faq();
            $args = $this->options;

            // error_log('args: ' . print_r($args, true));

            // Except other fields
            unset($args['title']);

            $args['products'] = $product_id;
            // The new tab content
            $faq_view = $Faq->get_view($args);

            $product_has_faqs = $this->does_product_have_faqs($args, $Faq);
            // error_log('woo_new_product_tab_content...' . print_r($faq_view, true));

            // echo "is_empty: " . empty($faq_view);
            // echo $faq_view;
            if ($product_has_faqs || $args['show_submission'] == 1) {
                hfaq_safe_echo($faq_view);
            } else {
                // hfaq_safe_echo($faq_view);
                echo "<style>li.desc_tab_tab{ display:none !important; }</style>";
            }
        }

        public function does_product_have_faqs($args, $Faq)
        {
            $viewProps = $Faq->model->get_viewProps($args);

            if (isset($viewProps['items']) && !empty($viewProps['items'])) {
                return true;
            } else {
                return false;
            }

        }

        public function is_woocommerce_activated()
        {
            if (class_exists('woocommerce')) {
                return true;
            } else {
                return false;
            }
        }

        public function add_meta_box()
        {
            $post_type = array('helpie_faq');
            add_meta_box(
                'helpie_woo_metabox',
                __('Woocommerce Products', 'sitepoint'),
                array($this, 'load_meta_box_content'),
                $post_type,
                'side',
                'high'
            );
        }

        public function load_meta_box_content($post)
        {
            wp_nonce_field('helpie_woo_metabox_' . $post->ID, 'helpie_woo_metabox_' . $post->ID . '_nonce');

            // Use get_post_meta to retrieve an existing value from the database.
            $selected_product_ids = get_post_meta($post->ID, 'helpie_woo_metabox', true);
            if (empty($selected_product_ids)) {
                $selected_product_ids = array();
            }
            $options = $this->get_products_option();

            $content = '';

            $content .= '<div class="helpie_faq__woo-metabox">';
            foreach ($options as $product_id => $product_title) {

                $checked = '';
                if (isset($selected_product_ids) && !empty($selected_product_ids) && in_array($product_id, $selected_product_ids)) {
                    $checked = 'checked';
                }

                $product_id = esc_attr($product_id);
                $content .= '<div class="meta-control">';
                $content .= '<input type="checkbox" name="helpie_woo_product[]" value="' . $product_id . '" id="helpie_woo_product_' . $product_id . '" ' . $checked . '>';
                $content .= '<label for="helpie_woo_product_' . $product_id . '">' . $product_title . '</label>';
                $content .= '</div>';
            }
            $content .= '</div>';

            hfaq_safe_echo($content);
        }

        public function save_woo_products($post_id)
        {
            if (empty($post_id)) {
                return $post_id;
            }

            $sanitized_data = hfaq_get_sanitized_data("POST", "READ_ALL_AS_TEXT");
            $nonce = 'helpie_woo_metabox_' . $post_id . '_nonce';

            if (!isset($sanitized_data[$nonce])) {
                return $post_id;
            }

            // Verify that the nonce is valid.
            if (!wp_verify_nonce($sanitized_data[$nonce], 'helpie_woo_metabox_' . $post_id)) {
                return $post_id;
            }

            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }

            $post_type = isset($sanitized_data['post_type']) ? $sanitized_data['post_type'] : '';
            if ($post_type != 'helpie_faq') {
                return $post_id;
            }
            $selected_items = isset($sanitized_data['helpie_woo_product']) ? $sanitized_data['helpie_woo_product'] : [];

            update_post_meta($post_id, 'helpie_woo_metabox', $selected_items);

        }

        public function get_products_option($show_all = false)
        {

            // error_log('get_products_option...');

            global $helpie_faq_product_options;
            if (!$this->is_woocommerce_activated()) {
                return new WP_Error('Woocommerce Not Found', __("Woocommerce Not Active", "my_textdomain"));
            }

            if (isset($helpie_faq_product_options) && !empty($helpie_faq_product_options)) {
                return $helpie_faq_product_options;
            }

            $products = get_posts(
                array(
                    'post_type' => 'product',
                    'posts_per_page' => 500,
                    'post_status' => 'publish',
                )
            );

            $products_option = array();
            foreach ($products as $product) {
                $product_id = $product->ID;
                $products_option[$product_id] = $product->post_title;
            }

            if ($show_all == true) {
                $products_option = array('all' => 'All') + $products_option;
            }

            $helpie_faq_product_options = $products_option;

            // error_log('products: ' . print_r($products, true));
            // error_log('helpie_faq_product_options: ' . print_r($helpie_faq_product_options, true));

            return $products_option;
        }

        public function get_products_option_old($show_all = false)
        {

            error_log('get_products_option...');

            global $helpie_faq_products_store;
            if (!$this->is_woocommerce_activated()) {
                return new WP_Error('Woocommerce Not Found', __("Woocommerce Not Active", "my_textdomain"));
            }

            if (!isset($helpie_faq_products_store) || empty($helpie_faq_products_store)) {
                $helpie_faq_products_store = get_posts(
                    array(
                        'post_type' => 'product',
                        'posts_per_page' => -1,
                    )
                );
            }

            $products = $helpie_faq_products_store;

            $products_option = array();
            foreach ($products as $product) {
                $product_id = $product->ID;
                $products_option[$product_id] = $product->post_title;
            }

            if ($show_all == true) {
                $products_option = array('all' => 'All') + $products_option;
            }

            return $products_option;
        }

        public function get_default_args()
        {
            $settings = new \HelpieFaq\Includes\Settings\Getters\Getter();
            $settings_handler = new \HelpieFaq\Features\Faq\Settings_Handlers();
            $user_defined_settings_args = $settings->get_settings();

            $interpreted_settings_args = $settings_handler->get_interpreted_settings_args($user_defined_settings_args);
            $args = array_merge($user_defined_settings_args, $interpreted_settings_args);
            return $args;
        }

    } // END CLASS
}

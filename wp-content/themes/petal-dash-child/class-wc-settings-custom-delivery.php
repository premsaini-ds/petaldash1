<?php

if (!class_exists('WC_Settings_Custom_Delivery')) {

    class WC_Settings_Custom_Delivery extends WC_Settings_Page {

        public function __construct() {
            $this->id = 'custom_delivery';
            $this->label = __('Custom Delivery Settings', 'woocommerce');

            add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 50);
            add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
            add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
        }

        public function get_settings() {
            return apply_filters(
                'woocommerce_custom_delivery_settings',
                array(
                    array(
                        'title' => __('Custom Delivery Options', 'woocommerce'),
                        'type' => 'title',
                        'desc' => __('Configure delivery date options and pricing.', 'woocommerce'),
                        'id'    => 'custom_delivery_options',
                    ),
                    array(
                        'title'   => __('Delivery Dates and Prices', 'woocommerce'),
                        'desc'    => __('Specify the number of days and price.', 'woocommerce'),
                        'id'      => 'custom_delivery_options_data',
                        'type'    => 'textarea',
                        'default' => "1|6.99\n2|6.99\n3|6.99\n4|12.99\n5|12.99\n6|6.99\n7|6.99\n8|12.99\n9|12.99", // Default options
                        'desc_tip' => true,
                    ),
                    array('type' => 'sectionend', 'id' => 'custom_delivery_options'),
                )
            );
        }

        public function output() {
            $settings = $this->get_settings();
            WC_Admin_Settings::output_fields($settings);
        }

        public function save() {
            $settings = $this->get_settings();
            WC_Admin_Settings::save_fields($settings);
        }
    }

    return new WC_Settings_Custom_Delivery();
}

?>
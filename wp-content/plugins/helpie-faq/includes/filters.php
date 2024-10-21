<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Filters')) {
    class Filters
    {
        public $faq_category_taxonomy_name = '';

        public function __construct()
        {
            $this->faq_category_taxonomy_name = HELPIE_FAQ_CATEGORY_TAXONOMY;
        }

        public function init()
        {
            $this->init_faq_category_filters();
        }

        public function init_faq_category_filters()
        {

            add_filter('manage_edit-' . $this->faq_category_taxonomy_name . '_columns', array($this, 'register_custom_columns_to_category_summary_table'));
            add_filter('manage_' . $this->faq_category_taxonomy_name . '_custom_column', array($this, 'set_custom_columns_values_to_category_summary_table'), 10, 3);
        }

        public function register_custom_columns_to_category_summary_table($columns)
        {
            $columns['order'] = __('Order', HELPIE_FAQ_DOMAIN);
            return $columns;
        }

        public function set_custom_columns_values_to_category_summary_table($content, $column_name, $term_id)
        {
            switch ($column_name) {
                case 'order':
                    $order = get_term_meta($term_id, 'order', true);
                    $content = isset($order) && !empty($order) && is_numeric($order) ? $order : 0;
                    break;
            }

            return $content;
        }
    }
}

<?php

namespace HelpieFaq\Features\Faq_Group;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Faq_Group\Filters')) {
    class Filters
    {
        public $taxonomy;

        public function __construct()
        {
            $this->taxonomy = 'helpie_faq_group';
            $this->load_filter_hooks();
        }

        /** load wp filter hooks */
        public function load_filter_hooks()
        {
            add_filter("manage_edit-{$this->taxonomy}_columns", array($this, 'add_faq_group_shortcode_column'), 10, 2);
            add_filter("manage_{$this->taxonomy}_custom_column", array($this, 'add_faq_group_shortcode_column_data'), 10, 3);
        }

        public function add_faq_group_shortcode_column($columns)
        {
            $addedcolumns = array_slice($columns, 0, 4, true) +
            array("faq_group_shortcode" => __("Shortcode", "helpie-faq")) +
            array_slice($columns, 3, count($columns) - 1, true);
            // Remove Description Table Column
            unset($addedcolumns['description']);
            return $addedcolumns;
        }

        public function add_faq_group_shortcode_column_data($content, $column_name, $term_id)
        {
            $html = '';
            if (empty($term_id)) {
                return $html;
            }
            if ($column_name != 'faq_group_shortcode') {
                return $html;
            }

            $html = '';
            $html .= '<span class="helpie-faq-group">';
            $html .= '<span class="shorcode-content">';
            $html .= "[helpie_faq group_id='" . esc_attr($term_id) . "'/]";
            $html .= '</span>';
            $html .= '<span class="clipboard dashicons dashicons-admin-page" title="' . esc_html__('Copy to Shortcode Clipboard', 'helpie-faq') . '" id="' . esc_attr('clipboard-' . $term_id) . '"></span>';
            $html .= '</span>';

            hfaq_safe_echo($html);

        }
    }
}

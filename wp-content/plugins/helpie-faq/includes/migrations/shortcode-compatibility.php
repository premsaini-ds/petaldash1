<?php

namespace HelpieFaq\Includes\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\HelpieFaq\Includes\Migrations\Shortcode_Compatibility')) {
    class Shortcode_Compatibility
    {
        public function get_attributes($atts)
        {
            $atts = $this->get_display_modes_attributes($atts);
            return $atts;
        }

        public function get_display_modes_attributes($atts)
        {
            /** don't do anything, if shortcodes didn't have an attributes */
            if (empty($atts)) {
                return $atts;
            }

            $used_display_mode = isset($atts['display_mode']) ? $atts['display_mode'] : '';

            $group_id = isset($atts['group_id']) && intval($atts['group_id']) ? $atts['group_id'] : 0;

            $display_mode_grouping_exists = (isset($atts['display_mode_group_by']) || isset($atts['display_mode_group_container'])) ? true : false;

            /** old category display modes */
            $category_display_modes = ['simple_accordion_category', 'category_accordion'];

            /** no need to updating the display mode compatibility, if the users using the v1.6.7 shortcodes */
            if ($display_mode_grouping_exists && !in_array($used_display_mode, $category_display_modes)) {
                return $atts;
            }

            $use_group_shortcode_without_display_mode = (!empty($group_id) && empty($used_display_mode)) ? true : false;

            /** It should be processed with global settings values if the group don't have a display mode attribute */
            if ($use_group_shortcode_without_display_mode) {
                return $atts;
            }

            $display_mode = ($used_display_mode == 'faq_list') ? 'faq_list' : 'simple_accordion';
            $display_mode_group_by = (in_array($used_display_mode, $category_display_modes) == true) ? 'category' : 'none';
            $display_mode_group_container = ($used_display_mode == 'category_accordion') ? 'category' : 'simple_section_with_header';

            $atts['display_mode'] = $display_mode;
            $atts['display_mode_group_by'] = $display_mode_group_by;
            $atts['display_mode_group_container'] = $display_mode_group_container;
            return $atts;
        }
    }
}
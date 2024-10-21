<?php

namespace HelpieFaq\Includes\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\HelpieFaq\Includes\Migrations\Version167')) {
    class Version167
    {

        public function run()
        {
            $this->global_settings_migrations();
            $this->elementor_widget_setting_migrations();
        }

        public function global_settings_migrations()
        {
            $settings = get_option('helpie-faq');

            $settings['last_version'] = '1.6.7';

            $settings = $this->update_display_mode_enhancements($settings);

            $result = \update_option('helpie-faq', $settings);
            $updated_option = get_option('helpie-faq');

            if (isset($updated_option['last_version']) && $updated_option['last_version'] == '1.6.7') {
                $result = true;
            }
            return $result;
        }

        public function update_display_mode_enhancements($settings)
        {

            $category_display_modes = ['simple_accordion_category', 'category_accordion'];

            /** Get the display mode for currently, users using  */
            $used_display_mode = isset($settings['display_mode']) ? $settings['display_mode'] : 'simple_accordion';

            /** Let set the display mode value to the current display enhancements feature-based */
            $display_mode = ($used_display_mode == 'faq_list') ? 'faq_list' : 'simple_accordion';

            $display_mode_group_by = (in_array($used_display_mode, $category_display_modes) == true) ? 'category' : 'none';

            $display_mode_group_container = ($display_mode_group_by == 'category' && $used_display_mode == 'category_accordion') ? 'accordion' : 'simple_section_with_header';

            $settings['display_mode'] = $display_mode;
            $settings['display_mode_group_by'] = $display_mode_group_by;
            $settings['display_mode_group_container'] = $display_mode_group_container;

            return $settings;
        }

        public function elementor_widget_setting_migrations()
        {
            $args = array(
                'widget_type' => 'helpie-faq',
                'callback' => array($this, 'update_display_mode_enhancements'),
                'migrated_fields' => array('display_mode', 'display_mode_group_by', 'display_mode_group_container'),
            );
            $migrations = new \Pauple\Pluginator\ElementorMigration($args);
            $migrations->run();
        }
    }
}
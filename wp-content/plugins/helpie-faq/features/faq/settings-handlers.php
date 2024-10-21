<?php

namespace HelpieFaq\Features\Faq;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('HelpieFaq\Features\Faq\Settings_Handlers')) {

    class Settings_Handlers
    {

        public function get_interpreted_settings_args(array $setting_args)
        {
            // get theme styles
            $args = $this->get_theme_styles_args($setting_args);
            $is_premium = hf_fs()->can_use_premium_code__premium_only() ? true : false;
            if ($is_premium == false) {
                return $args;
            }
            return $args;
        }
        public function get_theme_styles_args(array $setting_args)
        {
            $enabled_faq_styles = (isset($setting_args['enable_faq_styles']) && $setting_args['enable_faq_styles'] == 1) ? true : false;
            if ($enabled_faq_styles) {
                return $setting_args;
            }
            $setting_args = $this->set_backgrounds_values_as_transparent($setting_args);
            return $setting_args;
        }

        public function set_backgrounds_values_as_transparent(array $setting_args)
        {
            $setting_args['accordion_background'] = array(
                'header' => 'transparent',
                'body' => 'transparent',
            );
            return $setting_args;
        }
    }
}

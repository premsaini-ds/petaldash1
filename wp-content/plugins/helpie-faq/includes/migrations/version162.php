<?php

namespace HelpieFaq\Includes\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\HelpieFaq\Includes\Migrations\Version162')) {
    class Version162
    {
        public function run()
        {
            $settings = get_option('helpie-faq');

            /* Set new version */
            $settings['last_version'] = '1.6.2';

            $is_premium_plan_user = hf_fs()->can_use_premium_code__premium_only() ? true : false;
            /** set true for show the FAQ theme styles */
            $settings['enable_faq_styles'] = true;

            $theme = isset($settings['theme']) ? $settings['theme'] : 'light';

            $theme_styles = $this->get_default_theme_styles($theme);

            /** Get the accordion backgrounds header & body colors from default styles  */
            $header_background_color = $theme_styles['header'];
            $body_background_color = $theme_styles['body'];

            $use_custom_backgrounds = $this->use_custom_backgrounds($settings, $theme_styles);

            /** Set custom backgrounds, if existing users are using custom backgrounds colors */
            if ($is_premium_plan_user && $use_custom_backgrounds) {
                $header_background_color = $settings['accordion_background']['header'];
                $body_background_color = $settings['accordion_background']['body'];
            }

            $settings['accordion_background'] = array(
                'header' => $header_background_color,
                'body' => $body_background_color,
            );

            $settings = $this->get_border_styles($settings);

            $settings = $this->get_searchbar_styles($settings, $theme_styles);

            $result = \update_option('helpie-faq', $settings);
            $updated_option = get_option('helpie-faq');

            if (isset($updated_option['last_version']) && $updated_option['last_version'] == '1.0') {
                $result = true;
            }
            return $result;
        }

        public function get_default_theme_styles($theme)
        {
            $styles = array(
                'light' => array(
                    'header' => "#FFFFFF",
                    'body' => '#FCFCFC',
                    'search' => array(
                        'background' => "#FFFFFF",
                        'icon' => "#171717",
                        'font_color' => "#171717",
                    ),
                ),
                'dark' => array(
                    'header' => "#171717",
                    'body' => '#272727',
                    'search' => array(
                        'background' => "#171717",
                        'icon' => "#fcfcfc",
                        'font_color' => "#fcfcfc",
                    ),
                ),
            );

            return $styles[$theme];
        }

        public function use_custom_backgrounds($settings, $theme_styles)
        {
            $accordion_background = isset($settings['accordion_background']) ? $settings['accordion_background'] : [];
            if (empty($accordion_background)) {
                return false;
            }
            $header_style_matched = $accordion_background['header'] == $theme_styles['header'] ? true : false;
            $body_style_matched = ($header_style_matched && ($accordion_background['body'] == $theme_styles['body'])) ? true : false;
            return ($body_style_matched == false) ? true : false;
        }

        public function get_border_styles($settings)
        {
            /** set the border default values for existing users, cause the border styles are removed in stylesheet @since v1.6.2   */
            if (!isset($settings['accordion_border'])) {
                $settings['accordion_border'] = array(
                    'top' => '0',
                    'right' => '0',
                    'bottom' => '1',
                    'left' => '0',
                    'style' => 'solid',
                    'color' => '#f2f2f2',
                );
            }
            return $settings;
        }

        public function get_searchbar_styles($settings, $theme_styles)
        {
            $settings['search_background_color'] = isset($settings['search_background_color']) && !empty($settings['search_background_color']) ? $settings['search_background_color'] : $theme_styles['search']['background'];
            $settings['search_font_color'] = isset($settings['search_font_color']) && !empty($settings['search_font_color']) ? $settings['search_font_color'] : $theme_styles['search']['font_color'];
            $settings['search_icon_color'] = isset($settings['search_icon_color']) && !empty($settings['search_icon_color']) ? $settings['search_icon_color'] : $theme_styles['search']['icon'];
            return $settings;
        }
    }
}

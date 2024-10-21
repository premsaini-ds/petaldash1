<?php

namespace HelpieFaq\Includes\Components;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\HelpieFaq\Includes\Components\Shortcode_Builder')) {

    class Shortcode_Builder
    {

        public function __construct()
        {
            // add_action('init', [$this, 'shortcode_builder']);
            $this->shortcode_builder();
        }

        public function shortcode_builder()
        {

            if (class_exists('\CSF')) {
                //TODO: Set unique ID to helpie-faq shortcode builder
                $prefix = 'helpie-faq-shordcode';

                //TODO: Get Helpie FAQ general fields

                $fields = new \HelpieFaq\Includes\Settings\Fields();
                $shortcode_fields = array_merge([],
                    $fields->get_title_section_settings_fields(),
                    $fields->get_search_section_settings_fields(),
                    $fields->get_display_section_settings_fields(),
                    $fields->get_layout_section_settings_fields(),
                    $fields->get_pagination_section_settings_fields(),
                    $fields->get_others_section_settings_fields(),
                    $fields->get_categories_field()
                );

                //TODO: helpie faq shortcode builder button shows only into the code editor
                \CSF::createShortcoder($prefix, array(
                    'button_title' => 'Add Helpie FAQ Shortcode',
                    'select_title' => 'Select a shortcode',
                    'insert_title' => 'Insert Shortcode',
                    'show_in_editor' => true,
                    'gutenberg' => [
                        'title' => __('Helpie FAQ - Shortcode Builder', "helpie-faq"),
                        'icon' => 'screenoptions',
                        'category' => 'widgets',
                        'keywords' => array('table', 'data', 'faq', 'shortcode'),
                    ],
                ));

                \CSF::createSection($prefix, array(
                    'title' => 'Helpie FAQ',
                    'view' => 'normal',
                    'shortcode' => 'helpie_faq',
                    'fields' => $shortcode_fields,
                ));
            }
        }

    }

}

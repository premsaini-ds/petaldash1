<?php

namespace HelpieFaq\Includes\Settings\Getters;

if ( !class_exists( '\\HelpieFaq\\Includes\\Settings\\Getters\\Getter' ) ) {
    class Getter {
        public function get_settings() {
            $options = get_option( 'helpie-faq' );
            /* In case option is not set, set option as empty array */
            // echo $options;
            if ( !isset( $options ) ) {
                $options = array();
            }
            $defaults_settings = $this->get_default_settings();
            $settings = [];
            foreach ( $defaults_settings as $key => $value ) {
                if ( !empty( $options ) && array_key_exists( $key, $options ) ) {
                    $settings[$key] = $options[$key];
                } else {
                    $settings[$key] = $value;
                }
            }
            return $settings;
        }

        public function get_default_settings() {
            $defaults = array(
                'title'                           => 'Helpie FAQ',
                'title_tag'                       => 'h3',
                'show_search'                     => true,
                'search_placeholder'              => 'Search FAQ',
                'toggle'                          => true,
                'open_by_default'                 => 'open_first',
                'faq_url_attribute'               => true,
                'faq_url_type'                    => 'post_title',
                'display_mode'                    => 'simple_accordion',
                'num_of_cols'                     => 1,
                'display_mode_group_by'           => 'none',
                'display_mode_group_container'    => 'simple_section_with_header',
                'sortby'                          => 'publish',
                'order'                           => 'desc',
                'limit'                           => -1,
                'enable_wpautop'                  => false,
                'enable_content_hooks'            => true,
                'enable_schema'                   => true,
                'onload_scrollto_delay'           => 0,
                'enable_same_page_scroll'         => false,
                'enable_faq_styles'               => false,
                'theme'                           => 'light',
                'kb_integration_switcher'         => true,
                'kb_cat_content_show'             => ['before'],
                'woo_integration_switcher'        => true,
                'woo_integration_location'        => 'woocommerce_product_tabs',
                'woo_search_show'                 => true,
                'tab_title'                       => 'FAQ',
                'product_only'                    => false,
                'product_faq_relations'           => [],
                'exclude_from_search'             => true,
                'helpie_faq_slug'                 => 'helpie_faq',
                'helpie_faq_group_slug'           => 'helpie_faq_group',
                'show_title'                      => true,
                'accordion_background'            => [
                    'header' => '#FFFFFF',
                    'body'   => '#FCFCFC',
                ],
                'accordion_header_content_styles' => [],
                'accordion_body_content_styles'   => [],
                'accordion_header_spacing'        => [],
                'accordion_body_spacing'          => [],
                'accordion_border'                => [],
                'icon_color'                      => '#44596B',
                'accordion_margin'                => [],
                'ask_question_button_text'        => 'Add FAQ',
            );
            return $defaults;
        }

        public function get_premium_default_settings( $defaults ) {
            $premium_defaults = array();
            $premium_defaults = array_merge(
                $premium_defaults,
                $this->get_premium_general_fields(),
                $this->get_premium_display_layouts_fields(),
                $this->get_pagination_fields(),
                $this->get_premium_submission_fields(),
                $this->get_premium_styles_fields(),
                $this->get_premium_excerpt_fields()
            );
            return array_merge( $defaults, $premium_defaults );
        }

        public function get_premium_general_fields() {
            $fields = array(
                'search_by_tags' => true,
            );
            return $fields;
        }

        public function get_premium_display_layouts_fields() {
            return [
                'category_sortby' => 'publish',
                'category_order'  => 'desc',
            ];
        }

        public function get_pagination_fields() {
            return array(
                'pagination' => false,
            );
        }

        public function get_premium_styles_fields() {
            $fields = array(
                'toggle_icon_type'          => 'default',
                'toggle_open'               => 'fa fa-minus',
                'toggle_off'                => 'fa fa-plus',
                'icon_position'             => 'right',
                'title_styles'              => [],
                'accordion_header_tag'      => 'default',
                'title_icon'                => '',
                'title_icon_color'          => '#44596B',
                'category_accordion_styles' => [],
                'category_title_icon'       => '',
                'category_title_icon_color' => '#44596B',
                'search_background_color'   => '',
                'search_font_color'         => '',
                'search_icon_color'         => '',
            );
            return $fields;
        }

        public function get_premium_submission_fields() {
            $fields = array(
                'show_submission' => true,
                'ask_question'    => ['email'],
                'onsubmit'        => 'noapproval',
                'submitter_email' => [
                    'submitter_subject' => 'The FAQ you submitted has been approved',
                    'submitter_message' => 'A new FAQ you had submitted has been approved by the admin ',
                ],
                'notify_admin'    => true,
                'admin_email'     => get_option( 'admin_email' ),
            );
            return $fields;
        }

        public function get_premium_excerpt_fields() {
            $fields = array(
                'enable_excerpt'      => false,
                'read_more_link_text' => 'Read More',
                'excerpt_word_length' => '55',
                'open_new_window'     => true,
            );
            return $fields;
        }

    }

}
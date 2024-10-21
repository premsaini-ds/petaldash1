<?php

namespace HelpieFaq\Features\Faq;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\HelpieFaq\\Features\\Faq\\Faq_View' ) ) {
    class Faq_View {
        public function get( $viewProps ) {
            require_once HELPIE_FAQ_PATH . 'lib/stylus/stylus.php';
            $stylus = new \Stylus\Stylus();
            $handlers = new \HelpieFaq\Features\Faq\Handlers();
            $viewProps['collection'] = $handlers->boolean_conversion( $viewProps['collection'] );
            $additional_classes = $this->get_additional_classes( $viewProps );
            $id = '';
            if ( isset( $viewProps['collection']['id'] ) ) {
                $id .= $viewProps['collection']['id'];
            }
            $pagination_controller = new \HelpieFaq\Features\Pagination\Controller();
            $pagination_args = $this->get_pagination_args( $viewProps );
            $pagination_args = ( isset( $pagination_args ) && !empty( $pagination_args ) ? wp_json_encode( $pagination_args ) : "" );
            $pagination_enabled = ( !empty( $pagination_args ) ? "1" : "0" );
            $html = "<section id='{$id}' class='helpie-faq accordions {$additional_classes} ' data-collection='{$pagination_args}' data-pagination='0' data-search='0' data-pagination-enabled='{$pagination_enabled}'>";
            $html .= $this->get_the_title( $viewProps );
            // TODO check FAQ searchbar is enable or not
            $is_faq_search_enabled = $this->is_faq_search_enabled( $viewProps );
            if ( $is_faq_search_enabled ) {
                $html .= $stylus->search->get_view( $viewProps['collection'] );
            }
            $Actions = new \HelpieFaq\Includes\Actions();
            $Actions->handle_frontend_assets( 'helpie_faq_shortcode' );
            $html .= $stylus->accordion->get_view( $viewProps );
            $html .= '</section>';
            return $html;
        }

        public function get_additional_classes( $viewProps ) {
            $classes = [];
            $enable_faq_styles = ( isset( $viewProps['collection']['enable_faq_styles'] ) && $viewProps['collection']['enable_faq_styles'] == true ? true : false );
            if ( $enable_faq_styles && isset( $viewProps['collection']['theme'] ) && $viewProps['collection']['theme'] == 'dark' ) {
                $classes[] = "dark";
            }
            if ( isset( $viewProps['collection']['toggle'] ) && $viewProps['collection']['toggle'] ) {
                $classes[] = "faq-toggle";
            }
            if ( $enable_faq_styles ) {
                $classes[] = "custom-styles";
            }
            if ( isset( $viewProps['collection']['open_by_default'] ) && !empty( $viewProps['collection']['open_by_default'] ) ) {
                if ( $viewProps['collection']['open_by_default'] == 'open_all_faqs' ) {
                    $classes[] = 'open-all';
                }
                if ( $viewProps['collection']['open_by_default'] == 'open_first' ) {
                    $classes[] = 'open-first';
                }
            }
            $group_settings_style_enabled = ( isset( $viewProps['collection']['can_apply_group_style'] ) ? $viewProps['collection']['can_apply_group_style'] : 'no' );
            if ( $group_settings_style_enabled == 'yes' ) {
                $group_id = ( isset( $viewProps['collection']['group_id'] ) ? $viewProps['collection']['group_id'] : 0 );
                $classes[] = "groupSettings-{$group_id}__enabled";
            }
            $classes = implode( ' ', $classes );
            return $classes;
        }

        private function is_faq_search_enabled( $viewProps ) {
            if ( is_singular( 'product' ) ) {
                if ( isset( $viewProps['collection']['woo_search_show'] ) && $viewProps['collection']['woo_search_show'] ) {
                    return true;
                }
            } else {
                if ( isset( $viewProps['collection']['show_search'] ) && $viewProps['collection']['show_search'] ) {
                    return true;
                }
            }
            return false;
        }

        private function get_title_tag( $viewProps ) {
            $title_tag = '';
            $default_tag = 'h3';
            $setting_defaults = new \HelpieFaq\Includes\Settings\Option_Values();
            $allowed_title_tags = $setting_defaults->get_allowed_title_tags();
            $title_tag = ( isset( $viewProps['collection']['title_tag'] ) ? $viewProps['collection']['title_tag'] : '' );
            if ( empty( $title_tag ) ) {
                return $default_tag;
            }
            if ( isset( $allowed_title_tags[$title_tag] ) ) {
                return $title_tag;
            }
            return $default_tag;
        }

        public function get_the_title( $viewProps ) {
            $html = '';
            $show_title = ( isset( $viewProps['collection']['show_title'] ) && $viewProps['collection']['show_title'] == 1 ? true : false );
            $title = ( isset( $viewProps['collection']['title'] ) && !empty( $viewProps['collection']['title'] ) ? $viewProps['collection']['title'] : '' );
            if ( !$show_title || empty( $title ) ) {
                return $html;
            }
            $title_tag = $this->get_title_tag( $viewProps );
            $title_tag = esc_attr( $title_tag );
            $html = '<' . $title_tag . ' class="collection-title">' . $title . '</' . $title_tag . '>';
            return $html;
        }

        public function get_pagination_args( $viewProps ) {
            /** Now the pagination doesn't support elementor dynamic FAQs widget. */
            $pagination_controller = new \HelpieFaq\Features\Pagination\Controller();
            $e_widget_name = $pagination_controller->get_elementor_widget_name( $viewProps );
            if ( $e_widget_name == "helpie-faq-dynamic-add" ) {
                return [];
            }
            /** Dont show the pagination if the limit is below 1 */
            if ( !$pagination_controller->pagination_enabled( $viewProps ) || $pagination_controller->is_category_display_modes( $viewProps ) ) {
                return [];
            }
            $defaults = $this->get_paginate_default_args();
            $collection_props = $viewProps['collection'];
            foreach ( $defaults as $key => $value ) {
                if ( isset( $collection_props[$key] ) ) {
                    $defaults[$key] = $collection_props[$key];
                }
            }
            return $defaults;
        }

        public function get_paginate_default_args() {
            $dafaults = array(
                'group_id'                     => 0,
                'display_mode'                 => 'simple_accordion',
                'display_mode_group_by'        => 'none',
                'display_mode_group_container' => 'simple_section_with_header',
                'toggle'                       => 1,
                'open_by_default'              => 'open_first',
                'faq_url_attribute'            => 1,
                'faq_url_type'                 => 'post_id',
                'limit'                        => -1,
            );
            return $dafaults;
        }

    }

    // END CLASS
}
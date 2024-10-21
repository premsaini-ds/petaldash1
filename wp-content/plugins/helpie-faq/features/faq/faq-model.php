<?php

namespace HelpieFaq\Features\Faq;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( 'HelpieFaq\\Features\\Faq\\Faq_Model' ) ) {
    class Faq_Model {
        public $top_level = '';

        public $product_faq_relations = false;

        public $faq_repo;

        public $style_config;

        public $fields_model;

        public $handlers;

        public function __construct() {
            $this->faq_repo = new \HelpieFaq\Includes\Repos\Faq_Repo();
            $this->style_config = new \HelpieFaq\Features\Faq\Style_Config_Model();
            $this->fields_model = new \HelpieFaq\Features\Faq\Fields_Model();
            $this->handlers = new \HelpieFaq\Features\Faq\Handlers();
        }

        public function get_viewProps( $args ) {
            $display_mode_group_by = ( isset( $args['display_mode_group_by'] ) ? $args['display_mode_group_by'] : 'none' );
            if ( $display_mode_group_by == 'category' ) {
                $this->top_level = 'categories';
            }
            /* Get top level item objs */
            if ( $this->top_level == 'categories' ) {
                $items_wp_objs = $this->get_categories_as_items( $args );
            } else {
                $items_wp_objs = $this->faq_repo->get_faqs( $args );
                $args['total_no_of_pages'] = $this->handlers->get_total_no_of_pages( $args );
            }
            if ( isset( $args['integration'] ) && $args['integration'] == 'lms' ) {
                $items_wp_objs = $args['items_wp_objs'];
            }
            // FAQ Categories Props
            $items_props = $this->get_items_props( $items_wp_objs, $args );
            if ( $this->top_level == 'categories' ) {
                // Remove empty category faqs
                $items_props = $this->handlers->get_non_empty_items_props( $items_props );
            }
            $viewProps = array(
                'collection' => $this->get_collection_props( $args ),
                'items'      => $items_props,
            );
            return $viewProps;
        }

        public function get_categories_as_items( $args ) {
            // TODO Get FAQ Categories From Product FAQ Relations
            if ( is_singular( 'product' ) && is_single( get_the_ID() ) ) {
                $product_faq_relation = new \HelpieFaq\Features\Faq\Particles\Product_Faq_Relations();
                $product_faq_terms = $product_faq_relation->get_product_faq_relation_terms( $args );
                if ( isset( $product_faq_terms['faq_terms'] ) && count( $product_faq_terms['faq_terms'] ) > 0 ) {
                    // TODO Set product_faq_relations is true
                    $this->product_faq_relations = true;
                    $args['categories'] = $product_faq_terms['faq_terms'];
                }
            }
            $items_wp_objs = $this->faq_repo->get_faq_categories( $args );
            return $items_wp_objs;
        }

        public function get_style_config() {
            return $this->style_config->get_config();
        }

        public function get_fields() {
            return $this->fields_model->get_fields();
        }

        public function get_default_args() {
            // First Layer: Defaults
            $default_settings_args = $this->fields_model->get_default_args();
            // Second Layer: Helpie FAQ Settings Values
            $settings = new \HelpieFaq\Includes\Settings\Getters\Getter();
            $user_defined_settings_args = $settings->get_settings();
            // Third Layer: Interpreted Settings
            $interpreted_settings_args = $this->get_interpreted_settings_args( $user_defined_settings_args );
            $args = array_merge( $default_settings_args, $user_defined_settings_args, $interpreted_settings_args );
            return $args;
        }

        public function get_field( $field_name ) {
            $fields = $this->get_fields();
            return $fields[$field_name];
        }

        protected function get_items_props( $faq_wp_objs, $args ) {
            $itemsProps = array();
            $count = 0;
            foreach ( $faq_wp_objs as $faq_wp_obj ) {
                if ( $this->top_level == 'categories' ) {
                    $itemsProps[$count] = $this->handlers->map_category_props_to_view_item_props( $faq_wp_obj );
                    $term_id = $itemsProps[$count]['term_id'];
                    $cat_faq_args = array_merge( $args, array(
                        'categories' => $term_id,
                    ) );
                    if ( $this->product_faq_relations == true ) {
                        $args['term_id'] = $term_id;
                        // TODO Get all FAQs By FAQ Category Id
                        $wp_faqs_children = $this->faq_repo->get_faqs_by_category( $args );
                    } else {
                        $wp_faqs_children = $this->faq_repo->get_faqs( $cat_faq_args );
                    }
                    $this->top_level = 'articles';
                    $itemsProps[$count]['children'] = $this->get_items_props( $wp_faqs_children, $args );
                    $this->top_level = 'categories';
                } else {
                    $itemsProps[$count] = $this->handlers->convert_single_post_obj_to_itemProps( $faq_wp_obj, $args );
                }
                $count++;
            }
            return $itemsProps;
        }

        protected function get_collection_props( $args ) {
            $collectionProps = array(
                'context' => $this->handlers->get_context(),
            );
            $collectionProps = array_merge( $collectionProps, $args );
            return $collectionProps;
        }

        public function get_interpreted_settings_args( array $settings_args ) {
            $settings_handler = new \HelpieFaq\Features\Faq\Settings_Handlers();
            $args = $settings_handler->get_interpreted_settings_args( $settings_args );
            return $args;
        }

    }

}
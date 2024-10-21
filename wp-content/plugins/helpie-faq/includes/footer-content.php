<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Footer_Content')) {
    class Footer_Content {

        public function print_js_content() {
            /** Print Schema Snippet */
            $this->print_schema_snippet();

            /** Enqueueing the Overall FAQ collection */
            $this->print_faq_collections();
        }

        private function print_schema_snippet() {
            $schema_snippet = new \HelpieFaq\Includes\Services\Schema_Snippet();
            $schema_snippet->load_helpie_faq_schema_snippet();
        }

        private function print_faq_collections() {
            global $Helpie_Faq_Collections;
            if (empty($Helpie_Faq_Collections)) {
                return;
            }

            $faq_data = array();

            foreach ($Helpie_Faq_Collections as $index => $faq_collection) {
                $viewProps = array();
                /** Get the limit value from the collection props */
                $limit = $faq_collection['limit'];

                /** Set the limit value as -1 to the collection props */
                $faq_collection['limit'] = -1;

                $group_id = isset($faq_collection['group_id']) ? $faq_collection['group_id'] : 0;

                if (!empty($group_id) && intval($group_id)) {
                    $faq_group_model = new \HelpieFaq\Features\Faq_Group\Model();
                    $viewProps = $faq_group_model->get_viewProps($faq_collection);
                }

                if (empty($group_id)) {
                    $faq_model = new \HelpieFaq\Features\Faq\Faq_Model();
                    $viewProps = $faq_model->get_viewProps($faq_collection);
                }

                $filtered_items = $this->get_filtered_item_props($viewProps);

                $faq_collection = $this->get_required_collection_props($viewProps['collection']);
                $faq_collection['limit'] = $limit;

                $faq_data[] = array(
                    'collection' => $faq_collection,
                    'items' => $filtered_items,
                );
            }

            if (empty($faq_data)) {
                return;
            }
            echo "<script>";
            echo "window.HELPIE_FAQS = " . wp_json_encode($faq_data) . ";";
            echo "</script>";

        }

        private function get_required_collection_props($collectionProps) {
            $required_collection_props = array('display_mode', 'enable_faq_styles',
                'open_by_default', 'faq_url_attribute', 'faq_url_type', 'title_icon', 'category_title_icon', 'icon_position',
                'toggle_icon_type', 'toggle_open', 'toggle_off', 'accordion_background', 'accordion_header_tag', 'limit',
            );

            foreach ($collectionProps as $key => $value) {
                if (!in_array($key, $required_collection_props)) {
                    unset($collectionProps[$key]);
                }
            }
            /** convering text to boolean */
            $handlers = new \HelpieFaq\Features\Faq\Handlers();
            $collectionProps = $handlers->boolean_conversion($collectionProps);

            return $collectionProps;
        }

        private function get_filtered_item_props($given_viewProps) {
            $collectionProps = $given_viewProps['collection'];

            $is_category_mode = $this->check_is_category_mode($collectionProps);

            $filtered_itemProps = array();
            if ($is_category_mode) {
                foreach ($given_viewProps['items'] as $index => $props) {
                    $has_childrens = (isset($props['children']) && !empty($props['children'])) ? $props['children'] : [];
                    $filtered_itemProps[$index] = $props;
                    if (!empty($has_childrens)) {
                        $filtered_itemProps[$index]['children'] = $this->apply_content_filters($has_childrens, $collectionProps);
                    }
                }
            } else {
                $filtered_itemProps = $this->apply_content_filters($given_viewProps['items'], $collectionProps);
            }

            return $filtered_itemProps;
        }

        private function apply_content_filters($given_itemProps, $given_collectionProps) {
            foreach ($given_itemProps as $index => $props) {
                $given_itemProps[$index]['content'] = apply_filters('helpie_faq/read_more_content', array(
                    'props' => $props,
                    'collectionProps' => $given_collectionProps,
                ));
            }
            return $given_itemProps;
        }

        private function check_is_category_mode($collectionProps) {
            $display_mode_group_by = isset($collectionProps['display_mode_group_by']) ? $collectionProps['display_mode_group_by'] : 'none';
            $display_mode_group_container = isset($collectionProps['display_mode_group_container']) ? $collectionProps['display_mode_group_container'] : 'category';

            if ($collectionProps['display_mode'] == 'faq_list') {
                return ($display_mode_group_by == 'category') ? true : false;
            }
            return ($display_mode_group_by != 'none' && $display_mode_group_container == 'simple_section_with_header') ? true : false;
        }
    }

}
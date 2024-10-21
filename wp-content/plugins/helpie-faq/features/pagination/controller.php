<?php

namespace HelpieFaq\Features\Pagination;

if (!class_exists('\HelpieFaq\Features\Pagination\Controller')) {
    class Controller
    {

        public function get_view($viewProps)
        {
            $e_widget_name = $this->get_elementor_widget_name($viewProps);
            if ($e_widget_name == "helpie-faq-dynamic-add") {
                return;
            }

            /** Dont show the pagination if the limit is below 1 */
            if (!$this->pagination_enabled($viewProps) || $this->is_category_display_modes($viewProps) || !hf_fs()->can_use_premium_code__premium_only()) {
                return;
            }

            $view = new \HelpieFaq\Features\Pagination\View();
            $viewProps['pagination'] = $this->get_pagination_args($viewProps);
            return $view->get_pagination_content($viewProps);
        }

        public function pagination_enabled($viewProps)
        {
            $limit = isset($viewProps['collection']['limit']) ? $viewProps['collection']['limit'] : -1;
            $pagination = (isset($viewProps['collection']['pagination']) && $viewProps['collection']['pagination'] == 1) ? true : false;
            return ($limit > 0 && $pagination) ? true : false;
        }

        public function get_pagination_args($viewProps)
        {
            $defaults = $this->get_default_args();
            $collection_props = $viewProps['collection'];

            foreach ($defaults as $key => $value) {
                if (isset($collection_props[$key])) {
                    $defaults[$key] = $collection_props[$key];
                }
            }

            return $defaults;
        }

        public function get_default_args()
        {
            $dafaults = array(
                'group_id' => 0,
                'display_mode' => 'simple_accordion',
                'display_mode_group_by' => 'none',
                'display_mode_group_container' => 'simple_section_with_header',
                'toggle' => 1,
                'open_by_default' => 'open_first',
                'faq_url_attribute' => 1,
                'faq_url_type' => 'post_id',
                'limit' => -1,
                'sortby' => 'publish',
                'order' => 'desc',
                'enable_wpautop' => false,
            );
            return $dafaults;
        }

        public function is_category_display_modes($viewProps)
        {
            $display_mode_group_by = isset($viewProps['collection']['display_mode_group_by']) ? $viewProps['collection']['display_mode_group_by'] : 'none';
            return ($display_mode_group_by == 'category') ? true : false;
        }

        public function get_elementor_widget_name($viewProps)
        {
            return isset($viewProps['collection']['e_widget_name']) ? $viewProps['collection']['e_widget_name'] : '';
        }
    }
}
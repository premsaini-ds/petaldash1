<?php

namespace HelpieFaq\Features\Faq_Group;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Faq_Group\Controller')) {
    class Controller
    {
        public $third_party_filters_control;

        public function __construct()
        {
            $this->third_party_filters_control = new \HelpieFaq\Includes\Third_Party_Filters_Control();
        }

        public function init()
        {
            $this->load_hooks();

        }

        public function load_hooks()
        {
            $actions = new \HelpieFaq\Features\Faq_Group\Actions();
            $filters = new \HelpieFaq\Features\Faq_Group\Filters();
        }

        /**
         * FAQ Groups default arguments
         */
        public function get_default_args($args)
        {

            $fields = $this->get_default_fields();
            $faq_group_term = get_term($args['group_id']);

            $faq_group_args = array(
                'group_id' => $args['group_id'],
                'title' => isset($faq_group_term->name) ? $faq_group_term->name : '',
            );

            $args = array_merge($fields, $faq_group_args);
            return $args;
        }

        private function get_default_fields()
        {
            $fields = [
                'group_id' => 0,
                // 'display_mode' => 'simple_accordion',
                'product_only' => false,
                'categories' => '',
                'sortby' => 'post__in',
                'title' => '',
            ];

            return $fields;
        }

        public function get_view($args)
        {
            $html = '';
            global $Helpie_Faq_Collections;

            $this->third_party_filters_control->remove_filters();

            $style = array();

            if (isset($args['style'])) {
                $style = $args['style'];
            }
            $model = new \HelpieFaq\Features\Faq_Group\Model();
            $view = new \HelpieFaq\Features\Faq\Faq_View();

            $viewProps = $model->get_viewProps($args);

            if (isset($viewProps['items']) && !empty($viewProps['items'])) {
                $html = $view->get($viewProps, $style);
            }

            /** use this below filter for generating faq-schema snippet */
            apply_filters('helpie_faq_schema_generator', $viewProps);

            $Helpie_Faq_Collections[] = $viewProps['collection'];

            $this->third_party_filters_control->add_filters();

            return $html;
        }

        public function can_apply_group_style($group_id)
        {
            $stored_settings_data = get_term_meta($group_id, 'faq_group_settings', true);
            return !empty($stored_settings_data) ? "yes" : "no";
            // return !empty($stored_settings_data) && hf_fs()->can_use_premium_code__premium_only() ? "yes" : "no";
        }
    }
}

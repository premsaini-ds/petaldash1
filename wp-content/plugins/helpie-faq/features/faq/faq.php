<?php

namespace HelpieFaq\Features\Faq;

if (!class_exists('\HelpieFaq\Features\Faq')) {
    class Faq
    {

        public $model;
        public $view;
        public $third_party_filters_control;

        public function __construct()
        {
            // Models
            $this->model = new \HelpieFaq\Features\Faq\Faq_Model();

            // Views
            $this->view = new \HelpieFaq\Features\Faq\Faq_View();

            $this->third_party_filters_control = new \HelpieFaq\Includes\Third_Party_Filters_Control();

        }

        public function get_view($args)
        {
            global $Helpie_Faq_Collections;

            // $this->third_party_filters_control->remove_filters();
            $html = '';

            $style = array();

            if (isset($args['style'])) {
                $style = $args['style'];
            }

            $viewProps = $this->model->get_viewProps($args);
            $view = $this->get_view_from_viewProps($viewProps, $style);

            //  $this->third_party_filters_control->add_filters();

            return $view;
        }

        public function get_view_from_viewProps($viewProps, $style)
        {
            global $Helpie_Faq_Collections;

            $html = '';

            // if (isset($viewProps['items']) && !empty($viewProps['items'])) {
            $html = $this->view->get($viewProps, $style);
            // }

            // error_log('get_view_from_viewProps() html : ' . print_r($html, true));

            /** use this below filter for generating faq-schema snippet */
            apply_filters('helpie_faq_schema_generator', $viewProps);

            $Helpie_Faq_Collections[] = $viewProps['collection'];

            return $html;
        }

    }
}

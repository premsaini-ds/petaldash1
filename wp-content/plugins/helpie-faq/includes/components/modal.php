<?php

namespace HelpieFaq\Includes\Components;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\HelpieFaq\Includes\Components\Modal')) {

    class Modal
    {

        public function get_content(array $args = array())
        {

            $html = '<div id="helpie_faq__modal--premium-notice" class="helpie_faq__modal">';
            $html .= '<div class="helpie_faq__modal__content">';
            $html .= '<span class="helpie_faq__modal--close">&times;</span>';
            $html .= '<h1>' . esc_html__("Start Free Trial", "helpie-faq") . '</h1>';
            $html .= '<p>' . esc_html__("Start free trial to access the premium features", "helpie-faq") . '.</p>';
            $html .= '<a class="helpie-faq button-primary" href="' . esc_url(hf_fs()->get_trial_url()) . '">' . esc_html__("Start Free Trial", "helpie-faq") . '</a>';
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        }
    }
}
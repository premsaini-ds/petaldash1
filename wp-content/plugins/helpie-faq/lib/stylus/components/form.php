<?php

namespace Stylus\Components;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Stylus\Components\Form')) {

    class Form
    {

        public function get_form($viewProps)
        {
            $collection = $viewProps['collection'];

            $attr1 = '';
            $attr2 = '';
            $additional_class = "";

            if (isset($collection['context']['kb-category']) || isset($collection['context']['kb-category'])) {
                $attr1 = "data-kb-category = '" . $collection['context']['kb-category'] . "'";
            }

            if (isset($collection['context']['woo-product']) || isset($collection['context']['woo-product'])) {
                $attr1 = "data-woo-product = '" . $collection['context']['woo-product'] . "'";
            }

            if (isset($collection['theme']) && $collection['theme'] == "dark") {
                $additional_class = " dark";
            }
            $form = $this->get_toggle_button_field($collection);
            $form .= '<form class="form__section ' . esc_attr($additional_class) . ' ' . esc_attr($attr1) . ' ' . esc_attr($attr2) . ' ">';
            $form .= '<input type="hidden" class="faq_default_category_term_id" value="' . esc_attr($collection['default_category_id']) . '">';

            $form .= $this->get_hidden_group_field($collection);

            $form .= $this->get_text_field();

            if (isset($collection['ask_question']) && !empty($collection['ask_question'])) {

                foreach ($collection['ask_question'] as $field) {
                    if ($field == 'email') {
                        $form .= $this->get_email_field();
                    }
                    if ($field == 'answer') {
                        $form .= $this->get_textarea_field();
                    }
                    if ($field == 'categories') {
                        $form .= $this->get_category_field($collection);
                    }
                }
            }

            $form .= $this->get_submit_button_field();

            $form .= "</form>";

            return $form;
        }

        public function get_toggle_button_field($collection)
        {
            $value = esc_html__('Add FAQ', 'helpie-faq');
            if (isset($collection['ask_question_button_text']) && !empty($collection['ask_question_button_text'])) {
                $value = $collection['ask_question_button_text'];
            }

            $html = "<p align='center'>";
            $html .= "<input class='helpie-faq-form__toggle form__toggle' type='button' value='" . $value . "'/>";
            $html .= "</p>";

            return $html;
        }

        public function get_submit_button_field($value = "Submit")
        {
            $value = esc_html__($value, 'helpie-faq');

            $html = "<p>";
            $html .= "<input class='helpie-faq-form__submit form__submit' type='submit' value='" . $value . "'/>";
            $html .= "</p>";

            return $html;
        }

        public function get_text_field($label = 'Question ? ( required )')
        {
            $label = esc_html__($label, 'helpie-faq');

            $html = "<p>";
            $html .= "<label> " . $label . " <br> ";
            $html .= "<span>";
            $html .= " <input name='faq_question' class='form__text' type='text' required /> ";
            $html .= "</span>";
            $html .= "</label>";
            $html .= "</p>";

            return $html;
        }

        public function get_email_field($label = 'Your Email ( required )')
        {
            $label = esc_html__($label, 'helpie-faq');

            $html = "<p>";
            $html .= "<label> " . $label . " <br> ";
            $html .= "<span>";
            $html .= " <input name='faq_email' class='form__email' type='email' required pattern='[^@]+@[^@]+\.[a-zA-Z]{2,6}'/> ";
            $html .= "</span>";
            $html .= "</label>";
            $html .= "</p>";

            return $html;
        }

        public function get_textarea_field($label = 'Answer')
        {
            $label = esc_html__($label, 'helpie-faq');

            $html = "<p>";
            $html .= "<label> " . $label . " <br> ";
            $html .= "<span>";
            $html .= "<textarea name='faq_answer' class='form__textarea'></textarea>";
            $html .= "</span>";
            $html .= "</label>";
            $html .= "</p>";

            return $html;
        }

        public function get_category_field($collection)
        {
            $options = isset($collection['category_options']) ? $collection['category_options'] : [];
            $default_category_id = isset($collection['default_category_id']) ? $collection['default_category_id'] : '';

            $label = esc_html__('Categories', 'helpie-faq');
            $html = "<p>";
            $html .= "<label> " . $label . " </label><br> ";
            $html .= "<span>";
            $html .= "<select name='faq_categories' class='form__select faq_categories' data-placeholder='Select Categories' multiple='multiple'>";
            foreach ($options as $category_id => $category) {
                $selected = ($category_id == $default_category_id) ? 'selected' : '';
                $html .= '<option value="' . esc_attr($category_id) . '" ' . esc_attr($selected) . '>' . esc_html($category) . '</option>';
            }
            $html .= "</select>";
            $html .= "</span>";
            $html .= "</p>";

            return $html;
        }

        public function get_hidden_group_field($collection)
        {
            $group_id = isset($collection['group_id']) && !empty($collection['group_id']) && intval($collection['group_id']) ? $collection['group_id'] : 0;
            if (empty($group_id)) {
                return;
            }
            return '<input type="hidden" name="faq_group_id" value="' . esc_attr($group_id) . '">';
        }
    }
}

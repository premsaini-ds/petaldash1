<?php

namespace HelpieFaq\Features;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Qna_Settings')) {
    class Qna_Settings
    {

        public function __construct()
        {
        }

        public function settings_config()
        {
            $config = array(
                'show_products_qna' => array(
                    'type' => 'switcher',
                    'label' => __('Show Questions & Answers Section for Products', 'helpie-faq'),
                    'default' => false,
                    'description' => __('Enable / Disable Q&A in Products', 'helpie-faq'),
                ),
                // 'ask_question_button_text' => array(
                //     'type' => 'text',
                //     'label' => __('Ask Question - Button Text', 'helpie-faq'),
                //     'default' => 'Ask Question',
                //     'description' => __('', 'helpie-faq'),
                // ),
                'who_can_add_answer' => array(
                    'type' => 'select',
                    'label' => __('Who can add answer', 'helpie-faq'),
                    'options' => array(
                        'anyone' => __('Anyone', 'helpie-faq'),
                        'logged_in_users' => __('Logged In Users', 'helpie-faq'),
                        'customers' => __('Customers', 'helpie-faq'),
                        'admin' => __('Admin only', 'helpie-faq'),
                    ),
                    'default' => 'anyone',
                    'description' => __('Who can add answer', 'helpie-faq'),
                ),
                'who_can_add_question' => array(
                    'type' => 'select',
                    'label' => __('Who can add questions', 'helpie-faq'),
                    'options' => array(
                        'anyone' => __('Anyone', 'helpie-faq'),
                        'logged_in_users' => __('Logged In Users', 'helpie-faq'),
                        'customers' => __('Customers', 'helpie-faq'),
                        'admin' => __('Admin only', 'helpie-faq'),
                    ),
                    'default' => 'anyone',
                    'description' => __('Who can add questions', 'helpie-faq'),
                ),
                'who_can_vote_question' => array(
                    'type' => 'select',
                    'label' => __('Who can vote for questions', 'helpie-faq'),
                    'options' => array(
                        'anyone' => __('Anyone', 'helpie-faq'),
                        'logged_in_users' => __('Logged In Users', 'helpie-faq'),
                        'customers' => __('Customers', 'helpie-faq'),
                        'admin' => __('Admin only', 'helpie-faq'),
                    ),
                    'default' => 'anyone',
                    'description' => __('Who vote for questions', 'helpie-faq'),
                ),
                'question_approval' => array(
                    'type' => 'select',
                    'label' => __('When do you need question approval', 'helpie-faq'),
                    'options' => array(
                        'all' => __('Require approval for All', 'helpie-faq'),
                        'non_logged_in_users' => __('Non Logged In Users Only', 'helpie-faq'),
                    ),
                    'default' => 'all',
                    'description' => __('When do you need question approval', 'helpie-faq'),
                ),
                'answer_approval' => array(
                    'type' => 'select',
                    'label' => __('When do you need answer approval', 'helpie-faq'),
                    'options' => array(
                        'all' => __('Require approval for All', 'helpie-faq'),
                        'non_logged_in_users' => __('Non Logged In Users Only', 'helpie-faq'),
                    ),
                    'default' => 'all',
                    'description' => __('When do you need answer approval', 'helpie-faq'),
                ),
            );

            return $config;
        }
        public function get()
        {
            $default_settings = array(
                'show_products_qna' => false,
                'on_question_submission_notification' => 'admin',
                'on_answer_submission_notification' => 'admin',
                'who_can_add_answer' => 'anyone',
                'who_can_add_question' => 'anyone',
                'who_can_vote_question' => 'anyone',
                'question_approval' => 'always',
                'answer_approval' => 'non_logged_in_users',
            );

            $getter = new \HelpieFaq\Includes\Settings\Getters\Getter();
            $options = get_option('helpie-faq');
            $settings = $default_settings;

            foreach ($default_settings as $key => $singleField) {

                if (isset($options[$key]) && $options[$key] != '') {
                    $settings[$key] = $options[$key];
                }
            }

            return $settings;
        }

        public function on_new_answer()
        {
        }
    }
}

<?php

namespace HelpieFaq\Includes\Settings;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Settings\Settings')) {
    class Settings
    {

        public function __construct()
        {
            add_action('csf_loaded', [$this, 'init_settings'], 10, 1);

            /*** In the free version use this hook to update the default settings option when submitting the settings form */
            add_action("csf_helpie-faq_save_after", [$this, 'update_default_options'], 10, 2);

            /**
             * Use of this below hook for do something before submitting the helpie settings.
             */
            add_action("csf_helpie-faq_save_before", [$this, 'get_settings_before_save'], 10, 2);
        }

        public function get_settings_before_save($settings)
        {
            /** Getting the old setting from DB-Options */
            $getter = new \HelpieFaq\Includes\Settings\Getters\Getter();
            $old_settings = $getter->get_settings();

            // Store the old slugs names in DB before updating the current settings.
            $faq_slug = isset($old_settings['helpie_faq_slug']) ? $old_settings['helpie_faq_slug'] : '';
            update_option('helpie_faq_slug_changed_from', $faq_slug);

            $faq_group_slug = isset($old_settings['helpie_faq_group_slug']) ? $old_settings['helpie_faq_group_slug'] : '';
            update_option('helpie_faq_group_slug_changed_from', $faq_group_slug);
        }

        public function update_default_options($settings)
        {
            if (hf_fs()->can_use_premium_code__premium_only() == false) {
                $getter = new \HelpieFaq\Includes\Settings\Getters\Getter();
                $settings = $getter->get_premium_default_settings($settings);
                \update_option('helpie-faq', $settings);
            }

            /**
             * Get old-slug option values from the DB-Option table
             */
            $old_faq_slug = get_option('helpie_faq_slug_changed_from');
            $old_faq_group_slug = get_option('helpie_faq_group_slug_changed_from');

            /** Checking the slug names are changed or not */
            $slug_changed = ($old_faq_slug != $settings['helpie_faq_slug']);
            $group_slug_changed = ($old_faq_group_slug != $settings['helpie_faq_group_slug']);

            /** Set the `helpie_faq_slug_updated` option value is 1 when slugs (helpie_faq_slug,helpie_faq_group_slug)  are change. */
            if ($slug_changed || $group_slug_changed) {
                \update_option('helpie_faq_slug_updated', 'SLUG_CHANGED');
            }

            // Call a job for track the event for "Save Settings"
            helpie_faq_track_event('Settings Saved', $settings);
        }

        public function init_settings()
        {
            $cpt = new \HelpieFaq\Includes\Cpt();
            $cpt->register_helpie_faq_cpt();

            if (class_exists('\CSF')) {

                // Set a unique slug-like ID
                $prefix = 'helpie-faq';

                // Create options
                \CSF::createOptions($prefix, array(
                    'menu_title' => __('Settings', 'helpie-faq'),
                    'menu_parent' => 'edit.php?post_type=helpie_faq',
                    'menu_type' => 'submenu', // menu, submenu, options, theme, etc.
                    'menu_slug' => 'helpie-review-settings',
                    'framework_title' => 'Settings',
                    'theme' => 'light',
                    'show_search' => false, // TODO: Enable once autofill password is fixed
                    'class' => 'helpie-faq__settings',
                ));

                $this->general_settings($prefix);
                $this->display_and_layout_settings($prefix);
                $this->single_faq_page_settings($prefix);
                $this->faq_group_settings($prefix);
                $this->pagination_settings($prefix);
                $this->style_settings($prefix);
                $this->submission_settings($prefix);
                // $this->integration_settings($prefix);
                $this->kb_integration_settings($prefix);
                $this->woo_integration_settings($prefix);
                $this->roadmap_settings($prefix);
                $this->qna_settings($prefix);

                $faq_group_prefix = 'helpie_faq_group_items';
                $this->group_category_page($faq_group_prefix);

                new \HelpieFaq\Includes\Components\Shortcode_Builder();
            }
        }

        public function submission_settings($prefix)
        {

            \CSF::createSection(
                $prefix,
                array(

                    'id' => 'usersubmisson',
                    'title' => __('User Submission', 'helpie-faq'),
                    'icon' => 'fas fa-sign-out-alt',
                    'fields' => $this->submission_fields(),
                )

            );
        }

        public function qna_settings($prefix)
        {

            \CSF::createSection(
                $prefix,
                array(

                    'id' => 'qna',
                    'title' => __('Questions & Answers (Beta)', 'helpie-faq'),
                    'icon' => 'fas fa-sign-out-alt',
                    'fields' => $this->qna_fields(),
                )

            );
        }

        public function integration_settings($prefix)
        {
            \CSF::createSection(
                $prefix,
                array(
                    // 'parent' => 'user_access',
                    'id' => 'integrations',
                    'title' => __('Integrations', 'helpie-faq'),
                    'icon' => 'fa fa-plus',
                )

            );
        }

        public function kb_integration_settings($prefix)
        {
            \CSF::createSection(
                $prefix,
                array(
                    // 'parent' => 'integrations',
                    'id' => 'helpie_kb',
                    'title' => __('Helpie KB (integration)', 'helpie-faq'),
                    'icon' => 'fa fa-book',
                    'fields' => $this->kb_active_fields(),
                )

            );
        }

        public function woo_integration_settings($prefix)
        {
            \CSF::createSection(
                $prefix,
                array(
                    // 'parent' => 'integrations',
                    'id' => 'woocommerce',
                    'title' => __('WooCommerce (integration)', 'helpie-faq'),
                    'icon' => 'fa fa-cart-plus',
                    'fields' => $this->woo_active_fields(),
                )

            );
        }

        public function roadmap_settings($prefix)
        {
            \CSF::createSection(
                $prefix,
                array(
                    'id' => 'roadmap',
                    'title' => __('Roadmap', 'helpie-faq'),
                    'icon' => 'fa fa-map-signs',
                    'fields' => [
                        [
                            'type' => 'notice',
                            'style' => 'info',
                            'content' => sprintf(__("You can vote on Helpie FAQ's next feature %s", 'helpie-faq'), '<a href="' . esc_url('https://trello.com/b/5kFAtN80/faq-roadmap') . '" target="_blank">' . __('here', 'helpie-faq') . '</a>'),
                        ],
                    ],
                )

            );
        }

        public function kb_active_fields()
        {
            if (!(\is_plugin_active('helpie/helpie.php'))) {
                $options[] = array(
                    'type' => 'notice',
                    'class' => 'danger',
                    'content' => __('In order use this feature you need to purchase and activate the <a href="' . esc_url('https://checkout.freemius.com/mode/dialog/plugin/3014/plan/4858/?trial=paid') . '" target="_blank">Helpie KB</a> plugin.', 'helpie-faq'),
                );
            }

            $options[] = array(
                'id' => 'kb_integration_switcher',
                'type' => 'switcher',
                'title' => __('Enable FAQ in Helpie KB', 'helpie-faq'),
                'label' => __('Show FAQ In Helpie KB Category Page', 'helpie-faq'),
                'default' => true,
            );

            $options[] = array(
                'id' => 'kb_cat_content_show',
                'type' => 'select',
                'title' => __('Show FAQ In Helpie KB Category Page', 'helpie-faq'),
                'options' => array(
                    'before' => __('Before Content', 'helpie-faq'),
                    'after' => __('After Content', 'helpie-faq'),
                ),
                'default' => 'before',
                'info' => __('Select show faq before or after content in kb category page', 'helpie-faq'),
                'dependency' => array('kb_integration_switcher', '==', 'true'),
            );

            // error_log('$options : ' . print_r($options, true));
            return $options;
        }

        public function woo_active_fields()
        {
            $pro_feature_sub_title = $this->pro_feature_sub_title();

            $faq_category_options = $this->get_faq_category_options();

            $incr = 0;
            if (!(\is_plugin_active('woocommerce/woocommerce.php'))) {

                $options[$incr]['type'] = 'notice';
                $options[$incr]['class'] = 'danger';
                $options[$incr]['content'] = __('In order use this feature you need to activate the <a href="' . esc_url('/wp-admin/plugin-install.php?s=woocommerce&tab=search&type=term') . '" target="_blank">WooCommerce</a> plugin.', 'helpie-faq');

                $incr++;
            }

            $options[$incr]['id'] = 'woo_integration_switcher';
            $options[$incr]['type'] = 'switcher';
            $options[$incr]['title'] = __('Show FAQ in WooCommerce', 'helpie-faq');
            $options[$incr]['label'] = __('Show FAQ In WooCommerce product tab', 'helpie-faq');
            $options[$incr]['default'] = true;

            $incr++;

            $options[$incr]['id'] = 'woo_integration_location';
            $options[$incr]['type'] = 'select';
            $options[$incr]['attributes'] = hf_fs()->can_use_premium_code__premium_only() == false ? ['disabled' => true, 'readonly' => 'readonly'] : [];
            $options[$incr]['subtitle'] = hf_fs()->can_use_premium_code__premium_only() == false ? $pro_feature_sub_title : '';
            $options[$incr]['class'] = hf_fs()->can_use_premium_code__premium_only() == false ? 'helpie-disabled' : '';
            $options[$incr]['title'] = __('Location', 'helpie-faq');
            $options[$incr]['label'] = __('Where to show FAQs in Woocommerce Product page', 'helpie-faq');
            $options[$incr]['default'] = 'woocommerce_product_tabs';
            $options[$incr]['options'] = array(
                'woocommerce_product_tabs' => __('Product tabs', 'helpie-faq'),
                'woocommerce_before_add_to_cart_form' => __('Before Add to Cart', 'helpie-faq'),
                'woocommerce_after_add_to_cart_button' => __('After Add to Cart ', 'helpie-faq'),
                'woocommerce_after_single_product_summary' => __('After Product Summary', 'helpie-faq'),
                'woocommerce_after_single_product' => __('After Product', 'helpie-faq'),
            );

            $incr++;

            $options[$incr]['id'] = 'woo_search_show';
            $options[$incr]['type'] = 'switcher';
            $options[$incr]['title'] = __('Show FAQ Search', 'helpie-faq');
            $options[$incr]['label'] = __('Show FAQ Search In WooCommerce product', 'helpie-faq');
            $options[$incr]['default'] = true;
            $incr++;

            $options[$incr]['id'] = 'tab_title';
            $options[$incr]['type'] = 'text';
            $options[$incr]['title'] = __('Tab Title', 'helpie-faq');
            $options[$incr]['default'] = __('FAQ', 'helpie-faq');
            $options[$incr]['dependency'] = array('woo_integration_switcher', '==', 'true');

            /***
             *  1. Check FAQ Categories is Found Or Not
             *  If Found then Show Repeater Option, else then show notices for categories not found.
             *
             *  */

            if (count($faq_category_options) == 0) {

                $incr++;
                $options[$incr]['type'] = 'subheading';
                $options[$incr]['content'] = __('Product Faq Relations', 'helpie-faq');

                $incr++;
                $options[$incr]['type'] = 'notice';
                $options[$incr]['style'] = 'default';
                $options[$incr]['content'] = __('In order to use this feature you need to add some <a href="' . esc_url('/wp-admin/edit-tags.php?taxonomy=helpie_faq_category&post_type=helpie_faq') . '">FAQs Categories</a>.', 'helpie-faq');
            } else {

                $incr++;
                $options[$incr]['id'] = 'product_faq_relations';
                $options[$incr]['type'] = 'repeater';
                $options[$incr]['title'] = __('Product FAQ Relations', 'helpie-faq');
                $options[$incr]['subtitle'] = hf_fs()->can_use_premium_code__premium_only() == false ? $pro_feature_sub_title : '';
                $options[$incr]['class'] = hf_fs()->can_use_premium_code__premium_only() == false ? 'helpie-disabled' : '';

                $options[$incr]['fields'][0]['id'] = 'faq_category';
                $options[$incr]['fields'][0]['type'] = 'select';
                $options[$incr]['fields'][0]['title'] = __('FAQ Categories', 'helpie-faq');
                $options[$incr]['fields'][0]['subtitle'] = hf_fs()->can_use_premium_code__premium_only() == false ? $pro_feature_sub_title : '';
                $options[$incr]['fields'][0]['options'] = $faq_category_options;
                $options[$incr]['fields'][0]['class'] = hf_fs()->can_use_premium_code__premium_only() == false ? 'helpie-disabled faq_category' : 'faq_category';
                $options[$incr]['fields'][0]['attributes'] = [];
                if (hf_fs()->can_use_premium_code__premium_only() == false) {
                    $options[$incr]['fields'][0]['attributes']['disabled'] = false;
                    $options[$incr]['fields'][0]['attributes']['readonly'] = 'readonly';
                }

                $options[$incr]['fields'][1]['id'] = 'link_type';
                $options[$incr]['fields'][1]['type'] = 'select';
                $options[$incr]['fields'][1]['title'] = __('Link this Category to', 'helpie-faq');
                $options[$incr]['fields'][1]['subtitle'] = hf_fs()->can_use_premium_code__premium_only() == false ? $pro_feature_sub_title : '';
                $options[$incr]['fields'][1]['options']['all_woo_categories'] = __('All Woo Categories', 'helpie-faq');
                $options[$incr]['fields'][1]['options']['specific_woo_category'] = __('Specific Woo Categories', 'helpie-faq');
                $options[$incr]['fields'][1]['default'] = 'all_woo_categories';
                $options[$incr]['fields'][1]['class'] = hf_fs()->can_use_premium_code__premium_only() == false ? 'helpie-disabled woo_link' : 'woo_link';
                $options[$incr]['fields'][1]['attributes'] = [];
                if (hf_fs()->can_use_premium_code__premium_only() == false) {
                    $options[$incr]['fields'][1]['attributes']['disabled'] = false;
                    $options[$incr]['fields'][1]['attributes']['readonly'] = 'readonly';
                }

                $options[$incr]['fields'][2]['id'] = 'product_categories';
                $options[$incr]['fields'][2]['type'] = 'select';
                $options[$incr]['fields'][2]['title'] = __('Product of Woo Commerce Categories', 'helpie-faq');
                $options[$incr]['fields'][2]['subtitle'] = hf_fs()->can_use_premium_code__premium_only() == false ? $pro_feature_sub_title : '';
                $options[$incr]['fields'][2]['chosen'] = true;
                $options[$incr]['fields'][2]['multiple'] = true;
                $options[$incr]['fields'][2]['options'] = 'categories';
                $options[$incr]['fields'][2]['query_args'] = [
                    'taxonomy' => 'product_cat',
                ];
                $options[$incr]['fields'][2]['default'] = '';
                $options[$incr]['fields'][2]['class'] = hf_fs()->can_use_premium_code__premium_only() == false ? 'helpie-disabled woo_categories' : 'woo_categories';
                $options[$incr]['fields'][2]['dependency'] = array('link_type', '==', 'specific_woo_category');
                $options[$incr]['fields'][2]['attributes'] = [];
                if (hf_fs()->can_use_premium_code__premium_only() == false) {
                    $options[$incr]['fields'][2]['attributes']['disabled'] = true;
                    $options[$incr]['fields'][2]['attributes']['readonly'] = 'readonly';
                }
            }

            return $options;
        }

        public function style_settings($prefix)
        {
            $style_fields = $this->get_style_fields($prefix);

            \CSF::createSection(
                $prefix,
                array(
                    // 'parent' => 'user_access',
                    'id' => 'style',
                    'title' => __('Style', 'helpie-faq'),
                    'icon' => 'fa fa-paint-brush',
                    'fields' => $style_fields,
                )

            );
        }
        public function general_settings($prefix)
        {

            $fields = new \HelpieFaq\Includes\Settings\Fields();

            $general_settings_fields = array_merge(
                [],
                $fields->get_faq_slug_settings(),
                $fields->get_title_section_settings_fields(),
                $fields->get_search_section_settings_fields(),
                $fields->get_others_section_settings_fields()
            );

            $general_settings_fields = $this->add_premium_field_notice($general_settings_fields, 'general');

            \CSF::createSection(
                $prefix,
                array(
                    // 'parent' => 'user_access',
                    'id' => 'general',
                    'title' => __('General', 'helpie-faq'),
                    'icon' => 'fa fa-cogs',
                    'fields' => $general_settings_fields,
                )

            );
        }

        public function qna_fields()
        {
            $options = [];
            $pro_feature_sub_title = $this->pro_feature_sub_title();
            $is_premium = hf_fs()->can_use_premium_code__premium_only();

            $qna_settings = new \HelpieFaq\Features\Qna_Settings();
            $fields = $qna_settings->settings_config();

            $incr = 0;

            foreach ($fields as $key => $singleField) {
                $options[$incr]['id'] = $key;
                $options[$incr]['type'] = $singleField['type'];
                $options[$incr]['title'] = $singleField['label'];
                $options[$incr]['subtitle'] = $is_premium == false ? $pro_feature_sub_title : '';
                $options[$incr]['label'] = $singleField['description'];
                $options[$incr]['class'] = $is_premium == false ? 'helpie-disabled' : '';
                $options[$incr]['default'] = $singleField['default'];
                $options[$incr]['attributes'] = [];
                if ($is_premium == false) {
                    $options[$incr]['attributes']['disabled'] = true;
                    $options[$incr]['attributes']['readonly'] = 'readonly';
                }

                if ($singleField['type'] == 'select') {

                    $options[$incr]['options'] = $singleField['options'];
                }

                $incr++;
            }

            // error_log('$options : ' . print_r($options, true));
            return $options;
        }

        public function submission_fields()
        {

            $pro_feature_sub_title = $this->pro_feature_sub_title();
            $is_premium = hf_fs()->can_use_premium_code__premium_only();

            $incr = 0;

            $options[$incr]['id'] = 'show_submission';
            $options[$incr]['type'] = 'switcher';
            $options[$incr]['title'] = __('Submission', 'helpie-faq');
            $options[$incr]['subtitle'] = $is_premium == false ? $pro_feature_sub_title : '';
            $options[$incr]['label'] = __('Enable / Disable User Submission form in FAQ', 'helpie-faq');
            $options[$incr]['class'] = $is_premium == false ? 'helpie-disabled' : '';
            $options[$incr]['default'] = true;
            $options[$incr]['attributes'] = [];
            if ($is_premium == false) {
                $options[$incr]['attributes']['disabled'] = true;
                $options[$incr]['attributes']['readonly'] = 'readonly';
            }
            $incr++;

            $options[$incr] = array(
                'id' => 'ask_question_button_text',
                'type' => 'text',
                'title' => __('Ask Question Button Text', 'helpie-faq'),
                'subtitle' => $is_premium == false ? $pro_feature_sub_title : '',
                'class' => $is_premium == false ? 'helpie-disabled' : '',
                'default' => __('Add FAQ', 'helpie-faq'),
            );
            if ($is_premium == false) {
                $options[$incr]['attributes']['disabled'] = true;
                $options[$incr]['attributes']['readonly'] = 'readonly';
            }

            $incr++;

            $options[$incr]['id'] = 'ask_question';
            $options[$incr]['type'] = 'checkbox';
            $options[$incr]['title'] = __('Ask Question With', 'helpie-faq');
            $options[$incr]['subtitle'] = $is_premium == false ? $pro_feature_sub_title : '';
            $options[$incr]['options']['email'] = 'Email';
            $options[$incr]['options']['answer'] = 'Answer';
            $options[$incr]['options']['categories'] = 'Categories';
            $options[$incr]['default'] = array('email');
            $options[$incr]['class'] = $is_premium == false ? 'helpie-disabled' : '';
            $options[$incr]['attributes'] = [];
            if ($is_premium == false) {
                $options[$incr]['attributes']['disabled'] = true;
                $options[$incr]['attributes']['readonly'] = 'readonly';
            }

            $incr++;

            $options[$incr]['id'] = 'onsubmit';
            $options[$incr]['type'] = 'select';
            $options[$incr]['title'] = __('On Submission', 'helpie-faq');
            $options[$incr]['subtitle'] = $is_premium == false ? $pro_feature_sub_title : '';
            $options[$incr]['options']['approval'] = __('Dont Require Approval', 'helpie-faq');
            $options[$incr]['options']['noapproval'] = __('Require Approval', 'helpie-faq');
            $options[$incr]['info'] = __('Approval Before Showing', 'helpie-faq');
            $options[$incr]['default'] = 'noapproval';
            $options[$incr]['class'] = $is_premium == false ? 'helpie-disabled' : '';
            $options[$incr]['attributes'] = [];
            if ($is_premium == false) {
                $options[$incr]['attributes']['disabled'] = true;
                $options[$incr]['attributes']['readonly'] = 'readonly';
            }

            $incr++;

            $options[$incr]['type'] = 'notice';
            $options[$incr]['class'] = 'info';
            $options[$incr]['content'] = 'Once Approved, Submitter will be notified through email';
            // $options[$incr]['dependency']  = array('ask_question|onsubmit', '==|==', 'email|noapproval');

            $incr++;

            $options[$incr]['id'] = 'submitter_email';
            $options[$incr]['type'] = 'fieldset';
            $options[$incr]['title'] = __('Submitter Notification', 'helpie-faq');
            $options[$incr]['subtitle'] = $is_premium == false ? $pro_feature_sub_title : '';
            $options[$incr]['class'] = $is_premium == false ? 'helpie-disabled' : '';
            // $options[$incr]['dependency']  = array('ask_question|onsubmit', '==|==', 'email|noapproval');
            $options[$incr]['fields'][0]['id'] = 'submitter_subject';
            $options[$incr]['fields'][0]['type'] = 'text';
            $options[$incr]['fields'][0]['title'] = __('Subject', 'helpie-faq');
            $options[$incr]['fields'][0]['validate'] = 'required';
            $options[$incr]['fields'][0]['attributes'] = [];
            $options[$incr]['fields'][0]['attributes']['placeholder'] = __('Subject title', 'helpie-faq');
            if ($is_premium == false) {
                $options[$incr]['fields'][0]['attributes']['disabled'] = true;
                $options[$incr]['fields'][0]['attributes']['readonly'] = 'readonly';
            }
            $options[$incr]['fields'][0]['default'] = __('The FAQ you submitted has been approved ', 'helpie-faq');

            $options[$incr]['fields'][1]['id'] = 'submitter_message';
            $options[$incr]['fields'][1]['type'] = 'textarea';
            $options[$incr]['fields'][1]['title'] = __('Message', 'helpie-faq');
            $options[$incr]['fields'][1]['validate'] = 'required';
            $options[$incr]['fields'][1]['attributes'] = [];
            $options[$incr]['fields'][1]['attributes']['placeholder'] = __('Subject title', 'helpie-faq');
            if ($is_premium == false) {
                $options[$incr]['fields'][1]['attributes']['disabled'] = true;
                $options[$incr]['fields'][1]['attributes']['readonly'] = 'readonly';
            }
            $options[$incr]['fields'][1]['default'] = __('A new FAQ you had submitted has been approved by the admin ', 'helpie-faq');

            $incr++;

            $options[$incr]['id'] = 'notify_admin';
            $options[$incr]['type'] = 'switcher';
            $options[$incr]['title'] = __('Notify Admin', 'helpie-faq');
            $options[$incr]['subtitle'] = $is_premium == false ? $pro_feature_sub_title : '';
            $options[$incr]['default'] = true;
            $options[$incr]['class'] = $is_premium == false ? 'helpie-disabled' : '';
            $options[$incr]['attributes'] = [];
            if ($is_premium == false) {
                $options[$incr]['attributes']['disabled'] = true;
                $options[$incr]['attributes']['readonly'] = 'readonly';
            }

            $incr++;

            $options[$incr]['id'] = 'admin_email';
            $options[$incr]['type'] = 'text';
            $options[$incr]['title'] = __('Admin Mail', 'helpie-faq');
            $options[$incr]['subtitle'] = $is_premium == false ? $pro_feature_sub_title : '';
            $options[$incr]['default'] = get_option('admin_email');
            $options[$incr]['validate'] = 'required';
            $options[$incr]['dependency'] = array('notify_admin', '==', 'true');
            $options[$incr]['class'] = $is_premium == false ? 'helpie-disabled' : '';
            $options[$incr]['attributes'] = [];
            $options[$incr]['attributes']['placeholder'] = __('mail', 'helpie-faq');
            $options[$incr]['attributes']['type'] = 'email';
            $options[$incr]['attributes']['pattern'] = '[^@]+@[^@]+\.[a-zA-Z]{2,6}';
            if ($is_premium == false) {
                $options[$incr]['attributes']['disabled'] = true;
                $options[$incr]['attributes']['readonly'] = 'readonly';
            }

            $options = $this->submit_form_buttons($options, $incr);
            // error_log('[$options] : ' . print_r($options, true));

            return $options;
        }

        public function submit_form_button($options, $incr)
        {
            $incr++;

            $options[$incr] = array(
                'type' => 'subheading',
                'content' => __('Submit Form -  Button', 'helpie-faq'),
            );

            $incr++;

            $options[$incr] = array(
                'id' => 'submit_form_button_color',
                'type' => 'color',
                'title' => __('Submit Form - Button Color', 'helpie-faq'),

                'default' => '',
                'output' => array(
                    "color" => ".helpie-faq-form__submit",
                ),

                'output_important' => true,
                'dependency' => array('show_submission', '==', 'true'),
            );
            $incr++;

            $options[$incr] = array(
                'id' => 'submit_form_button_bg',
                'type' => 'color',
                'title' => __('Submit Form - Button Background Color', 'helpie-faq'),

                'default' => '',
                'output' => array(
                    "background-color" => ".helpie-faq-form__submit",
                ),

                'output_important' => true,
                'dependency' => array('show_submission', '==', 'true'),
            );

            $incr++;
            $options[$incr] = array(
                'id' => 'submit_form_button_padding',
                'type' => 'spacing',
                'title' => __('Form Submit Button - Padding', 'helpie-faq'),
                'output' => array('.helpie-faq-form__submit'),
                'output_mode' => 'padding',
                'default' => array(
                    'top' => '15',
                    'right' => '15',
                    'bottom' => '15',
                    'left' => '15',
                    'unit' => 'px',
                ),
                'output_important' => true,
                'dependency' => array('show_submission', '==', 'true'),
                'class' => 'faq_fields--accordion_header_spacing',
            );

            $incr++;

            return [
                'options' => $options,
                'incr' => $incr,
            ];
        }

        public function submit_form_buttons($options, $incr)
        {
            $results = $this->submit_form_toggle_button($options, $incr);
            $options = $results['options'];
            $incr = $results['incr'];

            $results = $this->submit_form_button($options, $incr);
            $options = $results['options'];
            $incr = $results['incr'];

            return $options;
        }
        public function submit_form_toggle_button($options, $incr)
        {

            $options[$incr] = array(
                'type' => 'subheading',
                'content' => __('Submit Form Toggle Button', 'helpie-faq'),
            );

            $incr++;

            $options[$incr] = array(
                'id' => 'submit_form_toggle_color',
                'type' => 'color',
                'title' => __('Submit Form Toggle - Button Color', 'helpie-faq'),
                'default' => '',
                'output' => array(
                    "color" => ".helpie-faq-form__toggle",
                ),
                'output_important' => true,
                'dependency' => array('show_submission', '==', 'true'),
            );
            $incr++;

            $options[$incr] = array(
                'id' => 'submit_form_toggle_bg',
                'type' => 'color',
                'title' => __('Submit Form Toggle - Button Background Color', 'helpie-faq'),

                'default' => '',
                'output' => array(
                    "background-color" => ".helpie-faq-form__toggle",
                ),

                'output_important' => true,
                'dependency' => array('show_submission', '==', 'true'),
            );

            $incr++;
            $options[$incr] = array(
                'id' => 'submit_form_toggle_padding',
                'type' => 'spacing',
                'title' => __('Form Toggle Button -  Padding', 'helpie-faq'),
                'output' => array('.helpie-faq-form__toggle'),
                'output_mode' => 'padding',
                'default' => array(
                    'top' => '15',
                    'right' => '15',
                    'bottom' => '15',
                    'left' => '15',
                    'unit' => 'px',
                ),
                'output_important' => true,
                'dependency' => array('show_submission', '==', 'true'),
                'class' => 'faq_fields--accordion_header_spacing',
            );

            return [
                'options' => $options,
                'incr' => $incr,
            ];
        }

        public function get_faq_category_options()
        {

            $faq_repo = new \HelpieFaq\Includes\Repos\Faq_Repo();

            $faq_categories = $faq_repo->get_faq_categories();

            $faq_category_options = array();

            if (count($faq_categories) > 0) {
                foreach ($faq_categories as $faq_category) {
                    $faq_category_options[$faq_category->term_id] = $faq_category->name;
                }
            }

            return $faq_category_options;
        }

        public function pro_feature_sub_title()
        {
            return '<span style="color: #5cb85c; font-weight: 600;">* Pro Feature</span>';
        }

        public function group_category_page($prefix)
        {
            // Create taxonomy options

            $faq_group_item_fields = $this->get_faq_group_item_fields($prefix);

            \CSF::createTaxonomyOptions($prefix, array(
                'taxonomy' => 'helpie_faq_group',
                'data_type' => 'serialize', // The type of the database save options. `serialize` or `unserialize`
                'class' => 'hfaq-groups-container',
            ));

            // Create a section
            \CSF::createSection($prefix, array(
                'title' => 'FAQ Group Items',
                'icon' => 'fa fa-list',
                'fields' => $faq_group_item_fields,
            ));
        }

        public function get_faq_group_item_fields($prefix)
        {
            $utils_helper = new \HelpieFaq\Includes\Utils\Helpers();
            $default_category_id = $utils_helper->get_default_category_term_id();
            $default_category_term = $utils_helper->get_the_category_term_by_id($default_category_id);
            $default_category_name = isset($default_category_term) ? $default_category_term->name : '';

            $category_url = admin_url('edit-tags.php?taxonomy=helpie_faq_category&post_type=helpie_faq');

            $message = '<b>Select the FAQ Categories</b>';
            // $message .= ' No FAQ Categories available. <a href="' . $category_url . '">Create a FAQ Category here</a>';
            $message .= ' (By default your faq\'s would be in <b>"' . $default_category_name . '"</b> section. You can create your own FAQ Category <a href="' . esc_url($category_url) . '">here</a>).';

            $fields = array(
                array(
                    'id' => 'faq_groups',
                    'type' => 'repeater',
                    'class' => 'hfaq-groups__repeaters',
                    'fields' => array(
                        array(
                            'id' => 'faq_item',
                            'type' => 'accordion',
                            'class' => 'hfaq-groups__accordion',
                            'accordions' => array(
                                array(
                                    'title' => 'FAQ Item',
                                    'icon' => 'fa fa-quora',
                                    'fields' => array(
                                        array(
                                            'id' => 'post_id',
                                            'type' => 'text',
                                            'default' => '0',
                                            'class' => 'helpie-group-posts helpie-display-none',
                                            'attributes' => array(
                                                'style' => 'display:none;',
                                            ),
                                        ),
                                        array(
                                            'id' => 'title',
                                            'type' => 'text',
                                            'class' => 'hfaq-groups__accordion--input-title',
                                            'before' => '<b>' . __('Title', HELPIE_FAQ_DOMAIN) . '</b>',
                                            'default' => 'Toggle Title',
                                        ),
                                        array(
                                            'id' => 'categories',
                                            'type' => 'select',
                                            'chosen' => true,
                                            'multiple' => true,
                                            'before' => $message,
                                            'placeholder' => 'Select a category', 'helpie-faq',
                                            'empty_message' => '.',
                                            'options' => 'categories',
                                            'query_args' => array(
                                                'taxonomy' => 'helpie_faq_category',
                                            ),
                                            'default' => $default_category_id,
                                        ),
                                        array(
                                            'id' => 'content',
                                            'type' => 'wp_editor',
                                            'before' => '<b>' . __('Content', HELPIE_FAQ_DOMAIN) . '</b>',
                                            'media_buttons' => true,
                                            'default' => 'Toggle Content',
                                            'height' => '350px',
                                        ),
                                    ),
                                ),

                            ),
                        ),
                    ),
                ),
            );

            return $fields;
        }

        public function get_style_fields($prefix)
        {

            $setting_defaults = new \HelpieFaq\Includes\Settings\Option_Values();

            $allowed_accordion_header_tags = array();
            $allowed_accordion_header_tags['default'] = __('Default', 'helpie-faq');
            $allowed_accordion_header_tags = array_merge($allowed_accordion_header_tags, $setting_defaults->get_allowed_title_tags());
            if (isset($allowed_accordion_header_tags['p'])) {
                unset($allowed_accordion_header_tags['p']);
            }

            $fields = array(
                /**
                 * @since v1.6.2
                 *  - Load default FAQ styles from theme
                 */
                array(
                    'id' => 'enable_faq_styles',
                    'title' => __('Enable FAQ Style', 'helpie-faq'),
                    'type' => 'switcher',
                    'label' => __('Enabling this will allow you to apply custom styling', 'helpie-faq'),
                    'default' => false,
                    'class' => 'faq_fields--enable_faq_styles', // For testing purpose
                ),
                array(
                    'id' => 'theme',
                    'type' => 'select',
                    'title' => __('FAQ Theme', 'helpie-faq'),
                    'options' => array(
                        'light' => __('Light', 'helpie-faq'),
                        'dark' => __('Dark', 'helpie-faq'),
                    ),
                    'default' => 'light',
                    'info' => __('Select Theme of FAQ Layout Section', 'helpie-faq'),
                    'desc' => __('Custom Styles will overwrite FAQ theme styles', 'helpie-faq'),
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                    'class' => 'faq_fields--theme',
                ),
                /** Accordions Headers & Body Background, Content Styles. */
                array(
                    'type' => 'subheading',
                    'content' => __('FAQ Title & Body Styles (Custom Styles)', 'helpie-faq'),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('Custom FAQ Styles are shown only for Enable FAQ Style option value as true', 'helpie-faq'),
                    'dependency' => array('enable_faq_styles', '==', 'false'),
                ),
                array(
                    'id' => 'accordion_background',
                    'type' => 'color_group',
                    'title' => __('FAQ Backgrounds', 'helpie-faq'),
                    'options' => array(
                        'header' => __('Title Background', 'helpie-faq'),
                        'body' => __('Body Background', 'helpie-faq'),
                    ),
                    'default' => array(
                        'header' => '#FFFFFF',
                        'body' => '#FCFCFC',
                    ),
                    'desc' => __('If you would like to show the FAQs without backgrounds, set the background value as transparent', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                    ),
                    'class' => 'faq_fields--color_group',
                ),
                array(
                    'id' => 'accordion_header_content_styles',
                    'type' => 'typography',
                    'title' => __('FAQ Item - Title', 'helpie-faq'),
                    'output' => array('.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__header .accordion__title'),
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                ),
                array(
                    'id' => 'accordion_header_tag',
                    'type' => 'select',
                    'title' => __('Select Accordion Header Tag', 'helpie-faq'),
                    'options' => $allowed_accordion_header_tags,
                    'default' => 'default',
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                ),
                array(
                    'id' => 'accordion_header_spacing',
                    'type' => 'spacing',
                    'title' => __('FAQ Item - Title Padding', 'helpie-faq'),
                    'output' => array('.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__header'),
                    'output_mode' => 'padding',
                    'default' => array(
                        'top' => '15',
                        'right' => '15',
                        'bottom' => '15',
                        'left' => '15',
                        'unit' => 'px',
                    ),
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                    'class' => 'faq_fields--accordion_header_spacing',
                ),
                array(
                    'id' => 'title_icon',
                    'type' => 'icon',
                    'title' => __('Title Icon', 'helpie-faq'),
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                ),
                array(
                    'id' => 'title_icon_color',
                    'type' => 'color',
                    'title' => __('Title Icon Color', 'helpie-faq'),
                    'default' => '#44596B',
                    'output' => array(
                        "color" => ".helpie-faq.custom-styles .accordion__item .accordion__header .accordion__title .accordion__title-icon i",
                    ),
                    'output_important' => true,
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                ),
                // array(
                //     'id' => 'title_icon_position',
                //     'type' => 'select',
                //     'title' => __('Title Icon Position', 'helpie-faq'),
                //     'options' => array(
                //         'left' => __('Left', 'helpie-faq'),
                //         'right' => __('Right', 'helpie-faq'),
                //     ),
                //     'default' => 'left',
                //     'dependency' => array('enable_faq_styles', '==', 'true'),
                // ),
                array(
                    'id' => 'accordion_body_content_styles',
                    'type' => 'typography',
                    'title' => __('FAQ Item - Body', 'helpie-faq'),
                    'output' => array(
                        '.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body',
                        '.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body p',
                        '.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body h1',
                        '.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body h2',
                        '.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body h3',
                        '.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body h4',
                        '.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body h5',
                        '.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body h6',
                    ),
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                ),
                array(
                    'id' => 'accordion_body_spacing',
                    'type' => 'spacing',
                    'title' => __('FAQ Item - Body Content Padding', 'helpie-faq'),
                    'output' => array('.helpie-faq.accordions.custom-styles .accordion .accordion__item .accordion__body'),
                    'output_mode' => 'padding',
                    'default' => array(
                        'top' => '15',
                        'right' => '15',
                        'bottom' => '0',
                        'left' => '15',
                        'unit' => 'px',
                    ),
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                    'class' => 'faq_fields--accordion_body_spacing',
                ),
                /** @since v1.6.1
                 *  - add accordion border option
                 */
                array(
                    'id' => 'accordion_border',
                    'type' => 'border',
                    'title' => __('FAQ Border', 'helpie-faq'),
                    'default' => array(
                        'top' => '0',
                        'right' => '0',
                        'bottom' => '1',
                        'left' => '0',
                        'style' => 'solid',
                        'color' => '#44596B',
                        'unit' => 'px',
                    ),
                    'output' => array('.helpie-faq.accordions.custom-styles .accordion .accordion__item'),
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                    'class' => 'faq_fields--accordion_border',
                ),
                /**
                 * @since v1.6.3
                 * - added accordion_margin field
                 */
                array(
                    'id' => 'accordion_margin',
                    'type' => 'spacing',
                    'title' => __('FAQ Spacing', 'helpie-faq'),
                    'default' => array(
                        'top' => '0',
                        'right' => '0',
                        'bottom' => '0',
                        'left' => '0',
                        'unit' => 'px',
                    ),
                    'output' => array('.helpie-faq.custom-styles .accordion .accordion__item'),
                    'output_mode' => 'margin',
                    'dependency' => array('enable_faq_styles', '==', 'true'),
                ),
                /** Toggle Icons Style Section */
                array(
                    'type' => 'subheading',
                    'content' => __('Toggle Icons', 'helpie-faq'),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('Toggle Icons Style are shown only when the FAQ Style is enabled', 'helpie-faq'),
                    'dependency' => array('enable_faq_styles', '!=', 'true'),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('Toggle Icons does not show if the display mode is FAQ List', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode', '==', 'faq_list', 'all'),
                    ),
                ),
                array(
                    'id' => 'toggle_icon_type',
                    'type' => 'select',
                    'title' => __('Toggle Icon', 'helpie-faq'),
                    'options' => array(
                        'default' => __('Default', 'helpie-faq'),
                        'custom' => __('Custom', 'helpie-faq'),
                    ),
                    'default' => 'default',
                    'desc' => __('Toggle Icons are not shown for FAQ List display mode', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode', '!=', 'faq_list', 'all'),
                    ),
                ),
                array(
                    'id' => 'toggle_open',
                    'type' => 'icon',
                    'title' => __('Toggle Open', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode', '!=', 'faq_list', 'all'),
                        array('toggle_icon_type', '==', 'custom'),
                    ),
                ),
                array(
                    'id' => 'toggle_off',
                    'type' => 'icon',
                    'title' => __('Toggle Off', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode', '!=', 'faq_list', 'all'),
                        array('toggle_icon_type', '==', 'custom'),
                    ),
                ),
                array(
                    'id' => 'icon_color',
                    'type' => 'color',
                    'title' => __('Icon Color', 'helpie-faq'),
                    'default' => '#44596B',
                    'output' => array(
                        "color" => ".helpie-faq.custom-styles .accordion__item .accordion__header .accordion__toggle .accordion__toggle-icons", // set custom font-awesome icon
                        "background-color" => ".helpie-faq.custom-styles .accordion__header:after,.helpie-faq.custom-styles .accordion__header:before", // set default icon pseudo class background-color
                    ),
                    'output_important' => true,
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode', '!=', 'faq_list', 'all'),
                    ),
                ),
                array(
                    'id' => 'icon_position',
                    'type' => 'select',
                    'title' => __('Icon Position', 'helpie-faq'),
                    'options' => array(
                        'right' => __('Right', 'helpie-faq'),
                        'left' => __('Left', 'helpie-faq'),
                    ),
                    'default' => 'right',
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode', '!=', 'faq_list', 'all'),
                    ),
                ),
                /** Title Styles section */
                array(
                    'type' => 'subheading',
                    'content' => __('FAQ Section - Title', 'helpie-faq'),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('FAQ Title Style are shown only when the FAQ Style is enabled', 'helpie-faq'),
                    'dependency' => array('enable_faq_styles', '!=', 'true'),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('FAQ Title Style does not apply if the title is hidden', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('show_title', '!=', 'true', 'all'),
                    ),
                ),
                array(
                    'id' => 'title_styles',
                    'type' => 'typography',
                    'title' => __('Title Styles', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('show_title', '==', 'true', 'all'),
                    ),
                    'output' => array('.helpie-faq.custom-styles .collection-title'),
                ),
                // Category Accordions Styles
                array(
                    'type' => 'subheading',
                    'content' => __('Category FAQ Styles', 'helpie-faq'),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('Category Style options are shown only when the FAQ Style is enabled', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '!=', 'true'),
                    ),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('Category Styles are shown only for FAQs Group By value as Category', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode_group_by', '==', 'none', 'all'),
                    ),
                ),
                array(
                    'id' => 'category_accordion_styles',
                    'type' => 'typography',
                    'title' => __('Category Style', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode_group_by', '==', 'category', 'all'),
                    ),
                    'output' => array(
                        '.helpie-faq.custom-styles .accordion__category.accordion__heading',
                        '.helpie-faq.custom-styles .accordion__category.accordion__item:not(.accordion__body) > .accordion__header .accordion__title',
                    ),
                ),
                array(
                    'id' => 'category_title_icon',
                    'type' => 'icon',
                    'title' => __('Category Title Icon', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode_group_by', '==', 'category', 'all'),
                    ),
                ),
                array(
                    'id' => 'category_title_icon_color',
                    'type' => 'color',
                    'title' => __('Category Title Icon Color', 'helpie-faq'),
                    'default' => '#44596B',
                    'output' => array(
                        "color" => ".helpie-faq.custom-styles .accordion__category.accordion__heading .accordion__title-icon i,.helpie-faq.custom-styles .accordion__category.accordion__item:not(.accordion__body) > .accordion__header .accordion__title .accordion__title-icon i",

                    ),
                    'output_important' => true,
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('display_mode_group_by', '==', 'category', 'all'),
                    ),
                ),
                array(
                    'type' => 'subheading',
                    'content' => __('Search Styles', 'helpie-faq'),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('Search Style are shown only when the FAQ Style is enabled', 'helpie-faq'),
                    'dependency' => array('enable_faq_styles', '!=', 'true'),
                ),
                array(
                    'type' => 'notice',
                    'content' => __('Search Style are shown only when the search is enabled', 'helpie-faq'),
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('show_search', '!=', 'true', 'all'),
                    ),
                ),
                array(
                    'id' => 'search_background_color',
                    'type' => 'color',
                    'title' => __('Background Color', 'helpie-faq'),
                    'output' => array('.helpie-faq.custom-styles .search__wrapper .search__input'),
                    'output_mode' => 'background-color',
                    'output_important' => true,
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('show_search', '==', 'true', 'all'),
                    ),
                    'class' => 'faq_fields--search_background_color',
                ),
                array(
                    'id' => 'search_font_color',
                    'type' => 'color',
                    'title' => __('Font Color', 'helpie-faq'),
                    'output' => array('.helpie-faq.custom-styles .search__wrapper .search__input', '.helpie-faq.custom-styles .search__wrapper .search__input:focus', '.helpie-faq.custom-styles .search__wrapper .search__input::placeholder'),
                    'output_mode' => 'color',
                    'output_important' => true,
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('show_search', '==', 'true', 'all'),
                    ),
                    'class' => 'faq_fields--search_font_color',
                ),
                array(
                    'id' => 'search_icon_color',
                    'type' => 'color',
                    'title' => __('Icon Color', 'helpie-faq'),
                    'output' => array('.helpie-faq.custom-styles .search__icon'),
                    'output_mode' => 'color',
                    'output_important' => true,
                    'dependency' => array(
                        array('enable_faq_styles', '==', 'true'),
                        array('show_search', '==', 'true', 'all'),
                    ),
                    'class' => 'faq_fields--search_icon_color',
                ),
            );

            $fields = $this->add_premium_field_notice($fields, 'styles');

            return $fields;
        }

        /** use of method, show premium notice to premium fields in free users */
        public function add_premium_field_notice($fields, $type)
        {
            $all_premium_fields = $this->get_all_premium_fields();
            $premium_fields = isset($all_premium_fields[$type]) ? $all_premium_fields[$type] : [];
            if (empty($premium_fields)) {
                return $fields;
            }

            if (!class_exists('\Pauple\Pluginator\Codester\Utils')) {
                return $fields;
            }
            $collections = array(
                'fields' => $fields,
                'is_premium' => hf_fs()->can_use_premium_code__premium_only() ? true : false,
                'plugin_slug' => HELPIE_FAQ_DOMAIN,
                'premium_fields_keys' => $premium_fields,
                'disabled_class_name' => 'helpie-disabled',
            );
            $utils = new \Pauple\Pluginator\Codester\Utils();
            $fields = $utils->add_premium_field_notice($collections);
            return $fields;
        }

        private function get_all_premium_fields()
        {

            $getter = new \HelpieFaq\Includes\Settings\Getters\Getter();
            $fields = array(
                'general' => array_keys($getter->get_premium_general_fields()),
                'display_and_layouts' => array_keys($getter->get_premium_display_layouts_fields()),
                'pagination' => array_keys($getter->get_pagination_fields()),
                'styles' => array_keys($getter->get_premium_styles_fields()),
                'submission' => array_keys($getter->get_premium_submission_fields()),
                'excerpt' => array_keys($getter->get_premium_excerpt_fields()),
            );
            return $fields;
        }

        public function single_faq_page_settings($prefix)
        {
            $fields = $this->get_single_faq_page_fields($prefix);

            \CSF::createSection(
                $prefix,
                array(
                    'id' => 'single_faq_page',
                    'title' => __('Single FAQ Page', 'helpie-faq'),
                    'icon' => 'fa fa-server',
                    'fields' => $fields,
                )
            );
        }

        public function get_single_faq_page_fields($prefix)
        {
            return array_merge([], $this->get_excerpt_fields($prefix));
        }

        public function get_excerpt_fields($prefix)
        {
            $fields = array(
                // array(
                //     'type' => 'subheading',
                //     'content' => __('Read more link', 'helpie-faq'),
                // ),
                array(
                    'id' => 'enable_single_faq_page',
                    'type' => 'switcher',
                    'title' => __('Enable - Single FAQ Page', 'helpie-faq'),
                    'default' => true,
                    'class' => 'faq_fields--enable_excerpt',
                ),
                array(
                    'id' => 'enable_excerpt',
                    'type' => 'switcher',
                    'title' => __('Enable Read More', 'helpie-faq'),
                    'desc' => __('<span style="color:#ff0000;font-weight: 700;">Shows post_excerpt, if not available takes the first \'n\' words from the post_content</span>', 'helpie-faq'),
                    'default' => false,
                    'class' => 'faq_fields--enable_excerpt',
                    'dependency' => array('enable_single_faq_page', '==', 'true'),
                ),
                array(
                    'id' => 'read_more_link_text',
                    'type' => 'text',
                    'title' => __('Read More Link Text', 'helpie-faq'),
                    'desc' => __('Enter a custom read more link text', 'helpie-faq'),
                    'default' => "Read More",
                    'class' => 'faq_fields--read_more_link_text',
                ),
                array(
                    'id' => 'excerpt_word_length',
                    'type' => 'number',
                    'title' => __('Excerpt word length', 'helpie-faq'),
                    'desc' => __('Enter a custom excerpt word length', 'helpie-faq'),
                    'default' => "55",
                    'class' => 'faq_fields--excerpt_word_length',
                ),
                array(
                    'id' => 'open_new_window',
                    'type' => 'checkbox',
                    'title' => __('Open in New Window', 'helpie-faq'),
                    'label' => __('Open in New Window', 'helpie-faq'),
                    'default' => true,
                    'class' => 'faq_fields--open_new_window',
                ),
            );
            $fields = $this->add_premium_field_notice($fields, 'excerpt');

            return $fields;
        }

        public function display_and_layout_settings($prefix)
        {
            $fields = new \HelpieFaq\Includes\Settings\Fields();
            $section_fields = array_merge(
                [],
                $fields->get_display_section_settings_fields(),
                $fields->get_layout_section_settings_fields()
            );

            $section_fields = $this->add_premium_field_notice($section_fields, 'display_and_layouts');
            \CSF::createSection(
                $prefix,
                array(
                    'id' => 'display_and_layout',
                    'title' => __('Display & Layout', 'helpie-faq'),
                    'icon' => 'fa fa-sitemap',
                    'fields' => $section_fields,
                )
            );
        }

        public function pagination_settings($prefix)
        {
            $fields = new \HelpieFaq\Includes\Settings\Fields();
            $data_fields = array_merge(
                [],
                $fields->get_pagination_section_settings_fields()
            );

            $data_fields = $this->add_premium_field_notice($data_fields, 'pagination');

            \CSF::createSection(
                $prefix,
                array(
                    'id' => 'pagination',
                    'title' => __('Pagination', 'helpie-faq'),
                    'icon' => 'fa fa-ellipsis-h',
                    'fields' => $data_fields,
                )
            );
        }

        public function faq_group_settings($prefix)
        {
            \CSF::createSection(
                $prefix,
                array(
                    'id' => 'faq_group',
                    'title' => __('FAQ Group', 'helpie-faq'),
                    'icon' => 'fa fa-list',
                    'fields' => [
                        [
                            'id' => 'helpie_faq_group_slug',
                            'type' => 'text',
                            'title' => __('Helpie FAQ Group Slug', 'helpie-faq'),
                            'attributes' => array(
                                'placeholder' => __('Helpie FAQ Group Slug', 'helpie-faq'),
                            ),
                            'default' => 'helpie_faq_group',
                        ],
                    ],
                )
            );
        }
    } // END CLASS
}

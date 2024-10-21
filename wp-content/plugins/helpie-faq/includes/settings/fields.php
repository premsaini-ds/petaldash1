<?php

namespace HelpieFaq\Includes\Settings;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( '\\HelpieFaq\\Includes\\Settings\\Fields' ) ) {
    class Fields {
        public function get_faq_slug_settings() {
            $fields = array(array(
                'id'         => 'helpie_faq_slug',
                'type'       => 'text',
                'title'      => __( 'Helpie FAQ Slug', 'helpie-faq' ),
                'attributes' => array(
                    'placeholder' => __( 'Helpie FAQ Slug', 'helpie-faq' ),
                ),
                'default'    => 'helpie_faq',
            ));
            return $fields;
        }

        public function get_categories_field() {
            $faq_repo = new \HelpieFaq\Includes\Repos\Faq_Repo();
            $options = $faq_repo->get_options( 'categories' );
            $field = array(array(
                'id'       => 'categories',
                'type'     => 'select',
                'title'    => __( 'Categories', 'helpie-faq' ),
                'options'  => $options,
                'chosen'   => true,
                'multiple' => true,
                'default'  => 'all',
            ));
            return $field;
        }

        public function get_sortby_field() {
            $field = array(
                'id'      => 'sortby',
                'title'   => __( 'Sort By', 'helpie-faq' ),
                'type'    => 'select',
                'options' => array(
                    'publish'      => __( 'Publish Date', 'helpie-faq' ),
                    'updated'      => __( 'Updated Date', 'helpie-faq' ),
                    'alphabetical' => __( 'Alphabetical', 'helpie-faq' ),
                    'menu_order'   => __( 'Menu Order', 'helpie-faq' ),
                ),
                'default' => 'publish',
                'desc'    => __( 'Sorting won\'t work for FAQ groups', 'helpie-faq' ),
            );
            return $field;
        }

        public function get_url_attribute_type_field() {
            $url = get_bloginfo( 'wpurl' );
            $post_id_url = $url . '/sample-post/#hfaq-post-{post_id}';
            $post_slug_url = $url . '/sample-post/#{post-slug}';
            $field = array(
                'id'         => 'faq_url_type',
                'type'       => 'radio',
                'title'      => __( 'FAQ Url Types', 'helpie-faq' ),
                'options'    => array(
                    'post_id'        => __( 'Using Post ID  -  ' . $post_id_url, 'helpie-faq' ),
                    'temp_post_slug' => __( 'Using Post Slug  -  ' . $post_slug_url, 'helpie-faq' ),
                ),
                'default'    => 'post_id',
                'dependency' => array('faq_url_attribute', '==', 'true'),
                'desc'       => __( '<span style="font-weight: 700;">URL Post Slug </span> option only available on premium plan.', 'helpie-faq' ),
                'class'      => 'faq_fields--faq_url_type',
            );
            return $field;
        }

        public function get_title_section_settings_fields() {
            $setting_defaults = new \HelpieFaq\Includes\Settings\Option_Values();
            $allowed_title_tags = $setting_defaults->get_allowed_title_tags();
            return array(
                array(
                    'type'    => 'subheading',
                    'content' => __( 'FAQ Title', 'helpie-faq' ),
                ),
                array(
                    'id'      => 'show_title',
                    'type'    => 'switcher',
                    'title'   => __( 'Show Title', 'helpie-faq' ),
                    'default' => true,
                ),
                array(
                    'id'         => 'title',
                    'type'       => 'text',
                    'title'      => __( 'Title', 'helpie-faq' ),
                    'attributes' => array(
                        'placeholder' => __( 'FAQ Title', 'helpie-faq' ),
                    ),
                    'default'    => __( 'Helpie FAQ', 'helpie-faq' ),
                    'dependency' => array(array('show_title', '==', 'true')),
                ),
                array(
                    'id'         => 'title_tag',
                    'type'       => 'select',
                    'title'      => __( 'Select FAQ Title Tag', 'helpie-faq' ),
                    'options'    => $allowed_title_tags,
                    'default'    => 'h3',
                    'dependency' => array(array('show_title', '==', 'true')),
                )
            );
        }

        public function get_search_section_settings_fields() {
            $is_premium = hf_fs()->can_use_premium_code__premium_only();
            $pro_feature_sub_title = $this->pro_feature_sub_title();
            $fields = [];
            $fields[] = array(
                'type'    => 'subheading',
                'content' => __( 'FAQ Search', 'helpie-faq' ),
            );
            $fields[] = array(
                'id'      => 'show_search',
                'type'    => 'switcher',
                'title'   => __( 'Show Search in FAQ', 'helpie-faq' ),
                'label'   => __( 'You can search through all FAQ items', 'helpie-faq' ),
                'default' => true,
            );
            $fields[] = array(
                'id'         => 'search_placeholder',
                'type'       => 'text',
                'title'      => __( 'Search Placeholder text', 'helpie-faq' ),
                'attributes' => array(
                    'placeholder' => __( 'FAQ Search Placeholder text', 'helpie-faq' ),
                ),
                'dependency' => array('show_search', '==', 'true'),
                'default'    => __( 'Search FAQ', 'helpie-faq' ),
            );
            $fields[] = array(
                'id'         => 'search_by_tags',
                'type'       => 'switcher',
                'title'      => __( 'Search By Tags', 'helpie-faq' ),
                'label'      => __( 'You can search through all FAQ items by post-tags', 'helpie-faq' ),
                'default'    => true,
                'dependency' => array('show_search', '==', 'true'),
            );
            $attributes = [];
            if ( $is_premium == false ) {
                $attributes = [
                    'disabled' => 'disabled',
                ];
            }
            $fields[] = array(
                'id'         => 'search_highlight',
                'subtitle'   => ( $is_premium == false ? $pro_feature_sub_title : '' ),
                'title'      => __( 'Search Highlight', 'helpie-faq' ),
                'default'    => false,
                'type'       => 'switcher',
                'attributes' => $attributes,
                'class'      => ( $is_premium == false ? 'helpie-disabled' : '' ),
            );
            $fields[] = array(
                'id'               => 'search_highlight_bg_color',
                'subtitle'         => ( $is_premium == false ? $pro_feature_sub_title : '' ),
                'type'             => 'color',
                'title'            => __( 'Search Highlight - Background Color', 'helpie-faq' ),
                'default'          => '#ff0',
                'output'           => array(
                    "background-color" => ".helpie-faq.accordions .helpie-mark",
                ),
                'output_important' => true,
                'dependency'       => array(array('search_highlight', '==', 'true')),
                'attributes'       => $attributes,
                'class'            => ( $is_premium == false ? 'helpie-disabled' : '' ),
            );
            $fields[] = array(
                'id'               => 'search_highlight_color',
                'subtitle'         => ( $is_premium == false ? $pro_feature_sub_title : '' ),
                'type'             => 'color',
                'title'            => __( 'Search Highlight - Text Color', 'helpie-faq' ),
                'default'          => '#000',
                'output'           => array(
                    "color" => ".helpie-faq.accordions .helpie-mark",
                ),
                'output_important' => true,
                'dependency'       => array(array('search_highlight', '==', 'true')),
                'attributes'       => $attributes,
                'class'            => ( $is_premium == false ? 'helpie-disabled' : '' ),
            );
            $fields[] = array(
                'type'    => 'subheading',
                'content' => __( 'WordPress Search', 'helpie-faq' ),
            );
            $fields[] = array(
                'id'      => 'exclude_from_search',
                'type'    => 'switcher',
                'title'   => __( 'Exclude FAQ posts from WordPress Search', 'helpie-faq' ),
                'label'   => __( 'Enable / Disable ', 'helpie-faq' ),
                'default' => true,
            );
            return $fields;
        }

        public function get_pagination_section_settings_fields() {
            return array(array(
                'type'    => 'subheading',
                'content' => __( 'Pagination', 'helpie-faq' ),
            ), array(
                'id'      => 'pagination',
                'type'    => 'switcher',
                'title'   => __( 'Enable Pagination', 'helpie-faq' ),
                'label'   => __( 'Enable / Disable pagination', 'helpie-faq' ),
                'default' => false,
                'desc'    => __( 'Pagination won\'t work for Category Accordion display modes.', 'helpie-faq' ),
            ), array(
                'id'         => 'limit',
                'type'       => 'number',
                'title'      => __( 'Limit ( number of items )', 'helpie-faq' ),
                'default'    => -1,
                'attributes' => array(
                    'min' => -1,
                ),
                'info'       => __( 'Limit of the FAQ items', 'helpie-faq' ),
            ));
        }

        public function get_display_section_settings_fields() {
            return array(
                array(
                    'type'    => 'subheading',
                    'content' => __( 'Display Settings', 'helpie-faq' ),
                ),
                array(
                    'id'      => 'display_mode',
                    'title'   => __( 'Display Mode', 'helpie-faq' ),
                    'default' => 'simple_accordion',
                    'options' => array(
                        'simple_accordion' => __( 'Simple Accordion', 'helpie-faq' ),
                        'faq_list'         => __( 'FAQ List', 'helpie-faq' ),
                    ),
                    'type'    => 'select',
                    'class'   => 'faq_fields--display_mode',
                ),
                array(
                    'id'         => 'toggle',
                    'type'       => 'switcher',
                    'title'      => __( 'Toggle', 'helpie-faq' ),
                    'label'      => __( 'Toggle Open / closed Previous Item', 'helpie-faq' ),
                    'default'    => true,
                    'dependency' => array(
                        'display_mode',
                        '!=',
                        'faq_list',
                        'all'
                    ),
                ),
                array(
                    'id'         => 'open_by_default',
                    'type'       => 'select',
                    'title'      => __( 'FAQ Open By Default', 'helpie-faq' ),
                    'options'    => array(
                        'none'          => __( 'None', 'helpie-faq' ),
                        'open_first'    => __( 'Open First FAQ', 'helpie-faq' ),
                        'open_all_faqs' => __( 'All FAQs', 'helpie-faq' ),
                    ),
                    'default'    => 'open_first',
                    'dependency' => array(
                        'display_mode',
                        '!=',
                        'faq_list',
                        'all'
                    ),
                ),
                array(
                    'id'      => 'faq_url_attribute',
                    'type'    => 'switcher',
                    'title'   => __( 'Add FAQ Url Attribute', 'helpie-faq' ),
                    'label'   => __( 'Faq item added in url', 'helpie-faq' ),
                    'default' => true,
                ),
                $this->get_url_attribute_type_field()
            );
        }

        public function pro_feature_sub_title() {
            return '<span style="color: #5cb85c; font-weight: 600;">* Pro Feature</span>';
        }

        public function get_layout_section() {
            $is_premium = hf_fs()->can_use_premium_code__premium_only();
            $pro_feature_sub_title = $this->pro_feature_sub_title();
            $fields = [];
            $fields[] = array(
                'type'    => 'subheading',
                'content' => __( 'Layout/Grouping Settings', 'helpie-faq' ),
            );
            // if (hf_fs()->can_use_premium_code__premium_only()) {
            $attributes = [];
            if ( $is_premium == false ) {
                $attributes = [
                    'disabled' => 'disabled',
                ];
            }
            // error_log('is_premium: ' . $is_premium);
            $fields[] = array(
                'id'         => 'num_of_cols',
                'subtitle'   => ( $is_premium == false ? $pro_feature_sub_title : '' ),
                'title'      => __( 'Number of Columns', 'helpie-faq' ),
                'default'    => '1',
                'options'    => array(
                    '1' => __( '1', 'helpie-faq' ),
                    '2' => __( '2', 'helpie-faq' ),
                    '3' => __( '3', 'helpie-faq' ),
                ),
                'type'       => 'select',
                'attributes' => $attributes,
                'class'      => ( $is_premium == false ? 'helpie-disabled' : '' ),
            );
            // }
            $fields[] = array(
                'id'      => 'display_mode_group_by',
                'title'   => __( 'Group FAQs By', 'helpie-faq' ),
                'default' => 'none',
                'options' => array(
                    'none'     => __( 'None', 'helpie-faq' ),
                    'category' => __( 'Category', 'helpie-faq' ),
                ),
                'type'    => 'select',
                'class'   => 'faq_fields--display_mode_group_by',
            );
            $fields[] = array(
                'id'         => 'display_mode_group_container',
                'title'      => __( 'Group Container', 'helpie-faq' ),
                'default'    => 'simple_section_with_header',
                'options'    => array(
                    'simple_section_with_header' => __( 'Simple Section With Header', 'helpie-faq' ),
                    'accordion'                  => __( 'Accordion', 'helpie-faq' ),
                ),
                'type'       => 'select',
                'class'      => 'faq_fields--display_mode_group_container',
                'dependency' => array(array('display_mode', '==', 'simple_accordion'), array('display_mode_group_by', '!=', 'none')),
            );
            $fields[] = $this->get_sortby_field();
            $fields[] = array(
                'id'      => 'order',
                'title'   => __( 'Order', 'helpie-faq' ),
                'default' => 'desc',
                'options' => array(
                    'asc'  => __( 'Ascending', 'helpie-faq' ),
                    'desc' => __( 'Descending', 'helpie-faq' ),
                ),
                'type'    => 'select',
            );
            // $layout_section = array($fields);
            // return $layout_section;
            return $fields;
        }

        public function get_category_section_fields() {
            $fields = array(
                // array(
                //     'id' => 'open_first',
                //     'type' => 'switcher',
                //     'title' => __('Open First FAQ Item', 'helpie-faq'),
                //     'label' => __('First item open by default', 'helpie-faq'),
                //     'default' => true,
                // ),
                array(
                    'type'    => 'subheading',
                    'content' => __( 'Category Sorting', 'helpie-faq' ),
                ),
                array(
                    'id'      => 'category_sortby',
                    'title'   => __( 'Category Sort By', 'helpie-faq' ),
                    'type'    => 'select',
                    'options' => array(
                        'publish'        => __( 'Publish Date', 'helpie-faq' ),
                        'updated'        => __( 'Updated Date', 'helpie-faq' ),
                        'alphabetical'   => __( 'Alphabetical', 'helpie-faq' ),
                        'menu_order'     => __( 'Menu Order', 'helpie-faq' ),
                        'articles_count' => __( 'Articles Count', 'helpie-faq' ),
                    ),
                    'default' => 'publish',
                ),
                array(
                    'id'      => 'category_order',
                    'title'   => __( 'Category Order', 'helpie-faq' ),
                    'default' => 'desc',
                    'options' => array(
                        'asc'  => __( 'Ascending', 'helpie-faq' ),
                        'desc' => __( 'Descending', 'helpie-faq' ),
                    ),
                    'type'    => 'select',
                ),
            );
            return $fields;
        }

        public function get_layout_section_settings_fields() {
            $layout_fields = $this->get_layout_section();
            $category_fields = $this->get_category_section_fields();
            $fields = array_merge( $layout_fields, $category_fields );
            return $fields;
        }

        public function get_others_section_settings_fields() {
            return array(
                array(
                    'type'    => 'subheading',
                    'content' => __( 'Other', 'helpie-faq' ),
                ),
                array(
                    'id'      => 'enable_content_hooks',
                    'type'    => 'switcher',
                    'title'   => __( 'Enable Content Hooks', 'helpie-faq' ),
                    'label'   => __( 'Allow/Disallow other plugins hooking onto the content in FAQs', 'helpie-faq' ),
                    'default' => true,
                ),
                array(
                    'id'      => 'enable_wpautop',
                    'type'    => 'switcher',
                    'title'   => __( 'Enable wpautop', 'helpie-faq' ),
                    'label'   => __( 'Enable / Disable wpautop', 'helpie-faq' ),
                    'default' => false,
                ),
                array(
                    'id'      => 'product_only',
                    'type'    => 'switcher',
                    'title'   => __( 'FAQs Shows Products Only', 'helpie-faq' ),
                    'label'   => __( 'True / False ', 'helpie-faq' ),
                    'default' => false,
                ),
                array(
                    'id'      => 'enable_schema',
                    'type'    => 'switcher',
                    'title'   => __( 'Enable FAQ Schema', 'helpie-faq' ),
                    'label'   => __( 'True / False (Disable if you are using another plugin for schema) ', 'helpie-faq' ),
                    'default' => true,
                ),
                array(
                    'id'      => 'onload_scrollto_delay',
                    'type'    => 'text',
                    'title'   => __( 'On Load Scroll to delay', 'helpie-faq' ),
                    'label'   => __( 'Increase value if theme loads js after document ready', 'helpie-faq' ),
                    'default' => 0,
                ),
                array(
                    'id'      => 'enable_same_page_scroll',
                    'type'    => 'switcher',
                    'title'   => __( 'Enable Same Page Scroll', 'helpie-faq' ),
                    'label'   => __( 'Scroll to FAQ when clicking on it', 'helpie-faq' ),
                    'default' => false,
                )
            );
        }

    }

}
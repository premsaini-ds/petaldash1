<?php

namespace Stylus\Components;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Stylus\Components\Accordion')) {

    class Accordion
    {
        public $display_mode;

        public $enabled_faq_styles;

        public function __construct()
        {}

        public function get_view($viewProps)
        {

            $html = '';
            $collectionProps = isset($viewProps['collection']) ? $viewProps['collection'] : [];
            $enabled_faq_styles = (isset($collectionProps['enable_faq_styles']) && $collectionProps['enable_faq_styles'] == true) ? true : false;

            $this->display_mode = isset($collectionProps['display_mode']) ? $collectionProps['display_mode'] : 'simple_accordion';
            $this->enabled_faq_styles = $enabled_faq_styles;

            $is_titled_view = $this->check_is_titled_view($collectionProps);

            if ($is_titled_view) {
                $html = $this->get_titled_view($viewProps['items'], $collectionProps);
            } else {
                $html = $this->get_accordion($viewProps['items'], $collectionProps);
            }

            return $html;
        }

        public function check_is_titled_view($collectionProps)
        {
            $display_mode_group_by = isset($collectionProps['display_mode_group_by']) ? $collectionProps['display_mode_group_by'] : 'none';
            $display_mode_group_container = isset($collectionProps['display_mode_group_container']) ? $collectionProps['display_mode_group_container'] : 'category';

            if ($this->display_mode == 'faq_list') {
                return ($display_mode_group_by == 'category') ? true : false;
            }
            return ($display_mode_group_by != 'none' && $display_mode_group_container == 'simple_section_with_header') ? true : false;
        }

        public function get_titled_view($props, $collectionProps)
        {
            $html = '';

            $title_icon = $this->get_the_title_icon($collectionProps, 'category_title_icon');
            for ($ii = 0; $ii < sizeof($props); $ii++) {
                $title_content = $this->get_the_title_content($props[$ii]['title']);
                $html .= "<h3 class='accordion__heading accordion__category'>" . $title_icon . $title_content . "</h3>";
                $children = isset($props[$ii]['children']) ? $props[$ii]['children'] : [];
                if (empty($children)) {
                    continue;
                }
                $html .= $this->get_accordion($props[$ii]['children'], $collectionProps);
            }

            return $html;
        }

        public function partition(array $list, $p)
        {
            $listlen = count($list);
            $partlen = floor($listlen / $p);
            $partrem = $listlen % $p;
            $partition = array();
            $mark = 0;
            for ($px = 0; $px < $p; $px++) {
                $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
                $partition[$px] = array_slice($list, $mark, $incr);
                $mark += $incr;
            }
            return $partition;
        }
        public function get_accordion($props, $collectionProps, $scope = 'none')
        {

            $faq_list_class = ($this->display_mode == 'faq_list') ? 'faq_list' : '';

            $html = '<article class="accordion ' . esc_attr($faq_list_class) . '">';

            $scope = isset($scope) ? $scope : 'none';
            $is_premium = hf_fs()->can_use_premium_code__premium_only();
            if ($scope == 'child' || false == $is_premium) {
                $num_of_cols = 1;
            } else {
                $num_of_cols = isset($collectionProps['num_of_cols']) && !empty($collectionProps['num_of_cols']) ? (int) $collectionProps['num_of_cols'] : 1;
            }

            if ($num_of_cols == 0) {
                $num_of_cols = 1;
            }

            // error_log('collectionProps num_of_cols: ' . $collectionProps['num_of_cols']);
            // error_log('num_of_cols: ' . $num_of_cols);

            $total_items = sizeof($props);
            $single_col_size = ceil($total_items / $num_of_cols);
            $group_by_columns = $this->partition($props, $num_of_cols);

            // error_log('group_by_columns: ' . print_r($group_by_columns, true));
            $html .= "<div class='helpie-faq-row'>";

            for ($jj = 0; $jj < $num_of_cols; $jj++) {
                $col_class_number = 12 / $num_of_cols;
                $html .= "<div class='helpie-faq-col helpie-faq-col-" . $col_class_number . "' >";
                $html .= "<ul>";
                $items = isset($group_by_columns[$jj]) ? $group_by_columns[$jj] : [];

                // error_log('num_of_cols: ' . $num_of_cols . ' jj: ' . $jj . ' single_col_size: ' . $single_col_size . ' total_items: ' . $total_items . ' items: ' . print_r($items, true));
                for ($ii = 0; $ii < $single_col_size; $ii++) {
                    if (isset($items[$ii]) && !empty($items[$ii])) {
                        $html .= $this->get_single_item($items[$ii], $collectionProps);
                    }

                }

                $html .= "</ul>";
                $html .= "</div>";
            }
            $html .= "</div>";

            $html .= '</article>';

            return $html;
        }

        public function get_single_item($props, $collectionProps)
        {
            $id = isset($props['post_id']) ? "post-" . $props['post_id'] : "term-" . $props['term_id'];

            $url_attribute = $this->get_url_attribute($props, $collectionProps);

            $accordion__header_classes = '';

            $show_accordion_body = '';
            if (isset($collectionProps['open_by_default']) && $collectionProps['open_by_default'] == 'open_all_faqs') {
                $show_accordion_body = 'display: block;';
                $accordion__header_classes .= ' active';
            }

            $custom_toggle_icon_content = $this->get_custom_toggle_icon($collectionProps);

            if (!empty($custom_toggle_icon_content)) {
                $accordion__header_classes .= ' custom-icon';
            }

            $icon_position_is_left = isset($collectionProps['icon_position']) && $collectionProps['icon_position'] == 'left' ? true : false;

            if ($icon_position_is_left && $this->enabled_faq_styles && $this->display_mode != 'faq_list') {
                $accordion__header_classes .= ' accordion__icon__position--ltr';
            }

            $accordion_styles = $this->get_accordion_styles($collectionProps);

            $accordion_body_styles = $show_accordion_body . $accordion_styles['body_styles'];

            $tags = isset($props['tags']) && !empty($props['tags']) ? $props['tags'] : '';
            $is_category = isset($props['term_id']) ? 'accordion__category' : '';

            $html = '<li class="accordion__item ' . esc_attr($is_category) . '">';
            $html .= '<div class="accordion__header ' . esc_attr($accordion__header_classes) . '" data-id="' . esc_attr($id) . '" data-item="' . esc_attr($url_attribute) . '" style="' . $accordion_styles['header_styles'] . '" data-tags="' . esc_attr($tags) . '">';

            /** Get the accordion title icon  */
            if (isset($props['term_id']) && !empty($props['term_id'])) {
                $title_icon = $this->get_the_title_icon($collectionProps, 'category_title_icon');
            } else {
                $title_icon = $this->get_the_title_icon($collectionProps, 'title_icon');
            }

            $accordion_tag = $this->get_accordion_title_tag($collectionProps);
            $accordion_tag = esc_attr($accordion_tag);

            $title_content = $this->get_the_title_content($props['title']);

            $html .= '<' . $accordion_tag . ' class="accordion__title">' . $title_icon . $title_content . '</' . $accordion_tag . '>';

            $html .= $custom_toggle_icon_content;
            $html .= '</div>';
            $html .= '<div class="accordion__body" style="' . $accordion_body_styles . '">';

            // error_log('$props : ' . print_r($props, true));
            // $collectionProps['raw_content'] = true;

            if (isset($collectionProps['enable_content_hooks']) && false == $collectionProps['enable_content_hooks']) {
                $content = $props['content'];
            } else {
                $content = apply_filters('helpie_faq/the_content', array(
                    'props' => $props,
                    'collectionProps' => $collectionProps,
                ));
            }

            /** stop using post content within the paragraph tag. sometimes it's added automatically at the top and bottom of the content.     */
            $html .= $content;

            if (isset($props['children'])) {
                $html .= $this->get_accordion($props['children'], $collectionProps, 'child');
            }

            $html .= '</div>';
            $html .= '</li>';

            return $html;
        }

        public function get_custom_toggle_icon($collectionProps)
        {

            $html = '';

            if ($this->display_mode == 'faq_list') {
                return $html;
            }

            if (!$this->enabled_faq_styles) {
                return $html;
            }

            $toggle_classes = '';
            if (isset($collectionProps['open_by_default']) && $collectionProps['open_by_default'] == 'open_all_faqs') {
                $toggle_classes .= ' open-all';
            }

            if (
                isset($collectionProps['toggle_icon_type']) && $collectionProps['toggle_icon_type'] == 'custom'
                && isset($collectionProps['toggle_open']) && !empty($collectionProps['toggle_open'])
                && isset($collectionProps['toggle_off']) && !empty($collectionProps['toggle_off'])
            ) {
                $html .= '<span class="accordion__toggle ' . esc_attr($toggle_classes) . '">';
                $html .= '<span class="accordion__toggle--open"><i class="accordion__toggle-icons ' . esc_attr($collectionProps['toggle_open']) . '"></i></span>';
                $html .= '<span class="accordion__toggle--close"><i class="accordion__toggle-icons ' . esc_attr($collectionProps['toggle_off']) . '"></i></span>';
                $html .= '</span>';
            }
            return $html;
        }

        public function get_accordion_styles($collectionProps)
        {
            $header_styles = '';
            $body_styles = '';

            if (isset($collectionProps['accordion_background']) && !empty($collectionProps['accordion_background'])) {
                $header_styles = 'background:' . $collectionProps['accordion_background']['header'] . ';';
                $body_styles = 'background:' . $collectionProps['accordion_background']['body'] . ';';
            }

            return [
                'header_styles' => $header_styles,
                'body_styles' => $body_styles,
            ];
        }

        public function get_url_attribute($props, $collectionProps)
        {
            $faq_url_attribute_enabled = isset($collectionProps['faq_url_attribute']) && $collectionProps['faq_url_attribute'] == 1 ? true : false;
            if (!$faq_url_attribute_enabled) {
                return;
            }

            $id = isset($props['post_id']) ? "post-" . $props['post_id'] : "term-" . $props['term_id'];
            $id = esc_attr($id);
            $url_type = isset($collectionProps['faq_url_type']) ? $collectionProps['faq_url_type'] : 'post_id';

            $attribute = '';
            $post_slug = isset($props['slug']) ? $props['slug'] : '';
            if ($url_type == 'post_slug' && hf_fs()->can_use_premium_code__premium_only() && !empty($post_slug)) {
                $attribute = str_replace(" ", "-", strtolower($post_slug));
            } else {
                $attribute = 'hfaq-' . $id;
            }

            return $attribute;
        }

        public function get_the_title_icon($collectionProps, $type = 'title_icon')
        {
            $icon = isset($collectionProps[$type]) ? $collectionProps[$type] : '';
            if (empty($icon) || !$this->enabled_faq_styles) {
                return;
            }

            /** Icon Position */
            // $position = isset($collectionProps['title_icon_position']) && !empty($collectionProps['title_icon_position']) ? $collectionProps['title_icon_position'] : 'left';
            // accordion__title-iconPosition--' . $position . '

            $content = '<span class="accordion__title-icon "><i class="faq-title-icon ' . esc_attr($icon) . '"></i></span>';
            return $content;
        }

        public function get_accordion_title_tag($collectionProps)
        {
            $setting_defaults = new \HelpieFaq\Includes\Settings\Option_Values();
            $allowed_tags = $setting_defaults->get_allowed_title_tags();
            $title_tag = isset($collectionProps['accordion_header_tag']) ? $collectionProps['accordion_header_tag'] : 'default';

            $can_use_custom_accordion_tag = (isset($allowed_tags[$title_tag]) && hf_fs()->can_use_premium_code__premium_only() && $this->enabled_faq_styles);
            return $can_use_custom_accordion_tag && $title_tag != 'default' ? $title_tag : 'div';
        }

        public function get_the_title_content($title)
        {
            ob_start();
            hfaq_safe_echo($title);
            $title_content = ob_get_contents();
            ob_end_clean();
            return $title_content;
        }

    } // END CLASS
}

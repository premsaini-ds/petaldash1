<?php

namespace HelpieFaq\Features\Pagination;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Pagination\View')) {
    class View
    {

        public function get_pagination_content($viewProps)
        {
            $position_class = $this->get_position_class($viewProps);
            $position_class = esc_attr($position_class);

            $total_no_of_pages = 0;
            if (isset($viewProps['collection']['total_no_of_pages']) && is_numeric($viewProps['collection']['total_no_of_pages'])) {
                $total_no_of_pages = $viewProps['collection']['total_no_of_pages'];
            }

            $html = '';
            $html .= "<div class='helpie-faq__pagination $position_class' >";
            $html .= '<ul class="helpie-faq__pagination__list">';

            $html .= $this->get_page_link(array("page" => 0, "label" => __('First', 'helpie-faq')));
            $html .= $this->get_page_link(array("page" => "PREV", "label" => __('Previous', 'helpie-faq')));
            $html .= $this->get_pages_links($total_no_of_pages);
            $html .= $this->get_page_link(array("page" => "NEXT", "label" => __('Next', 'helpie-faq')));
            $html .= $this->get_page_link(array("page" => $total_no_of_pages, "label" => __('Last', 'helpie-faq')));

            $html .= '</ul>';
            $html .= '<div class="helpie-faq__spinner"><div class="helpie-faq__loader"></div></div>';

            $html .= '</div>';

            return $html;
        }

        public function get_position_class($viewProps)
        {

            $position = 'left';
            if (isset($viewProps['collection']['pagination_position']) && !empty($viewProps['collection']['pagination_position'])) {
                $position = $viewProps['collection']['pagination_position'];
            }

            return 'helpie-faq__pagination-positions--' . $position;
        }

        public function get_pages_links($total_no_of_pages)
        {
            $html = '';
            /** Return the first page only when the total no of pages value is 0 */
            if ($total_no_of_pages == 0) {
                $html = $this->get_page_link(array("classes" => 'active', "page" => 0, "label" => 1));
                return $html;
            }

            $buttons = [-2, -1, 0, 1, 2];
            $current_page = 0;

            foreach ($buttons as $button) {
                $page_no = ($current_page + $button);
                $active = ($current_page == $page_no) ? 'active' : '';
                if ($page_no >= 0 && $page_no <= $total_no_of_pages) {
                    $html .= $this->get_page_link(array("classes" => $active, "page" => $page_no, "label" => ($page_no + 1)));
                }
            }

            return $html;
        }

        public function get_page_link($args)
        {
            $classes = isset($args['classes']) && !empty($args['classes']) ? $args['classes'] : '';
            $html = '';
            $html .= '<li class="helpie-faq__pagination__listItem">';
            $html .= '<a class="helpie-faq__pagination__listItem--anchor ' . esc_attr($classes) . ' " data-page="' . esc_attr($args['page']) . '">' . esc_html($args['label']) . '</a>';
            $html .= '</li>';
            return $html;
        }
    }
}

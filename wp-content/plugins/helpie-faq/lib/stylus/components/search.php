<?php

namespace Stylus\Components;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\Stylus\Components\Search')) {

    class Search
    {

        public function __construct()
        {}

        public function get_view($props)
        {

            $search_icon = $this->get_search_icon($props);

            $html = '<form class="search" onSubmit="return false;">';
            $html .= '<div class="search__wrapper">';
            $html .= '<input type="text" class="search__input" placeholder="' . esc_html($props['search_placeholder']) . '">';
            // $html .= '<img class="search__icon" src="' . HELPIE_FAQ_URL . '/assets/img/search-icon.png">';
            // $html .= '<span class="search__icon"><i class="fa fa-search"></i></span>';
            $html .= '<span class="search__icon">' . $search_icon . '</span>';
            $html .= '</div>';
            $html .= '<div class="search__message">';
            $html .= '</div>';
            $html .= '</form>';

            return $html;
        }

        public function get_search_icon($props)
        {
            $icon_color = $this->get_icon_color($props);

            $html = '<svg class="svg-icon--search" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 22">
                        <g fill="none" stroke="' . esc_attr($icon_color) . '">
                            <path stroke-linecap="square" stroke-width="2" d="M18.5 18.3l-5.4-5.4"/>
                            <circle cx="8" cy="8" r="7" stroke-width="2"/>
                        </g>
                    </svg>';
            return $html;
        }

        private function get_icon_color($props)
        {
            $search_icon_color = (isset($props['search_icon_color']) && $props['search_icon_color']) ? $props['search_icon_color'] : '';
            if (!empty($search_icon_color) && $props['enable_faq_styles'] == 1) {
                return $search_icon_color;
            }
            $search_icon_color = '#171717';
            if (isset($props['theme']) && $props['theme'] == 'dark') {
                $search_icon_color = '#fcfcfc';
            }
            return $search_icon_color;
        }
    } // END CLASS
}
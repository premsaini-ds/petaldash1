<?php

namespace HelpieFaq\Includes\Core;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Core\Helpie_Faq_Model')) {
    class Helpie_Faq_Model
    {
        private $option;

        public function __construct()
        {
            $this->option = get_option('helpie-faq');
            // error_log(' [option] ' . print_r($this->option, true));
        }

        public function get_option($option_name)
        {
            // error_log(' [option] ' . print_r($this->option, true) . ' [option_name] ' . $option_name);
            $option = '';
            if (isset($this->option[$option_name]) && !empty($this->option[$option_name])) {
                $option = $this->option[$option_name];
            }
            return $option;

        }

        public function get_enable_single_faq_page()
        {
            $enable_single_faq_page = true;
            if (isset($this->option['enable_single_faq_page'])) {
                $enable_single_faq_page = ($this->option['enable_single_faq_page'] == 1) ? true : false;
            }
            return $enable_single_faq_page;
        }

        public function get_configured_slug($option_name)
        {
            $slug = '';
            if (isset($this->option[$option_name]) && !empty($this->option[$option_name])) {
                $slug = $this->option[$option_name];
            }
            return $slug;
        }

        public function get_global_search_option()
        {
            $faq_option = $this->option;
            $exclude_from_search = false;
            if (isset($faq_option['exclude_from_search'])) {
                $exclude_from_search = ($faq_option['exclude_from_search'] == 1) ? true : false;
            }
            return $exclude_from_search;
        }
    }
}

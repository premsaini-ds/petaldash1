<?php

namespace HelpieFaq\Features\Insights\Insights;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

// TODO: Pre-load post for all methods in the correct loading point ( hook )

if (!class_exists('\HelpieFaq\Features\Insights\Search\Search_Insights')) {
    class Search_Insights extends \HelpieFaq\Features\Insights\Insights\Abstract_Insight

    {
        public $counter_data = array();
        private $option_name = 'helpie_faq_searches';
        public $search_type = 'search_term';

        public function __construct()
        {
            parent::__construct();

            // error_log('Search_Insights $this->counter_data : ' . print_r($this->counter_data , true));
        }

        public function set_counter_data()
        {
            $data = get_option($this->option_name);

            $this->counter_data = array();

            if (!isset($data) || empty($data)) {
                return;
            }

            foreach ($data as $searchTerm => $values) {

                $is_search_query = $this->is_search_query($searchTerm);

                // Skip if search term is a full word
                if ($this->search_type == 'search_term' && $is_search_query) {
                    continue;
                }

                // skip if search term is a single word
                if ($this->search_type == 'search_queries' && !$is_search_query) {
                    continue;
                }

                foreach ($values as $datekey => $value) {

                    // Defaults
                    if (!isset($this->counter_data[$datekey])) {
                        $this->counter_data[$datekey] = array();
                    }

                    if (!isset($this->counter_data[$datekey][$searchTerm])) {
                        $this->counter_data[$datekey][$searchTerm] = 0;
                    }

                    // Increment
                    $this->counter_data[$datekey][$searchTerm] += $value;
                }
            }
        }

        public function most_event_label($searchTerm)
        {
            return $searchTerm;
        }

        public function clear()
        {
            delete_option($this->option_name);
        }

        public function is_search_query($search_term)
        {
            $words = explode(' ', $search_term);
            return count($words) > 1;
        }

        public function set_search_type($type)
        {
            $this->search_type = $type;
        }

    } // END CLASS

}

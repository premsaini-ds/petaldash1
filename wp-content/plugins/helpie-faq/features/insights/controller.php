<?php

namespace HelpieFaq\Features\Insights;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Insights\Controller')) {
    class Controller
    {

        public function get_insights()
        {
            $click_insights_handler = new \HelpieFaq\Features\Insights\Insights\Click_Insights();
            $insights = array(
                'click' => array(
                    '7day-total' => $click_insights_handler->get_total_events(7),
                    '30day-total' => $click_insights_handler->get_total_events(30),
                    'year-total' => $click_insights_handler->get_total_events_last_year(),
                    'all-time-total' => $click_insights_handler->get_total_events_all_time(),
                    'most-7day' => $click_insights_handler->get_most_frequent_terms(7),
                    'most-30day' => $click_insights_handler->get_most_frequent_terms(30),
                    'most-year' => $click_insights_handler->get_most_frequent_terms_last_year(),
                    'most-all-time' => $click_insights_handler->get_most_frequent_terms_all_time(),
                    'last_30days' => $click_insights_handler->get_last_30days(),
                    'last_year' => $click_insights_handler->get_last_year(),
                ),
            );

            $search_insights_handler = new \HelpieFaq\Features\Insights\Insights\Search_Insights();
            $search_insights_handler->set_search_type('search_queries');
            $search_insights_handler->set_counter_data();

            $insights['queries'] = array(
                '7day-total' => $search_insights_handler->get_total_events(7),
                '30day-total' => $search_insights_handler->get_total_events(30),
                'year-total' => $search_insights_handler->get_total_events_last_year(),
                'all-time-total' => $search_insights_handler->get_total_events_all_time(),
                'most-7day' => $search_insights_handler->get_most_frequent_terms(7),
                'most-30day' => $search_insights_handler->get_most_frequent_terms(30),
                'most-year' => $search_insights_handler->get_most_frequent_terms_last_year(),
                'most-all-time' => $search_insights_handler->get_most_frequent_terms_all_time(),
                'last_30days' => $search_insights_handler->get_last_30days(),
                'last_year' => $search_insights_handler->get_last_year(),
            );

            $search_insights_handler = new \HelpieFaq\Features\Insights\Insights\Search_Insights();
            $search_insights_handler->set_search_type('search_term');
            $search_insights_handler->set_counter_data();

            $insights['terms'] = array(
                '7day-total' => $search_insights_handler->get_total_events(7),
                '30day-total' => $search_insights_handler->get_total_events(30),
                'year-total' => $search_insights_handler->get_total_events_last_year(),
                'all-time-total' => $search_insights_handler->get_total_events_all_time(),
                'most-7day' => $search_insights_handler->get_most_frequent_terms(7),
                'most-30day' => $search_insights_handler->get_most_frequent_terms(30),
                'most-year' => $search_insights_handler->get_most_frequent_terms_last_year(),
                'most-all-time' => $search_insights_handler->get_most_frequent_terms_all_time(),
                'last_30days' => $search_insights_handler->get_last_30days(),
                'last_year' => $search_insights_handler->get_last_year(),
            );

            return $insights;
        }

        // public function clear()
        // {
        //     $this->click_insights->clear();
        //     $this->search_insights->clear();
        // }
    } // END CLASS
}

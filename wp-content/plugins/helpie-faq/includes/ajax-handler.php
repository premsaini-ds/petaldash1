<?php

namespace HelpieFaq\Includes;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\HelpieFaq\\Includes\\Ajax_Handler' ) ) {
    class Ajax_Handler {
        public function __construct() {
        }

        public function action() {
            $this->insights_controller = new \HelpieFaq\Features\Insights\Controller();
            $this->insights_controller->clear();
        }

        public function track_shortcodes_and_widgets() {
            $event_name = ( isset( $_REQUEST['event_name'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_name'] ) ) : '' );
            $event_value = ( isset( $_REQUEST['event_value'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_value'] ) ) : '' );
            if ( empty( $event_name ) || empty( $event_value ) ) {
                return;
            }
            helpie_faq_track_event( $event_name, $event_value );
        }

    }

    // END CLASS
}
$ajax_hanlder = new \HelpieFaq\Includes\Ajax_Handler();
$click_tracker = new \HelpieFaq\Features\Insights\Trackers\Click_Tracker();
$search_tracker = new \HelpieFaq\Features\Insights\Trackers\Search_Tracker();
add_action( 'wp_ajax_helpie_faq_click_counter', array($click_tracker, 'action') );
add_action( 'wp_ajax_nopriv_helpie_faq_click_counter', array($click_tracker, 'action') );
add_action( 'wp_ajax_helpie_faq_search_counter', array($search_tracker, 'action') );
add_action( 'wp_ajax_nopriv_helpie_faq_search_counter', array($search_tracker, 'action') );
add_action( 'wp_ajax_helpie_faq_reset_insights', array($ajax_hanlder, 'action') );
add_action( 'wp_ajax_nopriv_helpie_faq_reset_insights', array($ajax_hanlder, 'action') );
add_action( 'wp_ajax_helpie_faq_track_shortcodes_and_widgets', array($ajax_hanlder, 'track_shortcodes_and_widgets') );
add_action( 'wp_ajax_nopriv_helpie_faq_track_shortcodes_and_widgets', array($ajax_hanlder, 'track_shortcodes_and_widgets') );
add_action( 'wp_ajax_update_feature_notice_dismissal_data_via_ajax', array(new \HelpieFaq\Features\Feature_Notice(), 'update_feature_notice_dismissal_data_via_ajax') );
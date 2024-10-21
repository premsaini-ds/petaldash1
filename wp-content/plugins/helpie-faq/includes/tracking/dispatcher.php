<?php

namespace HelpieFaq\Includes\Tracking;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Tracking\Dispatcher')) {
    class Dispatcher
    {

        public $project_token = '3a6501ab94b489c473bb7ca8281db677';

        public $api_secret = '23a6aaaf53995141fd606dbcdd5428e5';

        public $project_id = 2928780;
        public $event_properties_handler;

        public function __construct()
        {
            $this->event_properties_handler = new \HelpieFaq\Includes\Tracking\Event_Properties();
        }
        public function send_single_event($event_name = '', $value = null)
        {
            $properties = $this->get_properties($value);

            $event = array(
                'event' => $event_name,
                'properties' => $properties,
            );

            $url = "https://api.mixpanel.com/track";

            $params = array(
                'ip' => 1,
                'verbose' => 1,
                'test' => 0,
                'api_key' => $this->api_secret,
                'track_id' => uniqid(),
                'data' => base64_encode(wp_json_encode($event)),
            );

            $url .= '?' . http_build_query($params);

            $response = wp_remote_post(esc_url_raw($url), array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
            ));
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            if ($response_code != 200) {
                error_log('Mixpanel API Error');
                return false;
            }
            return true;
        }

        private function get_properties($value)
        {
            $additional_properties = $this->event_properties_handler->get_properties();

            $event_properties = array(
                'token' => $this->project_token,
                'distinct_id' => isset($additional_properties['site_id']) ? $additional_properties['site_id'] : '',
                'time' => time(),
                'value' => $value,
            );

            return array_merge($event_properties, $additional_properties);
        }
    }
}

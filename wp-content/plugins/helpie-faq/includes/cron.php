<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Cron')) {
    class Cron
    {
        public $cron_action_hook_name = 'helpie_faq/track_events';

        public $current_interval = 'helpie_faq_track_events_interval';

        public function init()
        {
            if (!helpie_faq_can_track_user()) {
                return;
            }
            $this->start();
        }

        public function set_intervals($schedules)
        {
            $schedules['helpie_faq_track_events_interval'] = array(
                'interval' => 7 * 24 * 60 * 60,
                'display' => __('Every Week', 'tablesome'),
            );
            $schedules['helpie_faq_track_events_test_interval'] = array(
                'interval' => 5 * 60,
                'display' => __('Every 5 min', 'tablesome'),
            );
            return $schedules;
        }

        public function clear_schedule()
        {
            wp_clear_scheduled_hook($this->cron_action_hook_name);
        }

        public function start($args = array())
        {
            $timestamp = wp_next_scheduled($this->cron_action_hook_name);
            if ($timestamp == false) {
                /*** Schedule the event  */
                wp_schedule_event(time(), $this->current_interval, $this->cron_action_hook_name);
            }
        }

        public function action($type, $args = array())
        {
            if ($type == 'clear') {
                $this->clear_schedule();
            } else if ($type == 'start') {
                if (!helpie_faq_can_track_user()) {
                    $this->action('clear');
                    return;
                }
                $this->init();
            }
        }

        public function run()
        {
            if (!helpie_faq_can_track_user()) {
                return;
            }
            $event_handler = new \HelpieFaq\Includes\Tracking\Events();
            $events = $event_handler->get_events();
            helpie_faq_track_event("FAQ Counts Insights", $events);
        }
    }
}

<?php

namespace HelpieFaq\Includes;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Deactivation')) {
    class Deactivation
    {
        public function init()
        {
            helpie_faq_track_event('Deactivation', array());
        }
    }
}

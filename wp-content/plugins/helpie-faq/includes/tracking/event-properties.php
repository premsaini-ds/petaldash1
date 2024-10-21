<?php

namespace HelpieFaq\Includes\Tracking;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Tracking\Event_Properties')) {
    class Event_Properties
    {

        public function get_properties()
        {
            $defaults = [
                'site_url' => get_site_url(),
                'language' => get_locale(),
                'wp_version' => get_bloginfo('version'),
                'php_version' => phpversion(),
                'plugin_version' => HELPIE_FAQ_VERSION,
                'is_multisite' => is_multisite(),
            ];

            $fs_properties = $this->get_fs_properties();

            return array_merge($defaults, $fs_properties);
        }

        public function get_fs_properties()
        {
            global $hf_fs;
            $site_info = $hf_fs->get_site();
            $user_info = $hf_fs->get_user();

            return [
                'user_id' => isset($user_info->id) ? $user_info->id : 0,
                'site_id' => isset($site_info->id) ? $site_info->id : 0,
                'plan' => $hf_fs->get_plan_name(),
                'is_trial' => $hf_fs->is_trial(),
                'is_free_plan' => $hf_fs->is_free_plan(),
                'user_email' => isset($user_info->email) ? $user_info->email : '',
            ];
        }
    }
}

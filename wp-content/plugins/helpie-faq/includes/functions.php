<?php

if (!function_exists('hfaq_clean_content')) {
    function hfaq_clean_content($content_type, $content)
    {
        $output = "";
        if ($content_type == "url") {
            $output = esc_url($content);
        } else if ($content_type == 'html') {
            $output = esc_html__($content);
        } else if ($content_type == "textarea") {
            $output = esc_textarea($content);
        } else if ($content_type == "raw_url") {
            $output = esc_url_raw($content);
        } else if ($content_type == "attr") {
            $output = esc_attr($content);
        }
        return $output;
    }
}

if (!function_exists('hfaq_get_sanitized_value')) {
    function hfaq_get_sanitized_value($format, $value)
    {
        if ($format == "String") {
            return sanitize_text_field($value);
        } else if ($format == "Email") {
            return sanitize_email($value);
        } else if ($format == "Textarea") {
            return wp_kses_post($value);
        } else if ($format == "Number") {
            $value = intval($value) > 0 ? intval($value) : 0;
            return $value;
        } else if ($format == "Array") {
            return wp_kses_post_deep($value);
        }
    }
}

if (!function_exists('hfaq_get_sanitized_data')) {
    function hfaq_get_sanitized_data($method, $validation_map)
    {
        $sanitized_data = array();
        $request = hfaq_get_request_data_by_method($method);

        if (empty($request) || empty($validation_map)) {return $sanitized_data;}

        if (!is_array($validation_map) && $validation_map == "READ_ALL_AS_TEXT") {
            $sanitized_data = hfaq_get_sanitized_value("Array", $request);
            return $sanitized_data;
        }

        foreach ($validation_map as $var => $format_type) {
            $value = isset($request[$var]) ? $request[$var] : '';
            if (!empty($value)) {
                $value = hfaq_get_sanitized_value($format_type, $value);
            }
            $sanitized_data[$var] = $value;
        }

        return $sanitized_data;
    }
}

if (!function_exists('hfaq_get_request_data_by_method')) {
    function hfaq_get_request_data_by_method($method)
    {
        $request = array();
        if ($method == "POST") {
            $request = $_POST;
        } else if ($method == "GET") {
            $request = $_GET;
        } else if ($method == "REQUEST") {
            $request = $_REQUEST;
        }
        return $request;
    }
}

if (!function_exists('hfaq_allowed_html_tags')) {
    function hfaq_allowed_html_tags()
    {
        global $allowedposttags;

        $allowed_form_attrs = array(
            'type' => true,
            'name' => true,
            'value' => true,
            'placeholder' => true,
            'id' => true,
            'class' => true,
            'required' => true,
            'size' => true,
            'action' => true,
            'method' => true,
            'novalidate' => true,
            'tabindex' => true,
            'for' => true,
            'width' => true,
            'height' => true,
            'title' => true,
            'cols' => true,
            'rows' => true,
            'disabled' => true,
            'readonly' => true,
            'style' => true,
            'role' => true,
            'data-*' => true,
            'aria-live' => true,
            'aria-describedby' => true,
            'aria-details' => true,
            'aria-label' => true,
            'aria-labelledby' => true,
            'aria-hidden' => true,
            'aria-required' => true,
            'aria-invalid' => true,
            'checked' => true,
        );

        $allowedposttags['form'] = $allowed_form_attrs;
        $allowedposttags['input'] = $allowed_form_attrs;
        $allowedposttags['select'] = $allowed_form_attrs;
        $allowedposttags['option'] = $allowed_form_attrs;
        $allowedposttags['textarea'] = $allowed_form_attrs;
        $allowedposttags['script'] = $allowed_form_attrs;
        // $allowedposttags['a'] = $allowed_form_attrs;
    }
}

if (!function_exists('hfaq_safe_echo')) {
    function hfaq_safe_echo($content)
    {
        hfaq_allowed_html_tags();
        $allowed_form_tags = wp_kses_allowed_html('post');
        echo wp_kses($content, $allowed_form_tags);
    }
}

if (!function_exists('hfaq_safe_kses')) {
    function hfaq_safe_kses($content)
    {
        hfaq_allowed_html_tags();
        $allowed_form_tags = wp_kses_allowed_html('post');
        return wp_kses($content, $allowed_form_tags);
    }
}

if (!function_exists('helpie_faq_env_defined')) {
    function helpie_faq_env_defined()
    {
        return defined('HELPIE_FAQ_ENV') ? constant('HELPIE_FAQ_ENV') : null;
    }
}

if (!function_exists('helpie_error_log')) {
    function helpie_error_log($content, $data = array())
    {

        if (!helpie_faq_env_defined() || !in_array(HELPIE_FAQ_ENV, ['development', 'testing'])) {
            return;
        }

        if (func_num_args() == 2) {
            error_log($content . print_r($data, true));
        } else {
            error_log(print_r($content, true));
        }

    }
}

if (!function_exists('helpie_faq_track_event')) {
    function helpie_faq_track_event($name, $value = null)
    {
        $dispathcher = new \HelpieFaq\Includes\Tracking\Dispatcher();
        $can_track = helpie_faq_can_track_user();
        if (!$can_track) {
            return;
        }
        $dispathcher->send_single_event($name, $value);
    }
}

if (!function_exists('helpie_faq_can_track_user')) {
    function helpie_faq_can_track_user()
    {
        global $hf_fs;
        if (empty($hf_fs)) {
            return false;
        }
        return ($hf_fs->is_registered() && $hf_fs->is_tracking_allowed());
    }
}

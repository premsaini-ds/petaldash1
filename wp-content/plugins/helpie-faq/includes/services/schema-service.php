<?php

namespace HelpieFaq\Includes\Services;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Services\Schema_Service')) {
    class Schema_Service
    {

        public function __construct()
        {

        }

        public function get_faq_schema($viewProps)
        {
            $items = isset($viewProps['items']) ? $viewProps['items'] : [];

            // error_log('[$viewProps] : ' . print_r($viewProps, true));
            // error_log('[$items] : ' . print_r($items, true));
            $schema = '';
            if (!empty($items)) {

                $faq_entity_data = $this->get_faq_schema_entity_data($viewProps);

                if (!empty($faq_entity_data)) {
                    $schema = $this->get_faq_schema_snippet($faq_entity_data);
                }

            }
            return $schema;
        }

        public function get_faq_schema_snippet($faq_entity_data)
        {
            $settings = new \HelpieFaq\Includes\Settings\Getters\Getter();
            $options = $settings->get_settings();
            $is_schema_enabled = isset($options['enable_schema']) ? $options['enable_schema'] : 0;

            $schema = '';

            // error_log('is_schema_enabled : ' . $is_schema_enabled);

            if ($is_schema_enabled == 0) {
                return '';
            }

            if (empty($faq_entity_data)) {
                return '';
            }

            //  error_log('[$faq_entity_data] : ' . print_r($faq_entity_data, true));

            $schema = '<script type="application/ld+json" class="helpie-faq-schema">{
                    "@context": "https://schema.org",
                    "@type": "FAQPage",
                    "mainEntity": ' . wp_json_encode($faq_entity_data) . '
                }</script>';

            return $schema;
        }

        public function get_faq_schema_entity_data($viewProps)
        {

            $items = $this->get_faqs_only($viewProps);
            $faqs = [];
            // $permalink = get_permalink() . '#hfaq-';
            $permalink = get_permalink();

            $faq_url_attribute_enabled = false;

            if (isset($viewProps['collection']['faq_url_attribute'])
                && $viewProps['collection']['faq_url_attribute'] == 1) {
                $faq_url_attribute_enabled = true;
            }

            for ($ii = 0; $ii < count($items); $ii++) {

                $faq_item_content = wp_strip_all_tags($items[$ii]['content']);
                $faq_item_content = preg_replace('#\[[^\]]+\]#', '', $faq_item_content);

                if (!empty($faq_item_content)) {

                    $id = isset($items[$ii]['post_id']) ? "post-" . $items[$ii]['post_id'] : "term-" . $items[$ii]['term_id'];

                    if ($faq_url_attribute_enabled == true) {
                        $permalink = get_permalink() . '#hfaq-' . $id;
                    }

                    $faqs[$id] = array(
                        '@type' => 'Question',
                        'url' => $permalink,
                        'name' => wp_strip_all_tags($items[$ii]['title']),
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text' => $faq_item_content,
                        ),
                    );
                }
            }

            return $faqs;
        }

        public function get_faqs_only($viewProps)
        {

            $faq_items = isset($viewProps['items']) ? $viewProps['items'] : [];

            if (isset($viewProps['collection']['display_mode_group_by']) && $viewProps['collection']['display_mode_group_by'] == 'none') {
                return $faq_items;
            }

            $items = array();

            foreach ($faq_items as $item) {
                if (isset($item['children']) && count($item['children']) > 0) {
                    for ($ii = 0; $ii < count($item['children']); $ii++) {
                        $items[] = $item['children'][$ii];
                    }
                }
            }

            return $items;
        }

    }
}

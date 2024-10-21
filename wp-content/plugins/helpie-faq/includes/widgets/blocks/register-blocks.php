<?php

namespace HelpieFaq\Includes\Widgets\Blocks;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Widgets\Blocks\Register_Blocks')) {
    class Register_Blocks
    {
        public $fields;
        public $style_config;

        public function __construct($fields, $style_config)
        {
            // error_log('load fields: ' . print_r($fields, true) );
            $this->fields = $fields;
            $this->fields['style'] = array();
            $this->style_config = $style_config;
            $this->get_element_config($style_config);
        }

        public function get_element_config($style_config)
        {

            foreach ($style_config as $key => $element) {
                # code...
                $name = $element['name'];

                // $styleProps = $element['styleProps'];

                if (isset($element['styleProps'])) {
                    $this->fields['style'][$name] = $element;
                    $this->fields['style']['type'] = 'object';
                }

                if (isset($element['children'])) {
                    $this->get_element_config($element['children']);
                }
            }

        }
        public function load()
        {
            $fields = $this->convert_fields($this->fields);
            /* Editor only assets */
            add_action('enqueue_block_editor_assets', array($this, 'helpie_faq_block'));

            /* For both frontend and editor */
            add_action('enqueue_block_assets', array($this, 'helpie_faq_block_assets'));

            // error_log('register_block_type $his->fields: ' . print_r($this->fields, true));
            register_block_type('helpie-faq/helpie-faq', array(
                'attributes' => $fields,
                'render_callback' => array($this, 'render'),
            ));
        }

        public function convert_fields($fields)
        {

            // Convert field type from text to string
            foreach ($fields as $key => $field) {
                if ($field['type'] == 'text') {
                    $fields[$key]['type'] = 'string';
                }
            }

            return $fields;
        }

        public function helpie_faq_block_assets()
        {
            // $Actions = new \HelpieFaq\Includes\Actions();
            // $Actions->handle_frontend_assets('helpie_faq_block');
            // error_log('helpie_faq_block_assets');
            wp_enqueue_style('helpie-faq-bundle-styles', HELPIE_FAQ_URL . 'assets/bundles/main.app.css', array(), HELPIE_FAQ_VERSION, 'all');
        }

        public function helpie_faq_block()
        {
            wp_enqueue_script(
                'helpie-faq/helpie-faq', // Unique handle.
                HELPIE_FAQ_URL . 'assets/bundles/block.app.js', // block js
                array('wp-blocks', 'wp-components', 'wp-i18n', 'wp-element', 'wp-editor'), // Dependencies, defined above.
                filemtime(HELPIE_FAQ_PATH . 'assets/bundles/block.app.js') // filemtime — Gets file modification time.
            );
            wp_localize_script('helpie-faq/helpie-faq', 'BlockFields', $this->fields);
        }

        public function render($attributes)
        {
            if (!isset($attributes['id'])) {
                $attributes['id'] = uniqid('helpie-faq-');
            }
            $attributes = $this->block_editor_faq_widget_attributes_compatibility($attributes);

            $faq = new \HelpieFaq\Features\Faq\Faq();
            $faq_model = new \HelpieFaq\Features\Faq\Faq_Model();
            $style = '';
            if (isset($attributes['style'])) {
                $style = $this->get_block_styles($attributes);
            }
            // Get and Set the default args
            $defaults = $faq_model->get_default_args();
            // error_log('defaults: ' . print_r($defaults, true));
            $args = array_merge($defaults, $attributes);
            // $args['enable_faq_styles'] = true;
            // error_log('args: ' . print_r($args, true));

            $view_html = $faq->get_view($args);

            return $style . $view_html;

        }

        public function block_editor_faq_widget_attributes_compatibility($attributes)
        {
            $compatibility = new \HelpieFaq\Includes\Migrations\Shortcode_Compatibility();
            return $compatibility->get_attributes($attributes);
        }

        public function get_block_styles($attributes)
        {
            $id = $attributes['id'];
            $block_style = $attributes['style'];
            $style = '<style>';
            $style .= $this->get_style($block_style, $this->style_config, $id);
            $style .= '</style>';
            return $style;
        }

        public function get_style($block_style, $style_config, $id)
        {
            $style = '';

            foreach ($style_config as $key => $config) {
                $element_name = $config['name'];

                if (isset($block_style[$element_name])) {
                    $style .= "#" . $id . $config['selector'] . " { ";
                    /* Loop through each style attribute */
                    foreach ($block_style[$element_name] as $key1 => $value) {
                        $style .= $key1 . " : " . $value . ";";
                    }
                    $style .= " } ";

                }

                if (isset($config['children'])) {
                    $style .= $this->get_style($block_style, $config['children'], $id);
                }
            }
            return $style;
        }

    } // END CLASS
}

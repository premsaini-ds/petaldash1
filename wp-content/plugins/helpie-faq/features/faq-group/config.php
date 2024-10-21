<?php

namespace HelpieFaq\Features\Faq_Group;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Faq_Group\Config')) {
    class Config
    {
        public $fields = array(
            "header-background" => array(
                "id" => "header-background",
                "label" => "Background",
                "default" => "",
                "type" => "color",
            ),
            "header-font-color" => array(
                "label" => "Font Color",
                "id" => "header-font-color",
                "default" => "",
                "type" => "color",
            ),
            "body-background" => array(
                "label" => "Background",
                "default" => "",
                "type" => "color",
            ),
            "body-font-color" => array(
                "label" => "Font Color",
                "default" => "",
                "type" => "color",
            ),
            "post_type_1" => array(
                "label" => "Post Type",
                "default" => "products",
                "type" => "select",
                "options" => [],
                "optionsGetter" => array("source" => "wp", "method" => "get_post_types"),
            ),
            "tax_or_post_1" => array(
                "id" => "tax_or_post_1",
                "label" => "Taxonomy or Post",
                "default" => "taxonomy",
                "type" => "select",
                "value" => 'post',
                "options" => [],
                "optionsGetter" => array(
                    array("label" => "Post", "value" => "post"),
                    array("label" => "Taxonomy", "value" => "taxonomy"),
                ),
            ),
            "taxonomy_1" => array(
                "label" => "Taxonomy",
                "default" => "",
                "type" => "select",
                "display" => "hidden",
                "options" => [],
                "optionsGetter" => array(
                    "source" => "wp",
                    "method" => "get_taxonomy",
                    "post_type" => array("field", "post_type_1"),
                ),
                "dependency" => [
                    [
                        "id" => "tax_or_post_1",
                        "operator" => "==",
                        "value" => "taxonomy",
                    ],
                ],

            ),
            "post_1" => array(
                "label" => "Post",
                "default" => "",
                "type" => "select",
                "options" => [],
                "optionsGetter" => array(
                    "source" => "wp",
                    "method" => "get_posts",
                    "post_type" => array("field", "post_type_1"),
                ),
                "dependency" => [
                    [
                        "id" => "tax_or_post_1",
                        "operator" => "==",
                        "value" => "post",
                    ],
                ],
            ),
            // 'post_group_repeater' => [
            //     "label" => "Add Location",
            //     "type" => 'button',
            //     "action" => "add_post_group"
            // ],
            // 'post_group_repeater_count' => [
            //     "type" => 'hidden_number_field',
            // ]
        );


        public $layout = array(
            array(
                "type" => "direct",
                "fields" => ["header-background", "header-font-color"]
            ),
            array(
                "type" => "direct",
                "fields" => ["body-background", "body-font-color"]
            ),
            array(
                "type" => "direct",
                "fields" => ["post_type_1", "tax_or_post_1", "taxonomy_1", "post_1"]
            )
        );

        public $notice = array(
            "woocommerce_not_ative" => array(
                "condition" => 'isWoocommerceActive', // name of method
                "message" =>
                "The above styles would not be applied on the products page. The products page style would come from Global Settings.",
                "location" => ["after", "helpiefaq__heading"],
            ),
            "premium_notice_overall_settings" => array(
                "condition" => 'isWoocommerceActive', // name of method
                "message" =>
                "The above styles would not be applied on the products page. The products page style would come from Global Settings.",
                "location" => ["after", "helpiefaq__title"],
            ),
            "woocommerce_styles_from_global" => array(
                "condition" => ['isWoocommerceActive', 'isPremium'], // names of method
                "message" =>
                "The above styles would not be applied on the products page. The products page style would come from Global Settings.",
                "location" => ["after", "helpiefaq__heading"],
            ),
            "plugin_not_active" => array(
                "condition" => [], // names of method
                "message" =>
                "The Plugin is not active now.",
                "location" => ["after", "helpiefaq__heading"],
            )
        );

        public function set_fields_for_layout_builder()
        {
            $new_fields = [];
            $ii = 0;
            foreach ($this->fields as $key => $value) {
                $new_fields[$ii] = $value;
                $new_fields[$ii]['id'] = $key;
                $ii++;
            }

            $this->fields_for_layout_builder = $new_fields;
        }

        public function set_layout_for_layout_builder()
        {
            $new_layout = [];
            $ii = 0;
            foreach ($this->layout as $key => $value) {
                $new_layout[$ii] = $value;
                $new_layout[$ii]['id'] = $key;
                $ii++;
            }

            $this->layout_for_layout_builder = $new_layout;
        }

        public function get_config()
        {
            $this->set_fields_for_layout_builder();
            $this->set_layout_for_layout_builder();
            $config = array(
                'layout' => $this->layout,
                'fields' => $this->fields,
                'lb_layout' => $this->layout_for_layout_builder,
                'lb_fields' => $this->fields_for_layout_builder,
                'notice' => $this->notice
            );

            return $config;
        }
    }
}

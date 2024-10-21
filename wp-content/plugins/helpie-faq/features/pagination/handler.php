<?php
namespace HelpieFaq\Features\Pagination;

if (!class_exists('\HelpieFaq\Features\Pagination\Handler')) {
    class Handler
    {
        public $request_data = array();

        public function __construct()
        {
            $validation_map = array(
                'page' => 'Number',
            );
            $sanitized_data = hfaq_get_sanitized_data("REQUEST", $validation_map);
            $page = isset($sanitized_data['page']) ? $sanitized_data['page'] : 0;
            $this->request_data['page'] = $page;
        }

        public function get_shortcode_faqs()
        {
            $sanitized_data = hfaq_get_sanitized_data("REQUEST", "READ_ALL_AS_TEXT");
            $faq_model = new \HelpieFaq\Features\Faq\Faq_Model();
            $defaults = $faq_model->get_default_args();
            $args = array_merge($defaults, $sanitized_data);
            $limit = $sanitized_data['limit'];
            /** add an offset value to get the faqs to the currentpage */
            // $args['offset'] = $page * $args['limit'];
            $args['limit'] = -1;

            if (isset($args['group_id']) && !empty($args['group_id']) && intval($args['group_id'])) {
                $faq_group_controller = new \HelpieFaq\Features\Faq_Group\Controller();
                $faq_group_model = new \HelpieFaq\Features\Faq_Group\Model();

                $faq_groups_args = $faq_group_controller->get_default_args($args);
                $args = array_merge($args, $faq_groups_args);
                $view_props = $faq_group_model->get_viewProps($args);
            } else {
                $view_props = $faq_model->get_viewProps($args);
            }

            $view_props = $this->apply_the_content_filters($view_props);
            $view_props['collection']['limit'] = $limit;
            // error_log('[$view_props] : ' . print_r($view_props, true));

            $view_props['collection'] = $this->get_required_collection_props($view_props['collection']);
            wp_send_json(
                [
                    'status' => 'success',
                    // 'content' => $content,
                    'view_props' => $view_props,
                ]
            );
        }

        public function apply_the_content_filters($view_props)
        {
            $itemsProps = isset($view_props['items']) && !empty($view_props['items']) ? $view_props['items'] : [];
            $collectionProps = isset($view_props['collection']) && !empty($view_props['collection']) ? $view_props['collection'] : [];
            foreach ($itemsProps as $index => $props) {
                $props['content'] = apply_filters('helpie_faq/the_content', array(
                    'props' => $props,
                    'collectionProps' => $collectionProps,
                ));
            }

            $view_props['items'] = $itemsProps;
            return $view_props;
        }

        public function get_required_collection_props($collectionProps)
        {
            $required_collection_props = array('display_mode', 'enable_faq_styles',
                'open_by_default', 'faq_url_attribute', 'faq_url_type', 'title_icon', 'category_title_icon', 'icon_position',
                'toggle_icon_type', 'toggle_open', 'toggle_off', 'accordion_background', 'accordion_header_tag', 'limit',
            );

            foreach ($collectionProps as $key => $value) {
                if (!in_array($key, $required_collection_props)) {
                    unset($collectionProps[$key]);
                }
            }
            return $collectionProps;
        }
    }
}

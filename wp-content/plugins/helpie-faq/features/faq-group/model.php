<?php

namespace HelpieFaq\Features\Faq_Group;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Faq_Group\Model')) {
    class Model
    {
        public $display_mode = '';
        public $handlers;

        public function __construct()
        {
            $this->handlers = new \HelpieFaq\Features\Faq\Handlers();
        }

        public function get_group_settings($term_id)
        {
            $term_meta = get_term_meta($term_id, 'faq_group_settings', true);
            $settings = isset($term_meta['fields']) ? $term_meta['fields'] : [];

            return $settings;
        }

        public function overwrite_args_with_group_level($args)
        {
            $group_settings = $this->get_group_settings($args['group_id']);
            foreach ($group_settings as $key => $single_setting) {
                if (isset($single_setting['value'])) {
                    $args[$key] = $single_setting['value'];
                }

            }

            if (isset($args['sortby']) && $args['sortby'] == 'menu_order') {
                $args['sortby'] = 'post__in';
                $args['order'] = 'asc';
            }
            return $args;
        }

        public function get_viewProps($args)
        {
            // error_log('get_viewProps');
            // error_log("args: " . print_r($args, true));

            $args = $this->overwrite_args_with_group_level($args);

            //  error_log("after merge args: " . print_r($args, true));
            $display_mode_group_by = isset($args['display_mode_group_by']) ? $args['display_mode_group_by'] : 'none';

            if ($display_mode_group_by == 'category') {
                $this->display_mode = 'categories';
            }

            if ($this->display_mode == 'categories') {
                $faq_group = new \HelpieFaq\Includes\Repos\Faq_Group();
                $category_posts_items = $faq_group->get_categories_with_posts($args);
                $items_props = $this->get_the_items_props_by_category_mode($category_posts_items, $args);
            } else {
                $faq_repo = new \HelpieFaq\Includes\Repos\Faq_Repo();
                $posts = $faq_repo->get_faqs($args);
                $args['total_no_of_pages'] = $this->handlers->get_total_no_of_pages($args);
                $items_props = $this->get_the_items_props_by_accordion_mode($posts, $args);
            }
            $viewProps = array(
                'collection' => $this->get_collection_props($args),
                'items' => $items_props,
            );

            return $viewProps;
        }

        protected function get_collection_props($args)
        {
            $collectionProps = array(
                'context' => $this->handlers->get_context(),
            );

            $collectionProps = array_merge($collectionProps, $args);

            return $collectionProps;
        }

        /***
         * @since v1.6.5
         * use of this method to getting the FAQ group items props values, if the display mode type is simple accordion (or) faqlist
         */
        public function get_the_items_props_by_accordion_mode($posts, $args)
        {
            $items_props = array();
            foreach ($posts as $index => $post) {
                $items_props[$index] = $this->handlers->convert_single_post_obj_to_itemProps($post, $args);
            }
            return $items_props;
        }

        /***
         * @since v1.6.5
         * use of this method to getting the FAQ group items props values, if the display mode type is category
         */
        public function get_the_items_props_by_category_mode($category_posts_obj, $args)
        {
            $items_props = array();
            if (empty($category_posts_obj)) {
                return $items_props;
            }

            foreach ($category_posts_obj as $index => $item_obj) {

                $items_props[$index] = $this->handlers->map_category_props_to_view_item_props($item_obj['term']);

                /** Get all the posts object */
                $posts = $item_obj['posts'];

                foreach ($posts as $post) {
                    $items_props[$index]['children'][] = $this->handlers->convert_single_post_obj_to_itemProps($post, $args);
                }
            }

            return $items_props;
        }
    }
}

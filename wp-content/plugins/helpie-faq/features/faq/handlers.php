<?php

namespace HelpieFaq\Features\Faq;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Faq\Handlers')) {
    class Handlers
    {

        /**
         * use of the method for removing the category term if the category term doesn't have posts
         *
         * @param [array] $props
         * @return $props
         */
        public function get_non_empty_items_props($props)
        {
            foreach ($props as $key => $prop) {
                if (empty($prop['children'])) {
                    unset($props[$key]);
                }
            }
            $props = array_values($props); // 'reindex' array (edited)

            return $props;
        }

        public function get_context()
        {
            $context = array();
            // Woo Product
            if (is_singular('product') && is_single(get_the_ID())) {
                $context['woo-product'] = get_queried_object()->ID;
            } elseif (is_tax('helpdesk_category')) {
                $context['kb-category'] = get_queried_object()->term_id;
            }
            // Wiki KB Category

            return $context;
        }

        /***
         * @since v1.6.5
         * method name changed, faq/faq_model/map_domain_props_category() to faq/handlers/map_category_props_to_view_item_props()
         */
        public function map_category_props_to_view_item_props($category)
        {
            $order = get_term_meta($category->term_id, 'order', true);
            $order = isset($order) && !empty($order) ? $order : 0;

            $props = array(
                'title' => $category->name,
                'slug' => $category->slug,
                'content' => $category->description,
                'term_id' => $category->term_id,
                'count' => get_term_meta($category->term_id, 'click_counter', false),
                'order' => $order,
            );
            return $props;
        }

        /***
         * @since v1.6.5
         * method name changed, faq/faq_model/map_domain_props_to_view_item_props() to faq/handlers/convert_single_post_obj_to_itemProps()
         */
        public function convert_single_post_obj_to_itemProps($post, $args)
        {
            $props = array(
                'slug' => $post->post_name,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'post_id' => $post->ID,
                'count' => get_post_meta($post->ID, 'click_counter', false),
                'excerpt' => $post->post_excerpt,
                'post_date' => $post->post_date,
                'post_modified' => $post->post_modified,
            );
            $search_tags_enabled = (isset($args['search_by_tags']) && $args['search_by_tags'] == 1) ? true : false;
            if (hf_fs()->can_use_premium_code__premium_only() && $search_tags_enabled) {
                $tags = new \HelpieFaq\Features\Faq\Tags();
                $props = $tags->get_tags($props);
            }

            return $props;
        }

        public function get_total_no_of_pages($args)
        {
            /***
             *  dont need to calculate the total no of pages count when the limit value is -1
             *
             */
            if (!isset($args['limit']) || $args['limit'] < 1) {
                return 0;
            }
            /** Get the limit from the settings (what we configured in settings) */
            $page_limit = $args['limit'];
            /** overrite the limit option value as -1 for get all the FAQ posts  */
            $args['limit'] = -1;
            $faq_repo = new \HelpieFaq\Includes\Repos\Faq_Repo();
            $posts = $faq_repo->get_faqs($args);
            $posts_count = count($posts);
            /** Calculating the total no of pages count */
            $total_no_pages = ceil($posts_count / $page_limit);
            return ($total_no_pages > 1) ? (($total_no_pages) - 1) : 0;
        }

        public function boolean_conversion($args)
        {
            foreach ($args as $key => $arg) {
                if ($arg == 'on') {
                    $args[$key] = true;
                } else if ($arg == 'off') {
                    $args[$key] = false;
                }
            }
            return $args;
        }

        public function apply_sorting($items_props, $args)
        {
            $sortby = isset($args['category_sortby']) ? $args['category_sortby'] : '';
            if (empty($sortby)) {
                return $items_props;
            }
            if (in_array($sortby, ['publish', 'updated'])) {
                $items_props = $this->apply_date_sorting($items_props, $args);
            } elseif ($sortby == 'menu_order') {
                $items_props = $this->apply_order_sorting($items_props, $args);
            }

            return $items_props;
        }

        public function apply_order_sorting($items_props, $args)
        {
            $orderby = isset($args['category_order']) ? $args['category_order'] : 'asc';
            $orderby = strtolower($orderby);
            $orderby = ($orderby == 'asc') ? SORT_ASC : SORT_DESC;
            $order_array = array();
            foreach ($items_props as $key => $item) {
                $order = isset($item['order']) ? $item['order'] : 0;
                $order_array[$key] = $order;
            }
            array_multisort($order_array, $orderby, $items_props);
            return $items_props;
        }

        public function apply_date_sorting($items_props, $args)
        {
            $order = isset($args['category_order']) ? $args['category_order'] : 'asc';

            $categories = array();
            $overall_articles = array();

            foreach ($items_props as $key => $item) {
                $articles = $item['children'];
                $parent = $item;
                $parent_id = $parent['term_id'];
                if (isset($parent['children'])) {
                    unset($parent['children']);
                }

                $categories[$parent_id] = $parent;

                foreach ($articles as $article) {
                    $article['parent_id'] = $parent_id;
                    $overall_articles[] = $article;
                }
            }

            uasort($overall_articles, function ($a, $b) use ($args) {
                $a_date = ($args['sortby'] == 'publish') ? $a['post_date'] : $a['post_modified'];
                $b_date = ($args['sortby'] == 'publish') ? $b['post_date'] : $b['post_modified'];
                return strtotime($a_date) - strtotime($b_date);
            });
            if ($order == 'desc') {
                $overall_articles = array_reverse($overall_articles);
            }

            $new_items_props = array();

            foreach ($overall_articles as $article) {
                $parent_id = $article['parent_id'];
                $parent = $categories[$parent_id];

                if (!isset($new_items_props[$parent_id])) {
                    $new_items_props[$parent_id] = $parent;
                }

                if (!isset($new_items_props[$parent_id]['children'])) {
                    $new_items_props[$parent_id]['children'] = array();
                }

                $children = $new_items_props[$parent_id]['children'];
                $children[] = $article;
                $new_items_props[$parent_id]['children'] = $children;
            }

            $new_items_props = array_values($new_items_props);
            return $new_items_props;
        }

    }
}

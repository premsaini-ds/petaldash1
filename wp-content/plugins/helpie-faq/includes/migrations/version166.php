<?php

namespace HelpieFaq\Includes\Migrations;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\HelpieFaq\Includes\Migrations\Version166')) {
    class Version166
    {

        public function run()
        {
            $utils_helper = new \HelpieFaq\Includes\Utils\Helpers();
            $term_id = $utils_helper->create_uncategorized_faq_term();
            if (empty($term_id)) {
                return;
            }
            $this->set_the_term_in_all_faq_posts($term_id);
            $this->set_the_term_in_group_meta_data($term_id);
        }

        public function set_the_term_in_group_meta_data($category_id)
        {
            $terms = get_terms(array(
                'taxonomy' => 'helpie_faq_group',
                'hide_empty' => false,
            ));
            if (empty($terms)) {return;}

            $faq_group_repo = new \HelpieFaq\Includes\Repos\Faq_Group();

            foreach ($terms as $term) {

                $faq_group_items = $faq_group_repo->get_faq_group_items($term->term_id);

                $faq_group_items = $this->add_uncategorized_term_in_group_meta($faq_group_items, $category_id);

                $faq_group_repo->update_faq_group_term_meta($term->term_id, $faq_group_items);
            }
            return;
        }

        public function add_uncategorized_term_in_group_meta($meta_data, $category_id)
        {
            foreach ($meta_data as $index => $data) {
                $item = isset($data['faq_item']) ? $data['faq_item'] : '';
                if (empty($item)) {
                    continue;
                }
                $post_id = isset($item['post_id']) ? $item['post_id'] : 0;

                if (empty($post_id)) {
                    continue;
                }

                $terms = get_the_terms($post_id, 'helpie_faq_category');

                $categories = isset($item['categories']) ? $item['categories'] : [];

                if (!empty($terms)) {
                    $categories = array_column($terms, 'term_id');
                }

                if (empty($categories)) {
                    $categories[] = $category_id;
                }
                $meta_data[$index]['faq_item']['categories'] = $categories;
            }
            return $meta_data;
        }

        public function set_the_term_in_all_faq_posts($term_id)
        {
            $cat_ids = array_map('intval', (array) $term_id);
            $posts = get_posts(
                array(
                    'post_type' => HELPIE_FAQ_POST_TYPE,
                    'numberposts' => -1,
                )
            );
            if (empty($posts)) {
                return;
            }

            foreach ($posts as $post) {
                $terms = get_the_terms($post->ID, 'helpie_faq_category');
                if (empty($terms)) {
                    wp_set_object_terms($post->ID, $cat_ids, 'helpie_faq_category');
                }
            }
            return;
        }
    }
}
<?php

namespace HelpieFaq\Includes\Utils;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Includes\Utils\Defaults')) {

    class Defaults
    {

        public $utils_helper;

        public function __construct()
        {
            $this->utils_helper = new \HelpieFaq\Includes\Utils\Helpers();
        }

        public function load_default_contents()
        {

            $args = array('post_type' => 'helpie_faq', 'post_status' => array('publish', 'pending', 'trash'));
            $the_query = new \WP_Query($args);
            $faq_group_id = '';

            $faqs_count = $the_query->post_count;

            // Create Post only if it does not already exists
            if (0 < $faqs_count) {
                return;
            }

            $category_term_id = $this->utils_helper->create_uncategorized_faq_term();
            /* Setup Demo FAQ Question And Answer */
            $this->faq_group_demo_setup($category_term_id);
            $this->all_faq_page_demo_setup($category_term_id);

            wp_reset_postdata();
        }

        public function all_faq_page_demo_setup($category_term_id)
        {
            // error_log('all_faq_page_demo_setup');
            // 1. Create FAQ Posts
            $post_id1 = $this->utils_helper->insert_post_with_term('helpie_faq', $category_term_id, 'helpie_faq_category', 'Simple FAQ', 'Simple FAQ Content');
            $post_id2 = $this->utils_helper->insert_post_with_term('helpie_faq', $category_term_id, 'helpie_faq_category', 'Simple FAQ - 2', 'Simple FAQ Content - 2');

            $result1 = add_post_meta($post_id1, 'question_types', array('faq'), true);
            $result2 = add_post_meta($post_id2, 'question_types', array('faq'), true);

            // 2. Create All FAQ Page
            $create_page = new \HelpieFaq\Includes\Utils\Create_Pages();
            $content = "<p>Sample of All FAQs (Helpie FAQ)</p>";
            $content .= "[helpie_faq]";
            $page_id = $create_page::create('all_helpie_faq_page', 'all_helpie_faq_page_id', 'All FAQs (Helpie FAQ)', $content);
            // error_log('page_id: ' . $page_id);
            return $page_id;
        }

        public function faq_group_demo_setup($category_term_id)
        {
            $output = $this->create_faq_group();
            $post_id = $output['post_id'];
            $faq_group_id = $output['faq_group_id'];

            // error_log('faq_group_demo_setup');
            // error_log('output' . print_r($output, true));

            if (!empty($post_id)) {

                /** linking the category term with default faq post */

                $this->add_post_to_category($post_id, $category_term_id);

                $post = get_post($post_id);

                $props = array(
                    'group_id' => $faq_group_id,
                    'category_id' => $category_term_id,
                );

                // error_log('props' . print_r($props, true));
                /* Inserting FAQ Groups Term-Metadata */
                $this->utils_helper->insert_faq_group_metadata($post, $props);
            }

            $this->create_faq_group_page_on_activate($faq_group_id);

        }

        public function add_post_to_category($post_id, $category_term_id)
        {

            $faq_category_id = array_map('intval', (array) $category_term_id);
            wp_set_object_terms($post_id, $faq_category_id, 'helpie_faq_category');
        }

        /* Setup Demo FAQ Question And Answer */
        public function create_faq_group()
        {

            /* Insert FAQ Group Term with Post */
            $args = $this->utils_helper->insert_term_with_post("helpie_faq", "Getting Started", "helpie_faq_group", "Your First FAQ Question", "Your relevent FAQ answer.");

            $post_id = isset($args[0]) ? $args[0] : 0;
            $faq_group_id = isset($args[1]) ? $args[1] : '';

            return [
                'post_id' => $post_id,
                'faq_group_id' => $faq_group_id,
            ];
        }

        public function create_faq_group_page_on_activate($faq_group_id)
        {
            $create_page = new \HelpieFaq\Includes\Utils\Create_Pages();
            // $content = "[helpie_faq]";
            $content = "<p>Sample of 'Getting Started' FAQ Group</p>";
            if (!empty($faq_group_id)) {
                $content = "[helpie_notices group_id='" . $faq_group_id . "'/]";
                $content .= "<p></p>";
                $content .= "[helpie_faq group_id='" . $faq_group_id . "'/]";
            }
            $page_id = $create_page::create('helpie_faq_page', 'helpie_faq_page_id', 'Helpie FAQ - Group Sample', $content);
            // error_log('page_id: ' . $page_id);
            return $page_id;

        }
    }
}

<?php

namespace HelpieFaq\Includes\Query;

/**
 * FAQ REPO
 *
 */

if (!class_exists('\HelpieFaq\Includes\Query\Faq_Query')) {
    class Faq_Query extends \HelpieFaq\Includes\Abstracts\Object_Query
    {
        public function get_faqs()
        {
            $args = apply_filters('helpie_faq_object_query_args', $this->get_query_vars());
            $results = $this->query($args);
            // error_log('get_faqs $args : ' . print_r($args, true));
            return apply_filters('helpie_faq_object_query', $results, $args);
        }

        /***
         * Implements User Queries from Shortcodes
         */
        public function query($query_vars)
        {
            $query_vars = $this->pre_query_processor($query_vars);

            $wp_query_args = $this->get_wp_query_args($query_vars);

            // TODO product_faq_relations only process on product page
            if (is_singular('product') && is_single(get_the_ID())) {
                // TODO Check and Processing Custom Product FAQ Relations
                if ((isset($wp_query_args['product_faq_relations']) && !empty($wp_query_args['product_faq_relations'])) &&
                    (isset($wp_query_args['meta_query']) && count($wp_query_args['meta_query']) > 0)
                ) {
                    $wp_query_args = $this->get_product_faq_relations_args($wp_query_args);
                }
            }

            $posts = get_posts($wp_query_args);

            /**
             * NOTE:- Didn't gets any posts data by using the get_posts() if the meta-query and post__in props are used
             *
             * So, First gets the posts by using meta-query. Then collect post_id from the posts.
             * After run the query again with post__in prop. It brings the expected data.
             */
            if (is_singular('product') && is_single(get_the_ID()) && hf_fs()->can_use_premium_code__premium_only()) {

                // Get post-ids from faq-groups.
                // TODO: After the v1.8 release should remove the "get_post_ids_by_group_settings" method
                // $post_ids = $this->get_post_ids_by_group_settings($wp_query_args['products']);
                $integration = new \HelpieFaq\Includes\Integration();
                $post_ids = $integration->get_post_ids_from_group_settings(get_the_ID());

                if (!empty($post_ids) && is_array($post_ids)) {
                    $exists_post_ids = array_column($posts, 'ID');
                    $post_ids = array_merge($post_ids, $exists_post_ids);
                    $wp_query_args['post__in'] = $post_ids;
                    $wp_query_args['orderby'] = 'post__in';
                    if ($wp_query_args['meta_query']) {
                        unset($wp_query_args['meta_query']);
                    }
                    $posts = get_posts($wp_query_args);
                }
            }

            // Need to refactor to have 'off' and false equalised ( from settings / elementor / widgets )
            $is_wpautop_enabled = $this->is_wpautop_enabled($query_vars);

            if ($is_wpautop_enabled) {
                // helpie_error_log('true : ' . $query_vars['enable_wpautop']);
                foreach ($posts as $post) {
                    $post->post_content = wpautop($post->post_content);
                }
            }

            // error_log('posts : ' . print_r($posts, true));

            return $posts;
        }

        /**
         * Remove unwanted params causing problems in query
         */
        public function pre_query_processor($query_vars)
        {
            unset($query_vars['title']);
            // unset($query_vars['products']);
            return $query_vars;
        }

        /**
         * Valid default query vars for faqs.
         *
         * @return array
         */

        protected function get_default_query_vars()
        {
            return array_merge(
                parent::get_default_query_vars(),
                array(
                    'post_type' => 'helpie_faq',
                    'sortby' => 'default',
                    'numberposts' => -1,
                )
            );
        }

        protected function get_wp_query_args($query_vars)
        {
            $wp_query_args = $query_vars;
            $store = new \HelpieFaq\Includes\Stores\Faq_Store($query_vars);
            $interpreted_wp_query_args = $store->interprete($query_vars)->get();
            if ($interpreted_wp_query_args) {
                $wp_query_args = wp_parse_args($interpreted_wp_query_args, $query_vars);
            } else {
                $wp_query_args = $query_vars;
            }

            return $wp_query_args;
        }

        protected function is_wpautop_enabled($query_vars)
        {

            if (isset($query_vars['enable_wpautop']) && ($query_vars['enable_wpautop'] === 'on' || $query_vars['enable_wpautop'] === true || $query_vars['enable_wpautop'] == 1)) {
                return true;
            }
            return false;
        }

        public function get_product_faq_relations_args($wp_query_args)
        {

            $product_faq_relation = new \HelpieFaq\Features\Faq\Particles\Product_Faq_Relations();
            $product_faq_terms = $product_faq_relation->get_product_faq_relation_terms($wp_query_args);

            if (isset($product_faq_terms['is_product_faq_relation']) && $product_faq_terms['is_product_faq_relation'] == true) {

                $faq_terms = $product_faq_terms['faq_terms'];
                // Remove Existing Product Meta Query
                unset($wp_query_args['meta_query']);

                $wp_query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'helpie_faq_category',
                        'field' => 'term_id',
                        'terms' => $faq_terms,
                        'include_children' => false,
                    ),
                );
            }

            return $wp_query_args;
        }

        public function get_post_ids_by_group_settings($product_id)
        {
            $post_ids = array();

            // Collect the faq-group ids
            $configured_groups = array();

            $faq_categories = get_terms(array(
                'taxonomy' => 'helpie_faq_group',
                'parent' => 0,
            ));

            if (is_wp_error($faq_categories) || empty($faq_categories)) {
                return $post_ids;
            }

            $product = wc_get_product($product_id);
            // helpie_error_log('$product : ' . print_r($product, true));

            $product_categories = $this->get_all_product_categories($product_id);
            // helpie_error_log('$product_categories : ' . print_r($product_categories, true));

            $faq_group_repository = new \HelpieFaq\Includes\Repos\Faq_Group();

            foreach ($faq_categories as $faq_category) {

                if (empty($faq_category)) {
                    continue;
                }

                $settings_data = get_term_meta($faq_category->term_id, 'faq_group_settings', true);

                if (empty($settings_data)) {
                    continue;
                }

                /**
                 * Get products & product categories from the group settings.
                 */
                $configured_fields = isset($settings_data['fields']) ? $settings_data['fields'] : [];
                $product_terms = $this->get_product_terms_from_group_settings($configured_fields);

                $configured_products = isset($settings_data['products']) ? $settings_data['products'] : array();
                $configured_product_categories = isset($settings_data['product_categories']) ? $settings_data['product_categories'] : array();

                for ($ii = 0; $ii < count($configured_product_categories); $ii++) {
                    $product_category_id = (int) $configured_product_categories[$ii];
                    if (in_array($product_category_id, $product_categories)) {
                        $configured_groups[] = $faq_category->term_id;
                    }
                }

                for ($ii = 0; $ii < count($configured_products); $ii++) {
                    $configured_product_id = (int) $configured_products[$ii];
                    if ($configured_product_id == $product_id) {
                        $configured_groups[] = $faq_category->term_id;
                    }
                }
            }

            /** Return empty , if there is no configure with this product and this product categories */
            if (empty($configured_groups)) {
                return $post_ids;
            }

            foreach ($configured_groups as $group_id) {
                $post_ids = array_merge($post_ids, $faq_group_repository->get_post_ids_from_faq_group(array('group_id' => $group_id)));
            }

            /** Remove Duplicates */
            $post_ids = count($post_ids) > 0 ? array_unique($post_ids) : array();

            return $post_ids;
        }

        public function get_all_product_categories($product_id)
        {
            $categories = array();
            $terms = get_the_terms($product_id, 'product_cat');

            if (is_wp_error($terms) || empty($terms)) {
                return $categories;
            }
            foreach ($terms as $term) {

                $categories[] = $term->term_id;

                if (!empty($term->parent)) {
                    $parent_categories = $this->get_all_parents($term->parent);
                    $categories = array_merge($categories, $parent_categories);
                }
            }
            $categories = array_unique($categories);
            return $categories;
        }

        public function get_all_parents($category_id)
        {
            $parent_categories = array();
            $parent_category = get_term_by('id', $category_id, 'product_cat');
            if (!empty($parent_category->parent)) {
                $parent_categories = $this->get_all_parents($parent_category->parent);
            }
            $parent_categories[] = $category_id;
            return $parent_categories;
        }
    } // END CLASS
}

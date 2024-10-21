<?php

namespace HelpieFaq\Features\Faq\Particles;

/**
 * FAQ REPO
 *
 */

if (!class_exists('\HelpieFaq\Features\Faq\Particles\Product_Faq_Relations')) {
    class Product_Faq_Relations
    {
        public function get_product_faq_relation_terms($wp_query_args)
        {

            /** Get current Product linked all the categories */
            $product_id = isset($wp_query_args['products']) ? $wp_query_args['products'] : 0;

            $product_terms = array();
            if (!empty($product_id) && (int) $product_id) {
                $product_terms = get_the_terms($product_id, 'product_cat');
            }

            // Get all Category Ids in Current Product post
            $product_terms_ids = (!is_wp_error($product_terms) && !empty($product_terms)) ? array_column($product_terms, 'term_id') : [];

            $product_faq_relations = isset($wp_query_args['product_faq_relations']) ? $wp_query_args['product_faq_relations'] : [];

            /** default values */
            $is_product_faq_relation = false;
            $faq_terms = array();

            $product_faq_relations_data = array(
                'faq_terms' => array(),
                'is_product_faq_relation' => $is_product_faq_relation,
            );

            /*** return the default values, if $product_faq_relations is empty  */
            if (empty($product_faq_relations)) {
                return $product_faq_relations_data;
            }

            foreach ($product_faq_relations as $index => $faq_relation) {
                /*** For Specific Woo Categories */
                $product_categories = isset($faq_relation['product_categories']) ? $faq_relation['product_categories'] : [];

                $is_specific_woo_category = ($faq_relation['link_type'] == 'specific_woo_category' && !empty($product_categories)) ? true : false;

                /*** For All Woo-Categories  */
                $is_all_woo_categories = ($faq_relation['link_type'] == 'all_woo_categories') ? true : false;

                if ($is_specific_woo_category) {
                    $product_relation_categories_exists_in_product_terms = $this->product_relation_categories_exists_in_product_terms($product_categories, $product_terms_ids);

                    /** add the term, if not exists in collection of exists. when checking the specific woo-cateogory */
                    $relation_term_not_exists = ($product_relation_categories_exists_in_product_terms && !in_array($faq_relation['faq_category'], $faq_terms)) ? true : false;

                    if ($relation_term_not_exists) {
                        $faq_terms[] = $faq_relation['faq_category'];
                        $is_product_faq_relation = true;
                    }
                }

                /** add category term, when configured the link type is 'all_woo_categories' and that category not exists in collection of $faq_term var  */
                $add_category_in_all_woo_categories = ($is_all_woo_categories && !in_array($faq_relation['faq_category'], $faq_terms)) ? true : false;
                if ($add_category_in_all_woo_categories) {
                    $faq_terms[] = $faq_relation['faq_category'];
                }
            }

            $product_faq_relations_data['faq_terms'] = $faq_terms;
            $product_faq_relations_data['is_product_faq_relation'] = $is_product_faq_relation;
            return $product_faq_relations_data;
        }

        public function product_relation_categories_exists_in_product_terms($product_categories, $product_terms_ids)
        {
            for ($ii = 0; $ii < count($product_categories); $ii++) {
                if (in_array($product_categories[$ii], $product_terms_ids)) {
                    return true;
                }
            }
            return false;
        }
    }
}
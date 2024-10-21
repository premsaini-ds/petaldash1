<?php

namespace HelpieFaq\Includes\Repos;

/**
 * FAQ REPO
 *
 */
if ( !class_exists( '\\HelpieFaq\\Includes\\Repos\\Faq_Repo' ) ) {
    class Faq_Repo {
        private $query;

        public function __construct() {
            $this->query = new \HelpieFaq\Includes\Query\Faq_Query();
        }

        public function get_faqs( $args = array() ) {
            // Test
            // unset($args['categories']);
            if ( isset( $args['categories'] ) && is_array( $args['categories'] ) && in_array( "all", $args['categories'] ) ) {
                unset($args['categories']);
            }
            // error_log('get_faqs args : ' . print_r($args, true));
            if ( isset( $args ) && !empty( $args ) ) {
                add_filter( 'helpie_faq_object_query_args', function ( $query_vars ) use(&$args) {
                    // error_log('$query_vars : ' . print_r($query_vars, true));
                    $query_vars = $this->add_meta_query( $query_vars );
                    return wp_parse_args( $args, $query_vars );
                } );
            }
            // error_log('get_faqs args : ' . print_r($args, true));
            return $this->query->get_faqs();
        }

        public function add_meta_query( $args ) {
            $args['meta_query'] = array(
                'relation' => 'OR',
                array(
                    'key'     => 'question_types',
                    'value'   => 'qna',
                    'compare' => 'NOT LIKE',
                ),
                array(
                    'key'     => 'question_types',
                    'compare' => 'NOT EXISTS',
                ),
            );
            return $args;
        }

        // public function add_meta_query_not_working($args)
        // {
        //     if (!isset($args['meta_query'])) {
        //         $args['meta_query'] = array();
        //     }
        //     if (!empty($args['meta_query'])) {
        //         $args['meta_query']['relation'] = 'OR';
        //     }
        //     // Exclude Question & Answer
        //     array_push(
        //         $args['meta_query'],
        //         array(
        //             'key'       => 'question_types',
        //             'value'     => 'qna',
        //             'compare' => 'NOT LIKE'
        //         )
        //     );
        //     array_push(
        //         $args['meta_query'],
        //         array(
        //             'key'       => 'question_types',
        //             'compare' => 'NOT EXISTS'
        //         )
        //     );
        //     return $args;
        // }
        public function get_all_faqs() {
            return $this->get_faqs();
        }

        public function get_faq_by_category( $category_id = 0 ) {
            $args = array(
                'tax_query' => array(array(
                    'taxonomy' => 'helpie_faq_category',
                    'field'    => 'term_id',
                    'terms'    => $category_id,
                )),
            );
            return $this->get_faqs( $args );
        }

        public function get_faq_by_wiki_category() {
            $args = array(
                'tax_query' => array(array(
                    'taxonomy' => 'helpdesk_category',
                    'field'    => 'term_id',
                    'terms'    => 6,
                )),
            );
            return $this->get_faqs( $args );
        }

        /* OPTIONS */
        public function get_faq_categories( $args = array() ) {
            $category_sortby = ( isset( $args['category_sortby'] ) ? $args['category_sortby'] : '' );
            $category_order = ( isset( $args['category_order'] ) ? $args['category_order'] : 'desc' );
            $term_args = array(
                'taxonomy'   => 'helpie_faq_category',
                'parent'     => 0,
                'hide_empty' => false,
                'orderby'    => 'term_id',
                'order'      => $category_order,
            );
            if ( $category_sortby == 'alphabetical' ) {
                $term_args['orderby'] = 'name';
            } else {
                if ( $category_sortby == 'articles_count' ) {
                    $term_args['orderby'] = 'count';
                }
            }
            $term_args['orderby'] = 'term_id';
            if ( isset( $args['categories'] ) && !empty( $args['categories'] ) ) {
                $category_is_all = is_array( $args['categories'] ) && in_array( 'all', $args['categories'] );
                if ( !$category_is_all ) {
                    $term_args['include'] = $args['categories'];
                }
            }
            // helpie_error_log(' $term_args : ' . print_r($term_args, true));
            $faq_categories = get_terms( $term_args );
            // helpie_error_log(' $faq_categories : ' . print_r($faq_categories, true));
            return $faq_categories;
            return $faq_categories;
        }

        public function sort( $args ) {
            $sort = new \HelpieFaq\Includes\Query\Sort();
            $sortBy_args = $sort->get_sort_args( $args );
            return $sortBy_args;
        }

        // end sort()
        public function get_faq_categories_option( $show_all = false ) {
            $faq_categories = $this->get_faq_categories();
            $faq_categories_option = array();
            if ( $show_all == true ) {
                $faq_categories_option = array(
                    'all' => 'All',
                );
            }
            if ( !isset( $faq_categories ) || empty( $faq_categories ) ) {
                return $faq_categories_option;
            }
            foreach ( $faq_categories as $category ) {
                // error_log('$category: ' . print_r($category, true));
                $term_id = $category->term_id;
                $faq_categories_option[$term_id] = $category->name;
            }
            // if ($show_all == true) {
            //     $faq_categories_option = array('all' => 'All') + $faq_categories_option;
            // }
            return $faq_categories_option;
        }

        public function get_options( $option_field ) {
            switch ( $option_field ) {
                case 'woo-products':
                    $woo_integrator = new \HelpieFaq\Includes\Woo_Integrator();
                    return $woo_integrator->get_products_option( true );
                    // 'show_all' = false;
                    break;
                case 'kb-categories':
                    $kb_integrator = new \HelpieFaq\Lib\Kb_Integrator();
                    return $kb_integrator->get_kb_categories_option();
                    // 'show_all' = false;
                    break;
                case 'categories':
                    return $this->get_faq_categories_option( true );
                    // 'show_all' = false;
                    break;
            }
        }

        public function get_faqs_by_category( $args ) {
            $category_id = ( isset( $args['term_id'] ) && !empty( $args['term_id'] ) ? $args['term_id'] : 0 );
            $order = ( isset( $args['order'] ) ? $args['order'] : 'desc' );
            $wp_query_args = $this->sort( $args );
            $term_args = array_merge( $wp_query_args, $args );
            $post_args = array(
                'post_type'   => 'helpie_faq',
                'numberposts' => -1,
                'order'       => $order,
                'tax_query'   => array(array(
                    'taxonomy'         => 'helpie_faq_category',
                    'field'            => 'term_id',
                    'terms'            => $category_id,
                    'include_children' => false,
                )),
            );
            $post_args = array_merge( $term_args, $post_args );
            $result = get_posts( $post_args );
            return $result;
        }

    }

    // END CLASS
}
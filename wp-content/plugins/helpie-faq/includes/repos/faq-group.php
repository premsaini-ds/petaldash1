<?php

namespace HelpieFaq\Includes\Repos;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\HelpieFaq\\Includes\\Repos\\Faq_Group' ) ) {
    class Faq_Group {
        public function update_faq_group( $request, $term_id ) {
            $this->update_group_settings( $term_id );
            // 0. Settings Updated Already via Rest API in faq_groups.js
            // 1. Update Posts in the Group
            $faq_groups = ( isset( $request['faq_groups'] ) ? $request['faq_groups'] : [] );
            $collections = $this->insert_or_update_the_posts( $term_id, $faq_groups );
            // 2. Update Groups with Post information
            $faq_group_items = ( isset( $collections['faq_group_items'] ) ? $collections['faq_group_items'] : [] );
            $post_ids = ( isset( $collections['post_ids'] ) ? $collections['post_ids'] : [] );
            $this->update_faq_group_term_meta( $term_id, $faq_group_items );
            // 3. Remove Posts not in Group...?
            $this->remove_posts_not_in_faq_group( $term_id, $post_ids );
        }

        public function insert_or_update_the_posts( $term_id, array $faq_group_items ) {
            $post_ids = [];
            // 1. Return values,if empty $faq_group_items
            if ( empty( $faq_group_items ) ) {
                return array(
                    'post_ids'        => $post_ids,
                    'faq_group_items' => $faq_group_items,
                );
            }
            // 2. Loop through $faq_group_items
            for ($ii = 0; $ii < count( $faq_group_items ); $ii++) {
                $faq = ( isset( $faq_group_items[$ii]['faq_item'] ) ? $faq_group_items[$ii]['faq_item'] : '' );
                if ( isset( $faq ) && !empty( $faq ) ) {
                    $post_id = ( isset( $faq['post_id'] ) && !empty( $faq['post_id'] ) ? $faq['post_id'] : '' );
                    $post_data = array(
                        'ID'           => $post_id,
                        'post_title'   => ( isset( $faq['title'] ) ? $faq['title'] : '' ),
                        'post_content' => ( isset( $faq['content'] ) ? $faq['content'] : '' ),
                    );
                    /** 2.1. Update the post  */
                    if ( !empty( $post_id ) ) {
                        wp_update_post( $post_data );
                    }
                    /** 2.2. create new faq post  */
                    if ( empty( $post_id ) ) {
                        $post_data['term_id'] = $term_id;
                        $post_id = $this->store_faq_post_by_tax_id( $post_data );
                        /** set post_id */
                        $faq_group_items[$ii]['faq_item']['post_id'] = $post_id;
                    }
                    // Create question_types post meta
                    $question_types = array('faq');
                    update_post_meta( $post_id, 'question_types', $question_types );
                    /** Get the current item FAQ-Categories */
                    $categories = ( isset( $faq['categories'] ) ? $faq['categories'] : [] );
                    // 2.3 collect current group all post-ids
                    if ( $post_id ) {
                        $post_ids[] = $post_id;
                        $this->set_category_terms( $post_id, $categories );
                    }
                }
            }
            // 3. Return faq_posts with updated post data and also post_ids
            return array(
                'post_ids'        => $post_ids,
                'faq_group_items' => $faq_group_items,
            );
        }

        public function remove_posts_not_in_faq_group( $term_id, array $post_ids ) {
            //1. Get All Posts by term id
            $posts = $this->get_posts_by_term_id( $term_id );
            //2. return, if the posts is empty
            if ( empty( $posts ) && count( $posts ) == 0 ) {
                return;
            }
            foreach ( $posts as $post ) {
                if ( !isset( $post ) ) {
                    continue;
                }
                $postId = $post->ID;
                //3. remove the post, if not exists in $post_ids data
                if ( !in_array( $postId, $post_ids ) ) {
                    /** @since v1.6 use the "wp_trash_post" hook instead of "wp_delete_post"  */
                    wp_trash_post( $postId );
                }
            }
        }

        public function store_faq_post_by_tax_id( array $args ) {
            $utils_helper = new \HelpieFaq\Includes\Utils\Helpers();
            $post_id = $utils_helper->insert_post_with_term(
                HELPIE_FAQ_POST_TYPE,
                $args['term_id'],
                'helpie_faq_group',
                $args['post_title'],
                $args['post_content']
            );
            return $post_id;
        }

        public function update_faq_group_term_meta( $term_id, array $term_meta_data ) {
            update_term_meta( $term_id, 'helpie_faq_group_items', [
                'faq_groups' => $term_meta_data,
            ] );
        }

        public function modify_faq_group_items( $action, $postId, array $faq_group_items ) {
            // error_log('modify_faq_group_items');
            // error_log("action: $action");
            // error_log("postId: $postId");
            // error_log("faq_group_items: " . print_r($faq_group_items, true));
            $post = get_post( $postId );
            /** Get the current FAQ-post category terms */
            $categories = get_the_terms( $post->ID, 'helpie_faq_category' );
            $allowed_actions = ['remove', 'update', 'add'];
            // 1. validate the $action name
            if ( !in_array( $action, $allowed_actions ) ) {
                return $faq_group_items;
            }
            $faq_repo = new \HelpieFaq\Includes\Repos\Faq();
            // 2. Add new faq item to the group
            /*** @since v1.6 */
            $faq_exists_in_group = $this->faq_post_exists_in_group( $post, $faq_group_items );
            if ( isset( $action ) && $action != 'remove' && !$faq_exists_in_group ) {
                $item = $faq_repo->get_post_content( $post );
                $item['categories'] = $this->get_faq_group_category_ids( $categories );
                $faq_group_items[] = [
                    'faq_item' => $item,
                ];
                return $faq_group_items;
            }
            // 3. return empty array value, if $faq_group_items is empty
            if ( empty( $faq_group_items ) ) {
                return [];
            }
            // 4. Loop through $faq_group_items to remove or update the faq item
            for ($ii = 0; $ii < count( $faq_group_items ); $ii++) {
                $faq_item = ( isset( $faq_group_items[$ii]['faq_item'] ) ? $faq_group_items[$ii]['faq_item'] : '' );
                // continue the loop, if not found faq or not match post ID
                $is_faq_empty = ( empty( $faq_item ) ? true : false );
                $faq_is_not_equal = ( $post->ID != $faq_item['post_id'] ? true : false );
                if ( $is_faq_empty || $faq_is_not_equal ) {
                    continue;
                }
                if ( $action == 'remove' ) {
                    unset($faq_group_items[$ii]);
                    // reindexing FAQ Group Items
                    $faq_group_items = array_values( $faq_group_items );
                } else {
                    if ( $action == 'update' ) {
                        $faq_group_items[$ii]['faq_item'] = $faq_repo->get_post_content( $post );
                        /** overrite the current faq post category ids  */
                        $faq_group_items[$ii]['faq_item']['categories'] = $this->get_faq_group_category_ids( $categories );
                    }
                }
            }
            return $faq_group_items;
        }

        /**
         * Use of this method for getting the current post category ids
         *
         * @param [array] $post_categories
         *
         */
        public function get_faq_group_category_ids( $categories ) {
            /** return empty array if $categories is empty */
            if ( empty( $categories ) ) {
                return [];
            }
            /** return the current post category term ids */
            return array_column( $categories, 'term_id' );
        }

        public function get_faq_group_items( $term_id ) {
            $term_meta = get_term_meta( $term_id, 'helpie_faq_group_items' );
            $faq_group_items = ( isset( $term_meta[0]['faq_groups'] ) ? $term_meta[0]['faq_groups'] : [] );
            return $faq_group_items;
        }

        public function get_posts_by_term_id( $term_id ) {
            $post_args = array(
                'post_type'   => 'helpie_faq',
                'numberposts' => -1,
                'tax_query'   => array(array(
                    'taxonomy'         => 'helpie_faq_group',
                    'field'            => 'term_id',
                    'terms'            => $term_id,
                    'include_children' => false,
                )),
            );
            $posts = get_posts( $post_args );
            return $posts;
        }

        public function get_post_ids_from_faq_group( $args ) {
            $post_ids = array();
            $term_id = ( isset( $args['group_id'] ) ? $args['group_id'] : '' );
            if ( empty( $term_id ) ) {
                return $post_ids;
            }
            $faq_group_items = $this->get_faq_group_items( $term_id );
            $post_ids = $this->get_post_ids_by_items( $faq_group_items );
            return $post_ids;
        }

        public function faq_post_exists_in_group( $post, $group_items ) {
            if ( empty( $group_items ) || count( $group_items ) == 0 ) {
                return false;
            }
            $post_id = ( isset( $post ) ? $post->ID : 0 );
            $items_post_ids = $this->get_post_ids_by_items( $group_items );
            if ( in_array( $post_id, $items_post_ids ) ) {
                return true;
            }
            return false;
        }

        public function get_post_ids_by_items( $faq_group_items ) {
            $post_ids = [];
            if ( isset( $faq_group_items ) && empty( $faq_group_items ) && count( $faq_group_items ) == 0 ) {
                return $post_ids;
            }
            for ($ii = 0; $ii < count( $faq_group_items ); $ii++) {
                $faq = ( isset( $faq_group_items[$ii]['faq_item'] ) ? $faq_group_items[$ii]['faq_item'] : '' );
                if ( isset( $faq ) && !empty( $faq ) ) {
                    $post_ids[] = ( isset( $faq['post_id'] ) && !empty( $faq['post_id'] ) ? $faq['post_id'] : '' );
                }
            }
            return $post_ids;
        }

        public function set_category_terms( $post_id, $faq_category_ids ) {
            /** unlink, all category terms to the post if the category_ids are empty.   */
            if ( empty( $faq_category_ids ) ) {
                wp_set_object_terms( $post_id, [0], 'helpie_faq_category' );
            }
            /** string to integer convertion.  */
            $term_ids = array_map( function ( $id ) {
                return (int) $id;
            }, $faq_category_ids );
            /** link the category terms to the post  */
            wp_set_object_terms( $post_id, $term_ids, 'helpie_faq_category' );
            return true;
        }

        public function get_categories_with_posts( $args ) {
            /** Get group id from the shortcode arguments */
            $group_id = ( isset( $args['group_id'] ) && !empty( $args['group_id'] ) ? $args['group_id'] : 0 );
            if ( empty( $group_id ) ) {
                return [];
            }
            /** Get the group meta data by group_id */
            $term_meta = $this->get_faq_group_items( $group_id );
            /** Get all category ids from the current group without duplicates */
            $category_ids = $this->get_unique_category_ids_by_meta_data( $term_meta );
            /** Get the category with posts object data */
            return $this->get_categories_with_posts_data_by_term_meta( $category_ids, $term_meta );
        }

        public function get_categories_with_posts_data_by_term_meta( $group_category_ids, $term_meta ) {
            $data = [];
            if ( empty( $group_category_ids ) ) {
                return $data;
            }
            $group_category_terms = array();
            foreach ( $group_category_ids as $group_category_id ) {
                /*** get the category term by term id */
                $category_term = get_term( $group_category_id, 'helpie_faq_category' );
                if ( empty( $category_term ) ) {
                    continue;
                }
                $group_category_terms[] = $category_term;
            }
            if ( empty( $group_category_terms ) ) {
                return $data;
            }
            for ($ii = 0; $ii < count( $group_category_terms ); $ii++) {
                $category_term = $group_category_terms[$ii];
                /** get the post ids to the linked category */
                $post_ids = $this->get_post_ids_by_category_term( $category_term, $term_meta );
                $posts = array();
                if ( !empty( $post_ids ) ) {
                    $posts = get_posts( array(
                        'post__in'    => $post_ids,
                        'post_type'   => HELPIE_FAQ_POST_TYPE,
                        'numberposts' => -1,
                        'orderby'     => 'post__in',
                    ) );
                }
                $data[] = array(
                    'term'  => $category_term,
                    'posts' => $posts,
                );
            }
            return $data;
        }

        public function get_post_ids_by_category_term( $category_term, $term_meta_data ) {
            $post_ids = array();
            if ( empty( $term_meta_data ) ) {
                return $post_ids;
            }
            foreach ( $term_meta_data as $data ) {
                $post_id = ( isset( $data['faq_item']['post_id'] ) && !empty( $data['faq_item']['post_id'] ) ? $data['faq_item']['post_id'] : 0 );
                $categories = ( isset( $data['faq_item']['categories'] ) ? $data['faq_item']['categories'] : [] );
                if ( $post_id && in_array( $category_term->term_id, $categories ) ) {
                    $post_ids[] = $post_id;
                }
            }
            return $post_ids;
        }

        public function get_unique_category_ids_by_meta_data( $term_meta ) {
            $category_ids = [];
            if ( empty( $term_meta ) ) {
                return $category_ids;
            }
            foreach ( $term_meta as $data ) {
                $categories = ( isset( $data['faq_item']['categories'] ) ? $data['faq_item']['categories'] : [] );
                for ($ii = 0; $ii < count( $categories ); $ii++) {
                    $category_ids[] = (int) $categories[$ii];
                }
            }
            /** return the unique ids from the collected category ids  */
            return array_values( array_unique( $category_ids ) );
        }

        public function get_pending_post_ids( $term_id ) {
            $pending_post_ids = array();
            $faq_group_items = $this->get_faq_group_items( $term_id );
            $post_ids = $this->get_post_ids_by_items( $faq_group_items );
            if ( empty( $post_ids ) ) {
                return $pending_post_ids;
            }
            $posts = get_posts( array(
                'post__in'    => $post_ids,
                'post_type'   => HELPIE_FAQ_POST_TYPE,
                'numberposts' => -1,
                'orderby'     => 'post__in',
                'post_status' => 'pending',
            ) );
            if ( !empty( $posts ) ) {
                $pending_post_ids = array_column( $posts, 'ID' );
            }
            return $pending_post_ids;
        }

        public function update_group_settings( $group_id ) {
            $free_allowed_fields = [
                'header-background',
                'header-font-color',
                'body-font-color',
                'body-background',
                "show_search",
                "sortby",
                "order"
            ];
            $original_settings_data = ( isset( $_REQUEST['faq_group_setting_fields_object'] ) ? json_decode( sanitize_text_field( wp_unslash( $_REQUEST['faq_group_setting_fields_object'] ) ), true ) : [] );
            $settings_data = [];
            if ( isset( $original_settings_data['fields'] ) && !empty( $original_settings_data['fields'] ) ) {
                $settings_data['fields'] = array_filter( $original_settings_data['fields'], function ( $key ) use($free_allowed_fields) {
                    return in_array( $key, $free_allowed_fields );
                }, ARRAY_FILTER_USE_KEY );
            }
            //  $filtered_settings_data = [];
            //  return;
            //  $filtered_settings_data = [];
            // error_log('original_settings_data: ' . print_r($original_settings_data, true));
            //  error_log('settings_data: ' . print_r($settings_data, true));
            \update_term_meta( $group_id, 'faq_group_settings', $settings_data );
        }

    }

}
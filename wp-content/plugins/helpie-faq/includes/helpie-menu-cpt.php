<?php

namespace HelpieFaq\Includes;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\HelpieFaq\\Includes\\Helpie_Menu_Cpt' ) ) {
    class Helpie_Menu_Cpt {
        private $post_type_name = HELPIE_MENU_POST_TYPE;

        private $helpie_faq_model;

        public function __construct() {
            $this->helpie_faq_model = new \HelpieFaq\Includes\Core\Helpie_Faq_Model();
        }

        /* Register post type in init Hook */
        public function register() {
            add_action( 'init', array($this, 'register_post_type_with_taxonomy') );
            $helpie_menu_init = new \HelpieFaq\Features\Helpie_Menu\Init();
        }

        /* Register post type on activation hook cause can't call other filter and actions */
        public function register_helpie_faq_cpt() {
            $this->register_post_type_with_taxonomy();
        }

        public function register_post_type_with_taxonomy() {
            $labels = array(
                'name'                  => _x( 'Menus', 'post type general name', 'helpie-faq' ),
                'singular_name'         => _x( 'Menu', 'post type singular name', 'helpie-faq' ),
                'menu_name'             => _x( 'Helpie Menu', 'admin menu', 'helpie-faq' ),
                'name_admin_bar'        => _x( 'Menu', 'add new on admin bar', 'helpie-faq' ),
                'add_new'               => _x( 'Add New', 'Menu', 'helpie-faq' ),
                'add_new_item'          => __( 'Add New Menu', 'helpie-faq' ),
                'new_item'              => __( 'New Menu', 'helpie-faq' ),
                'edit_item'             => __( 'Edit Menu', 'helpie-faq' ),
                'update_item'           => __( 'Update Menu', 'helpie-faq' ),
                'view_item'             => __( 'View Menu', 'helpie-faq' ),
                'all_items'             => __( 'All Menus', 'helpie-faq' ),
                'search_items'          => __( 'Search Menus', 'helpie-faq' ),
                'not_found'             => __( 'No Menus found', 'helpie-faq' ),
                'parent_item_colon'     => __( 'Parent Menus:', 'helpie-faq' ),
                'not_found'             => __( 'No Menus found.', 'helpie-faq' ),
                'not_found_in_trash'    => __( 'No Menus found in Trash.', 'helpie-faq' ),
                'items_list'            => __( 'Menu Items list', 'helpie-faq' ),
                'items_list_navigation' => __( 'Menu Items list Navigation', 'helpie-faq' ),
                'filter_items_list'     => __( 'Filter Menu Items list', 'helpie-faq' ),
            );
            $cpt_slug = 'helpie_menu';
            // default slug if not set in 'helpie_faq_slug
            // $cpt_slug = $this->helpie_faq_model->get_configured_slug('helpie_faq_slug');
            // $cpt_slug = isset($cpt_slug) && !empty($cpt_slug) ? $cpt_slug : 'helpie_menu';
            // $global_search_option = $this->helpie_faq_model->get_global_search_option();
            // $enable_single_faq_page = $this->helpie_faq_model->get_option('enable_single_faq_page');
            $enable_single_faq_page = $this->helpie_faq_model->get_enable_single_faq_page();
            //
            $args = array(
                'labels'              => $labels,
                'public'              => true,
                'menu_position'       => 26,
                'menu_icon'           => 'dashicons-feedback',
                'show_in_nav_menus'   => false,
                'show_in_rest'        => true,
                'map_meta_cap'        => true,
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => false,
                'supports'            => array(
                    'title',
                    'editor',
                    'excerpt',
                    'custom-fields',
                    'comments',
                    'revisions',
                    'page-attributes',
                    'post-formats',
                    'thumbnail',
                    'author'
                ),
                'rewrite'             => array(
                    'slug'       => $cpt_slug,
                    'with_front' => false,
                ),
            );
            // error_log('enable_single_faq_page : ' . $enable_single_faq_page);
            if ( isset( $enable_single_faq_page ) && ($enable_single_faq_page == false || $enable_single_faq_page == 0 || $enable_single_faq_page == '0') ) {
                $args['publicly_queryable'] = false;
            }
            register_post_type( $this->post_type_name, $args );
            // $this->register_category();
            // $this->register_tag();
            // $this->register_faq_group();
        }

    }

    // END CLASS
}
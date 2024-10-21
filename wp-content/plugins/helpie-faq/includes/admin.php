<?php

namespace HelpieFaq\Includes;

//
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Helpie-faq admin.
 *
 * Helpie-FAQ admin handler class is responsible for initializing Helpie-FAQ in
 * WordPress admin.
 *
 * @since 1.0.0
 */

if (!class_exists('\HelpieFaq\Includes\Admin')) {

    class Admin
    {
        public $plugin_domain;
        public $version;
        public $faq_default_category_id;

        public function __construct($plugin_domain, $version)
        {
            $this->plugin_domain = $plugin_domain;
            $this->version = $version;

            add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
            // Enqueue the faq group collection
            add_action('admin_enqueue_scripts', array($this, 'enqueue_faq_group_collection'));

            add_action('admin_enqueue_scripts', array($this, 'enqueue_helpie_menu_collection'));

            // add_action('admin_enqueue_scripts', array($this, 'enqueue_faq_group_config_new'));

            $validation_map = array(
                'post_type' => 'String',
                'page' => 'String',
            );

            $sanitized_data = hfaq_get_sanitized_data("GET", $validation_map);

            if (isset($sanitized_data['post_type']) && $sanitized_data['post_type'] == "helpie_faq") {
                add_action('admin_enqueue_scripts', array($this, 'set_admin_pointers'), 10, 1);

                if (isset($sanitized_data['page']) && $sanitized_data['page'] == 'helpie-review-settings') {
                    // Helpie-FAQ Pro feature popup Modal only rendering for helpie-faq admin setting page.
                    add_action('admin_footer', array($this, 'load_modal'));
                }
            }

            $this->filters();
        }

        public function add_management_page()
        {
            $title = __('Helpie FAQ', $this->plugin_domain);

            $hook_suffix = add_management_page($title, $title, 'export', $this->plugin_domain, array(
                $this,
                'load_admin_view',
            ));

            add_action('load-' . $hook_suffix, array($this, 'load_assets'));
        }

        public function load_qna_scripts()
        {
        }

        public function enqueue_scripts()
        {
            wp_enqueue_style($this->plugin_domain . '-bundle-styles', HELPIE_FAQ_URL . 'assets/bundles/admin.app.css', array(), $this->version, 'all');
            wp_enqueue_script($this->plugin_domain . '-bundle-admin-scripts', HELPIE_FAQ_URL . 'assets/bundles/admin.app.js', array('jquery'), $this->version, 'all');

            $nonce = wp_create_nonce('helpie_faq_nonce');
            $rest_nonce = wp_create_nonce('wp_rest');

            $helpie_faq_object = array(
                'nonce' => $nonce,
                'ajax_url' => admin_url('admin-ajax.php'),
                'site_url' => get_site_url(),
                'rest_nonce' => $rest_nonce,
                'trial_url' => hf_fs()->get_trial_url(),
                'faq_plan' => hf_fs()->can_use_premium_code__premium_only() ? 'premium' : 'free',
                'supported_plugins' => array(
                    'woocommerce' => class_exists('woocommerce') ? 'active' : 'inactive',
                    'tutor_lms' => class_exists('\TUTOR\Tutor') ? 'active' : 'inactive',
                    'learn_dash' => class_exists('SFWD_LMS') ? 'active' : 'inactive',
                    'learn_press' => class_exists('LearnPress') ? 'active' : 'inactive',
                ),
                'api_endpoints' => array(
                    'update_faq_group_settings' => get_rest_url(null, 'helpie-faq/v1/update-faq-group-settings'),
                    'get_post_type_data' => get_rest_url(null, 'helpie-faq/v1/get-post-type-data'),
                    'get_post_type_data_multiple' => get_rest_url(null, 'helpie-faq/v1/get-post-type-data-multiple'),
                    'get_taxonomy_data' => get_rest_url(null, 'helpie-faq/v1/get-taxonomy-data'),
                    'save_menu_settings' => get_rest_url(null, 'helpie-faq/v1/save-menu-settings'),
                    // 'get_posts' => get_rest_url(null, 'helpie-faq/v1/get-posts'),
                    // 'get_taxonomies' => get_rest_url(null, 'helpie-faq/v1/get-taxonomies'),
                ),
            );

            wp_localize_script($this->plugin_domain . '-bundle-admin-scripts', 'helpie_faq_object', $helpie_faq_object);

            do_action('helpie_faq_admin_localize_script');

            global $current_screen;
            // check current page is faq-group page or not. if true then, get the current page.
            $helpie_faq_group_page = false;
            $helpie_faq_page_action = 'show_faq_groups';
            $helpie_faq_group_create_link = admin_url('edit-tags.php?taxonomy=helpie_faq_group&post_type=helpie_faq&helpie_faq_page_action=create');
            if (isset($current_screen) && (isset($current_screen->post_type) && $current_screen->post_type == HELPIE_FAQ_POST_TYPE)) {
                if (isset($current_screen->id) && $current_screen->id == 'edit-helpie_faq_group') {
                    $helpie_faq_group_page = true;
                    $helpie_faq_page_action = $this->get_faq_group_current_page();
                }
            }
            // FAQ group-summary page styles
            $faq_group_tax_styles['show_faq_groups'] = '#col-left { display: none; }#col-right { float:none; width: auto; }';

            // FAQ group-create page styles
            $faq_group_tax_styles['create_faq_group'] = '#col-right { display: none; }#col-left { float:none; width: auto; }';

            $helpie_faq_group_js_args = array(
                'is_page' => $helpie_faq_group_page,
                'page_action' => $helpie_faq_page_action,
                'create_link' => $helpie_faq_group_create_link,
            );

            $validation_map = array(
                'tag_ID' => 'Number',
            );
            $sanitized_data = hfaq_get_sanitized_data("GET", $validation_map);

            $group_term_id = isset($sanitized_data['tag_ID']) ? $sanitized_data['tag_ID'] : 0;

            /** Getting pending post status ids from the group  */
            $pending_post_ids = array();
            if (!empty($group_term_id) && intval($group_term_id)) {
                $faq_repo = new \HelpieFaq\Includes\Repos\Faq_Group();
                $pending_post_ids = $faq_repo->get_pending_post_ids($group_term_id);
            }

            $helpie_faq_group_js_args['pending_post_ids'] = $pending_post_ids;

            wp_localize_script($this->plugin_domain . '-bundle-admin-scripts', 'helpie_faq_group', $helpie_faq_group_js_args);

            if ($helpie_faq_group_page) {
                $styles = isset($faq_group_tax_styles[$helpie_faq_page_action]) ? $faq_group_tax_styles[$helpie_faq_page_action] : '';
                wp_add_inline_style('common', $styles);
            }
        }

        public function get_faq_group_current_page()
        {
            $page = 'show_faq_groups';

            $validation_map = array(
                'helpie_faq_page_action' => 'String',
            );
            $sanitized_data = hfaq_get_sanitized_data("GET", $validation_map);
            $page_action = isset($sanitized_data['helpie_faq_page_action']) ? $sanitized_data['helpie_faq_page_action'] : '';

            if ($page_action == 'create') {
                $page = "create_faq_group";
            }
            return $page;
        }

        public function remove_kb_category_submenu()
        {
            remove_submenu_page('edit.php?post_type=helpie_faq', 'edit-tags.php?taxonomy=helpdesk_category&amp;post_type=helpie_faq');
        }

        public function set_admin_pointers($page)
        {
            $pointer = new \HelpieFaq\Lib\Pointers\Pointers();
            $pointers = $pointer->return_pointers();

            //Arguments: pointers php file, version (dots will be replaced), prefix
            $manager = new \HelpieFaq\Lib\Pointers\Pointers_Manager($pointers, '1.0', 'hfaq_admin_pointers');
            $manager->parse();
            $pointers = $manager->filter($page);

            if (empty($pointers)) { // nothing to do if no pointers pass the filter
                return;
            }
            wp_enqueue_style('wp-pointer');
            $js_url = HELPIE_FAQ_URL . 'lib/pointers/pointers.js';

            wp_enqueue_script('hfaq_admin_pointers', $js_url, array('wp-pointer'), null, true);
            //data to pass to javascript
            $data = array(
                'next_label' => __('Next'),
                'close_label' => __('Close'),
                'pointers' => $pointers,
            );
            wp_localize_script('hfaq_admin_pointers', 'MyAdminPointers', $data);
        }

        public function load_modal()
        {
            $model = new \HelpieFaq\Includes\Components\Modal();
            $content = $model->get_content();
            hfaq_safe_echo($content);
        }

        public function filters()
        {
            $helpers = new \HelpieFaq\Includes\Utils\Helpers();
            $this->faq_default_category_id = $helpers->get_default_category_term_id();
            add_filter("helpie_faq_category_row_actions", array($this, 'modifying_the_faq_category_list'), 10, 2);
        }

        public function modifying_the_faq_category_list($actions, $term)
        {
            if (isset($term) && ($term->term_id == $this->faq_default_category_id)) {
                unset($actions['delete']);
            }
            return $actions;
        }

        public function enqueue_faq_group_config_new()
        {
            // $config = new \HelpieFaq\Features\Faq_Group\Config();
            // $faq_groups_config =  $config->get_config();
            // wp_localize_script(HELPIE_FAQ_DOMAIN . '-bundle-admin-scripts', 'faq_groups_config', $faq_groups_config);
        }

        public function enqueue_helpie_menu_collection()
        {

            // error_log('enqueue_helpie_menu_collection');
            $post_id = isset($_GET['post']) ? (int) sanitize_text_field(wp_unslash($_GET['post'])) : 0;
            $action = isset($_GET['action']) ? sanitize_text_field(wp_unslash($_GET['action'])) : '';

            if ($post_id != null && $post_id != 0) {
                $settings = get_post_meta($post_id, 'helpie_menu_settings', true);
            } else {
                $settings = array();
            }

            $title = get_the_title($post_id);
            // error_log(' enqueue_helpie_menu_collection settings: ' . print_r($settings, true));

            $helpie_menu = array(
                'title' => $title,
                'post_id' => $post_id,
                'action' => $action,
                'settings' => $settings,

            );

            wp_localize_script(HELPIE_FAQ_DOMAIN . '-bundle-admin-scripts', 'helpie_menu', $helpie_menu);
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script($this->plugin_domain . '-bundle-admin-scripts', HELPIE_FAQ_URL . 'assets/bundles/admin.app.js', array('wp-color-picker'), false, true);

        }
        public function enqueue_faq_group_collection()
        {
            $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field(wp_unslash($_GET['taxonomy'])) : '';
            if ($taxonomy != 'helpie_faq_group') {
                return;
            }
            $tag_ID = isset($_GET['tag_ID']) && is_numeric($_GET['tag_ID']) ? (int) sanitize_text_field(wp_unslash($_GET['tag_ID'])) : 0;

            $page_action = isset($_GET['helpie_faq_page_action']) ? sanitize_text_field(wp_unslash($_GET['helpie_faq_page_action'])) : '';
            $is_faq_group_add_or_edit_page = ($page_action == 'create' || $tag_ID > 0);
            if (!$is_faq_group_add_or_edit_page) {
                return;
            }

            // $categories = $this->get_categories();
            $products = $this->get_products();

            $enqueue_data = array(
                'faq_group' => array(
                    // 'categories' => $categories,
                    'products' => $products,
                    'settings' => get_term_meta($tag_ID, 'faq_group_settings', true),
                    'tag_ID' => $tag_ID,
                    'page_action' => $page_action,
                    'product_categories' => $this->get_product_categories(),
                    'post_types' => $this->get_post_types(),
                    'faq_group_edit_url' => wp_json_encode(admin_url("term.php?taxonomy=helpie_faq_group&tag_ID={$tag_ID}&post_type=helpie_faq")),
                ),
            );
            wp_localize_script(HELPIE_FAQ_DOMAIN . '-bundle-admin-scripts', 'helpie_faq', $enqueue_data);
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script($this->plugin_domain . '-bundle-admin-scripts', HELPIE_FAQ_URL . 'assets/bundles/admin.app.js', array('wp-color-picker'), false, true);
        }

        public function get_post_types()
        {
            $args = array(
                'public' => true,
                '_builtin' => false,
            );

            $output = 'names'; // 'names' or 'objects' (default: 'names')
            $operator = 'and'; // 'and' or 'or' (default: 'and')

            $active_post_types = get_post_types($args, $output, $operator);
            helpie_error_log('active_post_types: ' . print_r($active_post_types, true));

            $available_post_types = $this->get_available_post_types();

            foreach ($available_post_types as $key => $value) {

                if (in_array($key, $active_post_types)) {
                    $available_post_types[$key]['active'] = true;
                }
            }

            return $available_post_types;
        }

        public function get_available_post_types()
        {
            $available_post_types = array(
                'product' => array(
                    'label' => 'Woocommerce Product',
                    'active' => false,
                ),
                'lp_course' => array(
                    'label' => 'LearnPress Course',
                    'active' => false,
                ),
                'lp_lesson' => array(
                    'label' => 'LearnPress Lesson',
                    'active' => false,
                ),
                'sfwd-courses' => array(
                    'label' => 'LearnDash Courses',
                    'active' => false,
                ),
                'sfwd-lessons' => array(
                    'label' => 'LearnDash Lessons',
                    'active' => false,
                ),
                'courses' => array(
                    'label' => 'Tutor Courses',
                    'active' => false,
                ),
                'lesson' => array(
                    'label' => 'Tutor Lesson',
                    'active' => false,
                ),
            );

            return $available_post_types;
        }

        public function get_categories()
        {
            $categories_obj = get_terms(array(
                'taxonomy' => 'helpie_faq_category',
                'hide_empty' => false,
                'parent' => 0,
            ));

            $categories = array();
            if ((isset($categories_obj) && empty($categories_obj)) || is_wp_error($categories_obj)) {
                return $categories;
            }
            foreach ($categories_obj as $category_obj) {
                $categories[] = array(
                    'value' => $category_obj->term_id,
                    'label' => $category_obj->name,
                );
            }

            return $categories;
        }

        public function get_products()
        {
            $products = array();

            if (!class_exists('woocommerce')) {
                return $products;
            }

            $args = array(
                'post_type' => 'product',
                'posts_per_page' => 500,
                'post_status' => 'publish',
            );
            $posts = get_posts($args);

            if ((isset($posts) && empty($posts)) || is_wp_error($posts)) {
                return $products;
            }

            foreach ($posts as $post) {
                $products[] = array(
                    'value' => $post->ID,
                    'label' => $post->post_title,
                );
            }

            return $products;
        }

        public function get_product_categories()
        {
            $product_categories = array();

            if (!class_exists('woocommerce')) {
                return $product_categories;
            }

            $terms = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'orderby' => 'name',
            ]);

            if ((isset($terms) && empty($terms)) || is_wp_error($terms)) {
                return $product_categories;
            }

            foreach ($terms as $term) {
                $product_categories[] = array(
                    'value' => $term->term_id,
                    'label' => $term->name,
                );
            }

            return $product_categories;
        }
    }
}

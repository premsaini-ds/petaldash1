<?php

namespace HelpieFaq\Includes\Modules\Faq_Rest_Api;

//
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('\HelpieFaq\Includes\Modules\Faq_Rest_Api\Faq_Rest_Api')) {

    class Faq_Rest_Api
    {

        public $error_messages = array(
            'UNAUTHORIZED' => "You don't have an permission to access this resource",
            'REQUIRED_TERM_ID' => "Required, FAQ G table ID ",
            'INVALID_POST' => "Invalid, Tablesome post",
            'REQUIRED_RECORD_IDS' => "Required, Tablesome table record IDs",
            'UNABLE_TO_CREATE' => "Unable to create a post.",
        );

        public $namespace = 'helpie-faq/v1';

        public function __construct()
        {
            helpie_error_log('faq-rest-api... construct()');
        }

        public function init()
        {
            foreach ($this->get_routes() as $route) {
                /** Register the REST route */
                register_rest_route($this->namespace, $route['url'], $route['args']);
            }
        }

        public function get_routes()
        {
            return array(
                array(
                    'url' => '/update-faq-group-settings',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'modify_faq_group_settings_new'),
                        'permission_callback' => array($this, 'api_access_permission'),
                    ),
                ),
                array(
                    'url' => '/save-menu-settings',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'save_menu_settings'),
                        'permission_callback' => array($this, 'api_access_permission'),
                    ),
                ),
                array(
                    'url' => '/get-post-type-data-multiple',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'get_post_type_data_multiple'),
                        'permission_callback' => array($this, 'api_access_permission'),
                    ),
                ),
                array(
                    'url' => '/get-post-type-data',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'get_post_type_data'),
                        'permission_callback' => array($this, 'api_access_permission'),
                    ),
                ),
                array(
                    'url' => '/get-taxonomy-data',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'get_taxonomy_data'),
                        'permission_callback' => array($this, 'api_access_permission'),
                    ),
                ),
                array(
                    'url' => '/get-posts',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'get_posts'),
                        'permission_callback' => array($this, 'api_access_permission'),
                    ),
                ),
                array(
                    'url' => '/get-taxonomies',
                    'args' => array(
                        'methods' => \WP_REST_Server::EDITABLE,
                        'callback' => array($this, 'get_taxonomies'),
                        'permission_callback' => array($this, 'api_access_permission'),
                    ),
                ),
            );
        }

        public function get_taxonomy_data($request)
        {
            // $data = [];
            $params = $request->get_params();

            return $this->get_terms_endFunction($params);
        }

        public function get_terms_endFunction($params)
        {
            $taxonomy = $params['taxonomy'] ? $params['taxonomy'] : 'categories';
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ));

            return $terms;
        }

        public function get_post_type_data_multiple($request)
        {
            // helpie_error_log('get_post_type_data_multiple() $request: ' . print_r($request, true));
            $multi_post_type_data = [];
            $params = $request->get_params();
            $repeatedGroups = $params['repeatedGroups'] ? $params['repeatedGroups'] : 'post';
            // $repeatedGroups = $request['repeatedGroups'];

            foreach ($repeatedGroups as $key => $repeatedGroup) {
                $idNumber = $repeatedGroup['idNumber'];
                $sub_params = ['post_type' => $repeatedGroup['post_type'], 'taxonomy' => $repeatedGroup['taxonomy']];
                $multi_post_type_data[$idNumber] = [];

                $data = [];
                $data['posts'] = $this->get_posts_endFunction($sub_params);
                $data['taxonomies'] = $this->get_taxonomies_endFunction($sub_params);
                $data['terms'] = $this->get_terms_endFunction($sub_params);
                $multi_post_type_data[$idNumber] = $data;
            }

            // helpie_error_log('multi_post_type_data: ' . print_r($multi_post_type_data, true));

            return $multi_post_type_data;
        }

        public function get_post_type_data($request)
        {
            $data = [];

            $data['posts'] = $this->get_posts($request);
            $data['taxonomies'] = $this->get_taxonomies($request);
            $data['terms'] = $this->get_taxonomy_data($request);

            return $data;
        }

        public function get_posts($request)
        {
            $params = $request->get_params();
            $posts = $this->get_posts_endFunction($params);

            return $posts;
        }

        public function get_taxonomies($request)
        {
            $params = $request->get_params();

            return $this->get_taxonomies_endFunction($params);
        }

        public function get_posts_endFunction($params)
        {
            $post_type = $params['post_type'] ? $params['post_type'] : 'post';
            $args = array(
                'numberposts' => -1,
                'post_type' => $post_type,
            );

            $posts = get_posts($args);

            return $posts;
        }

        public function get_taxonomies_endFunction($params)
        {
            $post_type = $params['post_type'] ? $params['post_type'] : 'post';
            $taxonomy_objects = get_object_taxonomies($post_type, 'objects');

            return $taxonomy_objects;
        }

        public function api_access_permission()
        {
            if (current_user_can('edit_posts')) {
                return true;
            }
            $error_code = "UNAUTHORIZED";
            return new \WP_Error($error_code, $this->get_error_message($error_code));
        }

        public function get_error_message($error_code)
        {
            $message = isset($this->error_messages[$error_code]) ? $this->error_messages[$error_code] : 'Something Went Wrong, try later';
            return $message;
        }

        public function save_menu_settings($request)
        {
            $params = $request->get_params();

            $title = isset($params['title']) ? $params['title'] : 'Menu';
            $settings = isset($params['settings']) ? $params['settings'] : array();
            $post_id = isset($params['post_id']) ? $params['post_id'] : 0;
            $action = isset($params['action']) ? $params['action'] : 'add';

            // error_log('save_menu_settings() $post_id: ' . print_r($post_id, true));
            // error_log('save_menu_settings() $action: ' . print_r($action, true));
            // error_log('save_menu_settings() $params: ' . print_r($params, true));

            /* Escaping */
            foreach ($settings as $key => $setting) {

                if (is_array($setting)) {
                    foreach ($setting as $sub_key => $sub_setting) {
                        $settings[$key][$sub_key] = esc_html($sub_setting);
                    }
                } else {
                    $settings[$key] = esc_html($setting);
                }
            }

            if ($action == 'add') {
                $post_id = wp_insert_post(array(
                    'post_type' => HELPIE_MENU_POST_TYPE,
                    'post_title' => $title,
                    'post_status' => 'publish',
                ));
            }

            // error_log('save_menu_settings() $settings: ' . print_r($settings, true));

            update_post_meta($post_id, 'helpie_menu_settings', $settings);
            $redirect_url = admin_url('edit.php?post_type=helpie_menu&page=tablesome_admin_page&action=' . $action . '&post=' . $post_id);
            // wp_safe_redirect(admin_url('edit.php?post_type=helpie_menu&page=tablesome_admin_page&action=' . $action . '&post=' . $post_id));
            return $redirect_url;
        }

        public function modify_faq_group_settings_new($request)
        {
            $params = $request->get_params();
            // helpie_error_log('$params: ');
            // helpie_error_log($params);
            helpie_error_log('params : ' . print_r($params, true));

            $group_id = isset($params['group_id']) ? $params['group_id'] : 0;
            $fields = isset($params['fields']) ? $params['fields'] : array();
            $elements = isset($params['elements']) ? $params['elements'] : array();
            $faq_group_location_index = isset($params['faq_group_location_index']) ? $params['faq_group_location_index'] : 1;

            if (empty($group_id)) {
                $error_code = "REQUIRED_TERM_ID";
                return new \WP_Error($error_code, 'Required, FAQ Group ID ', array('status' => 400));
            }

            $settings = array(
                'fields' => $fields,
                'elements' => $elements,
                'faq_group_location_index' => $faq_group_location_index,
            );

            \update_term_meta($group_id, 'faq_group_settings', $settings);

            $settings_data = get_term_meta($group_id, 'faq_group_settings', true);
            return rest_ensure_response(array(
                'status' => 'success',
                'message' => 'FAQ Group Settings Updated',
                'data' => $settings_data,
            ));
        }

        public function modify_faq_group_settings($request)
        {
            $params = $request->get_params();
            $group_id = isset($params['group_id']) ? $params['group_id'] : 0;
            $category_ids = isset($params['category_ids']) ? $params['category_ids'] : array();
            $product_ids = isset($params['product_ids']) ? $params['product_ids'] : array();

            if (empty($group_id)) {
                $error_code = "REQUIRED_TERM_ID";
                return new \WP_Error($error_code, 'Required, FAQ Group ID ', array('status' => 400));
            }

            $settings = array(
                'categories' => $category_ids,
                'products' => $product_ids,
            );

            \update_term_meta($group_id, 'faq_group_settings', $settings);

            $settings_data = get_term_meta($group_id, 'faq_group_settings', true);
            return rest_ensure_response(array(
                'status' => 'success',
                'message' => 'FAQ Group Settings Updated',
                'data' => $settings_data,
            ));
        }
    }
}

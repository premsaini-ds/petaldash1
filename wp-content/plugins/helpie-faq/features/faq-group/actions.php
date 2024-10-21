<?php

namespace HelpieFaq\Features\Faq_Group;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Faq_Group\Actions')) {
    class Actions
    {
        public $taxonomy = 'helpie_faq_group';
        public function __construct()
        {
            $this->load_action_hooks();
            $this->load_filter_hooks();
            $this->load_csf_action_hooks();
        }

        /** load wp action hooks */
        public function load_action_hooks()
        {
            add_action('admin_menu', array($this, 'add_submenu_for_creating_faq_group'));
            add_action('admin_menu', array($this, 'add_submenu_for_external_docs_link'));
            add_action('admin_menu', array($this, 'add_submenu_for_onboarding_page'));

            add_action("{$this->taxonomy}_edit_form", array($this, 'edit_form'), 10, 2);
            add_action("{$this->taxonomy}_add_form", array($this, 'hide_slug_and_description_rows'), 10, 2);
            add_action("delete_{$this->taxonomy}", array($this, 'delete_faq_group_posts'), 10, 4);

            add_action($this->taxonomy . '_add_form_fields', array($this, 'render_group_settings_fields'));
            add_action($this->taxonomy . '_edit_form_fields', array($this, 'render_group_settings_fields'));

            add_action("created_{$this->taxonomy}", array($this, 'faq_group_created'), 10, 3);

            /**
             * Faq Groups Core Actions for edit,delete post actions
             */
            $faq_repository = new \HelpieFaq\Includes\Repos\Faq();

            add_action('pre_post_update', array($faq_repository, 'pre_post_update'), 10, 2);

            add_action('save_post', array($faq_repository, 'save_post'), 10, 3);

            add_action('edit_post', function ($postId) use ($faq_repository) {
                global $post;
                $post_type = isset($post) ? $post->post_type : get_post_type($postId);
                if (is_null($post) || empty($post) || $post_type != HELPIE_FAQ_POST_TYPE) {
                    return;
                }

                $faq_repository->update_post($postId);
            });

            add_action('wp_trash_post', function ($postId) use ($faq_repository) {
                global $post;
                $post_type = isset($post) ? $post->post_type : get_post_type($postId);
                if (is_null($post) || empty($post) || $post_type != HELPIE_FAQ_POST_TYPE) {
                    return;
                }
                $faq_repository->remove_post($postId);
            });

            /** @since v1.6 */
            add_action('transition_post_status', array($faq_repository, 'updating_the_post_status'), 10, 3);

            add_action('admin_init', [new \HelpieFaq\Features\Feature_Notice(), 'init']);
        }
        public function load_filter_hooks()
        {
            add_filter('get_the_archive_title', [$this, 'remove_prefix_from_group_archive_page_title'], 10, 1);
        }

        public function render_group_settings_fields()
        {

            $html = '';
            echo '<input type="hidden" name="faq_group_setting_fields_object" value="" />';

        }

        public function load_csf_action_hooks()
        {
            $faq_group_prefix = 'helpie_faq_group_items';

            $faq_group_repository = new \HelpieFaq\Includes\Repos\Faq_Group();
            add_action("csf_{$faq_group_prefix}_saved", [$faq_group_repository, 'update_faq_group'], 10, 2);
            add_action("csf_{$faq_group_prefix}_save_after", [$this, 'faq_group_process_is_done'], 11, 2);
        }

        public function init_admin_menu()
        {
            /** submenu */
            add_action('admin_menu', array($this, 'add_submenu_for_creating_faq_group'));
        }

        public function edit_form($taxonomy)
        {
            echo "<style>.term-slug-wrap { display:none; } .term-description-wrap { display:none; } #edittag{ max-width: 100%;}</style>";
            $term_id = $taxonomy->term_id;
            $this->get_shortcode_field($term_id);
            $this->get_warning_field();
        }

        public function get_warning_field()
        {
            $html = '';
            $html .= '<table class="form-table helpie-faq-groups-table warning" role="presentation">';
            $html .= '<tbody>';
            $html .= '<tr class="form-field term-shortcode-wrap">';
            $html .= '<td>';
            $html .= '<p class="warning">' . esc_html__("Videos and Images wont show if you have excerpt option enabled (Helpie FAQ Settings -> Single FAQ Page)", "helpie-faq") . '</p>';
            $html .= '</td></tr>';
            $html .= '</tbody>';
            $html .= '</table>';

            hfaq_safe_echo($html);
        }

        public function get_shortcode_field($term_id)
        {

            $shortcode_text = "[helpie_faq group_id='" . esc_attr($term_id) . "'/]";

            $html = '';
            $html .= $this->get_faq_group_settings_html_content();
            $html .= '<table class="form-table helpie-faq-groups-table" role="presentation">';
            $html .= '<tbody>';
            $html .= '<tr class="form-field term-shortcode-wrap">';
            $html .= '<td>';
            $html .= '<div class="shortcode-clipboard-field"><input type="text" readonly id="faq-group-shortcode" value="' . esc_attr($shortcode_text) . '">';
            $html .= '<span class="clipboard-text" title="' . esc_html__('Copy Shortcode Clipboard', 'helpie-faq') . '">' . esc_html__("Copy Shortcode", "helpie-faq") . '</span>';
            $html .= '</div>';
            $html .= '<p class="description">' . esc_html__("Paste this shortcode in any page to display this FAQ Group.", "helpie-faq") . '</p>';
            $html .= '</td></tr>';
            $html .= '</tbody>';
            $html .= '</table>';

            hfaq_safe_echo($html);
        }

        // public function hide_slug_and_description_rows() {
        //     error_log(' hide_slug_and_description_rows ');
        //     echo "<style>.term-slug-wrap { display:none; } .term-description-wrap { display:none; } #edittag{ max-width: 100%;}</style>";
        //     echo $this->get_faq_group_settings_html_content();
        // }

        public function hide_slug_and_description_rows()
        {

            echo "<style>.term-slug-wrap { display:none; } .term-description-wrap { display:none; } #edittag{ max-width: 100%;}</style>";
            $page_action = isset($_GET['helpie_faq_page_action']) ? sanitize_text_field(wp_unslash($_GET['helpie_faq_page_action'])) : '';
            // error_log('page_action: ' . $page_action);
            if ('create' == $page_action) {
                hfaq_safe_echo($this->get_faq_group_settings_html_content());
            }
        }

        public function get_faq_group_settings_html_content()
        {
            return '<div id="svelte-faqs-group-settings"></div>';
        }

        public function delete_faq_group_posts($term, $tt_id, $deleted_term, $object_ids)
        {

            if (count($object_ids) == 0) {
                return;
            }
            // Removed posts links with faq group
            foreach ($object_ids as $post_id) {
                /** @since v1.6 use the "wp_trash_post" hook instead of "wp_delete_post"  */
                wp_trash_post($post_id);
            }
        }

        public function add_submenu_for_onboarding_page()
        {
            $onboarding_menu_label = __('Onboarding', 'pauple-helpie');
            add_submenu_page(
                'edit.php?post_type=helpie_faq',
                $onboarding_menu_label,
                $onboarding_menu_label,
                'manage_options',
                'helpie-faq-onboarding',
                array($this, 'render_onboarding_page')
            );
        }

        public function render_onboarding_page()
        {
            $onboarding = new \HelpieFaq\Includes\Onboarding_Page();
            $onboarding->render();
        }

        public function add_submenu_for_creating_faq_group()
        {
            $create_faq_group_menu_label = __('Add New FAQ Group', 'pauple-helpie');
            add_submenu_page(
                'edit.php?post_type=helpie_faq',
                $create_faq_group_menu_label,
                $create_faq_group_menu_label,
                'manage_categories',
                'edit-tags.php?taxonomy=helpie_faq_group&post_type=helpie_faq&helpie_faq_page_action=create'
            );
        }

        public function add_submenu_for_external_docs_link()
        {

            $docs_link = '<span style="color:greenyellow;">Helpie Docs/KB ➤</span>';
            add_submenu_page(
                'edit.php?post_type=helpie_faq',
                $docs_link,
                $docs_link,
                'manage_options',
                'helpie-docs-page',
                array($this, 'handle_external_links')
            );
        }

        public function handle_external_links()
        {
            return false;
        }

        public function faq_group_process_is_done($request, $term_id)
        {
            $faq_group_edit_url = admin_url("term.php?taxonomy=helpie_faq_group&tag_ID={$term_id}&post_type=helpie_faq");
            echo "<script>";
            echo "var faq_group_edit_url = " . wp_json_encode($faq_group_edit_url) . ";";
            echo "if (faq_group_edit_url != '') {";
            echo "location.replace(faq_group_edit_url);";
            echo "}";
            echo "</script>";
            exit;
        }

        public function remove_prefix_from_group_archive_page_title($title, $original_title = '')
        {

            // error_log('remove_prefix_from_group_archive_page_title');
            /** return the original title content if the page is "FAQ Group" archive page */
            if (is_tax($this->taxonomy)) {
                return $original_title;
            }
            return $title;
        }

        public function faq_group_created($term_id, $tt_id, $taxonomy)
        {
            helpie_faq_track_event("FAQ Group Created", true);
        }
    }
}

<?php

namespace HelpieFaq\Features\Helpie_Menu;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\HelpieFaq\\Features\\Helpie_Menu\\Init' ) ) {
    class Init {
        public function __construct() {
            add_action( 'admin_menu', array($this, 'add_single_menu_post_page') );
            add_action(
                "edit_form_after_title",
                array($this, 'get_add_template_view'),
                10,
                2
            );
            // add_action("helpie_menu_edit_form", array($this, 'get_add_template_view'), 10, 2);
            // add_action("helpie_menu_add_form", array($this, 'hide_slug_and_description_rows'), 10, 2);
        }

        public function get_frontend_view( $shortcode_params = [] ) {
            // $menu = new Menu();
            // return $menu->get_view();
            $html = "<div id='helpie-menu-frontend-app'>";
            // $html .= '<h1>Helpie Menu - Shortcode</h1>';
            $html .= '<div id="helpie-menu-app"></div>';
            $html .= '</div>';
            $html .= "</div>";
            $Actions = new \HelpieFaq\Includes\Actions();
            $Actions->handle_helpie_menu_assets( 'helpie_menu_shortcode', $shortcode_params );
            return $html;
        }

        public function add_single_menu_post_page( $submenu_page ) {
            $params = $this->get_params_from_url();
            $edit_title = __( "Edit Menu", "tablesome" );
            $create_new_title = __( "Create New Menu", "helpie-faq" );
            $page_title = ( isset( $params['action'] ) && $params['action'] == 'edit' ? $edit_title : $create_new_title );
            $submenu_page = [
                'name'     => 'tablesome_admin_page',
                'title'    => "Add/Edit Menu",
                'menu'     => "Add/Edit Menu",
                'callback' => [
                    'controller' => $this,
                    'method'     => 'get_add_template_view',
                ],
            ];
            add_submenu_page(
                'edit.php?post_type=' . HELPIE_MENU_POST_TYPE,
                /* main menu slug */
                $submenu_page["title"],
                /* page title */
                $submenu_page["menu"],
                /* page submenu title */
                'manage_categories',
                /* page roles and capability needed*/
                $submenu_page["name"],
                /* page name */
                array($submenu_page["callback"]["controller"], $submenu_page["callback"]["method"])
            );
        }

        public function get_params_from_url() {
            return [];
        }

        public function get_add_template_view( $post ) {
            if ( HELPIE_MENU_POST_TYPE != $post->post_type ) {
                return;
            }
            // error_log('get_add_template_view');
            // echo "<styl> body{display:none;}</style>";
            $defaults = array(
                'table_mode'     => 'editor',
                'pagination'     => true,
                'last_record_id' => 0,
            );
            $params = $defaults;
            $insight_image = HELPIE_FAQ_URL . '/assets/img/insights.png';
            echo '<section id="content-tease">';
            hfaq_safe_echo( $this->faq_pro_buy_notice_info() );
            // echo '<img src="' . esc_url($insight_image) . '" alt="' . esc_html__("FAQ Insights", "helpie-faq") . '" title="' . esc_html__("FAQ Insights", "helpie-faq") . '">';
            echo '</section>';
        }

        public function faq_pro_buy_notice_info() {
            $html = '';
            $html = "<div class='helpie-notice notice notice-success'>";
            $html .= '<p style="font-weight:bold;">';
            $html .= __( 'In order use this feature you need to purchase and activate the <a href="' . esc_url( admin_url( 'edit.php?post_type=helpie_faq&page=helpie_faq-pricing' ) ) . '">Helpie FAQ Pro</a> plugin.', 'helpie-faq' );
            $html .= '</p>';
            $html .= '</div>';
            return $html;
        }

    }

    // END CLASS
}
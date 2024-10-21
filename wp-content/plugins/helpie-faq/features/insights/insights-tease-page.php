<?php

namespace HelpieFaq\Features\Insights;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

if (!class_exists('\HelpieFaq\Features\Insights\Insights_Tease_Page')) {
    class Insights_Tease_Page extends \HelpieFaq\Features\Insights\Admin_Page
    {
        private $options;
        private $page_name = 'insights_model_page';
        private $opts_grp = 'pauple_insights_model';

        public function __construct()
        {
            parent::__construct();

            add_action('admin_menu', array($this, 'add_insigts_tease_page'));
            add_action('admin_init', array($this, 'page_init'));
        }

        public function add_insigts_tease_page()
        {
            $insights = __('Insights', 'pauple-helpie');
            // This page will be under "Settings"
            add_submenu_page(
                'edit.php?post_type=helpie_faq',
                $insights,
                $insights,
                'manage_options',
                'helpie-faq-insights',
                array($this, 'show_insights_tease_page')
            );
        }

        public function show_insights_tease_page()
        {
            $content = '';
            $content = "<div class='helpie-faq dashboard'>";
            $content .= $this->content();
            $content .= '</div>';

            hfaq_safe_echo($content);
        }

        public function content()
        {

            $insight_image = HELPIE_FAQ_URL . '/assets/img/insights.png';

            $html = '';
            $html = '<section id="content-tease">';
            $html .= $this->faq_pro_buy_notice_info();
            $html .= '<img src="' . esc_url($insight_image) . '" alt="' . esc_html__("FAQ Insights", "helpie-faq") . '" title="' . esc_html__("FAQ Insights", "helpie-faq") . '">';
            $html .= '</section>';

            return $html;
        }

        public function faq_pro_buy_notice_info()
        {

            $html = '';
            $html = "<div class='helpie-notice notice notice-success'>";
            $html .= '<p style="font-weight:bold;">';
            $html .= __('In order use this feature you need to purchase and activate the <a href="' . esc_url(admin_url('edit.php?post_type=helpie_faq&page=helpie_faq-pricing')) . '">Helpie FAQ Pro</a> plugin.', 'helpie-faq');
            $html .= '</p>';
            $html .= '</div>';

            return $html;
        }

        /**
         * Register and add settings.
         */
        public function page_init()
        {
            add_settings_section(
                'helpie_core_settings', // ID
                __('Helpie Insights', 'pauple-shelpie'), // Title
                array($this, 'print_core_settings'), // Callback
                $this->page_name// Page
            );
        }

        public function print_core_settings()
        {
            echo "<span class='sub-title1'>" . esc_html(__('Insights to a better Knowledge base.', 'pauple-helpie')) . "</span>";
        }
    }
}

<?php

namespace HelpieFaq\Features\Insights;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( '\\HelpieFaq\\Features\\Insights\\View' ) ) {
    class View {
        public $insights;

        public function __construct() {
            add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
        }

        public function enqueue_scripts() {
        }

        public function get_view( $insights ) {
            $this->insights = $insights;
            $html = "<div class='helpie-faq dashboard'>";
            $html .= $this->controls();
            $html .= $this->tab_menu();
            $html .= $this->tab_body();
            $html .= "</div>";
            return $html;
        }

        public function controls() {
            $text = esc_html__( 'Reset All Insights', 'helpie-faq' );
            $html = '';
            $html .= get_submit_button( $text, 'delete', 'helpie_faq_delete' );
            return $html;
        }

        public function tab_menu() {
            $html = '
            <main>

            <input id="tab1" type="radio" name="tabs" checked>
            <label class="faq-label" for="tab1"><span class="dashicons dashicons-chart-pie"></span>' . esc_html__( "7 Days", "helpie-faq" ) . '</label>

            <input id="tab2" type="radio" name="tabs">
            <label class="faq-label" for="tab2"><span class="dashicons dashicons-chart-bar"></span> ' . esc_html__( "30 Days", "helpie-faq" ) . '</label>

            <input id="tab3" type="radio" name="tabs">
            <label class="faq-label" for="tab3"><span class="dashicons dashicons-chart-area"></span> ' . esc_html__( "1 Year", "helpie-faq" ) . '</label>

            <input id="tab4" type="radio" name="tabs">
            <label class="faq-label" for="tab4"><span class="dashicons dashicons-clock"></span> ' . esc_html__( "All Time", "helpie-faq" ) . '</label>
            ';
            return $html;
        }

        public function tab_body() {
            $html = $this->get_section( '7day', 1 );
            $html .= $this->get_section( '30day', 2 );
            $html .= $this->get_section( 'year', 3 );
            $html .= $this->get_section( 'all-time', 4 );
            return $html;
        }

        protected function get_section( $time, $id ) {
            $html = '<section id="' . esc_attr( 'content' . $id ) . '">';
            $html .= $this->get_total_card( $time );
            // TODO: implement event graph for 'all-time'
            if ( $id != 4 ) {
                $html .= $this->get_graph( $time );
            }
            $html .= $this->get_most_frequent_list_card( $time );
            $html .= '</section>';
            return $html;
        }

        protected function get_total_card( $time ) {
            $html = '<h2>' . esc_html__( "Events Count", "helpie-faq" ) . '</h2>';
            $html .= '<div class="card-list"><div class="row flex-grid">';
            foreach ( $this->insights as $event_key => $insight ) {
                $html .= '<p>' . $this->get_stats_view( $insight, $time, $event_key ) . '</p>';
            }
            $html .= ' </div></div>';
            return $html;
        }

        protected function get_graph( $time ) {
            $html = '<h2>' . esc_html__( "Events Graph", "helpie-faq" ) . '</h2>';
            $html .= '<p><div class="ct-chart ' . esc_attr( 'ct-chart-' . $time ) . ' ct-perfect-fourth"></div></p>';
            return $html;
        }

        protected function get_most_frequent_list_card( $time ) {
            $html = '<h2>' . esc_html__( "Events Count", "helpie-faq" ) . '</h2>';
            $html .= '<div class="card-list most-frequent"><div class="row flex-grid">';
            foreach ( $this->insights as $event_key => $insight ) {
                $html .= '<p>' . $this->get_most_events( $insight, $time, $event_key ) . '</p>';
            }
            $html .= ' </div></div>';
            return $html;
        }

        public function get_most_events( $insight, $key, $event_key ) {
            $html = '<div class="column"><div class="' . esc_attr( 'card ' . $event_key ) . '">';
            $html .= "<h4 class='card-title'>" . $this->get_title_by_event_name( $event_key ) . "</h4>";
            $most_events_list = $insight['most-' . $key];
            // error_log('$insight : '  . print_r($insight, true));
            for ($ii = 0; $ii < sizeof( $most_events_list ); $ii++) {
                $html .= '<div class="single-event">';
                $html .= '<span class="label w-75">' . esc_html( $most_events_list[$ii]['label'] ) . '</span>';
                $html .= '<span class="value w-25 text-align--end"> - ' . esc_html( $most_events_list[$ii]['value'] ) . '</span>';
                $html .= '</div>';
            }
            $html .= '</div></div>';
            return $html;
        }

        public function get_stats_view( $insight, $key, $event_key ) {
            $icon_content = '<span class="dashicons dashicons-search" style="color:#926f29;"></span>';
            if ( $event_key == 'click' ) {
                $icon_content = '<span class="dashicons dashicons-edit" style="color:#c52583;"></span>';
            }
            $html = '
                <div class="column">
                    <div class="card stat-card ' . esc_attr( $event_key ) . '">
                        <span class="stat-icon">
                            ' . $icon_content . '
                        </span>
                        <div class="value">' . esc_html( $insight[$key . '-total'] ) . '</div>
                        <div class="title">' . $this->get_title_by_event_name( $event_key ) . '</div>

                    </div>
                </div>
            ';
            // TODO: Implement increase code : <div class="stat increase"><b><i class="fa fa-angle-up"></i>13</b>% increase</div>
            return $html;
        }

        private function get_title_by_event_name( $name ) {
            $title = '';
            switch ( $name ) {
                case 'click':
                    $title = esc_html__( "Clicks", "helpie-faq" );
                    break;
                case 'queries':
                    $title = esc_html__( "Search Queries", "helpie-faq" );
                    break;
                case 'terms':
                    $title = esc_html__( "Search Terms", "helpie-faq" );
                    break;
                default:
                    $title = '';
                    break;
            }
            return $title;
        }

    }

    // END CLASS
}
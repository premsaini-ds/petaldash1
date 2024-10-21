<?php

// Create a helper function for easy SDK access.
if ( !function_exists( 'hf_fs' ) ) {
    // Create a helper function for easy SDK access.
    function hf_fs() {
        global $hf_fs;
        if ( !isset( $hf_fs ) ) {
            // Include Freemius SDK.
            // $freemius_wordpress_sdk = HELPIE_FAQ_PATH . "vendor/freemius/wordpress-sdk/start.php";
            // if (!file_exists($freemius_wordpress_sdk)) {
            // wp_die("composer package \"freemius/wordpress-sdk\" was not installed, Do run \"composer update.\"");
            // }
            // require_once $freemius_wordpress_sdk;
            $hf_fs = fs_dynamic_init( array(
                'id'              => '2442',
                'slug'            => 'helpie_faq',
                'type'            => 'plugin',
                'public_key'      => 'pk_65a62b52f2165799f7e9a3e1c9cd9',
                'is_premium'      => false,
                'premium_suffix'  => '',
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'trial'           => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                'has_affiliation' => 'selected',
                'menu'            => array(
                    'slug'       => 'edit.php?post_type=helpie_faq',
                    'first-path' => 'edit.php?post_type=helpie_faq&page=helpie-faq-onboarding',
                ),
                'is_live'         => true,
            ) );
        }
        return $hf_fs;
    }

    // Init Freemius.
    hf_fs();
    // Signal that SDK was initiated.
    do_action( 'hf_fs_loaded' );
    hf_fs()->add_action(
        'after_license_change',
        'hf_fs_after_license_change',
        10,
        2
    );
    hf_fs()->add_filter( 'support_forum_url', 'hf_fs_support_forum_url' );
    hf_fs()->add_filter( 'show_first_trial_after_n_sec', 'start_trial' );
    // hf_fs()->add_action('after_premium_version_activation', 'hf_fs_after_premium_version_activation');
    // hf_fs()->add_action('plugin_version_update', 'hf_fs_plugin_version_update');
    function hf_fs_support_forum_url(  $wp_support_url  ) {
        return 'https://wordpress.org/support/plugin/helpie-faq';
    }

    function start_trial(  $day_in_sec  ) {
        return 1;
    }

    function hf_fs_after_license_change(  $change, $current_plan  ) {
        helpie_error_log( '*** After License Change Hook ***' );
        helpie_error_log( '$change : ' . print_r( $change, true ) );
        helpie_error_log( '$current_plan : ' . print_r( $current_plan, true ) );
        switch ( $change ) {
            case 'activated':
                helpie_faq_track_event( 'License Activated', $current_plan );
                break;
            case 'cancelled':
                helpie_faq_track_event( 'License Cancelled', $current_plan );
                break;
            case 'expired':
                helpie_faq_track_event( 'License Expired', $current_plan );
                break;
            case 'downgraded':
                // Plan downgraded to free.
                helpie_faq_track_event( 'Plan Downgraded', $current_plan );
                break;
            case 'trial_started':
                helpie_faq_track_event( 'Trial Started', $current_plan );
                break;
            case 'trial_expired':
                helpie_faq_track_event( 'Trial Expired', $current_plan );
                break;
            case 'upgraded':
                helpie_faq_track_event( 'Plan Upgraded', $current_plan );
                break;
        }
    }

    function hf_fs_after_premium_version_activation() {
        // error_log('*** Activated Premium Version ***');
        helpie_faq_track_event( 'Activated Premium Version', true );
    }

    function hf_fs_plugin_version_update(  $last_version, $plugin_version  ) {
        // error_log('*** Plugin Version Updated ***');
        helpie_faq_track_event( 'Plugin Upgraded', $plugin_version );
    }

}
<?php
/*
 * This is the child theme for Petal Dash theme, generated with Generate Child Theme plugin by catchthemes.
 *
 * (Please see https://developer.wordpress.org/themes/advanced-topics/child-themes/#how-to-create-a-child-theme)
 */
add_action( 'wp_enqueue_scripts', 'petal_dash_child_enqueue_styles' );
function petal_dash_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',get_stylesheet_directory_uri() . '/style.css',array('parent-style'));

     // Enqueue Bootstrap CSS
wp_enqueue_style( 'bootstrap-css', get_stylesheet_directory_uri() . '/css/bootstrap.min.css' );
wp_enqueue_style( 'custom-css', get_stylesheet_directory_uri() . '/custom.css' );

    // Enqueue Bootstrap JS with jQuery as a dependency (if needed)
 wp_enqueue_script( 'bootstrap-js', get_stylesheet_directory_uri() . '/js/bootstrap.bundle.min.js', array( 'jquery' ), null, true );

  wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/custom.js');

}


// Ensure theme support for WooCommerce gallery features
function add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'add_woocommerce_support' );




// Load WooCommerce styles
add_action( 'wp_enqueue_scripts', 'load_woocommerce_styles' );
function load_woocommerce_styles() {
    wp_enqueue_style( 'woocommerce-general' );
    wp_enqueue_style( 'woocommerce-layout' );
    wp_enqueue_style( 'woocommerce-smallscreen' );
}


// Register the WooCommerce Shop Sidebar
function custom_shop_sidebar() {
    register_sidebar( array(
        'name'          => __( 'Shop Sidebar', 'your-theme-textdomain' ),
        'id'            => 'shop-sidebar',
        'description'   => __( 'Sidebar displayed on the WooCommerce shop page', 'your-theme-textdomain' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ) );
}
add_action( 'widgets_init', 'custom_shop_sidebar' );



// Display sidebar on WooCommerce shop page only
function add_shop_sidebar() {
    if ( is_shop() || is_product_category() ) { // Show on the shop and category archive pages
        get_sidebar( 'shop' ); // This calls sidebar-shop.php file
    }
}
add_action( 'woocommerce_sidebar', 'add_shop_sidebar' );



function set_recently_viewed_products() {
    if (is_product()) {
        global $post;
        
        // Get the current product ID
        $current_product_id = $post->ID;

        // Get the existing recently viewed products
        $recently_viewed = isset($_COOKIE['woocommerce_recently_viewed']) ? explode('|', $_COOKIE['woocommerce_recently_viewed']) : array();

        // Remove the current product ID if it exists
        $recently_viewed = array_diff($recently_viewed, array($current_product_id));

        // Add the current product ID to the beginning of the array
        array_unshift($recently_viewed, $current_product_id);

        // Limit the number of recently viewed products (e.g., to 10)
        $recently_viewed = array_slice($recently_viewed, 0, 10);

        // Set the updated cookie
        setcookie('woocommerce_recently_viewed', implode('|', $recently_viewed), time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
    }
}
add_action('template_redirect', 'set_recently_viewed_products');



add_theme_support( 'woocommerce' );
/*
 * Your code goes below
 */

add_filter( 'woocommerce_product_tabs', 'enable_product_tabs', 10 );
function enable_product_tabs( $tabs ) {
    // Adding the description tab
    if (!isset($tabs['description'])) {
        $tabs['description'] = array(
            'title'    => __( 'Description', 'woocommerce' ),
            'priority' => 10,
            'callback' => 'woocommerce_product_description_tab',
        );
    }

        // Ensure the reviews tab is present
    if (!isset($tabs['reviews'])) {
        $tabs['reviews'] = array(
            'title'    => __( 'Reviews', 'woocommerce' ),
            'priority' => 30,
            'callback' => 'comments_template', // WooCommerce review callback
        );
    }

    return $tabs;
}



function enqueue_font_awesome() {
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_font_awesome' );


function petal_dash_child_footer_widgets() {
    // Register four footer widget areas
    register_sidebar( array(
        'name'          => 'Footer Widget 1',
        'id'            => 'footer-1',
        'before_widget' => '<div class="footer-widget footer-widget-1">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => 'Footer Widget 2',
        'id'            => 'footer-2',
        'before_widget' => '<div class="footer-widget footer-widget-2">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => 'Footer Widget 3',
        'id'            => 'footer-3',
        'before_widget' => '<div class="footer-widget footer-widget-3">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => 'Footer Widget 4',
        'id'            => 'footer-4',
        'before_widget' => '<div class="footer-widget footer-widget-4">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}

add_action( 'widgets_init', 'petal_dash_child_footer_widgets' );



// Change related products title in WooCommerce
add_filter('woocommerce_product_related_products_heading', 'custom_related_products_title');

function custom_related_products_title() {
    return 'You may also like';
}


function add_postcode_and_delivery_fields() {
    global $post;

    echo '<div class="options_group">';

    // Postcode Field
    woocommerce_wp_text_input( 
        array( 
            'id'          => 'product_postcode', 
            'label'       => __('Postcode', 'woocommerce'), 
            'placeholder' => 'Enter Postcode',
            'desc_tip'    => 'true',
            'description' => __('Enter the postcode for this product.', 'woocommerce')
        )
    );



    // Postcode Field
    woocommerce_wp_text_input( 
        array( 
            'id'          => 'delivery_days', 
            'label'       => __('Delivery Days', 'woocommerce'), 
            'placeholder' => 'Enter Delivery Days',
            'desc_tip'    => 'true',
            'description' => __('Enter the delivery for this product.', 'woocommerce')
        )
    );

    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'add_postcode_and_delivery_fields');




function save_postcode_and_delivery_fields($post_id) {
    // Save Postcode
    $postcode = isset($_POST['product_postcode']) ? sanitize_text_field($_POST['product_postcode']) : '';
    update_post_meta($post_id, 'product_postcode', $postcode);


    $delivery_days = isset($_POST['delivery_days']) ? sanitize_text_field($_POST['delivery_days']) : '';
    update_post_meta($post_id, 'delivery_days', $delivery_days);

    // Save Delivery Days
}
add_action('woocommerce_process_product_meta', 'save_postcode_and_delivery_fields');



// Display postcode and delivery Days on the product page
function display_postcode_and_delivery_fields($post) {
    // Retrieve and display Postcode
    $postcode = get_post_meta($post->ID, 'product_postcode', true);
    echo '<p><strong>' . __('Postcode', 'woocommerce') . ':</strong> ' . esc_html($postcode) . '</p>';

    // Retrieve and display Delivery Days
      $delivery_days = get_post_meta($post->ID, 'delivery_days', true);
     echo '<p><strong>' . __('Delivery Days', 'woocommerce') . ':</strong> ' . esc_html($delivery_days) . '</p>';
}
add_action('woocommerce_admin_product_data_after_tabs', 'display_postcode_and_delivery_fields');





function filter_products_by_postcode_and_delivery_days($query) {

    if (!is_admin() && $query->is_main_query() && is_shop() || is_product_category()) {

        // Filter by postcode
        if (isset($_GET['postcode']) && !empty($_GET['postcode'])) {
            $postcode = sanitize_text_field($_GET['postcode']);
            
            $meta_query = array(
                array(
                    'key'     => 'product_postcode',
                    'value'   => $postcode,
                    'compare' => 'LIKE',
                ),
            );

            $query->set('meta_query', $meta_query);
        }

        // Filter by delivery date

        if (isset($_GET['delivery_date']) && !empty($_GET['delivery_date']) && $_GET['delivery_date'] !='anytime') {

             // Sanitize and get the delivery date from the query parameter
                $delivery_date = sanitize_text_field($_GET['delivery_date']);

                // Create a DateTime object for the delivery date
                $deliveryDate = new DateTime($delivery_date);


             // Get the current date
                $currentDate = new DateTime();

                // Calculate the difference
                 $interval = $currentDate->diff($deliveryDate);

                // Get the number of days
                 $daysBetween = $interval->days+1;
                
                // echo $daysBetween;die;

            $delivery_meta_query = array(
                array(
                    'key'     => 'delivery_days',
                    'value'   => $daysBetween,
                    'compare' => 'LIKE',
                ),
            );

            $query->set('meta_query', $delivery_meta_query);
        }
      
    }
}
add_action('pre_get_posts', 'filter_products_by_postcode_and_delivery_days');





/*capture_delivery_days*/

function capture_delivery_days() {
    if (isset($_GET['delivery_date']) && !empty($_GET['delivery_date'])) {
        // Sanitize and store delivery dates as a comma-separated string
        $delivery_days = sanitize_text_field($_GET['delivery_date']);
        WC()->session->set('delivery_days', $delivery_days);
    }
}
add_action('wp', 'capture_delivery_days');


/*add_delivery_days_to_checkout*/

function add_delivery_days_to_checkout($fields) {
    $delivery_days = WC()->session->get('delivery_days'); // Fetching the session value
    if ($delivery_days) {
        $fields['billing']['delivery_days'] = array(
            'type' => 'textarea', // Use textarea for multiple dates
            'label' => __('Delivery Days', 'woocommerce'),
            'placeholder' => __('Enter days separated by commas'),
            'default' => $delivery_days,
            'required' => true,
            'class' => array('form-row-wide'),
            'clear' => true,
        );
    }
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'add_delivery_days_to_checkout');

/*save_delivery_days_order_meta*/


// Add delivery days to order meta
function save_delivery_days_order_meta($order_id) {
    // Retrieve the delivery days from the session
    if ($delivery_days = WC()->session->get('delivery_days')) {
        // Assuming delivery_days is an array of selected days
        $delivery_days_string = is_array($delivery_days) ? implode(', ', $delivery_days) : sanitize_text_field($delivery_days);
        
        // Save to order meta
        update_post_meta($order_id, 'Delivery Days', $delivery_days_string);
    }
}
add_action('woocommerce_checkout_update_order_meta', 'save_delivery_date_order_meta');


// Display delivery days in order admin panel
function display_delivery_days_in_admin_order($order) {
    $delivery_days = get_post_meta($order->get_id(), 'Delivery Days', true);
    
    if ($delivery_days) {
        echo '<p><strong>' . __('Delivery Days:', 'woocommerce') . '</strong> ' . esc_html($delivery_days) . '</p>';
    }
}
add_action('woocommerce_admin_order_data_after_order_details', 'display_delivery_days_in_admin_order');



/* Filter sidebar */
function postcode_delivery_filter_form() {
    ob_start(); ?>
    
    <form id="postcode-filter" method="GET">
        <div class="woocommerce-widget-layered-nav">
            <h3 class="widget-title">Delivering to</h3>
            <input type="text" id="postcode" name="postcode" value="<?php echo isset($_GET['postcode']) ? esc_attr($_GET['postcode']) : ''; ?>" placeholder="postcode" />
            <button type="submit" id="apply_postcode_filter">Apply</button>
        </div>
    </form>

    <form id="delivery-date-filter" method="GET">
        <div class="woocommerce-widget-layered-nav">
            <h3 class="widget-title">Delivery Date</h3>
            <select id="delivery_date" name="delivery_date">
                <option value="" selected="">Select a delivery date</option>
                <option value="anytime">Anytime</option> <!-- Default anytime option -->
                <?php
                // Generate dynamic delivery dates for the next 30 days
                for ($i = 0; $i < 30; $i++) {
                    $date = date('Y-m-d', strtotime("+$i days")); // Get the date for the next X days
                    $date_label = date_i18n('l, jS F', strtotime($date)); // Format the date for display
                    
                    // Set special labels for today and tomorrow
                    if ($i === 0) {
                        $date_label = 'Today';
                    } elseif ($i === 1) {
                        $date_label = 'Tomorrow';
                    }

                    $selected = "";

                   // Check if the 'delivery_date' parameter is set and matches the date
                $selected = (isset($_GET['delivery_date']) && $_GET['delivery_date'] == $date) ? 'selected' : '';

                // Output the option for the select element
                echo '<option value="' . esc_attr($date) . '" ' . $selected . '>' . esc_html($date_label) . '</option>';

                }
                ?>
            </select>
            <button type="submit" id="apply_delivery_filter">Apply</button>
        </div>
    </form>
    
    <?php
    return ob_get_clean();
}

add_shortcode('postcode_delivery_filter', 'postcode_delivery_filter_form');

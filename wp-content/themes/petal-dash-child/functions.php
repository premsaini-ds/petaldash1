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




function enqueue_owl_carousel() {
    wp_enqueue_style('owl-carousel-css', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css');
    wp_enqueue_style('owl-theme-default-css', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css');
    
    wp_enqueue_script('jquery'); // Ensure jQuery is loaded
    wp_enqueue_script('owl-carousel-js', 'https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js', array('jquery'), null, true);
    wp_enqueue_script('custom-carousel-js', get_template_directory_uri() . '/js/custom-carousel.js', array('jquery', 'owl-carousel-js'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_owl_carousel');





// Ensure theme support for WooCommerce gallery features
function add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'add_woocommerce_support' );


function display_top_rated_products() {
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 12,
        'meta_key'       => '_wc_average_rating',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'meta_query'     => array(
            array(
                'key'     => '_wc_average_rating',
                'value'   => 0,
                'compare' => '>',
            ),
        ),
    );

    $loop = new WP_Query($args);

    if ($loop->have_posts()) {
        echo '<h2>Top Rated Products</h2>';
        echo '<div class="owl-carousel owl-theme owl-carouse-top">'; // Start carousel container

        while ($loop->have_posts()) : $loop->the_post();
            echo '<div class="item">';
            wc_get_template_part('content', 'product'); // Get the product template part
            echo '</div>'; // End item
        endwhile;

        echo '</div>'; // End carousel container
    }

    wp_reset_postdata();
}

add_action('woocommerce_after_shop_loop', 'display_top_rated_products', 15);





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





// Custom shortcode to display recent WooCommerce product reviews with Owl Carousel
function custom_recent_reviews_carousel_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'number' => 10, // Default number of reviews to show
        ),
        $atts,
        'custom_recent_reviews_carousel'
    );

    // Fetch recent approved product reviews
    $comments = get_comments(array(
        'number' => $atts['number'],
        'post_type' => 'product',
        'status' => 'approve',
    ));

    if ($comments) {
        $output = '<div class="owl-carousel reviewsCarousel">';

        foreach ($comments as $comment) {
            $rating = intval(get_comment_meta($comment->comment_ID, 'rating', true)); // Get the rating
            
            // Display review
            $output .= '<div class="review-item">';
            
            // Display star ratings (Font Awesome)
              $output .= '<div class="star-rating">';
            if ($rating && $rating > 0) {
              
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $rating) {
                        $output .= '<i class="fa fa-star text-warning"></i>'; // Full star
                    } else {
                           $output .= '<i class="fa fa-star-o text-warning"></i>'; 
                    }
                }
               
            }else{
                 $output .= '<i class="fa fa-star-o text-warning"></i><i class="fa fa-star-o text-warning"></i><i class="fa fa-star-o text-warning"></i><i class="fa fa-star-o text-warning"></i><i class="fa fa-star-o text-warning"></i>'; 
            }

            $output .= '</div>';
            // Display review content (comment)
            $output .= '<div class="review-content" >' . wp_trim_words($comment->comment_content, 20, '...') . '</div>'; // Review text

             $output .= '<div class="d-flex auther-review-time mt-2 mb-2" style="width:100%">';

            // Display review author (username)
            $output .= '<div class="review-author" style="width:50%"><strong>' . esc_html($comment->comment_author) . '</strong></div>'; // Username

            // Display time ago (how many days ago the review was posted)
            $days_ago = human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) . ' ago';
            $output .= '<div class="review-time-ago" style="width:50%">' . esc_html($days_ago) . '</div>'; // Time ago

            $output .= '</div>';



            $output .= '</div>'; // End review item
        }

        $output .= '</div>'; // End owl-carousel
    } else {
        $output = '<p>No reviews available.</p>';
    }

    return $output;
}
add_shortcode('custom_recent_reviews_carousel', 'custom_recent_reviews_carousel_shortcode');













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




// Remove the default related products action
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);


// Add custom related products section
add_action('woocommerce_after_single_product_summary', 'custom_related_products_section', 20);

function custom_related_products_section() {
    global $product;

    // Get related products
    $related_ids = $product->get_related();

    // Ensure there are related products
    if (empty($related_ids)) {
        echo '<section id="related-products" class="most-popular margin-top">
                <div class="container">
                    <h2 class="text-center mb-4">You may also like</h2>
                    <div class="owl-carousel owl-theme carousel">
                        <div class="item"><div class="text-center"><p>No related products found.</p></div></div>
                    </div>
                </div>
              </section>';
        return;
    }

    // Fetch related products excluding gift cards
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 8,
        'post__in' => $related_ids,
        'orderby' => 'post__in', // Preserve the order of IDs
        'tax_query' => array(
            array(
                'taxonomy' => 'product_type', // Change this to your taxonomy if different
                'field'    => 'slug', // You can also use 'term_id' if you prefer
                'terms'    => array('gift-card'), // Exclude products of this type
                'operator' => 'NOT IN', // Exclude products of this type
            ),
        ),
    );

    $loop = new WP_Query($args);

    // Start outputting the custom related products section
    echo '<section id="related-products" class="most-popular margin-top">
            <div class="container">
                <h2 class="text-center mb-4">You may also like</h2>
                <div id="related_products" class="owl-carousel carousel">';

    if ($loop->have_posts()) :
        while ($loop->have_posts()) : $loop->the_post();
            global $product;
            ?>
            <div class="item">
                <div class="card text-center">
                    <a href="#" class="product-hover" data-bs-toggle="modal" data-bs-target="#productModal" 
                       data-product-id="<?php echo esc_attr($product->get_id()); ?>" 
                       data-product-url="<?php echo esc_url(get_permalink()); ?>"
                       data-saved-postcode="<?php echo esc_js(get_post_meta($product->get_id(), 'product_postcode', true)); ?>"
                       data-delivery-options="<?php echo esc_attr(get_option('custom_delivery_options_data')); ?>"
                       data-delivery-after-days="<?php echo esc_attr(get_post_meta($product->get_id(), 'delivery_after_days', true)); ?>">
                        <?php the_post_thumbnail('medium', array('class' => 'img-fluid card-img-top')); ?>
                    </a>
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            <?php the_title(); ?>
                            <span class="star-rating">
                                <?php
                                $average_rating = number_format($product->get_average_rating(), 1);
                                echo '<i class="fa fa-star"></i> ' . esc_html($average_rating);
                                ?>
                            </span>
                        </h5>
                        <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 3); ?></p>
                        <p class="card-text"><strong><?php echo $product->get_price_html(); ?></strong></p>
                    </div>
                </div>
            </div>
            <?php
        endwhile;
    else :
        echo '<div class="item"><div class="text-center"><p>No related products found.</p></div></div>';
    endif;

    echo '        </div>
                </div>
            </section>';

    wp_reset_postdata();
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



    // Delivery Field
    woocommerce_wp_text_input( 
        array( 
            'id'          => 'delivery_days', 
            'label'       => __('Delivery Days', 'woocommerce'), 
            'placeholder' => 'Enter Delivery Days',
            'desc_tip'    => 'true',
            'description' => __('Enter the delivery for this product.', 'woocommerce')
        )
    );


     // Delivery Field
    woocommerce_wp_text_input( 
        array( 
            'id'          => 'delivery_after_days', 
            'label'       => __('How many days will it take to make the product after customer order', 'woocommerce'), 
            'placeholder' => 'Enter Days',
            'desc_tip'    => 'true',
            'description' => __('Enter the days for this product.', 'woocommerce')
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


     $delivery_after_days = isset($_POST['delivery_after_days']) ? sanitize_text_field($_POST['delivery_after_days']) : '';
    update_post_meta($post_id, 'delivery_after_days', $delivery_after_days);

    // Save Delivery Days
}
add_action('woocommerce_process_product_meta', 'save_postcode_and_delivery_fields');



add_filter('woocommerce_add_to_cart_validation', 'allow_only_same_product_in_cart', 10, 3);

function allow_only_same_product_in_cart($passed, $product_id, $quantity) {
    // Get the current cart items
    $cart_items = WC()->cart->get_cart();

    // Get the product type
    $product = wc_get_product($product_id);

    // Check if the product is a simple or variable product
    if ($product->is_type('simple') || $product->is_type('variable')) {
        // Loop through the cart items
        foreach ($cart_items as $cart_item_key => $cart_item) {
            $cart_product_id = $cart_item['product_id'];
            $cart_product = wc_get_product($cart_product_id);

            // Check if there's already a simple or variable product in the cart
            if ($cart_product->is_type('simple') || $cart_product->is_type('variable')) {
                wc_add_notice(__('Please Remove the existing item from your cart, You can only have one simple or variable product in your cart.', 'your-text-domain'), 'error');
                return false; // Prevent adding the new product
            }
        }
    }

    // Allow adding the product if checks pass
    return $passed;
}




// // Display postcode and delivery Days on the product page
// function display_postcode_and_delivery_fields($post) {
//     // Retrieve and display Postcode
//     $postcode = get_post_meta($post->ID, 'product_postcode', true);
//     echo '<p><strong>' . __('Postcode', 'woocommerce') . ':</strong> ' . esc_html($postcode) . '</p>';

//     // Retrieve and display Delivery Days
//       $delivery_days = get_post_meta($post->ID, 'delivery_days', true);
//      echo '<p><strong>' . __('Delivery Days', 'woocommerce') . ':</strong> ' . esc_html($delivery_days) . '</p>';
// }
// add_action('woocommerce_admin_product_data_after_tabs', 'display_postcode_and_delivery_fields');





function filter_products_by_postcode_and_delivery_days($query) {

    if (!is_admin() && $query->is_main_query() && is_shop() || is_product_category()) {

                   // Filter by postcode
            if (isset($_GET['postcode']) && !empty($_GET['postcode'])) {
                $postcode = sanitize_text_field($_GET['postcode']);
                
                // Create the meta query to search for the postcode within the stored postcodes
                $meta_query = array(
                    array(
                        'key'     => 'product_postcode',  // The custom field where postcodes are stored
                        'value'   => $postcode, // Match the exact postcode
                        'compare' => 'LIKE',               // Partial match to find the postcode in the list
                    ),
                    // To match the postcode at the start or end of the string or in between
                    'relation' => 'OR',                   // Use OR to cover different cases
                    array(
                        'key'     => 'product_postcode',
                        'value'   => $postcode . ',',     // Match if it's the first postcode in the string
                        'compare' => 'LIKE',
                    ),
                    array(
                        'key'     => 'product_postcode',
                        'value'   => ',' . $postcode,      // Match if it's the last postcode in the string
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
                $deliveryDate->setTime(0, 0, 0); // Set time to midnight

                // Create a DateTime object for the current date
                $currentDate = new DateTime();
                $currentDate->setTime(0, 0, 0); // Set time to midnight

                // Calculate the difference
                $interval = $currentDate->diff($deliveryDate);

                // Get the number of days
                $daysBetween = $interval->days+1; // Get total days between dates

                

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


/* Filter sidebar */
function postcode_delivery_filter_form() {
    ob_start(); ?>
    <form id="postcode-filter" method="GET">
    <h3 class="widget-title">Delivering to</h3>
        <div class="woocommerce-widget-layered-nav">
           <i class="fa fa-home" aria-hidden="true"></i>
            <input type="text" id="postcode" name="postcode" value="<?php echo isset($_GET['postcode']) ? esc_attr($_GET['postcode']) : ''; ?>" placeholder="postcode" />
            <button type="submit" id="apply_postcode_filter">Apply</button>
        </div>
    </form>

    <form id="delivery-date-filter" method="GET">
    <h3 class="widget-title">Delivery Date</h3>
        <div class="woocommerce-widget-layered-nav arcive-sidebar-lay">
        <i class="fa fa-calendar" aria-hidden="true"></i>

            <select id="delivery_date" name="delivery_date">
                <option value="" selected="">Select a delivery date</option>
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



/*Prem*/


add_filter('woocommerce_get_settings_pages', 'custom_delivery_settings_page');

function custom_delivery_settings_page($settings) {
    $settings[] = include 'class-wc-settings-custom-delivery.php';
    return $settings;
}


add_action('woocommerce_before_add_to_cart_button', 'add_postcode_delivery_fields_to_product_page');

function add_postcode_delivery_fields_to_product_page() {
    global $product;

    // Get the delivery options from WooCommerce settings
    $delivery_options = get_option('custom_delivery_options_data');
    $delivery_options = array_filter(array_map('trim', explode("\n", $delivery_options))); // Split and clean data

    $delivery_after_days = get_post_meta($product->get_id(), 'delivery_after_days', true);

    ?>
    <div class="form-group mb-4">
        <label for="delivery-date-<?php echo esc_attr($product->get_id()); ?>" class="form-label">Delivery Date</label>
                     <!-- Delivery Date Dropdown -->
            <select class="form-control" id="delivery-date-<?php echo esc_attr($product->get_id()); ?>" name="delivery_date" required>
                <?php
                $delivery_date_set = isset($_GET['delivery_date']) ? $_GET['delivery_date'] : null; // Get delivery date from URL if available
                $last_index = count($delivery_options) - 1; // Get the last option index

                $selectedPrice = ''; // To hold the selected price

                foreach ($delivery_options as $index => $option) {

                    list($days, $price) = explode('|', $option);

                    // Calculate future date based on days
                    $future_date = date('Y-m-d', strtotime("+$days days"));


                    // echo

                    // Disable input if future date matches delivery_after_days
                    $date_for_before_date = ($days <= $delivery_after_days) ? date('Y-m-d', strtotime("+$days days")) : '';
                    $disabledinput = ($future_date === $date_for_before_date) ? 'disabled' : '';

                    // Determine whether this option should be selected
                    $selected = '';
                    if ($delivery_date_set) {
                        // If delivery date is in URL, match it
                        $selected = ($delivery_date_set == $days) ? 'selected' : '';
                        if ($selected) {
                            $selectedPrice = $price;
                        }
                    } elseif ($index == $last_index) {
                        // If no delivery date in URL, select the last option
                        $selected = 'selected';
                        $selectedPrice = $price;
                    }


                    // Determine label for the date
                    if ($days == 1) {
                        $date_label = 'Today';
                    } elseif ($days == 2) {
                        $date_label = 'Tomorrow';
                    } else {
                        $date_label = date_i18n('l jS M', strtotime($future_date));
                    }

                    // Output the option with selected and disabled attributes
                    echo '<option ' . esc_attr($disabledinput) . ' value="' . esc_attr($days) . '" data-price="' . esc_attr($price) . '" ' . $selected . '>' . esc_html("$date_label - £$price") . '</option>';
                }
                ?>
            </select>

            <!-- Hidden field to store selected price -->
            <input type="hidden" id="selected_delivery_price" name="selected_delivery_price" value="<?php echo esc_attr($selectedPrice); ?>" />

            <!-- Postcode Validation and Output -->
            <?php  
            $postcodeArray = get_post_meta($product->get_id(), 'product_postcode', true); // Get the postcode meta

               // Convert comma-separated postcodes into an array
                $postcodeArray = explode(',', $postcodeArray);

                // Trim any whitespace
                $postcodeArray = array_map('trim', $postcodeArray);


            if (isset($_GET['postcode']) && !empty($_GET['postcode'])) {
                $enteredPostcode = $_GET['postcode'];

             
                // Check if the entered postcode exists in the array
                if (in_array($enteredPostcode, $postcodeArray)) {
                    // Postcode matches one of the saved postcodes
                    $postcode = $enteredPostcode;

                    // Output the hidden input with the entered postcode value
                    echo '<input type="hidden" id="product_postcode" name="product_postcode" value="' . esc_attr($postcode) . '" />';
                } else {
                    // Postcode not found
                    echo '<script>alert("Invalid Postcode");</script>';

                    // Output the select dropdown with available postcodes
                    echo '<select id="product_postcode" name="product_postcode">';
                    foreach ($postcodeArray as $postcodeArrayValue) {
                        echo '<option value="' . esc_attr($postcodeArrayValue) . '">' . esc_html($postcodeArrayValue) . '</option>';
                    }
                    echo '</select>';
                }
            }else{
                      // Output the select dropdown with available postcodes
                    echo '<select id="product_postcode" name="product_postcode">';
                    foreach ($postcodeArray as $postcodeArrayValue) {
                        echo '<option value="' . esc_attr($postcodeArrayValue) . '">' . esc_html($postcodeArrayValue) . '</option>';
                    }
                    echo '</select>';
            }
            ?>

   
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Update the hidden price field based on the selected delivery date
        $('#delivery-date-<?php echo esc_attr($product->get_id()); ?>').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var price = selectedOption.data('price');
            $('#selected_delivery_price').val(price);
        });
    });
    </script>
    <?php
}





add_filter('woocommerce_get_item_data', 'display_gift_message_cart_item_data', 10, 2);
function display_gift_message_cart_item_data($item_data, $cart_item) {
    if (isset($cart_item['gift_message'])) {
        $item_data[] = array(
            'key'   => __('Gift Message', 'your-text-domain'),
            'value' => sanitize_text_field($cart_item['gift_message']),
        );
    }
    return $item_data;
}




// Add or update delivery date and price in cart item data
add_filter('woocommerce_add_cart_item_data', 'add_delivery_date_to_cart_item_data', 10, 2);
function add_delivery_date_to_cart_item_data($cart_item_data, $product_id) {
    if (isset($_POST['delivery_date'])) {

        // Sanitize and retrieve the delivery days and price from POST data
        $delivery_days = sanitize_text_field($_POST['delivery_date']);
        $selected_delivery_price = sanitize_text_field($_POST['selected_delivery_price']);
        $postcode = sanitize_text_field($_POST['product_postcode']); 


        // Get the current date
        $current_date = current_time('Y-m-d');

        // Calculate the delivery date based on the number of delivery days
        $delivery_date = date('Y-m-d', strtotime($current_date . " + $delivery_days days"));

        // Format the delivery date (adjust the format as needed)
        $formatted_delivery_date = date_i18n('j F, Y', strtotime($delivery_date));

        // Add the delivery date and delivery price to the cart item data
        $cart_item_data['delivery_date'] = $formatted_delivery_date;
        $cart_item_data['delivery_price'] = $selected_delivery_price;



            if (!empty($postcode)) {
                // Add the postcode to the cart item data
                $cart_item_data['product_postcode'] = $postcode;
            }

    }
    return $cart_item_data;
}








// Check for existing product in cart and update quantity and delivery details
add_filter('woocommerce_add_to_cart', 'update_existing_cart_item', 10, 6);
function update_existing_cart_item($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    $cart = WC()->cart->get_cart();

    // Loop through the cart items
    foreach ($cart as $key => $cart_item) {
        // Check if the product is already in the cart (same product ID)
        if ($cart_item['product_id'] == $product_id) {
            // Update the delivery date and price if provided
                

            if (isset($cart_item_data['delivery_date'])) {
                 
                 WC()->cart->cart_contents[$key]['delivery_date'] = $cart_item_data['delivery_date'];
            }
            if (isset($cart_item_data['delivery_price'])) {
               
                WC()->cart->cart_contents[$key]['delivery_price'] = $cart_item_data['delivery_price'];
            }

            // Set the quantity to the new quantity provided
            WC()->cart->set_quantity($key, $quantity);

            // Stop the function after updating the item, no need to remove the newly added item
            return;
        }
    }


    $product_meta_delivery_date = get_post_meta($product_id, 'delivery_date', true);

    // Update the cart meta with the last added product ID
    $cart->add_cart_item_meta($cart_item_key, '_last_added_delivery_date', $product_meta_delivery_date);

    // If no existing item found, just return the original cart item key
    return $cart_item_key;
}







// Handle AJAX request to update the cart item's delivery date and price
add_action('wp_ajax_update_cart_delivery_date', 'update_cart_delivery_date');
add_action('wp_ajax_nopriv_update_cart_delivery_date', 'update_cart_delivery_date');

function update_cart_delivery_date() {
    // Sanitize incoming data from the AJAX request
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $selected_days = sanitize_text_field($_POST['selected_days']);
    $selected_price = sanitize_text_field($_POST['selected_price']);

    // Get the current cart
    $cart = WC()->cart->get_cart();

    // Update the specific cart item with the selected delivery date and price
    foreach ($cart as $key => $cart_item) {
        if ($key === $cart_item_key) {
            // Set the selected delivery date and price
            WC()->cart->cart_contents[$key]['delivery_date'] = date('j F, Y', strtotime("+$selected_days days"));
            WC()->cart->cart_contents[$key]['delivery_price'] = $selected_price;
            break;
        }
    }

    // Recalculate cart totals
    WC()->cart->calculate_totals();

    // Return a success response
    wp_send_json_success('Cart item updated successfully.');
}







add_action('woocommerce_before_cart_totals', 'display_last_added_product_meta');

function display_last_added_product_meta() {
    $cart = WC()->cart->get_cart(); // Get all items in the cart

    foreach ($cart as $cart_item_key => $cart_item) {
        // Get the product ID and delivery date from cart item meta
        $product_id = $cart_item['product_id'];
        $delivery_date = isset($cart_item['delivery_date']) ? $cart_item['delivery_date'] : '';

        // Only display if the delivery date exists
        if ($delivery_date) {
            // Get the delivery options from WooCommerce settings
            $delivery_options = get_option('custom_delivery_options_data');
            $delivery_options = array_filter(array_map('trim', explode("\n", $delivery_options))); // Split and clean data

            $delivery_after_days = get_post_meta($product_id, 'delivery_after_days', true);

            ?>

            <div class="form-group mb-4">
                <label for="delivery-date-<?php echo esc_attr($product_id); ?>" class="form-label">Change Delivery Date</label>
                <select class="form-control" id="delivery-date-<?php echo esc_attr($product_id); ?>" name="delivery_date[<?php echo esc_attr($cart_item_key); ?>]" required>
                    <?php
                    foreach ($delivery_options as $option) {
                        list($days, $price) = explode('|', $option);

                        // Calculate the future date based on the days
                        $future_date = date('Y-m-d', strtotime("+$days days"));
                        $future_Match = date('j F, Y', strtotime("+$days days"));

                        // Check if the 'delivery_date' parameter is set and matches the date
                        $selected = (isset($delivery_date) && $delivery_date == $future_Match) ? 'selected' : '';

                        // Disable input if future date matches delivery_after_days
                        $disabledinput = ($days <= $delivery_after_days) ? 'disabled' : '';

                        // Determine the label for the date
                        $date_label = ($days == 0) ? 'Today' : (($days == 1) ? 'Tomorrow' : date_i18n('l jS M', strtotime($future_date)));

                        // Output the option with disabled attribute if applicable
                        echo '<option ' . esc_attr($disabledinput) . ' value="' . esc_attr($days) . '" data-price="' . esc_attr($price) . '" ' . $selected . '>' . esc_html("$date_label - £$price") . '</option>';
                    }
                    ?>
                </select>
                <!-- Hidden field to store selected price -->
                <input type="hidden" id="selected_delivery_price" name="selected_delivery_price[<?php echo esc_attr($cart_item_key); ?>]" value="" />
            </div>
            <?php
        }
    }
}



// Add delivery date selection on the checkout page
add_action('woocommerce_review_order_before_payment', 'display_last_added_product_meta_checkout');
function display_last_added_product_meta_checkout() {
      $cart = WC()->cart->get_cart(); // Get all items in the cart

    foreach ($cart as $cart_item_key => $cart_item) {
        // Get the product ID and delivery date from cart item meta
        $product_id = $cart_item['product_id'];
        $delivery_date = isset($cart_item['delivery_date']) ? $cart_item['delivery_date'] : '';

        // Only display if the delivery date exists
        if ($delivery_date) {
            // Get the delivery options from WooCommerce settings
            $delivery_options = get_option('custom_delivery_options_data');
            $delivery_options = array_filter(array_map('trim', explode("\n", $delivery_options))); // Split and clean data

            $delivery_after_days = get_post_meta($product_id, 'delivery_after_days', true);

            ?>

            <div class="form-group mb-4">
                <label for="delivery-date-<?php echo esc_attr($product_id); ?>" class="form-label">Change Delivery Date</label>
                <select class="form-control" id="delivery-date-<?php echo esc_attr($product_id); ?>" name="delivery_date[<?php echo esc_attr($cart_item_key); ?>]" required>
                    <?php
                    foreach ($delivery_options as $option) {
                        list($days, $price) = explode('|', $option);

                        // Calculate the future date based on the days
                        $future_date = date('Y-m-d', strtotime("+$days days"));
                        $future_Match = date('j F, Y', strtotime("+$days days"));

                        // Check if the 'delivery_date' parameter is set and matches the date
                        $selected = (isset($delivery_date) && $delivery_date == $future_Match) ? 'selected' : '';

                        // Disable input if future date matches delivery_after_days
                        $disabledinput = ($days <= $delivery_after_days) ? 'disabled' : '';

                        // Determine the label for the date
                        $date_label = ($days == 0) ? 'Today' : (($days == 1) ? 'Tomorrow' : date_i18n('l jS M', strtotime($future_date)));

                        // Output the option with disabled attribute if applicable
                        echo '<option ' . esc_attr($disabledinput) . ' value="' . esc_attr($days) . '" data-price="' . esc_attr($price) . '" ' . $selected . '>' . esc_html("$date_label - £$price") . '</option>';
                    }
                    ?>
                </select>
                <!-- Hidden field to store selected price -->
                <input type="hidden" id="selected_delivery_price" name="selected_delivery_price[<?php echo esc_attr($cart_item_key); ?>]" value="" />
            </div>
            <?php
        }
    }
}

// Save the selected delivery date in the order meta
add_action('woocommerce_checkout_create_order_line_item', 'save_delivery_date_to_order', 10, 4);
function save_delivery_date_to_order($item, $cart_item_key, $values, $order) {
    if (isset($_POST['delivery_date'][$cart_item_key])) {
        $delivery_date = sanitize_text_field($_POST['delivery_date'][$cart_item_key]);
        $item->add_meta_data('_delivery_date', $delivery_date, true);
    }

    if (isset($_POST['selected_delivery_price'][$cart_item_key])) {
        $delivery_price = sanitize_text_field($_POST['selected_delivery_price'][$cart_item_key]);
        $item->add_meta_data('_delivery_price', $delivery_price, true);
    }
}



// Display delivery date in the cart without HTML tags
add_filter('woocommerce_get_item_data', 'display_delivery_date_in_cart', 10, 2);
function display_delivery_date_in_cart($item_data, $cart_item) {
    if (isset($cart_item['delivery_date'])) {
        $item_data[] = array(
            'name' => 'Delivery Date',
            'value' => esc_html($cart_item['delivery_date']),
        );
    }
    return $item_data;
}

add_action('woocommerce_cart_calculate_fees', 'apply_delivery_price_as_shipping_fee');
function apply_delivery_price_as_shipping_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Loop through cart items to find the delivery price
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['delivery_price'])) {
            $delivery_price = floatval($cart_item['delivery_price']);
            // Add the delivery price as a custom fee (could be used as a shipping cost)
            WC()->cart->add_fee('Delivery Date Fee', $delivery_price);
        }
    }
}








// Save delivery date to order meta
// function save_delivery_date_order_meta($order_id) {
//     foreach (WC()->cart->get_cart() as $cart_item) {
//         if (isset($cart_item['delivery_date'])) {
//             // Save to order meta
//             update_post_meta($order_id, 'Delivery Date', sanitize_text_field($cart_item['delivery_date']));
//         }
//     }
// }
// add_action('woocommerce_checkout_update_order_meta', 'save_delivery_date_order_meta');




/**
 * Add custom meta to order
 */
// Save delivery date to order item meta
function save_delivery_date_order_meta( $item, $cart_item_key, $values, $order ) {
    if ( isset( $values['delivery_date'] ) ) {
        // Add the delivery date as order item meta
        $item->add_meta_data( __( 'Delivery Date', 'delivery_date' ), $values['delivery_date'], true );
    }
}
add_action( 'woocommerce_checkout_create_order_line_item', 'save_delivery_date_order_meta', 10, 4 );



// Display delivery date in the admin order page
// function display_delivery_date_in_admin_order($order) {
//     $delivery_date = get_post_meta($order->get_id(), 'Delivery Date', true);
    
//     if (!empty($delivery_date)) {
//         echo '<p><strong>' . __('Delivery Date:', 'woocommerce') . '</strong> ' . esc_html($delivery_date) . '</p>';
//     }
// }
// add_action('woocommerce_admin_order_data_after_order_details', 'display_delivery_date_in_admin_order');



add_action('woocommerce_add_to_cart', 'update_address_with_postcode', 10, 6);
function update_address_with_postcode($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    if (isset($cart_item_data['product_postcode'])) {
        // Get the current user's shipping address
        $customer = WC()->customer;

        // Update the postcode in the shipping address
        $customer->set_shipping_postcode($cart_item_data['product_postcode']);
        
        // You may also want to update the billing postcode if needed
        $customer->set_billing_postcode($cart_item_data['product_postcode']);
        
        // Save the updated customer data
        $customer->save();
    }
}


add_filter('woocommerce_checkout_fields', 'set_postcode_readonly');
function set_postcode_readonly($fields) {
    // Get the current user's shipping address
    $customer = WC()->customer;

    // Check if the shipping postcode is set
    $shipping_postcode = $customer->get_shipping_postcode();

    if (!empty($shipping_postcode)) {
        // Set the checkout postcode field to readonly and autofill it
        $fields['billing']['billing_postcode']['custom_attributes'] = array('readonly' => 'readonly'); // Set the readonly attribute
        $fields['billing']['billing_postcode']['value'] = $shipping_postcode; // Autofill the postcode with the actual value
    }

    return $fields;
}




// Display postcode in the cart item
add_filter('woocommerce_cart_item_name', 'display_postcode_in_cart_item', 10, 3);
function display_postcode_in_cart_item($name, $cart_item, $cart_item_key) {
    if (isset($cart_item['product_postcode'])) {
        $name .= '<br><small>Delivery Postcode: ' . esc_html($cart_item['product_postcode']) . '</small>';
    }
    return $name;
}


add_action('woocommerce_checkout_create_order_line_item', 'add_postcode_to_order_items', 10, 4);
function add_postcode_to_order_items($item, $cart_item_key, $values, $order) {
    if (isset($values['product_postcode'])) {
        // Add postcode to order item meta
        $item->add_meta_data('Delivery Postcode', $values['product_postcode']);
    }
}


// Add Gift Card Popup and JavaScript functionality
function add_gift_card_popup() {
    if (is_product()) {

 global $product;
         // Check if the current product is simple or variable but not a gift card
    if ( $product->is_type( array( 'simple', 'variable' ) )) { ?>

        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var selectedGiftCardID = 0;

                  // Filter gift cards based on selected category
                        $('#giftcard-category').on('change', function() {
                            var selectedCategory = $(this).val();
                            // Show loader
                            $('#gift-card-list').html('<div class="col-12 text-center"><img src="/wp-content/themes/petal-dash-child/images/lg.gif" alt="Loading..."></div>');

                            // AJAX request to fetch filtered gift cards
                            $.ajax({
                                url: wc_add_to_cart_params.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'filter_gift_cards',
                                    category_slug: selectedCategory
                                },
                                success: function(response) {
                                    $('#gift-card-list').html(response);
                                },
                                error: function() {
                                    alert('Failed to load gift cards.');
                                    $('#gift-card-list').html('<p>Error loading gift cards. Please try again later.</p>');
                                }
                            });
                        });


                // Open the modal when Add to Cart button is clicked
                $('button.single_add_to_cart_button').on('click', function(e) {
                    e.preventDefault();
                    $('#giftCardModal').modal('show');
                });

                // Show the "Next" button when a gift card is selected
                $('input[name="gift_card_id"]').on('change', function() {
                    selectedGiftCardID = $(this).val();
                    $('#nextStep').show();


                });

                // Proceed to custom message step
                $('#nextStep').on('click', function() {
                    $('#giftCardForm').hide(); // Hide gift card selection
                    $('#giftMessageStep').show(); // Show message textarea
                });


                  // Handle "Previous" button click
                $('#previousStep').on('click', function() {
                    $('#giftMessageStep').hide();
                    $('#giftCardForm').show();
                });

                // Handle adding gift card and message to cart
                $('#addGiftCardToCart').on('click', function() {
                    var giftMessage = $('#giftMessage').val();
                    var mainProductID = $(this).data('main-product-id'); // Main product ID

                    if (selectedGiftCardID > 0) {
                        // Disable button and show loader
                        $(this).prop('disabled', true);
                        $(this).text('Adding...'); // Change button text
                        $('#loader').show(); // Show the loader

                        $.ajax({
                            url: wc_add_to_cart_params.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'add_giftcard_to_cart',
                                gift_card_id: selectedGiftCardID,
                                gift_message: giftMessage,
                                main_product_id: mainProductID // Add this dynamically
                            },
                            success: function(response) {
                                $('#loader').hide(); // Hide loader
                                if (response.success) {

                                   
                                    if(jQuery('#giftCardModal').modal('hide')){

              // Change class of the single add to cart button

                         jQuery('.single_add_to_cart_button').after(function(){
                                return '<button type="submit" name="add-to-cart" value="'+mainProductID+'" class="single_add_to_cart_button_custom button alt" style="display:none">Add to cart</button>';
                            });

                    // Trigger click event
                    jQuery('.single_add_to_cart_button_custom').trigger('click');


                                    } // Close modal

                                    
                                   
                                    // window.location.reload(); // Reload to update cart
                                } else {
                                    alert('Error adding to cart: ' + response.data.message);
                                }
                            },
                            error: function() {
                                $('#loader').hide(); // Hide loader on error
                                alert('There was an error processing your request. Please try again.');
                            },
                            complete: function() {
                                // Re-enable button and reset text
                                $('#addGiftCardToCart').prop('disabled', false).text('Add to Cart');
                            }
                        });
                    } else {
                        alert('Please select a gift card.');
                    }
                });
            });
        </script>

        <!-- Modal HTML for gift cards -->
        <div id="giftCardModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                    <!-- <h5 class="modal-title" id="productModalLabel">Luxurious Pink Roses</h5> -->
                     <h5 class="modal-title">Choose a Gift Card </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>


                    <div class="modal-body">
                        <div id="giftCardForm">
                            <div class="row">
                                   

                                    <!-- Category Filter Dropdown -->
                                    <div class="form-group">
                                        <label for="giftcard-category">Select Gift Card Category:</label>
                                        <select id="giftcard-category" class="form-control">
                                            <?php
                                            $terms = get_terms(array(
                                                'taxonomy' => 'giftcard-category',
                                                'hide_empty' => false,
                                            ));
                                            if (!empty($terms) && !is_wp_error($terms)) {
                                                foreach ($terms as $term) {
                                                    echo '<option value="' . esc_attr($term->slug) . '">' . esc_html($term->name) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                        <!-- Gift Cards List -->
                        <div id="gift-card-list" class="row">
                            <?php
                            // Fetch all gift cards
                            $args = array(
                                'post_type'      => 'product',
                                'posts_per_page' => -1,
                                'tax_query'      => array(
                                    array(
                                        'taxonomy' => 'giftcard-category',  // Specify the gift card category taxonomy
                                        'operator' => 'EXISTS',  // Ensures only products with the taxonomy are fetched
                                    ),
                                ),
                            );

                            $gift_cards = new WP_Query($args);

                            // Check if there are any gift cards available
                            if ($gift_cards->have_posts()) :
                                while ($gift_cards->have_posts()) : $gift_cards->the_post();
                                    global $product;
                                    ?>
                                    <div class="col-md-4 text-center mb-4">
                                        <div class="gift-card-item">
                                            <label>
                                                <!-- Radio button for selecting gift card -->
                                                <input type="radio" name="gift_card_id" value="<?php echo esc_attr($product->get_id()); ?>" required>

                                                <!-- Display gift card thumbnail -->
                                                <?php if (has_post_thumbnail()) : ?>
                                                    <?php the_post_thumbnail('medium', array('class' => 'img-fluid')); ?>
                                                <?php endif; ?>

                                                <!-- Gift card title -->
                                                <h5><?php the_title(); ?></h5>

                                                <!-- Display gift card price -->
                                                <p><?php echo wc_price($product->get_price()); ?></p>
                                            </label>
                                        </div>
                                    </div>
                                <?php endwhile;
                            else :
                                // Display message if no gift cards found
                                echo '<p>No gift cards found.</p>';
                            endif;

                            // Reset post data after the query
                            wp_reset_postdata();
                            ?>
                        </div>
                        </div>

                        <button id="nextStep" class="btn btn-secondary">Next</button>


                        </div>
                        <div id="giftMessageStep" style="display:none;">
                            <textarea id="giftMessage" placeholder="Enter your gift message here..."></textarea>
                           <button id="previousStep" class="btn btn-secondary">Previous</button>

                            <button id="addGiftCardToCart" class="btn btn-primary" data-main-product-id="<?php echo get_the_ID(); ?>">Add to Cart</button>
                        </div>
                        <div id="loader" style="display:none; text-align:center;">
                            <img src="/wp-content/themes/petal-dash-child/images/lg.gif" alt="Loading..." /> <!-- Replace with your loader image -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
}
add_action('wp_footer', 'add_gift_card_popup');



add_action('wp_ajax_add_giftcard_to_cart', 'add_giftcard_to_cart');
add_action('wp_ajax_nopriv_add_giftcard_to_cart', 'add_giftcard_to_cart');

function add_giftcard_to_cart() {
    $gift_card_id = intval($_POST['gift_card_id']);
    $gift_message = sanitize_text_field($_POST['gift_message']);

    $main_product_id = intval($_POST['main_product_id']); // Main product ID

    // Remove existing gift cards if any (optional based on your requirements)
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        if (has_term('gift-cards', 'product_cat', $product->get_id())) {
            WC()->cart->remove_cart_item($cart_item_key); // Remove existing gift card
        }
    }

    // Add the selected gift card to the cart
    $added_gift_card = WC()->cart->add_to_cart($gift_card_id, 1, '', '', array(
        'gift_message' => $gift_message // Add gift message as custom meta
    ));

    // Check if the gift card was added successfully
    if ($added_gift_card) {
          wp_send_json_success(array('message' => 'Gift card and main product added to cart successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to add gift card.'));
    }

    wp_die(); // End the AJAX call
}





add_action('wp_ajax_filter_gift_cards', 'filter_gift_cards');
add_action('wp_ajax_nopriv_filter_gift_cards', 'filter_gift_cards');

function filter_gift_cards() {
    $category_slug = sanitize_text_field($_POST['category_slug']);

    // Arguments for querying gift cards based on the selected category
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'giftcard-category',
                'field'    => 'slug',
                'terms'    => $category_slug,
                'operator' => $category_slug ? 'IN' : 'NOT IN', // Show all if no category is selected
            ),
        ),
    );

    $gift_cards = new WP_Query($args);

    if ($gift_cards->have_posts()) {
        while ($gift_cards->have_posts()) {
            $gift_cards->the_post();
            global $product;
            ?>
            <div class="col-md-4 text-center mb-4">
                <div class="gift-card-item">
                    <label>
                        <input type="radio" name="gift_card_id" value="<?php echo $product->get_id(); ?>" required>
                        <?php the_post_thumbnail('medium', array('class' => 'img-fluid')); ?>
                        <h5><?php the_title(); ?></h5>
                        <p><?php echo wc_price($product->get_price()); ?></p>
                    </label>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<p>No gift cards found in this category.</p>';
    }

    wp_die();
}

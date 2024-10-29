<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() || $product->is_type('gift-card')) {
	return;
}



?>

 <li <?php wc_product_class('', $product); ?>>

 <?php // Get the product permalink

// Get the product permalink
$product_permalink = esc_url(get_permalink($product->get_id()));

// Get the postcode and delivery date from the request, defaulting to empty strings if not set
$postcode = isset($_REQUEST['postcode']) && !empty($_REQUEST['postcode']) ? urlencode($_REQUEST['postcode']) : '';
$delivery_date = isset($_REQUEST['delivery_date']) && !empty($_REQUEST['delivery_date']) ? urlencode($_REQUEST['delivery_date']) : '';

// Construct the query parameters
$query_params = '?postcode=' . $postcode . '&delivery_date=' . $delivery_date;

// Ensure that both parameters are present in the URL
$query_params = '?postcode=' . ($postcode ?: '') . '&delivery_date=' . ($delivery_date ?: '');

// Output the final URL
$final_url = $product_permalink . $query_params;


?>
    <div class="card text-center">
            <a href="<?php 
            if (!isset($_REQUEST['postcode']) && !isset($_REQUEST['delivery_date']) && empty($_REQUEST['postcode'])) { 
                echo '#'; 
            } else { 
                echo $final_url;
            }
        ?>" 
        class="product-hover"
        <?php 
            if (!isset($_REQUEST['postcode']) && !isset($_REQUEST['delivery_date']) && empty($_REQUEST['postcode'])) { 
                echo 'data-bs-toggle="modal" data-bs-target="#productModal-' . esc_attr($product->get_id()) . '"';
            } 
        ?>>
            <?php
            // Get product images
            $attachment_ids = $product->get_gallery_image_ids();
            $first_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'medium')[0];
            $second_image = !empty($attachment_ids) ? wp_get_attachment_image_src($attachment_ids[0], 'medium')[0] : $first_image;

            // Display first image
            echo '<img src="' . esc_url($first_image) . '" class="img-fluid card-img-top product-image" alt="' . esc_attr(get_the_title()) . '">';

            // Display second image for hover effect
            echo '<img src="' . esc_url($second_image) . '" class="img-fluid card-img-top product-image-hover" alt="' . esc_attr(get_the_title()) . '">';
            ?>
        </a>
        <div class="card-body">
            <h5 class="card-title d-flex justify-content-between align-items-center">
                <?php the_title(); ?>
                <span class="star-rating">
                    <?php
                    // Get average rating
                    $average_rating = $product->get_average_rating();
                    $average_rating = number_format($average_rating, 1);
                    echo '<i class="fa fa-star"></i> ' . esc_html($average_rating);
                    ?>
                </span>
            </h5>
            <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 3); ?></p>
            <p class="card-text"><strong><?php echo $product->get_price_html(); ?></strong></p>
        </div>
    </div>
    <?php if(!isset($_REQUEST['postcode']) && !isset($_REQUEST['delivery_date']) && empty($_REQUEST['postcode']))  { ?>

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="productModal-<?php echo $product->get_id(); ?>" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- <h5 class="modal-title" id="productModalLabel"><?php the_title(); ?></h5> -->
                     <h5 class="modal-title">Let us know where you're sending to: </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    // Fetch the product postcode(s) stored in meta
                    $saved_postcode = get_post_meta($product->get_id(), 'product_postcode', true); // Assuming saved as a string

                    if (!empty($saved_postcode)) {
                        $saved_postcode = esc_js($saved_postcode); // Escape for use in JS
                    }



                      // Get the delivery options from WooCommerce settings
                    $delivery_options = get_option('custom_delivery_options_data');
                    $delivery_options = array_filter(array_map('trim', explode("\n", $delivery_options))); // Split and clean data
                    $delivery_after_days = get_post_meta($product->get_id(), 'delivery_after_days', true);



                    ?>
                    <form id="productForm-<?php echo $product->get_id(); ?>" action="<?php echo esc_url(get_permalink($product->get_id())); ?>" method="GET">
                        <div class="mb-3">
                            <label for="postcode-<?php echo $product->get_id(); ?>" class="form-label">Postcode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="postcode-<?php echo $product->get_id(); ?>" name="postcode" required>
                            <small id="postcode-error-<?php echo $product->get_id(); ?>" class="text-danger" style="display:none;">Invalid Postcode</small>
                        </div>
                        <div class="mb-3">
                            <label for="delivery-date-<?php echo $product->get_id(); ?>" class="form-label">Delivery Date (Optional)</label>
                            <select class="form-control" id="delivery-date-<?php echo $product->get_id(); ?>" name="delivery_date">
                                <option value="" selected="">Select a delivery date</option>
                                <?php
                                        foreach ($delivery_options as $option) {
                                            list($days, $price) = explode('|', $option);

                                            // Calculate the future date based on the days
                                            $future_date = date('Y-m-d', strtotime("+$days days"));


                                             $date_for_before_date ="";
                                            if($days <=  $delivery_after_days){
                                                $date_for_before_date = date('Y-m-d', strtotime("+$days days"));
                                            }

                                            

                                            // Disable input if future date matches delivery_after_days
                                            $disabledinput = ($future_date === $date_for_before_date) ? 'disabled' : '';

                                            // Determine the label for the date
                                            $date_label = '';
                                            if ($days == 0) {
                                                $date_label = 'Today';
                                            } elseif ($days == 1) {
                                                $date_label = 'Tomorrow';
                                            } else {
                                                $date_label = date_i18n('l jS M', strtotime($future_date));
                                            }

                                            // Output the option with disabled attribute if applicable
                                            echo '<option ' . esc_attr($disabledinput) . ' value="' . esc_attr($days) . '" data-price="' . esc_attr($price) . '">' . esc_html("$date_label - £$price") . '</option>';
                                        }
                                        ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submit-<?php echo $product->get_id(); ?>">Shop Now</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
jQuery(document).ready(function($) {
    $('#submit-<?php echo $product->get_id(); ?>').on('click', function(e) {
        var enteredPostcode = $('#postcode-<?php echo $product->get_id(); ?>').val();
        var savedPostcode = '<?php echo $saved_postcode; ?>'; // Fetch the saved postcode from PHP

        // Split the saved postcode string into an array
        var savedPostcodesArray = savedPostcode.split(',');

        // Check if the entered postcode exists in the saved postcodes array
        if ($.inArray(enteredPostcode, savedPostcodesArray) === -1) {
            e.preventDefault(); // Prevent form submission
            $('#postcode-error-<?php echo $product->get_id(); ?>').show(); // Show error message
        } else {
            $('#postcode-error-<?php echo $product->get_id(); ?>').hide(); // Hide error message
            // Allow form submission since postcode is valid
            $('#productForm-<?php echo $product->get_id(); ?>')[0].submit(); // Submit form
        }
    });
});
</script>
<?php } ?>

</li>




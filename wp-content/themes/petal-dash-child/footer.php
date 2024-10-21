<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?>



		</div><!-- .site-content -->
<div class="modal fade" id="customProductModal" tabindex="-1" aria-labelledby="customProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customProductModalLabel">Enter Details for Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="customProductForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="postcode" class="form-label">Postcode</label>
                        <input type="text" class="form-control" id="postcode" name="postcode" required>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_date" class="form-label">Delivery Date</label>
                        <input type="date" class="form-control" id="delivery_date" name="delivery_date" required>
                    </div>
                    <input type="hidden" id="product_id" name="product_id">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

		<footer id="colophon" class="site-footer">

		<div class="gift-img up-down"><img src="<?php echo get_template_directory_uri();?>/images/flowe-gift.png"></div>

			<div class="footer-widgets">
			    <div class="container">
			        <div class="row">
			            <div class="col-md-3">
			                <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			                    <?php dynamic_sidebar( 'footer-1' ); ?>
			                <?php endif; ?>
			            </div>
			            <div class="col-md-3">
			                <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
			                    <?php dynamic_sidebar( 'footer-2' ); ?>
			                <?php endif; ?>
			            </div>
			            <div class="col-md-3">
			                <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
			                    <?php dynamic_sidebar( 'footer-3' ); ?>
			                <?php endif; ?>
			            </div>
			            <div class="col-md-3">
			                <?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
			                    <?php dynamic_sidebar( 'footer-4' ); ?>
			                <?php endif; ?>
			            </div>
			        </div>
			    </div>
			</div>
			<div class="footer-widgets-second">
			<div class="container">
			<div class="site-info">
				<?php
					/**
					 * Fires before the twentysixteen footer text for footer customization.
					 *
					 * @since Twenty Sixteen 1.0
					 */
					do_action( 'twentysixteen_credits' );
				?>
				<span class="site-title">Copyright &#169; 2024 <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">Petaldash</a>. All Rights Reserved</span>
			</div><!-- .site-info -->
		</div>
			</div>
		</footer><!-- .site-footer -->
	</div><!-- .site-inner -->
</div><!-- .site -->

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Listen for changes on the delivery date dropdown
        $('select[id^="delivery-date-"]').on('change', function() {
            var selectElement = $(this);
            var cartItemKey = selectElement.attr('name').match(/\[(.*?)\]/)[1]; // Extract the cart item key
            var selectedDays = selectElement.val(); // Get selected days
            var selectedPrice = selectElement.find(':selected').data('price'); // Get selected price

            // Update the hidden input field with the selected price
            $('input[name="selected_delivery_price[' + cartItemKey + ']"]').val(selectedPrice);

            // Show loader on the Update Cart button
            var $updateButton = $('button[name="update_cart"]');
            $updateButton.prop('disabled', true); // Disable button to prevent multiple clicks
            var loader = $('<div class="loader"></div>');
            $updateButton.after(loader);
            loader.show(); // Show loader

            // Send an AJAX request to update the cart item with new delivery date and price
            $.ajax({
                url: wc_add_to_cart_params.ajax_url, // WooCommerce AJAX URL
                type: 'POST',
                data: {
                    action: 'update_cart_delivery_date',
                    cart_item_key: cartItemKey,
                    selected_days: selectedDays,
                    selected_price: selectedPrice,
                },
                success: function(response) {
                    if (response.success) {
                        // Update the cart totals without refreshing the page
                        $(document.body).trigger('updated_cart_totals');

                        // Optionally, display a success message
                        // alert('Delivery date updated successfully!');


                        location.reload();

                        // Recalculate and update the totals dynamically
                        updateCartTotals();
                    }
                },
                error: function(error) {
                    console.log('Error updating delivery date:', error);
                },
                complete: function() {
                    // Hide loader and enable button
                    loader.remove(); // Remove loader
                    $updateButton.prop('disabled', false); // Enable button
                }
            });
        });

        // Function to update cart totals dynamically
        function updateCartTotals() {

            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'woocommerce_update_cart_totals', // WooCommerce action to update cart totals
                },
                success: function(response) {
                    // Replace the cart totals section with the updated totals

                 


                    $('.cart_totals').html(response);
                }
            });
        }
    });
</script>

<script>

	jQuery(document).ready(function($) {
    jQuery('.owl-carousel.owl-carouse-top').owlCarousel({ // Ensure the class matches
        loop: true,
        margin: 20,
        nav: true,
        dots: false,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        navText: [
            '<button class="carousel-control-prev"><i class="fa fa-chevron-left"></i></button>',
            '<button class="carousel-control-next"><i class="fa fa-chevron-right"></i></button>'
        ],
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 2
            },
            1000: {
                items: 3
            }
        }
    });
});



    jQuery(document).ready(function($) {
    jQuery('#related_products').owlCarousel({ // Ensure the class matches
        loop: true,
        margin: 20,
        nav: true,
        dots: false,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        navText: [
            '<button class="carousel-control-prev"><i class="fa fa-chevron-left"></i></button>',
            '<button class="carousel-control-next"><i class="fa fa-chevron-right"></i></button>'
        ],
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 2
            },
            1000: {
                items: 3
            }
        }
    });
});

  jQuery(document).ready(function($) {
    jQuery('.owl-carousel.reviewsCarousel').owlCarousel({
        loop: true,
        margin: 20,
        nav: false,
        dots: false,
        autoplay: true,
        autoplayTimeout: 3000,
        autoplayHoverPause: true,
        responsive: {
            0: {
                items: 1 // 1 item for mobile
            },
            600: {
                items: 2 // 2 items for tablets
            },
            1000: {
                items: 5 // 5 items for desktop
            }
        }
    });
});


    </script>
      <!-- Bootstrap Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Let us know where you're order to:</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productForm" action="" method="GET">
                    <div class="mb-3">
                        <label for="postcode" class="form-label">Postcode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="postcodes" name="postcode" required>
                        <small id="postcode-error" class="text-danger" style="display:none;">Invalid Postcode</small>
                    </div>
                    <div class="mb-3">
                        <label for="delivery-date" class="form-label">Delivery Date (Optional)</label>
                        <select class="form-control" id="delivery-date" name="delivery_date">
                            <option value="" selected="">Select a delivery date</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" id="submit_post">Shop Now</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <script>
jQuery(document).ready(function() {
    // Handle product modal data
    jQuery('.product-hover').on('click', function() {
        var productId = jQuery(this).data('product-id');
        var savedPostcode = jQuery(this).data('saved-postcode');
        var deliveryOptions = jQuery(this).data('delivery-options'); // This should retrieve the string
        var afterDeliveryOptions = jQuery(this).data('delivery-after-days');
     
        let optionsArray = deliveryOptions.split("\n").map(function(option) {
            return jQuery.trim(option);  // Remove any surrounding spaces
        });

        optionsArray = optionsArray.filter(function(option) {
            return option.length > 0;  // Only keep non-empty strings
        });

    
        console.log('Splitted options array:', optionsArray);

        var productUrl = jQuery(this).data('product-url');

        // Set form action URL
        jQuery('#productForm').attr('action', productUrl);

        // Set saved postcode in the input field
        jQuery('#postcode-error').hide(); // Hide error message

        // // Populate delivery dates dropdown 
        // jQuery('#delivery-date').empty().append('<option value="" selected>Select a delivery date</option>');


        // Loop through each option
        optionsArray.forEach(function(option) {
            if (option.trim() !== '') { // Check if option is not empty
                var parts = option.split('|'); // Split by '|'
                var days = parseInt(parts[0]); // Delivery days
                var price = parts[1]; // Delivery price

                // Calculate the future date
                var futureDate = new Date();
                futureDate.setDate(futureDate.getDate() + days);

                // Determine date label
                var optionsDate = futureDate.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                var dateLabel = (days == 0) ? 'Today' : (days == 1) ? 'Tomorrow' : optionsDate;

                // Check if the delivery date is beyond a certain limit (e.g., 10 days)
                var isDisabled;

                if(afterDeliveryOptions !=''){
                            isDisabled = days <= afterDeliveryOptions; // Disable options that are more than 10 days away
                }
             

                // Append option to the select dropdown
                jQuery('#delivery-date').append('<option ' + (isDisabled ? 'disabled' : '') + ' value="' + days + '" data-price="' + price + '">' + dateLabel + ' - £' + price + '</option>');
            }
        });

        // Handle form submission

        var savedPostcodesArray;

       jQuery('#submit_post').on('click', function(e) {
                var enteredPostcode = jQuery('#postcodes').val().trim(); // Get the entered postcode and trim whitespace
              
                // Split the saved postcodes into an array
                if(savedPostcode != ''){

                savedPostcodesArray = savedPostcode.split(',');

                console.log('savedPostcodesArray', savedPostcodesArray);

                // Check if the entered postcode exists in the array
                if (savedPostcodesArray.includes(enteredPostcode)) {
                    jQuery('#postcode-error').hide(); // Hide error message
                    jQuery('#productForm').submit(); // Submit form
                } else {
                    e.preventDefault(); // Prevent form submission
                    jQuery('#postcode-error').show(); // Show error message
                }

                }else{
                    e.preventDefault(); // Prevent form submission
                    jQuery('#postcode-error').show(); // Show error message
                }


            });

    });


      // Remove the delivery date input field when the modal is closed
    jQuery('#productModal').on('hidden.bs.modal', function() {
                 jQuery('#delivery-date').empty().append('<option value="" selected>Select a delivery date</option>');
    });
});



jQuery(document).ready(function($) {
    // Prevent negative values in quantity input
    jQuery('input.qty').on('change', function() {
        var qty = $(this).val();
        if (qty <= 0) {
            alert('You cannot enter zero or negative quantity.');
            $(this).val(1); // Reset to 1
        }
    });
});


</script>

<?php wp_footer(); ?>
</body>
</html>

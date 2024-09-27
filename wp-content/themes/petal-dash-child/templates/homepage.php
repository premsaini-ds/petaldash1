<?php
/*
Template Name: Home Page
*/
get_header();

    // Fetch ACF fields

// Fetch the 'home_banner' group field
    $home_banner = get_field('home_banner'); // Replace 'option' with the correct context if needed


      // Access subfields within the 'home_banner' group
    if ($home_banner) {
        $banner_heading_1 = $home_banner['banner_heading_1'];
        $banner_heading_2 = $home_banner['banner_heading_2'];
        $banner_image = $home_banner['banner_image'];
    }

?>

<section class="flower-delivery-section " style="background-color: #f0d8d8;background-image: url(<?php echo $banner_image; ?>);">
    <div class="container">
        <div class="flower-delivery-section-content">
          
            <!-- Center Section: Main Heading, Subheading, Form, and Rating -->
                <!-- Main Heading -->
                <?php if( !empty($banner_heading_1) ): ?>
			        <h1 class="mb-3"><?php echo esc_html( $banner_heading_1 ); ?></h1>
			    <?php endif; ?>

                <!-- Subheading -->
                   <?php if( !empty($banner_heading_2) ): ?>
				        <h2 class="mb-4"><?php echo esc_html( $banner_heading_2 ); ?></h2>
				    <?php endif; ?>
                
                <!-- Form: Postcode and Shop Now button -->
              
                <form method="GET" action="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="postcode-form d-flex justify-content-center mb-4">
                <input type="text" id="postcode" name="postcode" class="form-control form-control w-20" placeholder="Enter your postcode" required>
                 <span class="home-search"><img src="<?php echo get_template_directory_uri();?>/images/search-home.png"></span>
                <!-- <input type="date" id="delivery_date" name="delivery_date" class="form-control w-50"> -->
                  <select id="delivery_date" name="delivery_date">
                  <option value="" selected="">Anytime</option> <!-- Default anytime option -->
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

                    echo '<option value="' . esc_attr($date) . '">' . esc_html($date_label) . '</option>'; // Output the date label
                }
                ?>
            </select>

                <span class="home-date"><img src="<?php echo get_template_directory_uri();?>/images/search-date.png"></span>
                <button type="submit" class="btn btn-primary ms-0">Shop Now</button>
         
                
                 </form>


                <!-- Rating Section with Font Awesome Stars -->
                <div class="rating-box">
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <i class="fa fa-star"></i>
                    <span>20,000+ Reviews</spam>
                </div>
        </div>
    </div>
</section>

<section id="most-popular-flowers" class="most-popular margin-top">
    <div class="container">
        <h2 class="text-center mb-4">Most Popular Flowers</h2>
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Fetch most popular WooCommerce products (modify for flowers category)
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => 8, // Adjust the number of products
                    'meta_key' => 'total_sales',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field'    => 'slug',
                            'terms'    => 'flowers', // Adjust to your flowers category slug
                        ),
                    ),
                );
                $loop = new WP_Query($args);
                $is_active = true;
                $product_count = 0;

                if ($loop->have_posts()) :
                    while ($loop->have_posts()) : $loop->the_post();
                        global $product;

                        if ($product_count % 4 == 0) {
                            // Start a new carousel item
                            echo '<div class="carousel-item ' . ($is_active ? 'active' : '') . '">';
                            echo '<div class="row">';
                        }
                        ?>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', array('class' => 'img-fluid card-img-top')); ?>
                                </a>
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between align-items-center">
                                        <?php the_title(); ?>
                                        <span class="star-rating">
                                            <?php
                                             // Get average rating
                                                $average_rating = $product->get_average_rating();
                                                // Format the rating to one decimal place
                                                $average_rating = number_format($average_rating, 1);
                                                // Display one star icon
                                                echo '<i class="fa fa-star"></i> ' . esc_html($average_rating);
                                            ?>
                                        </span>
                                    </h5>
                                     <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 3); // Short description ?></p>
                                    <p class="card-text"><strong><?php echo $product->get_price_html(); ?></strong></p>
                                 
                                </div>
                            </div>
                        </div>
                        <?php
                        $product_count++;

                        if ($product_count % 4 == 0 || $product_count == $loop->post_count) {
                            // Close the row and carousel item
                            echo '</div>'; // Close row
                            echo '</div>'; // Close carousel item
                            $is_active = false;
                        }
                    endwhile;
                else :
                    echo '<div class="carousel-item active"><div class="row"><div class="col-12 text-center"><p>No popular flowers found.</p></div></div></div>';
                endif;
                wp_reset_postdata();
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>




<?php

    $home_sustainability_section = get_field('home_sustainability_section'); 

    if ($home_sustainability_section) {
    	$sustainability_section_heading = $home_sustainability_section['section_heading'];
        $sustainability_col_1 = $home_sustainability_section['col_1'];
        $sustainability_col_2 = $home_sustainability_section['col_2'];
        $sustainability_col_3 = $home_sustainability_section['col_3'];
        $sustainability_col_4 = $home_sustainability_section['col_4'];
    }

?>

<section class="sustainability-section margin-top" style="background-color: #faf7f0;">
    <div class="container">
        <!-- Full-Width Section Heading -->





        
        <!-- Four-Grid Section with Icons and Headings -->
        <div class="row text-center">

        <?php if( !empty($sustainability_section_heading) ): ?>
			        <h2><?php echo esc_html( $sustainability_section_heading ); ?></h2>
			    <?php endif; ?>

            <!-- Grid Item 1: 100% Recyclable Packaging -->
            <div class="col-md-3">
<div class="sustainability-box">
            <span><img src="<?php echo get_template_directory_uri();?>/images/sustainability.png"></span>

                  <?php if( !empty($sustainability_col_1) ): ?>
			         <h4 class="grid-heading"><?php echo esc_html( $sustainability_col_1 ); ?></h4>
			    <?php endif; ?>
                  </div>
            </div>

            <!-- Grid Item 2: Ribbons Made from Recycled Bottles -->
            <div class="col-md-3">
            <div class="sustainability-box">
            <span><img src="<?php echo get_template_directory_uri();?>/images/recycled.png"></span>

                <?php if( !empty($sustainability_col_2) ): ?>
			         <h4 class="grid-heading"><?php echo esc_html( $sustainability_col_2 ); ?></h4>
			    <?php endif; ?>
                </div>
            </div>

            <!-- Grid Item 3: Zero Waste to Landfill -->
            <div class="col-md-3">
            <div class="sustainability-box">
            <span><img src="<?php echo get_template_directory_uri();?>/images/zero-flowee.png"></span>
                 <?php if( !empty($sustainability_col_3) ): ?>
			         <h4 class="grid-heading"><?php echo esc_html( $sustainability_col_3 ); ?></h4>
			    <?php endif; ?>
                 </div>
            </div>

            <!-- Grid Item 4: Carbon Neutral -->
            <div class="col-md-3">
            <div class="sustainability-box">
            <span><img src="<?php echo get_template_directory_uri();?>/images/carbon.png"></span>
                  <?php if( !empty($sustainability_col_4) ): ?>
			         <h4 class="grid-heading"><?php echo esc_html( $sustainability_col_4 ); ?></h4>
			    <?php endif; ?>
                  </div>
            </div>
        </div>
    </div>
</section>



<section id="best-selling-products" class="most-popular best-selling margin-top">
    <div class="container">
        <h2 class="text-center mb-4">Best Selling Products</h2>
        <div id="BestSellingProducts" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Fetch best-selling WooCommerce products
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => 8, // Adjust the number of products
                    'meta_key' => 'total_sales',
                    'orderby' => 'meta_value_num',
                    'order' => 'DESC',
                    // Remove the tax_query if you want all best-selling products, not just flowers
                );
                $loop = new WP_Query($args);
                $is_active = true;
                $product_count = 0;

                if ($loop->have_posts()) :
                    while ($loop->have_posts()) : $loop->the_post();
                        global $product;

                        if ($product_count % 4 == 0) {
                            // Start a new carousel item
                            echo '<div class="carousel-item ' . ($is_active ? 'active' : '') . '">';
                            echo '<div class="row">';
                        }
                        ?>
                        <div class="col-md-3">
                            <div class="card text-center">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium', array('class' => 'img-fluid card-img-top')); ?>
                                </a>
                                <div class="card-body">
                                    <h5 class="card-title d-flex justify-content-between align-items-center">
                                        <?php the_title(); ?>
                                        <span class="star-rating">
                                            <i class="fa fa-star"></i> <!-- Single star icon -->
                                            <span class="ms-2"><?php echo esc_html($product->get_average_rating()); ?></span> <!-- Show rating number -->
                                        </span>
                                    </h5>
                                    <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 10); // Short description ?></p>
                                    <p class="card-text"><strong><?php echo $product->get_price_html(); ?></strong></p>
                                </div>
                            </div>
                        </div>
                        <?php
                        $product_count++;

                        if ($product_count % 4 == 0 || $product_count == $loop->post_count) {
                            // Close the row and carousel item
                            echo '</div>'; // Close row
                            echo '</div>'; // Close carousel item
                            $is_active = false;
                        }
                    endwhile;
                else :
                    echo '<div class="carousel-item active"><div class="row"><div class="col-12 text-center"><p>No best selling products found.</p></div></div></div>';
                endif;
                wp_reset_postdata();
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#BestSellingProducts" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#BestSellingProducts" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>



<!-- Start gift-subscription -->

<?php

    $gift_subscription_home = get_field('gift_subscription_home');

    if ($gift_subscription_home) {
    	$subscription_section_heading_home = $gift_subscription_home['subscription_section_heading'];

        $subscription_box_one_title_home = $gift_subscription_home['subscription_box_one_title'];
        $subscription_box_one_description_home = $gift_subscription_home['subscription_box_one_description'];
        $subscription_box_one_button_home = $gift_subscription_home['subscription_box_one_button'];

        $subscription_box_two_title_home = $gift_subscription_home['subscription_box_two_title'];
        $subscription_box_two_description_home = $gift_subscription_home['subscription_box_two_description'];
        $subscription_box_two_button_home = $gift_subscription_home['subscription_box_two_button'];
        $subscription_right_section_image_home = $gift_subscription_home['subscription_right_section_image'];

    }

?>

<section class="gift-subscription-section margin-top">
    <div class="container">
        <div class="row">
            <!-- First Column with Heading and Two Boxes (6/6 layout) -->
            <div class="col-md-7">
            	 <?php if( !empty($subscription_section_heading_home) ): ?>
			       <h2 class="column-heading mb-4"><?php echo esc_html( $subscription_section_heading_home ); ?></h2>
			    <?php endif; ?>
               
                <div class="row">
                    <!-- Box 1: Prepaid Subscription (6/6) -->
                    <div class="col-md-6">
                        <div class="subscription-box text-center">
                        <span><img src="<?php echo get_template_directory_uri();?>/images/gift-flower1.png"></span>
                             <?php if( !empty($subscription_box_one_title_home) ): ?>
						         <h3 class="box-heading"><?php echo esc_html( $subscription_box_one_title_home ); ?></h3>
						    <?php endif; ?>

                          
                            <?php if( !empty($subscription_box_one_description_home) ): ?>
                              	  <p class="box-description"><?php echo esc_html( $subscription_box_one_description_home ); ?></p>
						    <?php endif; ?>

						       <?php if( !empty($subscription_box_one_button_home) ): ?>
                              	 <a href="<?php echo $subscription_box_one_button_home['url']; ?>" class="btn btn-primary"><?php echo $subscription_box_one_button_home['title']; ?></a>
						    <?php endif; ?>

                           
                        </div>
                    </div>

                    <!-- Box 2: Ongoing Subscription (6/6) -->
                    <div class="col-md-6">
                        <div class="subscription-box text-center">
                           
                              <?php if( !empty($subscription_box_two_title_home) ): ?>
                                <span><img src="<?php echo get_template_directory_uri();?>/images/gift-flower2.png"></span>
						         <h3 class="box-heading"><?php echo esc_html( $subscription_box_two_title_home ); ?></h3>
						    <?php endif; ?>
						      <?php if( !empty($subscription_box_two_description_home) ): ?>
                              	  <p class="box-description"><?php echo esc_html( $subscription_box_two_description_home ); ?></p>
						    <?php endif; ?>

                          
                            <?php if( !empty($subscription_box_two_button_home) ): ?>
                              	 <a href="<?php echo $subscription_box_two_button_home['url']; ?>" class="btn btn-primary"><?php echo $subscription_box_two_button_home['title']; ?></a>
						    <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Column with Full-Height Image -->
            <div class="col-md-5">
                <div class="gift-subscription-right">
            	  <?php if( !empty($subscription_right_section_image_home) ): ?>
                        <img src="<?php echo esc_html( $subscription_right_section_image_home ); ?>" alt="Flower Subscription Image" class="img-fluid full-width">
				 <?php endif; ?>
            </div>
        </div>
    </div>
</section>


<section id="recently-viewed-products" class="most-popular best-selling margin-top">
    <div class="container">
        <h2 class="text-center mb-4">Recently Viewed by You</h2>
        <div id="recentlyViewedCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Get the recently viewed products from the WooCommerce cookie
                $recently_viewed = isset($_COOKIE['woocommerce_recently_viewed']) ? explode('|', $_COOKIE['woocommerce_recently_viewed']) : array();

                // If there are no recently viewed products, display a message
                if (empty($recently_viewed)) {
                    echo '<div class="carousel-item active"><div class="row"><div class="col-12 text-center"><p>No recently viewed products.</p></div></div></div>';
                } else {
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => 8, // Adjust the number of products
                        'post__in' => $recently_viewed, // Get only the recently viewed products
                        'orderby' => 'post__in', // Preserve the order of the IDs
                    );
                    $loop = new WP_Query($args);
                    $is_active = true;
                    $product_count = 0;

                    if ($loop->have_posts()) :
                        while ($loop->have_posts()) : $loop->the_post();
                            global $product;

                            if ($product_count % 4 == 0) {
                                // Start a new carousel item
                                echo '<div class="carousel-item ' . ($is_active ? 'active' : '') . '">';
                                echo '<div class="row">';
                            }
                            ?>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail('medium', array('class' => 'img-fluid card-img-top')); ?>
                                    </a>
                                    <div class="card-body">
                                        <h5 class="card-title d-flex justify-content-between align-items-center">
                                            <?php the_title(); ?>
                                            <span class="star-rating">
                                                <i class="fa fa-star"></i> <!-- Single star icon -->
                                                <span class="ms-2"><?php echo esc_html($product->get_average_rating() ?: '0'); ?></span> <!-- Show rating number -->
                                            </span>
                                        </h5>
                                        <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 10); // Short description ?></p>
                                        <p class="card-text"><strong><?php echo $product->get_price_html(); ?></strong></p>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $product_count++;

                            if ($product_count % 4 == 0 || $product_count == $loop->post_count) {
                                // Close the row and carousel item
                                echo '</div>'; // Close row
                                echo '</div>'; // Close carousel item
                                $is_active = false;
                            }
                        endwhile;
                    else:
                        echo '<div class="carousel-item active"><div class="row"><div class="col-12 text-center"><p>No recently viewed products found.</p></div></div></div>';
                    endif;

                    wp_reset_postdata();
                }
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#recentlyViewedCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#recentlyViewedCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>



<?php

    $add_a_dose_of_love_section_home = get_field('add_a_dose_of_love_section_home'); 

    if ($add_a_dose_of_love_section_home) {
    	$background_image_dose_of_love = $add_a_dose_of_love_section_home['background_image'];

        $title_dose_of_love = $add_a_dose_of_love_section_home['title'];

        $description_dose_of_love = $add_a_dose_of_love_section_home['description'];
        $shop_now_button_dose_of_love = $add_a_dose_of_love_section_home['shop_now_button'];
    }

?>


<section class="dose-of-love-section margin-top" style="background-image: url(<?php echo $background_image_dose_of_love; ?>);">
  <div class="container">
      <!-- Left Empty Column -->


      <!-- Right Column with Text Content -->
      <div class="dose-of-love-section-contant">
      	<?php if( !empty($title_dose_of_love) ): ?>
			<h2 class="dose-heading"><?php echo esc_html( $title_dose_of_love ); ?></h2>
		<?php endif; ?>

		<?php if( !empty($description_dose_of_love) ): ?>
			 <p class="dose-description"><?php echo esc_html( $description_dose_of_love ); ?></p>
		<?php endif; ?>
      	
      	<?php if( !empty($shop_now_button_dose_of_love) ): ?>
			  <a href="<?php echo $shop_now_button_dose_of_love['url']; ?>" class="btn btn-primary dose-btn"><?php echo $shop_now_button_dose_of_love['title']; ?></a>
		<?php endif; ?>
       
    </div>
  </div>
</section>

<!-- end dose of love section -->


<?php get_footer(); ?>

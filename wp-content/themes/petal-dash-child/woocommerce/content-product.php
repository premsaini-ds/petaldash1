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
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<!-- <li <?php //wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	//do_action( 'woocommerce_before_shop_loop_item' );

	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	//do_action( 'woocommerce_before_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_product_title - 10
	 */
	//do_action( 'woocommerce_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item_title.
	 *
	 * @hooked woocommerce_template_loop_rating - 5
	 * @hooked woocommerce_template_loop_price - 10
	 */
	//do_action( 'woocommerce_after_shop_loop_item_title' );

	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	// do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
 -->

<li <?php wc_product_class('', $product); ?>>
    <div class="card text-center">
        <a href="<?php the_permalink(); ?>" class="product-hover">
            <?php
            // Get product images
            $attachment_ids = $product->get_gallery_image_ids();
            $first_image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'medium')[0];
            $second_image = !empty($attachment_ids) ? wp_get_attachment_image_src($attachment_ids[0], 'medium')[0] : $first_image;

            // Display first image
            echo '<img src="' . esc_url($first_image) . '" class="img-fluid card-img-top product-image" alt="' . esc_attr(get_the_title()) . '">';

            // Display second image for hover effect
            echo '<img src="' . esc_url($second_image) . '" class="img-fluid card-img-top product-image-hover" alt="' . esc_attr(get_the_title()) . '" >';
            ?>
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
                    // Display star icon with average rating
                    echo '<i class="fa fa-star"></i> ' . esc_html($average_rating);
                    ?>
                </span>
            </h5>
            <p class="card-text"><?php echo wp_trim_words(get_the_excerpt(), 3); ?></p>
            <p class="card-text"><strong><?php echo $product->get_price_html(); ?></strong></p>
        </div>
    </div>
</li>

<style>
.product-hover {
    position: relative;
}

.product-image,
.product-image-hover {
    width: 100%;
    height: auto;
    transition: opacity 0.3s ease;
}

.product-image-hover {
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0; /* Start hidden */
    z-index: 1; /* Ensure it's on top */
}

.product-hover:hover .product-image {
    opacity: 0 !important; /* Hide first image on hover */
}

.product-hover:hover .product-image-hover {
    opacity: 1 !important; /* Show second image on hover */
}


</style>
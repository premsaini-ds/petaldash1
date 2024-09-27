<?php
/**
 * The Template for displaying product archives (shop and category pages).
 *
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<div class="container">
    <div class="row">

        <!-- First Column: Sidebar (on Shop and Category pages only) -->
        <?php if ( is_shop() || is_product_category() ) : ?>
            <div class="col-md-3">
                <?php get_sidebar( 'shop' ); // Calls sidebar-shop.php ?>
            </div>
        <?php endif; ?>

        <!-- Second Column: Product Listings -->
        <div class="col-md-9">
            <?php woocommerce_content(); ?>
        </div>

    </div>
</div>

<?php get_footer( 'shop' ); ?>

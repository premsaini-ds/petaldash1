<?php
/**
 * The Template for displaying product archives (shop and category pages).
 *
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<div class="container category-tabs-filter-sec mb-4">
    <div class="row">
        <div class="category-tabs">
            <ul class="tabs">
                <?php
                // Get the current queried object (if on a category archive page)
                $current_category = get_queried_object();

                // Fetch product categories
                $categories = get_terms( 'product_cat', array(
                    'orderby'    => 'name',
                    'hide_empty' => true,
                ) );

                // Check if categories are available
                if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
                    foreach ( $categories as $category ) :
                        // Determine if the current category matches the loop category
                        $is_active = ( isset( $current_category->term_id ) && $current_category->term_id === $category->term_id ) ? 'active' : '';
                ?>
                    <li class="tab-item">
                        <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="<?php echo esc_attr( $is_active ); ?>">
                            <?php echo esc_html( $category->name ); ?>
                        </a>
                    </li>
                <?php
                    endforeach;
                endif;
                ?>
            </ul>
        </div>
    </div>
</div>



<div class="container">
    <div class="row">
        <!-- First Column: Sidebar (on Shop and Category pages only) -->
        <?php if ( is_shop() || is_product_category() ) : ?>
            <div class="col-md-3 sidebar-woocommerce">
                <?php get_sidebar( 'shop' ); // Calls sidebar-shop.php ?>
            </div>
        <?php endif; ?>

        <!-- Second Column: Product Listings -->
        <div class="col-md-9 woocommerproduct">
            <?php woocommerce_content(); ?>
        </div>

    </div>
</div>

<?php get_footer( 'shop' ); ?>

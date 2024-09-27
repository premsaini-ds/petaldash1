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

<?php wp_footer(); ?>
</body>
</html>

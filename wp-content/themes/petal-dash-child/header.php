<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "site-content" div.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>">
	<?php endif; ?>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<div class="site-inner">
		<a class="skip-link screen-reader-text" href="#content">
			<?php
			/* translators: Hidden accessibility text. */
			_e( 'Skip to content', 'twentysixteen' );
			?>
		</a>

		<header id="masthead" class="site-header-site">
			<div class="top_header">
			    <div class="container-fluid">
			        <div class="row">
			            <div class="col-md-4">
							<div class="topcall">
							<span><img src="<?php echo get_template_directory_uri();?>/images/call-icon.png"></span>
			                <a href="tel:0203 1234 5678"> 0203 1234 5678</a>
			            </div>
						</div>

			            <div class="col-md-4">
							<div class="top-heading">
			                <p>2 FREE Gifts with Redness Bundle Purchase!</p>
			            </div>
						</div>

			            <div class="col-md-4">
			                <!-- Social Icons -->
			                <div class="social-icons">
								<ul>
			                    <li><a href="https://facebook.com" target="_blank"><i class="fa fa-facebook-f"></i></a></li>
			                    <li><a href="https://twitter.com" target="_blank"><i class="fa fa-twitter"></i></a></li>
			                    <li><a href="https://instagram.com" target="_blank"><i class="fa fa-instagram"></i></a></li>
			                    <li><a href="https://youtube.com" target="_blank"><i class="fa fa-youtube-play" aria-hidden="true"></i></a></li>
								</ul>
							</div>
			            </div>
			        </div>
			    </div>
			</div>

			<div class="site-header-main">

				<div class="header-navigation">
				<?php if ( has_nav_menu( 'primary' ) || has_nav_menu( 'social' ) ) : ?>
					<button id="menu-toggle" class="menu-toggle"><?php _e( 'Menu', 'twentysixteen' ); ?></button>

					<div id="site-header-menu" class="site-header-menu">
						<?php if ( has_nav_menu( 'primary' ) ) : ?>
							<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'twentysixteen' ); ?>">
								<?php
									wp_nav_menu(
										array(
											'theme_location' => 'primary',
											'menu_class' => 'primary-menu',
										)
									);
								?>
							</nav><!-- .main-navigation -->
						<?php endif; ?>

						<?php if ( has_nav_menu( 'social' ) ) : ?>
							<nav id="social-navigation" class="social-navigation" aria-label="<?php esc_attr_e( 'Social Links Menu', 'twentysixteen' ); ?>">
								<?php
									wp_nav_menu(
										array(
											'theme_location' => 'social',
											'menu_class'  => 'social-links-menu',
											'depth'       => 1,
											'link_before' => '<span class="screen-reader-text">',
											'link_after'  => '</span>',
										)
									);
								?>
							</nav><!-- .social-navigation -->
						<?php endif; ?>
					</div><!-- .site-header-menu -->
				<?php endif; ?>


			
			</div><!-- .site-header-main -->

				<div class="site-branding">
					<?php twentysixteen_the_custom_logo(); ?>

					<?php if ( is_front_page() && is_home() ) : ?>
						<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
					<?php else : ?>
						<p class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></p>
						<?php
					endif;

					$description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) :
						?>
						<p class="site-description"><?php echo $description; ?></p>
					<?php endif; ?>
				</div><!-- .site-branding -->

				<div class="woocommerce_header_menu">
				    <ul class="woocommerce_list">
				          <li>

				        	<!-- <a href="<?php //echo site_url('/search'); ?>"><i class="fa fa-search"></i> Search</a> -->

				        	<?php echo do_shortcode('[ivory-search id="249" title="Custom Search Form"] '); ?>
				        </li>
				      <?php if ( is_user_logged_in() ) : ?>
						    <li><a href="<?php echo wc_get_page_permalink('myaccount'); ?>"><img src="<?php echo get_stylesheet_directory_uri();?>/images/user-login.png" class="user-icon" alt="user-icon"> My Account</a></li>
						<?php else : ?>
						    <li class="login-user"><a href="<?php echo wc_get_page_permalink('myaccount'); ?>"><img src="<?php echo get_stylesheet_directory_uri();?>/images/user-login.png" class="user-icon" alt="user-icon"> Login/Register</a></li>
						<?php endif; ?>

				        
				   	  <li class="topcart">
				        	<?php echo do_shortcode('[xoo_wsc_cart] '); ?>
				        	<!-- <a href="<?php echo wc_get_cart_url(); ?>"><i class="fa fa-shopping-cart"></i> Your Cart</a> -->
				        </li>
				    </ul>
				    </ul>
				</div>






			<?php if ( get_header_image() ) : ?>
				<?php
					/**
					 * Filters the default twentysixteen custom header sizes attribute.
					 *
					 * @since Twenty Sixteen 1.0
					 *
					 * @param string $custom_header_sizes sizes attribute
					 * for Custom Header. Default '(max-width: 709px) 85vw,
					 * (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px'.
					 */
					$custom_header_sizes = apply_filters( 'twentysixteen_custom_header_sizes', '(max-width: 709px) 85vw, (max-width: 909px) 81vw, (max-width: 1362px) 88vw, 1200px' );
				?>
				<div class="header-image">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php
						$custom_header = get_custom_header();
						$attrs         = array(
							'alt'    => get_bloginfo( 'name', 'display' ),
							'sizes'  => $custom_header_sizes,
							'height' => $custom_header->height,
							'width'  => $custom_header->width,
						);

						the_header_image_tag( $attrs );
						?>
					</a>
				</div><!-- .header-image -->
			<?php endif; // End header image check. ?>
		</header><!-- .site-header -->

		<div id="content" class="site-content">

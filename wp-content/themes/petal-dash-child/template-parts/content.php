<?php
/**
 * The template part for displaying content
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */
?><div class="col-md-4 mb-4">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
      
        <?php if ( has_post_thumbnail() ) : ?>
            <!-- Featured Image with Link -->
            <div class="post-thumbnail">
                <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail( 'medium' ); ?>
                </a>
            </div>
        <?php endif; ?>

          <header class="entry-header">
            <?php if ( is_sticky() && is_home() && ! is_paged() ) : ?>
                <span class="sticky-post"><?php _e( 'Featured', 'twentysixteen' ); ?></span>
            <?php endif; ?>

            <!-- Blog Title with Link -->
            <h2 class="entry-title">
                <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
            </h2>
        </header><!-- .entry-header -->


        <!-- Custom Excerpt with 10 Words -->
        <div class="entry-summary">
            <?php
                $content = wp_strip_all_tags( get_the_content() ); // Get content and strip all HTML tags
                $excerpt = wp_trim_words( $content, 10, '...' ); // Limit to 10 words and add "..."
                echo $excerpt;
            ?>
        </div><!-- .entry-summary -->

        <footer class="entry-footer">
            <?php twentysixteen_entry_meta(); ?>
            <?php
                edit_post_link(
                    sprintf(
                        /* translators: %s: Post title. Only visible to screen readers. */
                        __( 'Edit<span class="screen-reader-text"> "%s"</span>', 'twentysixteen' ),
                        get_the_title()
                    ),
                    '<span class="edit-link">',
                    '</span>'
                );
            ?>
        </footer><!-- .entry-footer -->
    </article><!-- #post-<?php the_ID(); ?> -->
</div><!-- .col-md-4 -->

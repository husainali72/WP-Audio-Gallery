<?php
get_header(); ?>

<div id="primary">
    <div id="content" role="main">
        <!-- Cycle through all posts -->
        <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <!-- Display featured image in right-aligned floating div -->
                <div style="float: right; margin: 10px">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
                <!-- Display Title and Author Name -->
                <strong>Title: </strong><?php the_title(); ?><br />
                <strong>Author: </strong>
                <?php echo esc_html( get_post_meta( get_the_ID(), 'gallery_author', true ) ); ?>
                <div style="margin-top: 20px; margin-bottom: 10px;">
                <?php echo do_shortcode('[wp-audio-list-in-gallery gid=' . get_the_ID() . ' autoplay="no"]'); ?>
                </div>
            </header>
            <!-- Display book review contents -->
            <div class="entry-content"><?php the_content(); ?></div>
        </article>

        <?php endwhile; ?>
    </div>
</div>
<?php get_footer(); ?>
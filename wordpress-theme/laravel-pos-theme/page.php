<?php
/**
 * The template for displaying all pages
 */

get_header();
?>

<div class="container">
    <div class="main-content">
        <?php
        while (have_posts()): the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php
        endwhile;
        ?>
    </div>
</div>

<?php get_footer(); ?>

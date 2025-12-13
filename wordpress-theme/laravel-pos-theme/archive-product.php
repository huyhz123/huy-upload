<?php
/**
 * Template for displaying product archive
 * Laravel equivalent: resources/views/products/index.blade.php
 */

get_header();
?>

<div class="container">
    <div class="main-content">
        <h1 class="page-title">Products</h1>

        <div class="products-toolbar">
            <form method="get" class="product-search">
                <input type="text" name="s" placeholder="Search products..." value="<?php echo get_search_query(); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <?php if (current_user_can('edit_posts')): ?>
                <a href="<?php echo admin_url('post-new.php?post_type=product'); ?>" class="btn btn-success">Add New Product</a>
            <?php endif; ?>
        </div>

        <div class="products-grid">
            <?php
            if (have_posts()):
                while (have_posts()): the_post();
                    $product = laravel_pos_get_product(get_the_ID());
                    ?>
                    <div class="product-card">
                        <?php if ($product['image']): ?>
                            <div class="product-image">
                                <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>">
                            </div>
                        <?php endif; ?>

                        <div class="product-info">
                            <h3><a href="<?php the_permalink(); ?>"><?php echo esc_html($product['title']); ?></a></h3>

                            <p class="product-sku">SKU: <?php echo esc_html($product['sku']); ?></p>

                            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>

                            <p class="product-stock">
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="in-stock">In Stock: <?php echo $product['stock']; ?></span>
                                <?php else: ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </p>

                            <div class="product-actions">
                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">View Details</a>
                                <?php if (current_user_can('edit_post', get_the_ID())): ?>
                                    <a href="<?php echo get_edit_post_link(); ?>" class="btn">Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;

                // Pagination
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '&laquo; Previous',
                    'next_text' => 'Next &raquo;',
                ));
            else:
                ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

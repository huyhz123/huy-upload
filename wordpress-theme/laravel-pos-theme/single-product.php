<?php
/**
 * Template for displaying single product
 * Laravel equivalent: resources/views/products/show.blade.php
 */

get_header();

if (have_posts()): while (have_posts()): the_post();
    $product = laravel_pos_get_product(get_the_ID());
?>

<div class="container">
    <div class="main-content">
        <div class="product-detail">
            <div class="product-gallery">
                <?php if ($product['image']): ?>
                    <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>">
                <?php else: ?>
                    <img src="<?php echo esc_url(LARAVEL_POS_THEME_URL . '/assets/images/no-image.png'); ?>" alt="No image">
                <?php endif; ?>
            </div>

            <div class="product-information">
                <h1><?php echo esc_html($product['title']); ?></h1>

                <div class="product-meta">
                    <p><strong>SKU:</strong> <?php echo esc_html($product['sku']); ?></p>
                    <p><strong>Category:</strong> <?php echo esc_html(ucfirst($product['category'])); ?></p>
                    <p><strong>Stock:</strong>
                        <?php if ($product['stock'] > 0): ?>
                            <span class="in-stock"><?php echo $product['stock']; ?> units available</span>
                        <?php else: ?>
                            <span class="out-of-stock">Out of stock</span>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="product-price">
                    <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                </div>

                <div class="product-description">
                    <h2>Description</h2>
                    <?php echo wp_kses_post($product['description']); ?>
                </div>

                <div class="product-actions">
                    <?php if ($product['stock'] > 0): ?>
                        <button class="btn btn-success add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                            Add to Cart
                        </button>
                    <?php endif; ?>

                    <?php if (current_user_can('edit_post', get_the_ID())): ?>
                        <a href="<?php echo get_edit_post_link(); ?>" class="btn">Edit Product</a>
                        <button class="btn btn-danger delete-product" data-product-id="<?php echo $product['id']; ?>">
                            Delete
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
endwhile; endif;

get_footer();
?>

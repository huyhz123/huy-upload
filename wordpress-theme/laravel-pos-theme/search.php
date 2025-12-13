<?php
/**
 * The template for displaying search results
 */

get_header();
?>

<div class="container">
    <div class="main-content">
        <header class="page-header">
            <h1 class="page-title">
                <?php
                printf(
                    'Search Results for: %s',
                    '<span>' . get_search_query() . '</span>'
                );
                ?>
            </h1>
        </header>

        <?php if (have_posts()): ?>
            <div class="search-results">
                <?php
                while (have_posts()): the_post();
                    $post_type = get_post_type();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('search-result-item'); ?>>
                        <div class="result-header">
                            <h2 class="result-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <span class="result-type"><?php echo ucfirst($post_type); ?></span>
                        </div>

                        <div class="result-content">
                            <?php
                            if ($post_type === 'product'):
                                $product = laravel_pos_get_product(get_the_ID());
                                ?>
                                <p><strong>SKU:</strong> <?php echo esc_html($product['sku']); ?></p>
                                <p><strong>Price:</strong> $<?php echo number_format($product['price'], 2); ?></p>
                                <p><strong>Stock:</strong> <?php echo $product['stock']; ?> units</p>
                            <?php elseif ($post_type === 'order'):
                                $order = laravel_pos_get_order(get_the_ID());
                                ?>
                                <p><strong>Order Number:</strong> <?php echo esc_html($order['order_number']); ?></p>
                                <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                                <p><strong>Status:</strong> <span class="status-badge status-<?php echo esc_attr($order['status']); ?>"><?php echo esc_html(ucfirst($order['status'])); ?></span></p>
                            <?php elseif ($post_type === 'customer'):
                                $customer = laravel_pos_get_customer(get_the_ID());
                                ?>
                                <p><strong>Email:</strong> <?php echo esc_html($customer['email']); ?></p>
                                <p><strong>Phone:</strong> <?php echo esc_html($customer['phone']); ?></p>
                            <?php else:
                                the_excerpt();
                            endif;
                            ?>
                        </div>

                        <div class="result-footer">
                            <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary">View Details</a>
                            <span class="result-date"><?php echo get_the_date(); ?></span>
                        </div>
                    </article>
                <?php
                endwhile;

                // Pagination
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '&laquo; Previous',
                    'next_text' => 'Next &raquo;',
                ));
                ?>
            </div>

        <?php else: ?>
            <div class="no-results">
                <h2>Nothing Found</h2>
                <p>Sorry, but nothing matched your search terms. Please try again with different keywords.</p>

                <div class="search-form-wrapper">
                    <form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
                        <input type="search" class="search-field" placeholder="Search..." value="<?php echo get_search_query(); ?>" name="s">
                        <button type="submit" class="search-submit btn btn-primary">Search</button>
                    </form>
                </div>

                <?php if (is_user_logged_in()): ?>
                    <div class="search-suggestions">
                        <h3>You might be looking for:</h3>
                        <ul>
                            <li><a href="<?php echo home_url('/products'); ?>">Browse Products</a></li>
                            <li><a href="<?php echo home_url('/orders'); ?>">View Orders</a></li>
                            <li><a href="<?php echo home_url('/customers'); ?>">Browse Customers</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .page-header {
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #dee2e6;
    }

    .page-title span {
        color: #007bff;
    }

    .search-results {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .search-result-item {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .result-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .result-title {
        margin: 0;
        font-size: 24px;
    }

    .result-title a {
        color: #333;
        text-decoration: none;
    }

    .result-title a:hover {
        color: #007bff;
    }

    .result-type {
        background: #e9ecef;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        color: #495057;
    }

    .result-content {
        margin-bottom: 15px;
        color: #666;
    }

    .result-content p {
        margin: 5px 0;
    }

    .result-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .result-date {
        color: #999;
        font-size: 14px;
    }

    .no-results {
        text-align: center;
        padding: 60px 20px;
    }

    .no-results h2 {
        font-size: 32px;
        margin-bottom: 20px;
    }

    .search-form-wrapper {
        max-width: 500px;
        margin: 30px auto;
    }

    .search-form {
        display: flex;
        gap: 10px;
    }

    .search-field {
        flex: 1;
        padding: 12px 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .search-suggestions {
        max-width: 400px;
        margin: 40px auto;
        text-align: left;
        background: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
    }

    .search-suggestions h3 {
        margin-top: 0;
    }

    .search-suggestions ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .search-suggestions li {
        margin-bottom: 10px;
    }

    .search-suggestions a {
        color: #007bff;
        text-decoration: none;
        font-size: 16px;
    }

    .search-suggestions a:hover {
        text-decoration: underline;
    }
</style>

<?php get_footer(); ?>

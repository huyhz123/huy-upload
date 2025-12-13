<?php
/**
 * The template for displaying 404 pages (not found)
 */

get_header();
?>

<div class="container">
    <div class="main-content error-404">
        <div class="error-content">
            <h1 class="error-title">404</h1>
            <h2>Page Not Found</h2>
            <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>

            <div class="error-search">
                <form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
                    <input type="search" class="search-field" placeholder="Search..." value="<?php echo get_search_query(); ?>" name="s">
                    <button type="submit" class="search-submit btn btn-primary">Search</button>
                </form>
            </div>

            <div class="error-actions">
                <a href="<?php echo home_url(); ?>" class="btn btn-lg btn-primary">Go to Homepage</a>
                <a href="javascript:history.back()" class="btn btn-lg btn-secondary">Go Back</a>
            </div>

            <?php if (is_user_logged_in()): ?>
                <div class="quick-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo home_url('/dashboard'); ?>">Dashboard</a></li>
                        <li><a href="<?php echo home_url('/pos'); ?>">Point of Sale</a></li>
                        <li><a href="<?php echo home_url('/products'); ?>">Products</a></li>
                        <li><a href="<?php echo home_url('/orders'); ?>">Orders</a></li>
                        <li><a href="<?php echo home_url('/customers'); ?>">Customers</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .error-404 {
        text-align: center;
        padding: 60px 20px;
    }

    .error-content {
        max-width: 600px;
        margin: 0 auto;
    }

    .error-title {
        font-size: 120px;
        margin: 0;
        font-weight: bold;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .error-404 h2 {
        font-size: 32px;
        margin: 20px 0;
    }

    .error-404 p {
        font-size: 16px;
        color: #666;
        margin-bottom: 40px;
    }

    .error-search {
        margin-bottom: 30px;
    }

    .search-form {
        display: flex;
        max-width: 400px;
        margin: 0 auto;
        gap: 10px;
    }

    .search-field {
        flex: 1;
        padding: 12px 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .error-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        margin-bottom: 40px;
    }

    .quick-links {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        margin-top: 40px;
    }

    .quick-links h3 {
        margin-top: 0;
    }

    .quick-links ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .quick-links li {
        margin-bottom: 10px;
    }

    .quick-links a {
        color: #007bff;
        text-decoration: none;
        font-size: 16px;
    }

    .quick-links a:hover {
        text-decoration: underline;
    }
</style>

<?php get_footer(); ?>

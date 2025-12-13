<?php
/**
 * The main template file
 * Laravel equivalent: resources/views/home.blade.php or resources/views/dashboard/index.blade.php
 */

get_header();
?>

<div class="container">
    <div class="main-content">
        <?php if (is_user_logged_in()): ?>
            <!-- Dashboard View for Logged-in Users -->
            <h1 class="page-title">Dashboard</h1>

            <div class="dashboard-stats">
                <?php
                $today_stats = laravel_pos_get_sales_stats('today');
                $week_stats = laravel_pos_get_sales_stats('week');
                $month_stats = laravel_pos_get_sales_stats('month');

                $total_products = wp_count_posts('product')->publish;
                $total_customers = wp_count_posts('customer')->publish;
                $total_orders = wp_count_posts('order')->publish;
                ?>

                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-value">$<?php echo number_format($today_stats['total_sales'], 2); ?></div>
                        <div class="stat-label">Today's Sales</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($today_stats['total_orders']); ?></div>
                        <div class="stat-label">Today's Orders</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">🛍️</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_products); ?></div>
                        <div class="stat-label">Total Products</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-value"><?php echo number_format($total_customers); ?></div>
                        <div class="stat-label">Total Customers</div>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Recent Orders -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Recent Orders</h2>
                        <a href="<?php echo home_url('/orders'); ?>" class="btn btn-sm">View All</a>
                    </div>
                    <div class="section-content">
                        <?php
                        $recent_orders = laravel_pos_get_orders(array('posts_per_page' => 5));
                        if (!empty($recent_orders)):
                            ?>
                            <table class="dashboard-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><a href="<?php echo get_permalink($order['id']); ?>"><?php echo esc_html($order['order_number']); ?></a></td>
                                            <td><?php echo esc_html($order['customer_name'] ?: 'Walk-in'); ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td><span class="status-badge status-<?php echo esc_attr($order['status']); ?>"><?php echo esc_html(ucfirst($order['status'])); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>No orders yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Top Products (This Month)</h2>
                    </div>
                    <div class="section-content">
                        <?php
                        $top_products = laravel_pos_get_top_products(5, 'month');
                        if (!empty($top_products)):
                            ?>
                            <div class="top-products-list">
                                <?php foreach ($top_products as $item): ?>
                                    <div class="top-product-item">
                                        <div class="product-name">
                                            <a href="<?php echo get_permalink($item['product']['id']); ?>">
                                                <?php echo esc_html($item['product']['title']); ?>
                                            </a>
                                        </div>
                                        <div class="product-stats">
                                            <span><?php echo $item['quantity_sold']; ?> sold</span>
                                            <span class="revenue">$<?php echo number_format($item['revenue'], 2); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>No sales data yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="dashboard-actions">
                <a href="<?php echo home_url('/pos'); ?>" class="action-card">
                    <div class="action-icon">🛒</div>
                    <h3>Point of Sale</h3>
                    <p>Process new sales</p>
                </a>
                <a href="<?php echo home_url('/products'); ?>" class="action-card">
                    <div class="action-icon">📦</div>
                    <h3>Products</h3>
                    <p>Manage inventory</p>
                </a>
                <a href="<?php echo home_url('/customers'); ?>" class="action-card">
                    <div class="action-icon">👥</div>
                    <h3>Customers</h3>
                    <p>View customer list</p>
                </a>
                <a href="<?php echo home_url('/orders'); ?>" class="action-card">
                    <div class="action-icon">📋</div>
                    <h3>Orders</h3>
                    <p>View all orders</p>
                </a>
            </div>

        <?php else: ?>
            <!-- Public Homepage for Non-logged-in Users -->
            <div class="homepage-hero">
                <h1>Welcome to <?php bloginfo('name'); ?></h1>
                <p><?php bloginfo('description'); ?></p>
                <div class="hero-actions">
                    <a href="<?php echo wp_login_url(); ?>" class="btn btn-primary btn-lg">Login</a>
                    <a href="<?php echo wp_registration_url(); ?>" class="btn btn-secondary btn-lg">Register</a>
                </div>
            </div>

            <div class="homepage-features">
                <div class="feature-card">
                    <div class="feature-icon">🛒</div>
                    <h3>Point of Sale</h3>
                    <p>Easy-to-use POS system for fast transactions</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Sales Analytics</h3>
                    <p>Track sales and monitor business performance</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <h3>Inventory Management</h3>
                    <p>Manage products and stock levels efficiently</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Customer Management</h3>
                    <p>Keep track of customer information and orders</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .stat-icon {
        font-size: 48px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 30px;
        margin-bottom: 40px;
    }

    .dashboard-section {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .section-header {
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header h2 {
        margin: 0;
        font-size: 18px;
    }

    .section-content {
        padding: 20px;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-table th {
        text-align: left;
        padding: 10px;
        background: #f8f9fa;
        font-weight: 600;
    }

    .dashboard-table td {
        padding: 10px;
        border-bottom: 1px solid #f0f0f0;
    }

    .top-products-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .top-product-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .top-product-item:last-child {
        border-bottom: none;
    }

    .product-stats {
        display: flex;
        gap: 15px;
        font-size: 14px;
    }

    .product-stats .revenue {
        color: #28a745;
        font-weight: 600;
    }

    .dashboard-actions {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .action-card {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.2s;
    }

    .action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .action-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .action-card h3 {
        margin: 0 0 10px 0;
        font-size: 18px;
    }

    .action-card p {
        margin: 0;
        color: #666;
        font-size: 14px;
    }

    .homepage-hero {
        text-align: center;
        padding: 80px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px;
        margin-bottom: 40px;
    }

    .homepage-hero h1 {
        font-size: 48px;
        margin: 0 0 20px 0;
    }

    .homepage-hero p {
        font-size: 20px;
        margin: 0 0 30px 0;
    }

    .hero-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
    }

    .homepage-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .feature-card {
        background: #fff;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-align: center;
    }

    .feature-icon {
        font-size: 64px;
        margin-bottom: 20px;
    }

    .feature-card h3 {
        margin: 0 0 15px 0;
        font-size: 24px;
    }

    .feature-card p {
        margin: 0;
        color: #666;
    }

    @media (max-width: 768px) {
        .dashboard-grid,
        .dashboard-actions {
            grid-template-columns: 1fr;
        }

        .homepage-hero h1 {
            font-size: 32px;
        }
    }
</style>

<?php get_footer(); ?>

<?php
/**
 * Template for displaying order archive
 * Laravel equivalent: resources/views/orders/index.blade.php
 */

get_header();
?>

<div class="container">
    <div class="main-content">
        <h1 class="page-title">Orders</h1>

        <div class="orders-toolbar">
            <form method="get" class="order-search">
                <input type="text" name="s" placeholder="Search orders..." value="<?php echo get_search_query(); ?>">
                <select name="order_status" class="status-filter">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php selected(isset($_GET['order_status']) && $_GET['order_status'] === 'pending'); ?>>Pending</option>
                    <option value="processing" <?php selected(isset($_GET['order_status']) && $_GET['order_status'] === 'processing'); ?>>Processing</option>
                    <option value="completed" <?php selected(isset($_GET['order_status']) && $_GET['order_status'] === 'completed'); ?>>Completed</option>
                    <option value="cancelled" <?php selected(isset($_GET['order_status']) && $_GET['order_status'] === 'cancelled'); ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <?php if (current_user_can('edit_posts')): ?>
                <a href="<?php echo admin_url('post-new.php?post_type=order'); ?>" class="btn btn-success">Create New Order</a>
            <?php endif; ?>
        </div>

        <div class="orders-stats">
            <?php
            $all_orders = laravel_pos_get_orders();
            $total_orders = count($all_orders);
            $pending = count(array_filter($all_orders, fn($o) => $o['status'] === 'pending'));
            $completed = count(array_filter($all_orders, fn($o) => $o['status'] === 'completed'));
            $total_revenue = array_sum(array_column($all_orders, 'total_amount'));
            ?>
            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?php echo number_format($pending); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?php echo number_format($completed); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
            </div>
        </div>

        <div class="orders-table">
            <?php
            if (have_posts()):
                ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment Status</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while (have_posts()): the_post();
                            $order = laravel_pos_get_order(get_the_ID());

                            $status_class = 'status-' . $order['status'];
                            $payment_class = 'payment-' . $order['payment_status'];
                            ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php the_permalink(); ?>">
                                            <?php echo esc_html($order['order_number']); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td><?php echo esc_html($order['customer_name']); ?></td>
                                <td class="price">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge <?php echo esc_attr($status_class); ?>">
                                        <?php echo esc_html(ucfirst($order['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="payment-badge <?php echo esc_attr($payment_class); ?>">
                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $order['payment_status']))); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary">View</a>
                                    <?php if (current_user_can('edit_post', get_the_ID())): ?>
                                        <a href="<?php echo get_edit_post_link(); ?>" class="btn btn-sm">Edit</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                        ?>
                    </tbody>
                </table>

                <?php
                // Pagination
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '&laquo; Previous',
                    'next_text' => 'Next &raquo;',
                ));
            else:
                ?>
                <div class="no-orders">
                    <p>No orders found.</p>
                    <?php if (current_user_can('edit_posts')): ?>
                        <a href="<?php echo admin_url('post-new.php?post_type=order'); ?>" class="btn btn-primary">Create Your First Order</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .orders-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .order-search {
        display: flex;
        gap: 10px;
        flex: 1;
        max-width: 600px;
    }

    .order-search input[type="text"] {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .order-search .status-filter {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-width: 150px;
    }

    .orders-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-align: center;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }

    .orders-table {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow-x: auto;
    }

    .orders-table table {
        width: 100%;
        min-width: 800px;
    }

    .orders-table th {
        background: #f8f9fa;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    .orders-table td {
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
    }

    .orders-table td.price {
        font-weight: 600;
        color: #28a745;
    }

    .orders-table td.actions {
        display: flex;
        gap: 5px;
    }

    .status-badge, .payment-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-processing {
        background: #cce5ff;
        color: #004085;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }

    .payment-unpaid {
        background: #f8d7da;
        color: #721c24;
    }

    .payment-paid {
        background: #d4edda;
        color: #155724;
    }

    .payment-partial {
        background: #fff3cd;
        color: #856404;
    }

    .payment-refunded {
        background: #d1ecf1;
        color: #0c5460;
    }

    .no-orders {
        text-align: center;
        padding: 60px 20px;
    }

    .no-orders p {
        font-size: 18px;
        color: #666;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .orders-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .order-search {
            max-width: 100%;
            flex-direction: column;
        }

        .orders-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<?php get_footer(); ?>

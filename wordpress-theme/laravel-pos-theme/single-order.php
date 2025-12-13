<?php
/**
 * Template for displaying single order
 * Laravel equivalent: resources/views/orders/show.blade.php
 */

get_header();

while (have_posts()): the_post();
    $order = laravel_pos_get_order(get_the_ID());
    $customer = $order['customer_id'] ? get_post($order['customer_id']) : null;

    $status_class = 'status-' . $order['status'];
    $payment_class = 'payment-' . $order['payment_status'];
?>

<div class="container">
    <div class="order-detail">
        <div class="order-header">
            <div class="order-header-left">
                <h1>Order <?php echo esc_html($order['order_number']); ?></h1>
                <p class="order-date">Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <div class="order-header-right">
                <span class="status-badge <?php echo esc_attr($status_class); ?>">
                    <?php echo esc_html(ucfirst($order['status'])); ?>
                </span>
                <span class="payment-badge <?php echo esc_attr($payment_class); ?>">
                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $order['payment_status']))); ?>
                </span>
            </div>
        </div>

        <div class="order-content">
            <div class="order-main">
                <!-- Order Summary -->
                <div class="card">
                    <div class="card-header">
                        <h2>Order Summary</h2>
                    </div>
                    <div class="card-body">
                        <table class="order-summary-table">
                            <tr>
                                <td><strong>Order Number:</strong></td>
                                <td><?php echo esc_html($order['order_number']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Order Status:</strong></td>
                                <td>
                                    <span class="status-badge <?php echo esc_attr($status_class); ?>">
                                        <?php echo esc_html(ucfirst($order['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Total Amount:</strong></td>
                                <td class="price">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Status:</strong></td>
                                <td>
                                    <span class="payment-badge <?php echo esc_attr($payment_class); ?>">
                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $order['payment_status']))); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Order Date:</strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Last Updated:</strong></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($order['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Customer Information -->
                <?php if ($customer): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Customer Information</h2>
                    </div>
                    <div class="card-body">
                        <table class="order-summary-table">
                            <tr>
                                <td><strong>Customer Name:</strong></td>
                                <td>
                                    <a href="<?php echo get_permalink($customer->ID); ?>">
                                        <?php echo esc_html($customer->post_title); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?php echo esc_html(get_post_meta($customer->ID, '_customer_email', true)); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td><?php echo esc_html(get_post_meta($customer->ID, '_customer_phone', true)); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Shipping & Billing Address -->
                <?php if ($order['shipping_address'] || $order['billing_address']): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Addresses</h2>
                    </div>
                    <div class="card-body">
                        <div class="addresses-grid">
                            <?php if ($order['shipping_address']): ?>
                            <div class="address-block">
                                <h3>Shipping Address</h3>
                                <p><?php echo nl2br(esc_html($order['shipping_address'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if ($order['billing_address']): ?>
                            <div class="address-block">
                                <h3>Billing Address</h3>
                                <p><?php echo nl2br(esc_html($order['billing_address'])); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Order Notes -->
                <?php if ($order['notes']): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Order Notes</h2>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(esc_html($order['notes'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="order-sidebar">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="action-buttons">
                            <?php if (current_user_can('edit_post', get_the_ID())): ?>
                                <a href="<?php echo get_edit_post_link(); ?>" class="btn btn-primary btn-block">
                                    Edit Order
                                </a>
                            <?php endif; ?>

                            <button onclick="window.print()" class="btn btn-secondary btn-block">
                                Print Order
                            </button>

                            <a href="<?php echo home_url('/orders'); ?>" class="btn btn-secondary btn-block">
                                Back to Orders
                            </a>

                            <?php if (current_user_can('delete_post', get_the_ID())): ?>
                                <button onclick="deleteOrder(<?php echo get_the_ID(); ?>)" class="btn btn-danger btn-block">
                                    Delete Order
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header">
                        <h2>Quick Info</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-stat">
                            <span class="stat-label">Created</span>
                            <span class="stat-value"><?php echo human_time_diff(strtotime($order['created_at']), current_time('timestamp')) . ' ago'; ?></span>
                        </div>
                        <div class="quick-stat">
                            <span class="stat-label">Updated</span>
                            <span class="stat-value"><?php echo human_time_diff(strtotime($order['updated_at']), current_time('timestamp')) . ' ago'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .order-detail {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 15px;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #dee2e6;
    }

    .order-header h1 {
        margin: 0 0 10px 0;
        font-size: 32px;
    }

    .order-date {
        color: #666;
        margin: 0;
    }

    .order-header-right {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .order-content {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 30px;
    }

    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .card-header {
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
    }

    .card-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .card-body {
        padding: 20px;
    }

    .order-summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .order-summary-table tr {
        border-bottom: 1px solid #f0f0f0;
    }

    .order-summary-table tr:last-child {
        border-bottom: none;
    }

    .order-summary-table td {
        padding: 12px 0;
    }

    .order-summary-table td:first-child {
        width: 180px;
        color: #666;
    }

    .order-summary-table td.price {
        font-size: 24px;
        font-weight: bold;
        color: #28a745;
    }

    .addresses-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .address-block h3 {
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 10px 0;
        color: #666;
    }

    .address-block p {
        margin: 0;
        line-height: 1.6;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-block {
        display: block;
        width: 100%;
        text-align: center;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .quick-stat {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .quick-stat:last-child {
        border-bottom: none;
    }

    .quick-stat .stat-label {
        color: #666;
        font-size: 14px;
    }

    .quick-stat .stat-value {
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .order-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .order-header-right {
            margin-top: 15px;
        }

        .order-content {
            grid-template-columns: 1fr;
        }

        .addresses-grid {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        .order-sidebar,
        .site-header,
        .site-footer {
            display: none;
        }

        .order-content {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
function deleteOrder(orderId) {
    if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
        return;
    }

    fetch('/wp-json/laravel-pos/v1/orders/' + orderId, {
        method: 'DELETE',
        headers: {
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Order deleted successfully');
            window.location.href = '<?php echo home_url('/orders'); ?>';
        } else {
            alert('Failed to delete order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the order');
    });
}
</script>

<?php
endwhile;

get_footer();
?>

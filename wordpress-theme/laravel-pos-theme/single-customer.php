<?php
/**
 * Template for displaying single customer
 * Laravel equivalent: resources/views/customers/show.blade.php
 */

get_header();

while (have_posts()): the_post();
    $customer = laravel_pos_get_customer(get_the_ID());
    $orders = laravel_pos_get_customer_orders(get_the_ID());
    $total_orders = count($orders);
    $total_spent = array_sum(array_column($orders, 'total_amount'));
    $avg_order = $total_orders > 0 ? $total_spent / $total_orders : 0;
?>

<div class="container">
    <div class="customer-detail">
        <div class="customer-header">
            <div class="customer-header-left">
                <h1><?php echo esc_html($customer['name']); ?></h1>
                <?php if ($customer['company']): ?>
                    <p class="customer-company"><?php echo esc_html($customer['company']); ?></p>
                <?php endif; ?>
            </div>
            <div class="customer-header-right">
                <span class="customer-type-badge type-<?php echo esc_attr($customer['customer_type']); ?>">
                    <?php echo esc_html(ucfirst($customer['customer_type'])); ?>
                </span>
            </div>
        </div>

        <div class="customer-stats-summary">
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <div class="stat-value">$<?php echo number_format($total_spent, 2); ?></div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-value">$<?php echo number_format($avg_order, 2); ?></div>
                    <div class="stat-label">Average Order</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-value"><?php echo date('M Y', strtotime($customer['created_at'])); ?></div>
                    <div class="stat-label">Member Since</div>
                </div>
            </div>
        </div>

        <div class="customer-content">
            <div class="customer-main">
                <!-- Contact Information -->
                <div class="card">
                    <div class="card-header">
                        <h2>Contact Information</h2>
                    </div>
                    <div class="card-body">
                        <table class="info-table">
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><a href="mailto:<?php echo esc_attr($customer['email']); ?>"><?php echo esc_html($customer['email']); ?></a></td>
                            </tr>
                            <?php if ($customer['phone']): ?>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td><a href="tel:<?php echo esc_attr($customer['phone']); ?>"><?php echo esc_html($customer['phone']); ?></a></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($customer['tax_number']): ?>
                            <tr>
                                <td><strong>Tax Number:</strong></td>
                                <td><?php echo esc_html($customer['tax_number']); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Address Information -->
                <?php if ($customer['address'] || $customer['city']): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Address</h2>
                    </div>
                    <div class="card-body">
                        <address>
                            <?php if ($customer['address']): ?>
                                <?php echo esc_html($customer['address']); ?><br>
                            <?php endif; ?>
                            <?php if ($customer['city']): ?>
                                <?php echo esc_html($customer['city']); ?><?php echo $customer['state'] ? ', ' . esc_html($customer['state']) : ''; ?> <?php echo esc_html($customer['postal_code']); ?><br>
                            <?php endif; ?>
                            <?php if ($customer['country']): ?>
                                <?php echo esc_html($customer['country']); ?>
                            <?php endif; ?>
                        </address>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Order History -->
                <div class="card">
                    <div class="card-header">
                        <h2>Order History</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($orders)): ?>
                            <div class="orders-table-wrapper">
                                <table class="orders-table">
                                    <thead>
                                        <tr>
                                            <th>Order Number</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo get_permalink($order['id']); ?>">
                                                        <?php echo esc_html($order['order_number']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo esc_attr($order['status']); ?>">
                                                        <?php echo esc_html(ucfirst($order['status'])); ?>
                                                    </span>
                                                </td>
                                                <td class="price">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                                <td>
                                                    <a href="<?php echo get_permalink($order['id']); ?>" class="btn btn-sm btn-primary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No orders yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Notes -->
                <?php if ($customer['notes']): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Notes</h2>
                    </div>
                    <div class="card-body">
                        <p><?php echo nl2br(esc_html($customer['notes'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="customer-sidebar">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h2>Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="action-buttons">
                            <?php if (current_user_can('edit_post', get_the_ID())): ?>
                                <a href="<?php echo get_edit_post_link(); ?>" class="btn btn-primary btn-block">
                                    Edit Customer
                                </a>
                            <?php endif; ?>

                            <a href="mailto:<?php echo esc_attr($customer['email']); ?>" class="btn btn-secondary btn-block">
                                Send Email
                            </a>

                            <a href="<?php echo home_url('/customers'); ?>" class="btn btn-secondary btn-block">
                                Back to Customers
                            </a>

                            <?php if (current_user_can('delete_post', get_the_ID())): ?>
                                <button onclick="deleteCustomer(<?php echo get_the_ID(); ?>)" class="btn btn-danger btn-block">
                                    Delete Customer
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="card">
                    <div class="card-header">
                        <h2>Quick Info</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-stat">
                            <span class="stat-label">Customer Since</span>
                            <span class="stat-value"><?php echo human_time_diff(strtotime($customer['created_at']), current_time('timestamp')) . ' ago'; ?></span>
                        </div>
                        <div class="quick-stat">
                            <span class="stat-label">Last Updated</span>
                            <span class="stat-value"><?php echo human_time_diff(strtotime($customer['updated_at']), current_time('timestamp')) . ' ago'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .customer-detail {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 15px;
    }

    .customer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #dee2e6;
    }

    .customer-header h1 {
        margin: 0 0 5px 0;
        font-size: 32px;
    }

    .customer-company {
        color: #666;
        margin: 0;
        font-size: 16px;
    }

    .customer-stats-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .customer-stats-summary .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        font-size: 32px;
    }

    .stat-content .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .stat-content .stat-label {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .customer-content {
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

    .info-table {
        width: 100%;
        border-collapse: collapse;
    }

    .info-table tr {
        border-bottom: 1px solid #f0f0f0;
    }

    .info-table tr:last-child {
        border-bottom: none;
    }

    .info-table td {
        padding: 12px 0;
    }

    .info-table td:first-child {
        width: 150px;
        color: #666;
    }

    .orders-table-wrapper {
        overflow-x: auto;
    }

    .orders-table {
        width: 100%;
        min-width: 600px;
    }

    .orders-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    .orders-table td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }

    .orders-table td.price {
        font-weight: 600;
        color: #28a745;
    }

    address {
        font-style: normal;
        line-height: 1.8;
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
        .customer-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .customer-header-right {
            margin-top: 15px;
        }

        .customer-stats-summary {
            grid-template-columns: repeat(2, 1fr);
        }

        .customer-content {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
function deleteCustomer(customerId) {
    if (!confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        return;
    }

    fetch('/wp-json/laravel-pos/v1/customers/' + customerId, {
        method: 'DELETE',
        headers: {
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Customer deleted successfully');
            window.location.href = '<?php echo home_url('/customers'); ?>';
        } else {
            alert('Failed to delete customer');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the customer');
    });
}
</script>

<?php
endwhile;

get_footer();
?>

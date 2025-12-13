<?php
/**
 * Template for displaying customer archive
 * Laravel equivalent: resources/views/customers/index.blade.php
 */

get_header();
?>

<div class="container">
    <div class="main-content">
        <h1 class="page-title">Customers</h1>

        <div class="customers-toolbar">
            <form method="get" class="customer-search">
                <input type="text" name="s" placeholder="Search customers..." value="<?php echo get_search_query(); ?>">
                <select name="customer_type" class="type-filter">
                    <option value="">All Types</option>
                    <option value="individual" <?php selected(isset($_GET['customer_type']) && $_GET['customer_type'] === 'individual'); ?>>Individual</option>
                    <option value="business" <?php selected(isset($_GET['customer_type']) && $_GET['customer_type'] === 'business'); ?>>Business</option>
                    <option value="wholesale" <?php selected(isset($_GET['customer_type']) && $_GET['customer_type'] === 'wholesale'); ?>>Wholesale</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <?php if (current_user_can('edit_posts')): ?>
                <a href="<?php echo admin_url('post-new.php?post_type=customer'); ?>" class="btn btn-success">Add New Customer</a>
            <?php endif; ?>
        </div>

        <div class="customers-stats">
            <?php
            $all_customers = laravel_pos_get_customers();
            $total_customers = count($all_customers);
            $individual = count(array_filter($all_customers, fn($c) => $c['customer_type'] === 'individual'));
            $business = count(array_filter($all_customers, fn($c) => $c['customer_type'] === 'business'));
            $wholesale = count(array_filter($all_customers, fn($c) => $c['customer_type'] === 'wholesale'));
            ?>
            <div class="stat-card">
                <div class="stat-label">Total Customers</div>
                <div class="stat-value"><?php echo number_format($total_customers); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Individual</div>
                <div class="stat-value"><?php echo number_format($individual); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Business</div>
                <div class="stat-value"><?php echo number_format($business); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Wholesale</div>
                <div class="stat-value"><?php echo number_format($wholesale); ?></div>
            </div>
        </div>

        <div class="customers-grid">
            <?php
            if (have_posts()):
                while (have_posts()): the_post();
                    $customer = laravel_pos_get_customer(get_the_ID());
                    $orders = laravel_pos_get_customer_orders(get_the_ID());
                    $total_orders = count($orders);
                    $total_spent = array_sum(array_column($orders, 'total_amount'));
                    ?>
                    <div class="customer-card">
                        <div class="customer-card-header">
                            <h3><a href="<?php the_permalink(); ?>"><?php echo esc_html($customer['name']); ?></a></h3>
                            <span class="customer-type-badge type-<?php echo esc_attr($customer['customer_type']); ?>">
                                <?php echo esc_html(ucfirst($customer['customer_type'])); ?>
                            </span>
                        </div>

                        <div class="customer-card-body">
                            <?php if ($customer['company']): ?>
                                <p class="customer-company">
                                    <strong>Company:</strong> <?php echo esc_html($customer['company']); ?>
                                </p>
                            <?php endif; ?>

                            <p class="customer-contact">
                                <strong>Email:</strong> <a href="mailto:<?php echo esc_attr($customer['email']); ?>"><?php echo esc_html($customer['email']); ?></a>
                            </p>

                            <?php if ($customer['phone']): ?>
                                <p class="customer-contact">
                                    <strong>Phone:</strong> <a href="tel:<?php echo esc_attr($customer['phone']); ?>"><?php echo esc_html($customer['phone']); ?></a>
                                </p>
                            <?php endif; ?>

                            <?php if ($customer['city']): ?>
                                <p class="customer-location">
                                    <strong>Location:</strong> <?php echo esc_html($customer['city']); ?><?php echo $customer['state'] ? ', ' . esc_html($customer['state']) : ''; ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="customer-card-footer">
                            <div class="customer-stats">
                                <div class="stat">
                                    <span class="stat-value"><?php echo $total_orders; ?></span>
                                    <span class="stat-label">Orders</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-value">$<?php echo number_format($total_spent, 0); ?></span>
                                    <span class="stat-label">Total Spent</span>
                                </div>
                            </div>

                            <div class="customer-actions">
                                <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary">View Details</a>
                                <?php if (current_user_can('edit_post', get_the_ID())): ?>
                                    <a href="<?php echo get_edit_post_link(); ?>" class="btn btn-sm">Edit</a>
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
                <div class="no-customers">
                    <p>No customers found.</p>
                    <?php if (current_user_can('edit_posts')): ?>
                        <a href="<?php echo admin_url('post-new.php?post_type=customer'); ?>" class="btn btn-primary">Add Your First Customer</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .customers-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .customer-search {
        display: flex;
        gap: 10px;
        flex: 1;
        max-width: 600px;
    }

    .customer-search input[type="text"] {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .customer-search .type-filter {
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        min-width: 150px;
    }

    .customers-stats {
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

    .customers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .customer-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .customer-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .customer-card-header {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .customer-card-header h3 {
        margin: 0;
        font-size: 18px;
    }

    .customer-card-header h3 a {
        color: #333;
        text-decoration: none;
    }

    .customer-card-header h3 a:hover {
        color: #007bff;
    }

    .customer-type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .type-individual {
        background: #d4edda;
        color: #155724;
    }

    .type-business {
        background: #cce5ff;
        color: #004085;
    }

    .type-wholesale {
        background: #fff3cd;
        color: #856404;
    }

    .customer-card-body {
        padding: 20px;
    }

    .customer-card-body p {
        margin: 0 0 10px 0;
        font-size: 14px;
    }

    .customer-card-body p:last-child {
        margin-bottom: 0;
    }

    .customer-card-body a {
        color: #007bff;
        text-decoration: none;
    }

    .customer-card-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }

    .customer-stats {
        display: flex;
        gap: 30px;
        margin-bottom: 15px;
    }

    .customer-stats .stat {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .customer-stats .stat-value {
        font-size: 20px;
        font-weight: bold;
        color: #333;
    }

    .customer-stats .stat-label {
        font-size: 12px;
        color: #666;
    }

    .customer-actions {
        display: flex;
        gap: 10px;
    }

    .no-customers {
        text-align: center;
        padding: 60px 20px;
        grid-column: 1 / -1;
    }

    .no-customers p {
        font-size: 18px;
        color: #666;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .customers-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .customer-search {
            max-width: 100%;
            flex-direction: column;
        }

        .customers-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .customers-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php get_footer(); ?>

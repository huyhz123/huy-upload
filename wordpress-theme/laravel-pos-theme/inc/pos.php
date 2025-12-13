<?php
/**
 * POS (Point of Sale) Module
 * Laravel equivalent: app/Http/Controllers/POSController.php
 */

/**
 * Process POS sale
 * Laravel: POSController::processSale()
 */
function laravel_pos_process_sale($data) {
    // Validate data
    if (empty($data['items']) || !is_array($data['items'])) {
        return array('success' => false, 'message' => 'No items in cart');
    }

    // Calculate totals
    $subtotal = 0;
    $items_data = array();

    foreach ($data['items'] as $item) {
        $product = laravel_pos_get_product($item['product_id']);

        if (!$product) {
            return array('success' => false, 'message' => 'Product not found: ' . $item['product_id']);
        }

        // Check stock
        if ($product['stock'] < $item['quantity']) {
            return array('success' => false, 'message' => 'Insufficient stock for: ' . $product['title']);
        }

        $line_total = $product['price'] * $item['quantity'];
        $subtotal += $line_total;

        $items_data[] = array(
            'product_id' => $product['id'],
            'product_name' => $product['title'],
            'sku' => $product['sku'],
            'price' => $product['price'],
            'quantity' => $item['quantity'],
            'total' => $line_total,
        );
    }

    // Calculate tax and total
    $tax_rate = isset($data['tax_rate']) ? floatval($data['tax_rate']) : 0;
    $tax_amount = $subtotal * ($tax_rate / 100);
    $discount = isset($data['discount']) ? floatval($data['discount']) : 0;
    $total = $subtotal + $tax_amount - $discount;

    // Create order
    $order_number = 'ORD-' . date('Ymd') . '-' . str_pad(wp_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    $order_data = array(
        'order_number' => $order_number,
        'customer_id' => isset($data['customer_id']) ? $data['customer_id'] : null,
        'total_amount' => $total,
        'order_status' => 'completed',
        'payment_method' => isset($data['payment_method']) ? $data['payment_method'] : 'cash',
        'payment_status' => 'paid',
    );

    $order_id = laravel_pos_create_order($order_data);

    if (!$order_id) {
        return array('success' => false, 'message' => 'Failed to create order');
    }

    // Save order items as post meta
    update_post_meta($order_id, '_order_items', $items_data);
    update_post_meta($order_id, '_order_subtotal', $subtotal);
    update_post_meta($order_id, '_order_tax_rate', $tax_rate);
    update_post_meta($order_id, '_order_tax_amount', $tax_amount);
    update_post_meta($order_id, '_order_discount', $discount);

    // Update product stock
    foreach ($data['items'] as $item) {
        $product = laravel_pos_get_product($item['product_id']);
        $new_stock = $product['stock'] - $item['quantity'];

        laravel_pos_update_product($item['product_id'], array(
            'stock' => max(0, $new_stock),
        ));
    }

    // Create notification if customer exists
    if (!empty($order_data['customer_id'])) {
        laravel_pos_create_notification(array(
            'title' => 'Order Placed: ' . $order_number,
            'message' => 'Your order has been successfully placed. Total: $' . number_format($total, 2),
            'type' => 'order',
            'priority' => 'normal',
            'status' => 'unread',
            'user_id' => get_post_meta($order_data['customer_id'], '_customer_user_id', true),
            'link' => get_permalink($order_id),
            'action_text' => 'View Order',
            'icon' => '📦',
        ));
    }

    return array(
        'success' => true,
        'message' => 'Sale processed successfully',
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total' => $total,
    );
}

/**
 * Get cart summary
 */
function laravel_pos_get_cart_summary($items, $tax_rate = 0, $discount = 0) {
    $subtotal = 0;
    $total_items = 0;

    foreach ($items as $item) {
        $product = laravel_pos_get_product($item['product_id']);
        if ($product) {
            $subtotal += $product['price'] * $item['quantity'];
            $total_items += $item['quantity'];
        }
    }

    $tax_amount = $subtotal * ($tax_rate / 100);
    $total = $subtotal + $tax_amount - $discount;

    return array(
        'subtotal' => $subtotal,
        'tax_rate' => $tax_rate,
        'tax_amount' => $tax_amount,
        'discount' => $discount,
        'total' => $total,
        'total_items' => $total_items,
    );
}

/**
 * Get sales statistics
 * Laravel: POSController::getSalesStats()
 */
function laravel_pos_get_sales_stats($period = 'today') {
    $args = array(
        'post_type' => 'order',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_order_status',
                'value' => 'completed',
            ),
        ),
    );

    // Add date filter based on period
    switch ($period) {
        case 'today':
            $args['date_query'] = array(
                array(
                    'after' => 'today',
                ),
            );
            break;
        case 'week':
            $args['date_query'] = array(
                array(
                    'after' => '1 week ago',
                ),
            );
            break;
        case 'month':
            $args['date_query'] = array(
                array(
                    'after' => '1 month ago',
                ),
            );
            break;
        case 'year':
            $args['date_query'] = array(
                array(
                    'after' => '1 year ago',
                ),
            );
            break;
    }

    $orders = laravel_pos_get_orders($args);

    $total_sales = 0;
    $total_orders = count($orders);
    $total_items = 0;

    foreach ($orders as $order) {
        $total_sales += $order['total_amount'];
        $order_items = get_post_meta($order['id'], '_order_items', true);
        if (is_array($order_items)) {
            foreach ($order_items as $item) {
                $total_items += $item['quantity'];
            }
        }
    }

    $avg_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;

    return array(
        'period' => $period,
        'total_sales' => $total_sales,
        'total_orders' => $total_orders,
        'total_items' => $total_items,
        'avg_order_value' => $avg_order_value,
    );
}

/**
 * Get top selling products
 */
function laravel_pos_get_top_products($limit = 10, $period = 'all') {
    $args = array(
        'post_type' => 'order',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_order_status',
                'value' => 'completed',
            ),
        ),
    );

    if ($period !== 'all') {
        switch ($period) {
            case 'today':
                $args['date_query'] = array(array('after' => 'today'));
                break;
            case 'week':
                $args['date_query'] = array(array('after' => '1 week ago'));
                break;
            case 'month':
                $args['date_query'] = array(array('after' => '1 month ago'));
                break;
        }
    }

    $orders = get_posts($args);
    $products_sales = array();

    foreach ($orders as $order) {
        $order_items = get_post_meta($order->ID, '_order_items', true);
        if (is_array($order_items)) {
            foreach ($order_items as $item) {
                $product_id = $item['product_id'];
                if (!isset($products_sales[$product_id])) {
                    $products_sales[$product_id] = array(
                        'product_id' => $product_id,
                        'quantity' => 0,
                        'revenue' => 0,
                    );
                }
                $products_sales[$product_id]['quantity'] += $item['quantity'];
                $products_sales[$product_id]['revenue'] += $item['total'];
            }
        }
    }

    // Sort by quantity
    usort($products_sales, function($a, $b) {
        return $b['quantity'] - $a['quantity'];
    });

    // Get product details and limit
    $top_products = array();
    $count = 0;

    foreach ($products_sales as $sale) {
        if ($count >= $limit) break;

        $product = laravel_pos_get_product($sale['product_id']);
        if ($product) {
            $top_products[] = array(
                'product' => $product,
                'quantity_sold' => $sale['quantity'],
                'revenue' => $sale['revenue'],
            );
            $count++;
        }
    }

    return $top_products;
}

// REST API Endpoints

function laravel_pos_register_pos_rest_routes() {
    // POST /wp-json/laravel-pos/v1/pos/process-sale
    register_rest_route('laravel-pos/v1', '/pos/process-sale', array(
        'methods' => 'POST',
        'callback' => 'laravel_pos_api_process_sale',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    // GET /wp-json/laravel-pos/v1/pos/stats
    register_rest_route('laravel-pos/v1', '/pos/stats', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_stats',
        'permission_callback' => '__return_true',
    ));

    // GET /wp-json/laravel-pos/v1/pos/top-products
    register_rest_route('laravel-pos/v1', '/pos/top-products', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_top_products',
        'permission_callback' => '__return_true',
    ));

    // GET /wp-json/laravel-pos/v1/pos/product-search
    register_rest_route('laravel-pos/v1', '/pos/product-search', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_product_search',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'laravel_pos_register_pos_rest_routes');

// REST API Callbacks

function laravel_pos_api_process_sale($request) {
    $data = $request->get_json_params();
    $result = laravel_pos_process_sale($data);

    if ($result['success']) {
        return rest_ensure_response($result);
    } else {
        return new WP_Error('sale_failed', $result['message'], array('status' => 400));
    }
}

function laravel_pos_api_get_stats($request) {
    $period = $request->get_param('period') ?: 'today';
    $stats = laravel_pos_get_sales_stats($period);
    return rest_ensure_response($stats);
}

function laravel_pos_api_get_top_products($request) {
    $limit = $request->get_param('limit') ?: 10;
    $period = $request->get_param('period') ?: 'all';
    $top_products = laravel_pos_get_top_products($limit, $period);
    return rest_ensure_response($top_products);
}

function laravel_pos_api_product_search($request) {
    $search = $request->get_param('search');

    $args = array(
        'post_type' => 'product',
        's' => $search,
        'posts_per_page' => 20,
    );

    $products = laravel_pos_get_products($args);

    return rest_ensure_response($products);
}

// AJAX Handlers

function laravel_pos_ajax_process_sale() {
    check_ajax_referer('laravel_pos_pos', 'nonce');

    $data = array(
        'items' => json_decode(stripslashes($_POST['items']), true),
        'customer_id' => sanitize_text_field($_POST['customer_id'] ?? ''),
        'payment_method' => sanitize_text_field($_POST['payment_method'] ?? 'cash'),
        'tax_rate' => floatval($_POST['tax_rate'] ?? 0),
        'discount' => floatval($_POST['discount'] ?? 0),
    );

    $result = laravel_pos_process_sale($data);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
add_action('wp_ajax_laravel_pos_process_sale', 'laravel_pos_ajax_process_sale');

function laravel_pos_ajax_get_product_by_barcode() {
    check_ajax_referer('laravel_pos_pos', 'nonce');

    $barcode = sanitize_text_field($_POST['barcode']);

    // Search by SKU (barcode)
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_product_sku',
                'value' => $barcode,
            ),
        ),
    );

    $products = laravel_pos_get_products($args);

    if (!empty($products)) {
        wp_send_json_success($products[0]);
    } else {
        wp_send_json_error(array('message' => 'Product not found'));
    }
}
add_action('wp_ajax_laravel_pos_get_product_by_barcode', 'laravel_pos_ajax_get_product_by_barcode');

/**
 * Enqueue POS-specific scripts
 */
function laravel_pos_enqueue_pos_scripts() {
    if (is_page_template('page-pos.php')) {
        wp_enqueue_script('laravel-pos-pos', get_template_directory_uri() . '/assets/js/pos.js', array('jquery'), LARAVEL_POS_THEME_VERSION, true);

        wp_localize_script('laravel-pos-pos', 'laravelPOS', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('laravel_pos_pos'),
            'rest_url' => rest_url('laravel-pos/v1'),
            'rest_nonce' => wp_create_nonce('wp_rest'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'laravel_pos_enqueue_pos_scripts');

/**
 * Print receipt (shortcode or function)
 */
function laravel_pos_print_receipt($order_id) {
    $order = laravel_pos_get_order($order_id);
    if (!$order) return '';

    $items = get_post_meta($order_id, '_order_items', true);
    $subtotal = get_post_meta($order_id, '_order_subtotal', true);
    $tax_amount = get_post_meta($order_id, '_order_tax_amount', true);
    $discount = get_post_meta($order_id, '_order_discount', true);

    ob_start();
    ?>
    <div class="receipt" style="width: 300px; font-family: monospace; padding: 20px;">
        <div class="receipt-header" style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin: 0;"><?php bloginfo('name'); ?></h2>
            <p style="margin: 5px 0;"><?php echo get_bloginfo('description'); ?></p>
            <p style="margin: 5px 0;">Receipt #<?php echo esc_html($order['order_number']); ?></p>
            <p style="margin: 5px 0;"><?php echo date('M j, Y g:i A', strtotime($order['created_at'])); ?></p>
        </div>

        <hr style="border: 1px dashed #000;">

        <div class="receipt-items" style="margin: 20px 0;">
            <?php if (is_array($items)): ?>
                <?php foreach ($items as $item): ?>
                    <div style="margin-bottom: 10px;">
                        <div><?php echo esc_html($item['product_name']); ?></div>
                        <div style="display: flex; justify-content: space-between;">
                            <span><?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></span>
                            <span>$<?php echo number_format($item['total'], 2); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <hr style="border: 1px dashed #000;">

        <div class="receipt-totals" style="margin: 20px 0;">
            <div style="display: flex; justify-content: space-between; margin: 5px 0;">
                <span>Subtotal:</span>
                <span>$<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <?php if ($tax_amount > 0): ?>
                <div style="display: flex; justify-content: space-between; margin: 5px 0;">
                    <span>Tax:</span>
                    <span>$<?php echo number_format($tax_amount, 2); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($discount > 0): ?>
                <div style="display: flex; justify-content: space-between; margin: 5px 0;">
                    <span>Discount:</span>
                    <span>-$<?php echo number_format($discount, 2); ?></span>
                </div>
            <?php endif; ?>
            <div style="display: flex; justify-content: space-between; margin: 10px 0; font-weight: bold; font-size: 18px;">
                <span>TOTAL:</span>
                <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>
        </div>

        <hr style="border: 1px dashed #000;">

        <div class="receipt-footer" style="text-align: center; margin-top: 20px;">
            <p style="margin: 5px 0;">Payment: <?php echo esc_html(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?></p>
            <p style="margin: 20px 0 5px 0;">Thank you for your business!</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Shortcode: [pos_receipt order_id="123"]
function laravel_pos_receipt_shortcode($atts) {
    $atts = shortcode_atts(array(
        'order_id' => 0,
    ), $atts);

    if (empty($atts['order_id'])) {
        return '<p>No order ID provided.</p>';
    }

    return laravel_pos_print_receipt($atts['order_id']);
}
add_shortcode('pos_receipt', 'laravel_pos_receipt_shortcode');

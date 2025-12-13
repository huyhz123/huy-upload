<?php
/**
 * Orders Module
 * Laravel equivalent: app/Models/Order.php + OrderController.php
 */

// Register Order meta boxes
function laravel_pos_order_meta_boxes() {
    add_meta_box(
        'order_details',
        __('Order Details', 'laravel-pos'),
        'laravel_pos_order_details_callback',
        'order',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'laravel_pos_order_meta_boxes');

// Order details meta box callback
function laravel_pos_order_details_callback($post) {
    wp_nonce_field('laravel_pos_save_order', 'laravel_pos_order_nonce');

    $order_number = get_post_meta($post->ID, '_order_number', true);
    $customer_id = get_post_meta($post->ID, '_customer_id', true);
    $total_amount = get_post_meta($post->ID, '_total_amount', true);
    $status = get_post_meta($post->ID, '_order_status', true);
    $payment_method = get_post_meta($post->ID, '_payment_method', true);
    $payment_status = get_post_meta($post->ID, '_payment_status', true);
    $shipping_address = get_post_meta($post->ID, '_shipping_address', true);
    $billing_address = get_post_meta($post->ID, '_billing_address', true);
    $notes = get_post_meta($post->ID, '_order_notes', true);

    // Get customers for dropdown
    $customers = get_posts(array(
        'post_type' => 'customer',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    ?>

    <div class="order-meta-fields">
        <p>
            <label for="order_number"><strong><?php _e('Order Number:', 'laravel-pos'); ?></strong></label><br>
            <input type="text" id="order_number" name="order_number" value="<?php echo esc_attr($order_number); ?>" style="width: 100%;" placeholder="ORD-001">
        </p>

        <p>
            <label for="customer_id"><strong><?php _e('Customer:', 'laravel-pos'); ?></strong></label><br>
            <select id="customer_id" name="customer_id" style="width: 100%;">
                <option value=""><?php _e('Select Customer', 'laravel-pos'); ?></option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?php echo $customer->ID; ?>" <?php selected($customer_id, $customer->ID); ?>>
                        <?php echo esc_html($customer->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="total_amount"><strong><?php _e('Total Amount:', 'laravel-pos'); ?></strong></label><br>
            <input type="number" id="total_amount" name="total_amount" value="<?php echo esc_attr($total_amount); ?>" step="0.01" min="0" style="width: 100%;" placeholder="0.00">
        </p>

        <p>
            <label for="order_status"><strong><?php _e('Order Status:', 'laravel-pos'); ?></strong></label><br>
            <select id="order_status" name="order_status" style="width: 100%;">
                <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'laravel-pos'); ?></option>
                <option value="processing" <?php selected($status, 'processing'); ?>><?php _e('Processing', 'laravel-pos'); ?></option>
                <option value="completed" <?php selected($status, 'completed'); ?>><?php _e('Completed', 'laravel-pos'); ?></option>
                <option value="cancelled" <?php selected($status, 'cancelled'); ?>><?php _e('Cancelled', 'laravel-pos'); ?></option>
            </select>
        </p>

        <p>
            <label for="payment_method"><strong><?php _e('Payment Method:', 'laravel-pos'); ?></strong></label><br>
            <select id="payment_method" name="payment_method" style="width: 100%;">
                <option value="cash" <?php selected($payment_method, 'cash'); ?>><?php _e('Cash', 'laravel-pos'); ?></option>
                <option value="card" <?php selected($payment_method, 'card'); ?>><?php _e('Card', 'laravel-pos'); ?></option>
                <option value="bank_transfer" <?php selected($payment_method, 'bank_transfer'); ?>><?php _e('Bank Transfer', 'laravel-pos'); ?></option>
                <option value="online" <?php selected($payment_method, 'online'); ?>><?php _e('Online Payment', 'laravel-pos'); ?></option>
            </select>
        </p>

        <p>
            <label for="payment_status"><strong><?php _e('Payment Status:', 'laravel-pos'); ?></strong></label><br>
            <select id="payment_status" name="payment_status" style="width: 100%;">
                <option value="unpaid" <?php selected($payment_status, 'unpaid'); ?>><?php _e('Unpaid', 'laravel-pos'); ?></option>
                <option value="paid" <?php selected($payment_status, 'paid'); ?>><?php _e('Paid', 'laravel-pos'); ?></option>
                <option value="partial" <?php selected($payment_status, 'partial'); ?>><?php _e('Partially Paid', 'laravel-pos'); ?></option>
                <option value="refunded" <?php selected($payment_status, 'refunded'); ?>><?php _e('Refunded', 'laravel-pos'); ?></option>
            </select>
        </p>

        <p>
            <label for="shipping_address"><strong><?php _e('Shipping Address:', 'laravel-pos'); ?></strong></label><br>
            <textarea id="shipping_address" name="shipping_address" rows="3" style="width: 100%;"><?php echo esc_textarea($shipping_address); ?></textarea>
        </p>

        <p>
            <label for="billing_address"><strong><?php _e('Billing Address:', 'laravel-pos'); ?></strong></label><br>
            <textarea id="billing_address" name="billing_address" rows="3" style="width: 100%;"><?php echo esc_textarea($billing_address); ?></textarea>
        </p>

        <p>
            <label for="order_notes"><strong><?php _e('Order Notes:', 'laravel-pos'); ?></strong></label><br>
            <textarea id="order_notes" name="order_notes" rows="4" style="width: 100%;"><?php echo esc_textarea($notes); ?></textarea>
        </p>
    </div>

    <style>
        .order-meta-fields p { margin-bottom: 15px; }
        .order-meta-fields input[type="text"],
        .order-meta-fields input[type="number"],
        .order-meta-fields select,
        .order-meta-fields textarea {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
    <?php
}

// Save Order meta
function laravel_pos_save_order_meta($post_id) {
    if (!isset($_POST['laravel_pos_order_nonce']) ||
        !wp_verify_nonce($_POST['laravel_pos_order_nonce'], 'laravel_pos_save_order')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'order_number',
        'customer_id',
        'total_amount',
        'order_status',
        'payment_method',
        'payment_status',
        'shipping_address',
        'billing_address',
        'order_notes',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post_order', 'laravel_pos_save_order_meta');

// CRUD Helper Functions (Laravel Eloquent equivalents)

/**
 * Get Order by ID
 * Laravel: Order::find($id)
 */
function laravel_pos_get_order($order_id) {
    $order = get_post($order_id);

    if (!$order || $order->post_type !== 'order') {
        return null;
    }

    $customer_id = get_post_meta($order->ID, '_customer_id', true);
    $customer = $customer_id ? get_post($customer_id) : null;

    return array(
        'id' => $order->ID,
        'order_number' => get_post_meta($order->ID, '_order_number', true),
        'customer_id' => $customer_id,
        'customer_name' => $customer ? $customer->post_title : '',
        'total_amount' => get_post_meta($order->ID, '_total_amount', true),
        'status' => get_post_meta($order->ID, '_order_status', true) ?: 'pending',
        'payment_method' => get_post_meta($order->ID, '_payment_method', true),
        'payment_status' => get_post_meta($order->ID, '_payment_status', true) ?: 'unpaid',
        'shipping_address' => get_post_meta($order->ID, '_shipping_address', true),
        'billing_address' => get_post_meta($order->ID, '_billing_address', true),
        'notes' => get_post_meta($order->ID, '_order_notes', true),
        'created_at' => $order->post_date,
        'updated_at' => $order->post_modified,
    );
}

/**
 * Get all Orders
 * Laravel: Order::all() or Order::paginate()
 */
function laravel_pos_get_orders($args = array()) {
    $default_args = array(
        'post_type' => 'order',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    $args = wp_parse_args($args, $default_args);
    $orders = get_posts($args);

    $result = array();
    foreach ($orders as $order) {
        $result[] = laravel_pos_get_order($order->ID);
    }

    return $result;
}

/**
 * Create Order
 * Laravel: Order::create($data)
 */
function laravel_pos_create_order($data) {
    $order_data = array(
        'post_title' => 'Order ' . ($data['order_number'] ?? ''),
        'post_type' => 'order',
        'post_status' => 'publish',
    );

    $order_id = wp_insert_post($order_data);

    if (is_wp_error($order_id)) {
        return false;
    }

    // Save meta data
    $meta_fields = array(
        'order_number',
        'customer_id',
        'total_amount',
        'order_status',
        'payment_method',
        'payment_status',
        'shipping_address',
        'billing_address',
        'order_notes',
    );

    foreach ($meta_fields as $field) {
        if (isset($data[$field])) {
            update_post_meta($order_id, '_' . $field, sanitize_text_field($data[$field]));
        }
    }

    return $order_id;
}

/**
 * Update Order
 * Laravel: $order->update($data)
 */
function laravel_pos_update_order($order_id, $data) {
    $order = get_post($order_id);

    if (!$order || $order->post_type !== 'order') {
        return false;
    }

    // Update post if needed
    if (isset($data['order_number'])) {
        wp_update_post(array(
            'ID' => $order_id,
            'post_title' => 'Order ' . $data['order_number'],
        ));
    }

    // Update meta data
    $meta_fields = array(
        'order_number',
        'customer_id',
        'total_amount',
        'order_status',
        'payment_method',
        'payment_status',
        'shipping_address',
        'billing_address',
        'order_notes',
    );

    foreach ($meta_fields as $field) {
        if (isset($data[$field])) {
            update_post_meta($order_id, '_' . $field, sanitize_text_field($data[$field]));
        }
    }

    return true;
}

/**
 * Delete Order
 * Laravel: $order->delete()
 */
function laravel_pos_delete_order($order_id) {
    $order = get_post($order_id);

    if (!$order || $order->post_type !== 'order') {
        return false;
    }

    return wp_delete_post($order_id, true);
}

// REST API Endpoints

/**
 * Register Order REST API routes
 */
function laravel_pos_register_order_rest_routes() {
    // GET /wp-json/laravel-pos/v1/orders
    register_rest_route('laravel-pos/v1', '/orders', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_orders',
        'permission_callback' => '__return_true',
    ));

    // GET /wp-json/laravel-pos/v1/orders/{id}
    register_rest_route('laravel-pos/v1', '/orders/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_order',
        'permission_callback' => '__return_true',
    ));

    // POST /wp-json/laravel-pos/v1/orders
    register_rest_route('laravel-pos/v1', '/orders', array(
        'methods' => 'POST',
        'callback' => 'laravel_pos_api_create_order',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    // PUT /wp-json/laravel-pos/v1/orders/{id}
    register_rest_route('laravel-pos/v1', '/orders/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'laravel_pos_api_update_order',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    // DELETE /wp-json/laravel-pos/v1/orders/{id}
    register_rest_route('laravel-pos/v1', '/orders/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'laravel_pos_api_delete_order',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));
}
add_action('rest_api_init', 'laravel_pos_register_order_rest_routes');

// REST API Callbacks

function laravel_pos_api_get_orders($request) {
    $orders = laravel_pos_get_orders();
    return rest_ensure_response($orders);
}

function laravel_pos_api_get_order($request) {
    $order_id = $request['id'];
    $order = laravel_pos_get_order($order_id);

    if (!$order) {
        return new WP_Error('order_not_found', 'Order not found', array('status' => 404));
    }

    return rest_ensure_response($order);
}

function laravel_pos_api_create_order($request) {
    $data = $request->get_json_params();
    $order_id = laravel_pos_create_order($data);

    if (!$order_id) {
        return new WP_Error('order_creation_failed', 'Failed to create order', array('status' => 500));
    }

    $order = laravel_pos_get_order($order_id);
    return rest_ensure_response($order);
}

function laravel_pos_api_update_order($request) {
    $order_id = $request['id'];
    $data = $request->get_json_params();

    $success = laravel_pos_update_order($order_id, $data);

    if (!$success) {
        return new WP_Error('order_update_failed', 'Failed to update order', array('status' => 500));
    }

    $order = laravel_pos_get_order($order_id);
    return rest_ensure_response($order);
}

function laravel_pos_api_delete_order($request) {
    $order_id = $request['id'];
    $success = laravel_pos_delete_order($order_id);

    if (!$success) {
        return new WP_Error('order_deletion_failed', 'Failed to delete order', array('status' => 500));
    }

    return rest_ensure_response(array('success' => true, 'message' => 'Order deleted'));
}

// AJAX Handlers

function laravel_pos_ajax_search_orders() {
    check_ajax_referer('laravel_pos_search', 'nonce');

    $search = sanitize_text_field($_POST['search'] ?? '');

    $args = array(
        'post_type' => 'order',
        's' => $search,
        'posts_per_page' => 20,
    );

    $orders = laravel_pos_get_orders($args);

    wp_send_json_success($orders);
}
add_action('wp_ajax_laravel_pos_search_orders', 'laravel_pos_ajax_search_orders');

// Shortcode: [orders_list]
function laravel_pos_orders_list_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10,
        'status' => '',
    ), $atts);

    $args = array(
        'posts_per_page' => intval($atts['limit']),
    );

    if (!empty($atts['status'])) {
        $args['meta_query'] = array(
            array(
                'key' => '_order_status',
                'value' => sanitize_text_field($atts['status']),
            ),
        );
    }

    $orders = laravel_pos_get_orders($args);

    ob_start();
    ?>
    <div class="orders-list">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Order Number', 'laravel-pos'); ?></th>
                    <th><?php _e('Customer', 'laravel-pos'); ?></th>
                    <th><?php _e('Total', 'laravel-pos'); ?></th>
                    <th><?php _e('Status', 'laravel-pos'); ?></th>
                    <th><?php _e('Payment', 'laravel-pos'); ?></th>
                    <th><?php _e('Date', 'laravel-pos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><a href="<?php echo get_permalink($order['id']); ?>"><?php echo esc_html($order['order_number']); ?></a></td>
                            <td><?php echo esc_html($order['customer_name']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><span class="status-badge status-<?php echo esc_attr($order['status']); ?>"><?php echo esc_html(ucfirst($order['status'])); ?></span></td>
                            <td><span class="payment-badge payment-<?php echo esc_attr($order['payment_status']); ?>"><?php echo esc_html(ucfirst($order['payment_status'])); ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6"><?php _e('No orders found.', 'laravel-pos'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('orders_list', 'laravel_pos_orders_list_shortcode');

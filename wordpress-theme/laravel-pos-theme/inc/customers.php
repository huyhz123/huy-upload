<?php
/**
 * Customers Module
 * Laravel equivalent: app/Models/Customer.php + CustomerController.php
 */

// Register Customer meta boxes
function laravel_pos_customer_meta_boxes() {
    add_meta_box(
        'customer_details',
        __('Customer Details', 'laravel-pos'),
        'laravel_pos_customer_details_callback',
        'customer',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'laravel_pos_customer_meta_boxes');

// Customer details meta box callback
function laravel_pos_customer_details_callback($post) {
    wp_nonce_field('laravel_pos_save_customer', 'laravel_pos_customer_nonce');

    $email = get_post_meta($post->ID, '_customer_email', true);
    $phone = get_post_meta($post->ID, '_customer_phone', true);
    $address = get_post_meta($post->ID, '_customer_address', true);
    $city = get_post_meta($post->ID, '_customer_city', true);
    $state = get_post_meta($post->ID, '_customer_state', true);
    $postal_code = get_post_meta($post->ID, '_customer_postal_code', true);
    $country = get_post_meta($post->ID, '_customer_country', true);
    $company = get_post_meta($post->ID, '_customer_company', true);
    $tax_number = get_post_meta($post->ID, '_customer_tax_number', true);
    $notes = get_post_meta($post->ID, '_customer_notes', true);
    $customer_type = get_post_meta($post->ID, '_customer_type', true);
    ?>

    <div class="customer-meta-fields">
        <div class="meta-row">
            <div class="meta-col">
                <p>
                    <label for="customer_email"><strong><?php _e('Email:', 'laravel-pos'); ?></strong></label><br>
                    <input type="email" id="customer_email" name="customer_email" value="<?php echo esc_attr($email); ?>" style="width: 100%;" placeholder="customer@example.com">
                </p>
            </div>
            <div class="meta-col">
                <p>
                    <label for="customer_phone"><strong><?php _e('Phone:', 'laravel-pos'); ?></strong></label><br>
                    <input type="tel" id="customer_phone" name="customer_phone" value="<?php echo esc_attr($phone); ?>" style="width: 100%;" placeholder="+1 234 567 8900">
                </p>
            </div>
        </div>

        <div class="meta-row">
            <div class="meta-col">
                <p>
                    <label for="customer_company"><strong><?php _e('Company:', 'laravel-pos'); ?></strong></label><br>
                    <input type="text" id="customer_company" name="customer_company" value="<?php echo esc_attr($company); ?>" style="width: 100%;" placeholder="Company Name">
                </p>
            </div>
            <div class="meta-col">
                <p>
                    <label for="customer_type"><strong><?php _e('Customer Type:', 'laravel-pos'); ?></strong></label><br>
                    <select id="customer_type" name="customer_type" style="width: 100%;">
                        <option value="individual" <?php selected($customer_type, 'individual'); ?>><?php _e('Individual', 'laravel-pos'); ?></option>
                        <option value="business" <?php selected($customer_type, 'business'); ?>><?php _e('Business', 'laravel-pos'); ?></option>
                        <option value="wholesale" <?php selected($customer_type, 'wholesale'); ?>><?php _e('Wholesale', 'laravel-pos'); ?></option>
                    </select>
                </p>
            </div>
        </div>

        <p>
            <label for="customer_address"><strong><?php _e('Address:', 'laravel-pos'); ?></strong></label><br>
            <input type="text" id="customer_address" name="customer_address" value="<?php echo esc_attr($address); ?>" style="width: 100%;" placeholder="Street Address">
        </p>

        <div class="meta-row">
            <div class="meta-col">
                <p>
                    <label for="customer_city"><strong><?php _e('City:', 'laravel-pos'); ?></strong></label><br>
                    <input type="text" id="customer_city" name="customer_city" value="<?php echo esc_attr($city); ?>" style="width: 100%;">
                </p>
            </div>
            <div class="meta-col">
                <p>
                    <label for="customer_state"><strong><?php _e('State/Province:', 'laravel-pos'); ?></strong></label><br>
                    <input type="text" id="customer_state" name="customer_state" value="<?php echo esc_attr($state); ?>" style="width: 100%;">
                </p>
            </div>
        </div>

        <div class="meta-row">
            <div class="meta-col">
                <p>
                    <label for="customer_postal_code"><strong><?php _e('Postal Code:', 'laravel-pos'); ?></strong></label><br>
                    <input type="text" id="customer_postal_code" name="customer_postal_code" value="<?php echo esc_attr($postal_code); ?>" style="width: 100%;">
                </p>
            </div>
            <div class="meta-col">
                <p>
                    <label for="customer_country"><strong><?php _e('Country:', 'laravel-pos'); ?></strong></label><br>
                    <input type="text" id="customer_country" name="customer_country" value="<?php echo esc_attr($country); ?>" style="width: 100%;">
                </p>
            </div>
        </div>

        <p>
            <label for="customer_tax_number"><strong><?php _e('Tax Number / VAT:', 'laravel-pos'); ?></strong></label><br>
            <input type="text" id="customer_tax_number" name="customer_tax_number" value="<?php echo esc_attr($tax_number); ?>" style="width: 100%;">
        </p>

        <p>
            <label for="customer_notes"><strong><?php _e('Notes:', 'laravel-pos'); ?></strong></label><br>
            <textarea id="customer_notes" name="customer_notes" rows="4" style="width: 100%;"><?php echo esc_textarea($notes); ?></textarea>
        </p>
    </div>

    <style>
        .customer-meta-fields p { margin-bottom: 15px; }
        .customer-meta-fields input[type="text"],
        .customer-meta-fields input[type="email"],
        .customer-meta-fields input[type="tel"],
        .customer-meta-fields select,
        .customer-meta-fields textarea {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .meta-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .meta-col {
            flex: 1;
        }
    </style>
    <?php
}

// Save Customer meta
function laravel_pos_save_customer_meta($post_id) {
    if (!isset($_POST['laravel_pos_customer_nonce']) ||
        !wp_verify_nonce($_POST['laravel_pos_customer_nonce'], 'laravel_pos_save_customer')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_state',
        'customer_postal_code',
        'customer_country',
        'customer_company',
        'customer_tax_number',
        'customer_notes',
        'customer_type',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post_customer', 'laravel_pos_save_customer_meta');

// CRUD Helper Functions

/**
 * Get Customer by ID
 * Laravel: Customer::find($id)
 */
function laravel_pos_get_customer($customer_id) {
    $customer = get_post($customer_id);

    if (!$customer || $customer->post_type !== 'customer') {
        return null;
    }

    return array(
        'id' => $customer->ID,
        'name' => $customer->post_title,
        'email' => get_post_meta($customer->ID, '_customer_email', true),
        'phone' => get_post_meta($customer->ID, '_customer_phone', true),
        'address' => get_post_meta($customer->ID, '_customer_address', true),
        'city' => get_post_meta($customer->ID, '_customer_city', true),
        'state' => get_post_meta($customer->ID, '_customer_state', true),
        'postal_code' => get_post_meta($customer->ID, '_customer_postal_code', true),
        'country' => get_post_meta($customer->ID, '_customer_country', true),
        'company' => get_post_meta($customer->ID, '_customer_company', true),
        'tax_number' => get_post_meta($customer->ID, '_customer_tax_number', true),
        'notes' => get_post_meta($customer->ID, '_customer_notes', true),
        'customer_type' => get_post_meta($customer->ID, '_customer_type', true) ?: 'individual',
        'created_at' => $customer->post_date,
        'updated_at' => $customer->post_modified,
    );
}

/**
 * Get all Customers
 * Laravel: Customer::all()
 */
function laravel_pos_get_customers($args = array()) {
    $default_args = array(
        'post_type' => 'customer',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );

    $args = wp_parse_args($args, $default_args);
    $customers = get_posts($args);

    $result = array();
    foreach ($customers as $customer) {
        $result[] = laravel_pos_get_customer($customer->ID);
    }

    return $result;
}

/**
 * Create Customer
 * Laravel: Customer::create($data)
 */
function laravel_pos_create_customer($data) {
    $customer_data = array(
        'post_title' => sanitize_text_field($data['name']),
        'post_type' => 'customer',
        'post_status' => 'publish',
    );

    $customer_id = wp_insert_post($customer_data);

    if (is_wp_error($customer_id)) {
        return false;
    }

    $meta_fields = array(
        'email', 'phone', 'address', 'city', 'state',
        'postal_code', 'country', 'company', 'tax_number',
        'notes', 'customer_type',
    );

    foreach ($meta_fields as $field) {
        if (isset($data[$field])) {
            update_post_meta($customer_id, '_customer_' . $field, sanitize_text_field($data[$field]));
        }
    }

    return $customer_id;
}

/**
 * Update Customer
 * Laravel: $customer->update($data)
 */
function laravel_pos_update_customer($customer_id, $data) {
    $customer = get_post($customer_id);

    if (!$customer || $customer->post_type !== 'customer') {
        return false;
    }

    if (isset($data['name'])) {
        wp_update_post(array(
            'ID' => $customer_id,
            'post_title' => sanitize_text_field($data['name']),
        ));
    }

    $meta_fields = array(
        'email', 'phone', 'address', 'city', 'state',
        'postal_code', 'country', 'company', 'tax_number',
        'notes', 'customer_type',
    );

    foreach ($meta_fields as $field) {
        if (isset($data[$field])) {
            update_post_meta($customer_id, '_customer_' . $field, sanitize_text_field($data[$field]));
        }
    }

    return true;
}

/**
 * Delete Customer
 * Laravel: $customer->delete()
 */
function laravel_pos_delete_customer($customer_id) {
    $customer = get_post($customer_id);

    if (!$customer || $customer->post_type !== 'customer') {
        return false;
    }

    return wp_delete_post($customer_id, true);
}

/**
 * Get customer orders
 * Laravel: $customer->orders
 */
function laravel_pos_get_customer_orders($customer_id) {
    $args = array(
        'post_type' => 'order',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_customer_id',
                'value' => $customer_id,
            ),
        ),
    );

    $orders = get_posts($args);
    $result = array();

    foreach ($orders as $order) {
        $result[] = laravel_pos_get_order($order->ID);
    }

    return $result;
}

// REST API Endpoints

function laravel_pos_register_customer_rest_routes() {
    // GET /wp-json/laravel-pos/v1/customers
    register_rest_route('laravel-pos/v1', '/customers', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_customers',
        'permission_callback' => '__return_true',
    ));

    // GET /wp-json/laravel-pos/v1/customers/{id}
    register_rest_route('laravel-pos/v1', '/customers/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_customer',
        'permission_callback' => '__return_true',
    ));

    // POST /wp-json/laravel-pos/v1/customers
    register_rest_route('laravel-pos/v1', '/customers', array(
        'methods' => 'POST',
        'callback' => 'laravel_pos_api_create_customer',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    // PUT /wp-json/laravel-pos/v1/customers/{id}
    register_rest_route('laravel-pos/v1', '/customers/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'laravel_pos_api_update_customer',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    // DELETE /wp-json/laravel-pos/v1/customers/{id}
    register_rest_route('laravel-pos/v1', '/customers/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'laravel_pos_api_delete_customer',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    // GET /wp-json/laravel-pos/v1/customers/{id}/orders
    register_rest_route('laravel-pos/v1', '/customers/(?P<id>\d+)/orders', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_customer_orders',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'laravel_pos_register_customer_rest_routes');

// REST API Callbacks

function laravel_pos_api_get_customers($request) {
    $customers = laravel_pos_get_customers();
    return rest_ensure_response($customers);
}

function laravel_pos_api_get_customer($request) {
    $customer_id = $request['id'];
    $customer = laravel_pos_get_customer($customer_id);

    if (!$customer) {
        return new WP_Error('customer_not_found', 'Customer not found', array('status' => 404));
    }

    return rest_ensure_response($customer);
}

function laravel_pos_api_create_customer($request) {
    $data = $request->get_json_params();
    $customer_id = laravel_pos_create_customer($data);

    if (!$customer_id) {
        return new WP_Error('customer_creation_failed', 'Failed to create customer', array('status' => 500));
    }

    $customer = laravel_pos_get_customer($customer_id);
    return rest_ensure_response($customer);
}

function laravel_pos_api_update_customer($request) {
    $customer_id = $request['id'];
    $data = $request->get_json_params();

    $success = laravel_pos_update_customer($customer_id, $data);

    if (!$success) {
        return new WP_Error('customer_update_failed', 'Failed to update customer', array('status' => 500));
    }

    $customer = laravel_pos_get_customer($customer_id);
    return rest_ensure_response($customer);
}

function laravel_pos_api_delete_customer($request) {
    $customer_id = $request['id'];
    $success = laravel_pos_delete_customer($customer_id);

    if (!$success) {
        return new WP_Error('customer_deletion_failed', 'Failed to delete customer', array('status' => 500));
    }

    return rest_ensure_response(array('success' => true, 'message' => 'Customer deleted'));
}

function laravel_pos_api_get_customer_orders($request) {
    $customer_id = $request['id'];
    $orders = laravel_pos_get_customer_orders($customer_id);

    return rest_ensure_response($orders);
}

// AJAX Handlers

function laravel_pos_ajax_search_customers() {
    check_ajax_referer('laravel_pos_search', 'nonce');

    $search = sanitize_text_field($_POST['search'] ?? '');

    $args = array(
        'post_type' => 'customer',
        's' => $search,
        'posts_per_page' => 20,
    );

    $customers = laravel_pos_get_customers($args);

    wp_send_json_success($customers);
}
add_action('wp_ajax_laravel_pos_search_customers', 'laravel_pos_ajax_search_customers');

// Shortcode: [customers_list]
function laravel_pos_customers_list_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10,
        'type' => '',
    ), $atts);

    $args = array(
        'posts_per_page' => intval($atts['limit']),
    );

    if (!empty($atts['type'])) {
        $args['meta_query'] = array(
            array(
                'key' => '_customer_type',
                'value' => sanitize_text_field($atts['type']),
            ),
        );
    }

    $customers = laravel_pos_get_customers($args);

    ob_start();
    ?>
    <div class="customers-list">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Name', 'laravel-pos'); ?></th>
                    <th><?php _e('Email', 'laravel-pos'); ?></th>
                    <th><?php _e('Phone', 'laravel-pos'); ?></th>
                    <th><?php _e('Type', 'laravel-pos'); ?></th>
                    <th><?php _e('City', 'laravel-pos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><a href="<?php echo get_permalink($customer['id']); ?>"><?php echo esc_html($customer['name']); ?></a></td>
                            <td><?php echo esc_html($customer['email']); ?></td>
                            <td><?php echo esc_html($customer['phone']); ?></td>
                            <td><?php echo esc_html(ucfirst($customer['customer_type'])); ?></td>
                            <td><?php echo esc_html($customer['city']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5"><?php _e('No customers found.', 'laravel-pos'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('customers_list', 'laravel_pos_customers_list_shortcode');

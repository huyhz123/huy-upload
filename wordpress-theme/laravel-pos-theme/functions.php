<?php
/**
 * Laravel POS Theme - Functions
 *
 * Main theme setup file converting Laravel functionality to WordPress
 *
 * @package Laravel_POS_Theme
 * @version 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ============================================================================
 * THEME SETUP & CONFIGURATION
 * ============================================================================
 */

// Define theme constants
define('LARAVEL_POS_THEME_VERSION', '1.0.0');
define('LARAVEL_POS_THEME_DIR', get_template_directory());
define('LARAVEL_POS_THEME_URL', get_template_directory_uri());

/**
 * Theme Setup
 * Similar to Laravel's AppServiceProvider boot()
 */
function laravel_pos_theme_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register navigation menus (equivalent to Laravel navigation)
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'laravel-pos'),
        'dashboard' => __('Dashboard Menu', 'laravel-pos'),
    ));

    // Add image sizes (for product images, etc.)
    add_image_size('product-thumb', 300, 300, true);
    add_image_size('product-large', 800, 800, true);
}
add_action('after_setup_theme', 'laravel_pos_theme_setup');

/**
 * ============================================================================
 * ENQUEUE SCRIPTS & STYLES
 * Similar to Laravel's vite() or mix() asset loading
 * ============================================================================
 */

function laravel_pos_enqueue_assets() {
    // Enqueue main stylesheet
    wp_enqueue_style('laravel-pos-style', get_stylesheet_uri(), array(), LARAVEL_POS_THEME_VERSION);

    // Enqueue custom CSS (Laravel's public/css equivalent)
    wp_enqueue_style('laravel-pos-custom', LARAVEL_POS_THEME_URL . '/assets/css/custom.css', array(), LARAVEL_POS_THEME_VERSION);

    // Enqueue Vue.js (if Laravel used Vue)
    wp_enqueue_script('vue', 'https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js', array(), '3.0', true);

    // Enqueue Alpine.js (if Laravel used Alpine)
    wp_enqueue_script('alpine', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', array(), '3.0', true);

    // Enqueue main JavaScript (Laravel's app.js equivalent)
    wp_enqueue_script('laravel-pos-main', LARAVEL_POS_THEME_URL . '/assets/js/main.js', array('jquery'), LARAVEL_POS_THEME_VERSION, true);

    // Localize script (similar to Laravel's @json blade directive)
    wp_localize_script('laravel-pos-main', 'laravelPOS', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('laravel_pos_nonce'),
        'restUrl' => get_rest_url(),
        'userId' => get_current_user_id(),
    ));
}
add_action('wp_enqueue_scripts', 'laravel_pos_enqueue_assets');

/**
 * ============================================================================
 * CUSTOM POST TYPES (Laravel Models → WordPress CPT)
 * ============================================================================
 */

/**
 * Register Product CPT (Laravel Product Model)
 * Equivalent to: app/Models/Product.php
 */
function laravel_pos_register_product_cpt() {
    $labels = array(
        'name' => 'Products',
        'singular_name' => 'Product',
        'menu_name' => 'Products',
        'add_new' => 'Add Product',
        'add_new_item' => 'Add New Product',
        'edit_item' => 'Edit Product',
        'new_item' => 'New Product',
        'view_item' => 'View Product',
        'search_items' => 'Search Products',
        'not_found' => 'No products found',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-products',
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'show_in_rest' => true, // Enable REST API
        'rewrite' => array('slug' => 'products'),
    );

    register_post_type('product', $args);
}
add_action('init', 'laravel_pos_register_product_cpt');

/**
 * Register Order CPT (Laravel Order Model)
 * Equivalent to: app/Models/Order.php
 */
function laravel_pos_register_order_cpt() {
    $labels = array(
        'name' => 'Orders',
        'singular_name' => 'Order',
        'menu_name' => 'Orders',
        'add_new' => 'Create Order',
        'edit_item' => 'Edit Order',
        'view_item' => 'View Order',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-cart',
        'supports' => array('title', 'custom-fields'),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'orders'),
        'capability_type' => 'post',
        'map_meta_cap' => true,
    );

    register_post_type('order', $args);
}
add_action('init', 'laravel_pos_register_order_cpt');

/**
 * Register Customer CPT (Laravel Customer Model)
 * Equivalent to: app/Models/Customer.php
 */
function laravel_pos_register_customer_cpt() {
    $labels = array(
        'name' => 'Customers',
        'singular_name' => 'Customer',
        'menu_name' => 'Customers',
        'add_new' => 'Add Customer',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-groups',
        'supports' => array('title', 'custom-fields'),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'customers'),
    );

    register_post_type('customer', $args);
}
add_action('init', 'laravel_pos_register_customer_cpt');

/**
 * Register Notification CPT (Laravel Notification Model)
 * Equivalent to: database/notifications table
 */
function laravel_pos_register_notification_cpt() {
    $labels = array(
        'name' => 'Notifications',
        'singular_name' => 'Notification',
        'menu_name' => 'Notifications',
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-bell',
        'supports' => array('title', 'editor', 'custom-fields'),
        'show_in_rest' => true,
    );

    register_post_type('notification', $args);
}
add_action('init', 'laravel_pos_register_notification_cpt');

/**
 * ============================================================================
 * CUSTOM FIELDS / META BOXES (Laravel Model Attributes)
 * Using native WordPress meta boxes (or ACF if available)
 * ============================================================================
 */

/**
 * Add Product Meta Boxes
 * Laravel equivalent: Product model fillable fields
 */
function laravel_pos_add_product_meta_boxes() {
    add_meta_box(
        'product_details',
        'Product Details',
        'laravel_pos_product_meta_box_callback',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'laravel_pos_add_product_meta_boxes');

/**
 * Product Meta Box Callback
 * Display custom fields for product
 */
function laravel_pos_product_meta_box_callback($post) {
    wp_nonce_field('laravel_pos_product_meta', 'laravel_pos_product_nonce');

    // Get existing values
    $sku = get_post_meta($post->ID, '_product_sku', true);
    $price = get_post_meta($post->ID, '_product_price', true);
    $stock = get_post_meta($post->ID, '_product_stock', true);
    $category = get_post_meta($post->ID, '_product_category', true);
    ?>

    <table class="form-table">
        <tr>
            <th><label for="product_sku">SKU</label></th>
            <td><input type="text" id="product_sku" name="product_sku" value="<?php echo esc_attr($sku); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="product_price">Price</label></th>
            <td><input type="number" id="product_price" name="product_price" value="<?php echo esc_attr($price); ?>" step="0.01" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="product_stock">Stock Quantity</label></th>
            <td><input type="number" id="product_stock" name="product_stock" value="<?php echo esc_attr($stock); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="product_category">Category</label></th>
            <td>
                <select id="product_category" name="product_category" class="regular-text">
                    <option value="">Select Category</option>
                    <option value="electronics" <?php selected($category, 'electronics'); ?>>Electronics</option>
                    <option value="clothing" <?php selected($category, 'clothing'); ?>>Clothing</option>
                    <option value="food" <?php selected($category, 'food'); ?>>Food & Beverage</option>
                    <option value="other" <?php selected($category, 'other'); ?>>Other</option>
                </select>
            </td>
        </tr>
    </table>

    <?php
}

/**
 * Save Product Meta Data
 * Laravel equivalent: ProductController@store and @update
 */
function laravel_pos_save_product_meta($post_id) {
    // Security checks
    if (!isset($_POST['laravel_pos_product_nonce'])) return;
    if (!wp_verify_nonce($_POST['laravel_pos_product_nonce'], 'laravel_pos_product_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Save meta data
    if (isset($_POST['product_sku'])) {
        update_post_meta($post_id, '_product_sku', sanitize_text_field($_POST['product_sku']));
    }

    if (isset($_POST['product_price'])) {
        update_post_meta($post_id, '_product_price', floatval($_POST['product_price']));
    }

    if (isset($_POST['product_stock'])) {
        update_post_meta($post_id, '_product_stock', intval($_POST['product_stock']));
    }

    if (isset($_POST['product_category'])) {
        update_post_meta($post_id, '_product_category', sanitize_text_field($_POST['product_category']));
    }
}
add_action('save_post_product', 'laravel_pos_save_product_meta');

/**
 * ============================================================================
 * HELPER FUNCTIONS (Laravel Model Methods → WP Functions)
 * ============================================================================
 */

/**
 * Get Product by ID
 * Laravel: Product::find($id)
 */
function laravel_pos_get_product($product_id) {
    $product = get_post($product_id);

    if (!$product || $product->post_type !== 'product') {
        return null;
    }

    return array(
        'id' => $product->ID,
        'title' => $product->post_title,
        'description' => $product->post_content,
        'sku' => get_post_meta($product->ID, '_product_sku', true),
        'price' => get_post_meta($product->ID, '_product_price', true),
        'stock' => get_post_meta($product->ID, '_product_stock', true),
        'category' => get_post_meta($product->ID, '_product_category', true),
        'image' => get_the_post_thumbnail_url($product->ID, 'product-thumb'),
        'created_at' => $product->post_date,
        'updated_at' => $product->post_modified,
    );
}

/**
 * Get All Products
 * Laravel: Product::all() or Product::paginate()
 */
function laravel_pos_get_products($args = array()) {
    $defaults = array(
        'post_type' => 'product',
        'posts_per_page' => 20,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    $args = wp_parse_args($args, $defaults);
    $query = new WP_Query($args);

    $products = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $products[] = laravel_pos_get_product(get_the_ID());
        }
        wp_reset_postdata();
    }

    return $products;
}

/**
 * Create Product
 * Laravel: Product::create($data)
 */
function laravel_pos_create_product($data) {
    $post_data = array(
        'post_title' => sanitize_text_field($data['title']),
        'post_content' => wp_kses_post($data['description']),
        'post_type' => 'product',
        'post_status' => 'publish',
    );

    $product_id = wp_insert_post($post_data);

    if (is_wp_error($product_id)) {
        return false;
    }

    // Save meta data
    if (isset($data['sku'])) {
        update_post_meta($product_id, '_product_sku', sanitize_text_field($data['sku']));
    }
    if (isset($data['price'])) {
        update_post_meta($product_id, '_product_price', floatval($data['price']));
    }
    if (isset($data['stock'])) {
        update_post_meta($product_id, '_product_stock', intval($data['stock']));
    }
    if (isset($data['category'])) {
        update_post_meta($product_id, '_product_category', sanitize_text_field($data['category']));
    }

    return $product_id;
}

/**
 * Update Product
 * Laravel: $product->update($data)
 */
function laravel_pos_update_product($product_id, $data) {
    $post_data = array(
        'ID' => $product_id,
    );

    if (isset($data['title'])) {
        $post_data['post_title'] = sanitize_text_field($data['title']);
    }
    if (isset($data['description'])) {
        $post_data['post_content'] = wp_kses_post($data['description']);
    }

    $result = wp_update_post($post_data);

    if (is_wp_error($result)) {
        return false;
    }

    // Update meta
    if (isset($data['price'])) {
        update_post_meta($product_id, '_product_price', floatval($data['price']));
    }
    if (isset($data['stock'])) {
        update_post_meta($product_id, '_product_stock', intval($data['stock']));
    }

    return true;
}

/**
 * Delete Product
 * Laravel: $product->delete()
 */
function laravel_pos_delete_product($product_id) {
    return wp_delete_post($product_id, true);
}

/**
 * ============================================================================
 * WORDPRESS REST API (Laravel Routes/API)
 * Equivalent to routes/api.php
 * ============================================================================
 */

/**
 * Register Custom REST API Endpoints
 * Laravel routes/api.php → WordPress REST API
 */
function laravel_pos_register_rest_routes() {
    // Products API
    register_rest_route('laravel-pos/v1', '/products', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_products',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('laravel-pos/v1', '/products/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_product',
        'permission_callback' => '__return_true',
    ));

    register_rest_route('laravel-pos/v1', '/products', array(
        'methods' => 'POST',
        'callback' => 'laravel_pos_api_create_product',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    register_rest_route('laravel-pos/v1', '/products/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'laravel_pos_api_update_product',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    register_rest_route('laravel-pos/v1', '/products/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'laravel_pos_api_delete_product',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));
}
add_action('rest_api_init', 'laravel_pos_register_rest_routes');

/**
 * API: Get Products
 * Laravel: ProductController@index
 */
function laravel_pos_api_get_products($request) {
    $per_page = $request->get_param('per_page') ?: 20;
    $page = $request->get_param('page') ?: 1;

    $products = laravel_pos_get_products(array(
        'posts_per_page' => $per_page,
        'paged' => $page,
    ));

    return new WP_REST_Response($products, 200);
}

/**
 * API: Get Single Product
 * Laravel: ProductController@show
 */
function laravel_pos_api_get_product($request) {
    $product_id = $request->get_param('id');
    $product = laravel_pos_get_product($product_id);

    if (!$product) {
        return new WP_Error('not_found', 'Product not found', array('status' => 404));
    }

    return new WP_REST_Response($product, 200);
}

/**
 * API: Create Product
 * Laravel: ProductController@store
 */
function laravel_pos_api_create_product($request) {
    $data = $request->get_json_params();

    // Validation (similar to Laravel's FormRequest)
    if (empty($data['title'])) {
        return new WP_Error('invalid_data', 'Title is required', array('status' => 400));
    }

    $product_id = laravel_pos_create_product($data);

    if (!$product_id) {
        return new WP_Error('creation_failed', 'Failed to create product', array('status' => 500));
    }

    $product = laravel_pos_get_product($product_id);
    return new WP_REST_Response($product, 201);
}

/**
 * API: Update Product
 * Laravel: ProductController@update
 */
function laravel_pos_api_update_product($request) {
    $product_id = $request->get_param('id');
    $data = $request->get_json_params();

    $result = laravel_pos_update_product($product_id, $data);

    if (!$result) {
        return new WP_Error('update_failed', 'Failed to update product', array('status' => 500));
    }

    $product = laravel_pos_get_product($product_id);
    return new WP_REST_Response($product, 200);
}

/**
 * API: Delete Product
 * Laravel: ProductController@destroy
 */
function laravel_pos_api_delete_product($request) {
    $product_id = $request->get_param('id');

    $result = laravel_pos_delete_product($product_id);

    if (!$result) {
        return new WP_Error('delete_failed', 'Failed to delete product', array('status' => 500));
    }

    return new WP_REST_Response(array('message' => 'Product deleted successfully'), 200);
}

/**
 * Check Admin Permission
 * Laravel: middleware auth:admin
 */
function laravel_pos_check_admin_permission() {
    return current_user_can('manage_options');
}

/**
 * ============================================================================
 * AJAX HANDLERS (Laravel AJAX Routes)
 * ============================================================================
 */

/**
 * AJAX: Search Products
 * Laravel: Route::post('/api/products/search')
 */
function laravel_pos_ajax_search_products() {
    check_ajax_referer('laravel_pos_nonce', 'nonce');

    $search_term = sanitize_text_field($_POST['search']);

    $products = laravel_pos_get_products(array(
        's' => $search_term,
        'posts_per_page' => 10,
    ));

    wp_send_json_success($products);
}
add_action('wp_ajax_search_products', 'laravel_pos_ajax_search_products');
add_action('wp_ajax_nopriv_search_products', 'laravel_pos_ajax_search_products');

/**
 * ============================================================================
 * AUTHENTICATION & USER ROLES (Laravel Auth)
 * ============================================================================
 */

/**
 * Add Custom User Roles
 * Laravel: Spatie Roles & Permissions
 */
function laravel_pos_add_custom_roles() {
    // Add POS Manager role
    add_role('pos_manager', 'POS Manager', array(
        'read' => true,
        'edit_posts' => true,
        'delete_posts' => true,
        'publish_posts' => true,
        'upload_files' => true,
    ));

    // Add Cashier role
    add_role('cashier', 'Cashier', array(
        'read' => true,
        'edit_posts' => false,
    ));
}
register_activation_hook(__FILE__, 'laravel_pos_add_custom_roles');

/**
 * ============================================================================
 * INCLUDE MODULE FILES
 * Similar to Laravel's service providers loading different modules
 * ============================================================================
 */

// Include module-specific files
require_once LARAVEL_POS_THEME_DIR . '/inc/orders.php';
require_once LARAVEL_POS_THEME_DIR . '/inc/customers.php';
require_once LARAVEL_POS_THEME_DIR . '/inc/notifications.php';
require_once LARAVEL_POS_THEME_DIR . '/inc/pos.php';

/**
 * ============================================================================
 * SHORTCODES (For easy content insertion)
 * ============================================================================
 */

/**
 * Products List Shortcode
 * Usage: [products_list]
 */
function laravel_pos_products_list_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 12,
        'category' => '',
    ), $atts);

    $args = array(
        'posts_per_page' => $atts['limit'],
    );

    if (!empty($atts['category'])) {
        $args['meta_query'] = array(
            array(
                'key' => '_product_category',
                'value' => $atts['category'],
            ),
        );
    }

    $products = laravel_pos_get_products($args);

    ob_start();
    ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>">
                <h3><?php echo esc_html($product['title']); ?></h3>
                <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
                <a href="<?php echo get_permalink($product['id']); ?>" class="btn btn-primary">View Details</a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('products_list', 'laravel_pos_products_list_shortcode');

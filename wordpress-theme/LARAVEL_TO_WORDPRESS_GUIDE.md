# 🔄 Laravel to WordPress Theme - Complete Migration Guide

## 📋 Table of Contents
1. [Overview](#overview)
2. [Theme Structure](#theme-structure)
3. [Database Migration](#database-migration)
4. [Module Mapping](#module-mapping)
5. [Installation](#installation)
6. [Extending to Other Modules](#extending-to-other-modules)
7. [Testing](#testing)

---

## 🎯 Overview

This guide shows how to convert Laravel POS system to a fully functional WordPress theme.

### **What's Been Created:**

✅ **WordPress Theme** (`laravel-pos-theme/`)
✅ **Product Module** (Complete example with CRUD)
✅ **REST API** endpoints
✅ **Custom Post Types** for all entities
✅ **Helper functions** (Laravel Model equivalents)
✅ **Template files** (Blade equivalents)

---

## 📁 Theme Structure

```
wordpress-theme/laravel-pos-theme/
│
├── style.css                    ← Theme metadata & base styles
├── functions.php                ← Main functions (Models, API, helpers)
│
├── header.php                   ← Site header (Laravel layout header)
├── footer.php                   ← Site footer (Laravel layout footer)
├── index.php                    ← Default template
│
├── archive-product.php          ← Products listing (resources/views/products/index.blade.php)
├── single-product.php           ← Single product (resources/views/products/show.blade.php)
├── archive-order.php            ← Orders listing
├── single-order.php             ← Single order
├── page-dashboard.php           ← Dashboard page
├── page-pos.php                 ← POS interface
│
├── inc/                         ← Module files (like Laravel app/)
│   ├── orders.php               ← Order functions (OrderController logic)
│   ├── customers.php            ← Customer functions (CustomerController logic)
│   ├── notifications.php        ← Notification functions
│   └── pos.php                  ← POS functionality
│
└── assets/                      ← Laravel public/ equivalent
    ├── css/
    │   └── custom.css
    ├── js/
    │   └── main.js
    └── images/
```

---

## 🗄️ Database Migration

### **Laravel Tables → WordPress Mapping**

| Laravel Table | WordPress Equivalent | Method |
|--------------|---------------------|--------|
| `users` | `wp_users` + `wp_usermeta` | Use WP user system |
| `products` | `wp_posts` (post_type=product) + `wp_postmeta` | Custom Post Type |
| `orders` | `wp_posts` (post_type=order) + `wp_postmeta` | Custom Post Type |
| `customers` | `wp_posts` (post_type=customer) + `wp_postmeta` | Custom Post Type |
| `notifications` | `wp_posts` (post_type=notification) + `wp_postmeta` | Custom Post Type |

### **Migration Script (SQL)**

```sql
-- ============================================
-- MIGRATE PRODUCTS
-- ============================================

-- Insert products from Laravel to WordPress
INSERT INTO wp_posts (
    post_title,
    post_content,
    post_type,
    post_status,
    post_date,
    post_modified
)
SELECT
    name AS post_title,
    description AS post_content,
    'product' AS post_type,
    'publish' AS post_status,
    created_at AS post_date,
    updated_at AS post_modified
FROM laravel_database.products;

-- Migrate product meta (SKU, price, stock)
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
SELECT
    wp_posts.ID,
    '_product_sku',
    laravel_database.products.sku
FROM wp_posts
JOIN laravel_database.products ON wp_posts.post_title = laravel_database.products.name
WHERE wp_posts.post_type = 'product';

INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
SELECT
    wp_posts.ID,
    '_product_price',
    laravel_database.products.price
FROM wp_posts
JOIN laravel_database.products ON wp_posts.post_title = laravel_database.products.name
WHERE wp_posts.post_type = 'product';

INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
SELECT
    wp_posts.ID,
    '_product_stock',
    laravel_database.products.stock
FROM wp_posts
JOIN laravel_database.products ON wp_posts.post_title = laravel_database.products.name
WHERE wp_posts.post_type = 'product';

-- ============================================
-- MIGRATE ORDERS
-- ============================================

INSERT INTO wp_posts (
    post_title,
    post_content,
    post_type,
    post_status,
    post_date,
    post_author
)
SELECT
    CONCAT('Order #', id) AS post_title,
    notes AS post_content,
    'order' AS post_type,
    status AS post_status,
    created_at AS post_date,
    user_id AS post_author
FROM laravel_database.orders;

-- Migrate order meta
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
SELECT
    wp_posts.ID,
    '_order_total',
    laravel_database.orders.total
FROM wp_posts
JOIN laravel_database.orders ON CONCAT('Order #', laravel_database.orders.id) = wp_posts.post_title
WHERE wp_posts.post_type = 'order';

-- ============================================
-- MIGRATE USERS
-- ============================================

INSERT INTO wp_users (
    user_login,
    user_pass,
    user_nicename,
    user_email,
    user_registered,
    display_name
)
SELECT
    email AS user_login,
    password AS user_pass,
    name AS user_nicename,
    email AS user_email,
    created_at AS user_registered,
    name AS display_name
FROM laravel_database.users
WHERE NOT EXISTS (
    SELECT 1 FROM wp_users WHERE user_email = laravel_database.users.email
);
```

### **PHP Migration Helper**

Create `migrate-laravel-to-wp.php` in theme root:

```php
<?php
/**
 * Laravel to WordPress Database Migration Script
 *
 * Usage: php migrate-laravel-to-wp.php
 */

// Database connections
$laravel_db = new PDO('mysql:host=localhost;dbname=laravel_pos', 'root', 'password');
$wp_db = new PDO('mysql:host=localhost;dbname=wordpress', 'root', 'password');

// Migrate Products
function migrate_products($laravel_db, $wp_db) {
    $products = $laravel_db->query("SELECT * FROM products")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        // Insert into wp_posts
        $stmt = $wp_db->prepare("
            INSERT INTO wp_posts (post_title, post_content, post_type, post_status, post_date)
            VALUES (:title, :content, 'product', 'publish', :date)
        ");

        $stmt->execute([
            ':title' => $product['name'],
            ':content' => $product['description'],
            ':date' => $product['created_at'],
        ]);

        $post_id = $wp_db->lastInsertId();

        // Insert meta data
        $meta = [
            '_product_sku' => $product['sku'],
            '_product_price' => $product['price'],
            '_product_stock' => $product['stock'],
            '_product_category' => $product['category'],
        ];

        foreach ($meta as $key => $value) {
            $stmt = $wp_db->prepare("
                INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
                VALUES (:post_id, :meta_key, :meta_value)
            ");

            $stmt->execute([
                ':post_id' => $post_id,
                ':meta_key' => $key,
                ':meta_value' => $value,
            ]);
        }

        echo "Migrated product: {$product['name']}\n";
    }
}

// Migrate Orders
function migrate_orders($laravel_db, $wp_db) {
    $orders = $laravel_db->query("SELECT * FROM orders")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $order) {
        $stmt = $wp_db->prepare("
            INSERT INTO wp_posts (post_title, post_type, post_status, post_date, post_author)
            VALUES (:title, 'order', :status, :date, :author)
        ");

        $stmt->execute([
            ':title' => "Order #{$order['id']}",
            ':status' => $order['status'],
            ':date' => $order['created_at'],
            ':author' => $order['user_id'],
        ]);

        $post_id = $wp_db->lastInsertId();

        // Insert order meta
        $meta = [
            '_order_total' => $order['total'],
            '_order_items' => json_encode($order['items']),
            '_order_customer_id' => $order['customer_id'],
        ];

        foreach ($meta as $key => $value) {
            $stmt = $wp_db->prepare("
                INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
                VALUES (:post_id, :meta_key, :meta_value)
            ");

            $stmt->execute([
                ':post_id' => $post_id,
                ':meta_key' => $key,
                ':meta_value' => $value,
            ]);
        }

        echo "Migrated order: #{$order['id']}\n";
    }
}

// Run migrations
echo "Starting migration...\n";
migrate_products($laravel_db, $wp_db);
migrate_orders($laravel_db, $wp_db);
echo "Migration completed!\n";
```

---

## 🔄 Module Mapping (Laravel → WordPress)

### **1. Products Module (COMPLETE EXAMPLE)**

| Laravel | WordPress |
|---------|-----------|
| `app/Models/Product.php` | CPT 'product' + meta fields |
| `app/Http/Controllers/ProductController.php` | `functions.php` functions + REST API |
| `resources/views/products/index.blade.php` | `archive-product.php` |
| `resources/views/products/show.blade.php` | `single-product.php` |
| `Route::resource('products', ProductController::class)` | REST API endpoints |

**Already Created:**
- ✅ CPT registration in `functions.php`
- ✅ Meta boxes for product fields
- ✅ CRUD helper functions
- ✅ REST API endpoints
- ✅ Template files

### **2. Orders Module** (Follow same pattern)

Create `inc/orders.php`:

```php
<?php
/**
 * Orders Module
 * Laravel OrderController → WordPress Functions
 */

// Register Order Meta Boxes
function laravel_pos_add_order_meta_boxes() {
    add_meta_box(
        'order_details',
        'Order Details',
        'laravel_pos_order_meta_box_callback',
        'order',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'laravel_pos_add_order_meta_boxes');

// Order Meta Box Callback
function laravel_pos_order_meta_box_callback($post) {
    wp_nonce_field('laravel_pos_order_meta', 'laravel_pos_order_nonce');

    $total = get_post_meta($post->ID, '_order_total', true);
    $customer_id = get_post_meta($post->ID, '_order_customer_id', true);
    $items = get_post_meta($post->ID, '_order_items', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label>Order Total</label></th>
            <td><input type="number" name="order_total" value="<?php echo esc_attr($total); ?>" step="0.01" readonly /></td>
        </tr>
        <tr>
            <th><label>Customer</label></th>
            <td>
                <select name="order_customer_id">
                    <?php
                    $customers = get_posts(array('post_type' => 'customer', 'posts_per_page' => -1));
                    foreach ($customers as $customer) {
                        echo '<option value="' . $customer->ID . '" ' . selected($customer_id, $customer->ID, false) . '>' . $customer->post_title . '</option>';
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label>Order Items</label></th>
            <td><textarea name="order_items" rows="5" class="large-text"><?php echo esc_textarea($items); ?></textarea></td>
        </tr>
    </table>
    <?php
}

// Save Order Meta
function laravel_pos_save_order_meta($post_id) {
    if (!isset($_POST['laravel_pos_order_nonce'])) return;
    if (!wp_verify_nonce($_POST['laravel_pos_order_nonce'], 'laravel_pos_order_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['order_total'])) {
        update_post_meta($post_id, '_order_total', floatval($_POST['order_total']));
    }
    if (isset($_POST['order_customer_id'])) {
        update_post_meta($post_id, '_order_customer_id', intval($_POST['order_customer_id']));
    }
    if (isset($_POST['order_items'])) {
        update_post_meta($post_id, '_order_items', sanitize_textarea_field($_POST['order_items']));
    }
}
add_action('save_post_order', 'laravel_pos_save_order_meta');

// Get Order by ID
function laravel_pos_get_order($order_id) {
    $order = get_post($order_id);
    if (!$order || $order->post_type !== 'order') return null;

    return array(
        'id' => $order->ID,
        'title' => $order->post_title,
        'total' => get_post_meta($order->ID, '_order_total', true),
        'customer_id' => get_post_meta($order->ID, '_order_customer_id', true),
        'items' => json_decode(get_post_meta($order->ID, '_order_items', true), true),
        'status' => $order->post_status,
        'created_at' => $order->post_date,
    );
}

// Create Order
function laravel_pos_create_order($data) {
    $post_data = array(
        'post_title' => 'Order #' . time(),
        'post_type' => 'order',
        'post_status' => 'pending',
    );

    $order_id = wp_insert_post($post_data);
    if (is_wp_error($order_id)) return false;

    update_post_meta($order_id, '_order_total', floatval($data['total']));
    update_post_meta($order_id, '_order_customer_id', intval($data['customer_id']));
    update_post_meta($order_id, '_order_items', json_encode($data['items']));

    return $order_id;
}
```

**Create templates:**
- `archive-order.php` (list orders)
- `single-order.php` (order details)

---

## 🚀 Installation

### **Step 1: Install Theme**

```bash
# Copy theme to WordPress
cp -r wordpress-theme/laravel-pos-theme /path/to/wordpress/wp-content/themes/

# Or via ZIP
cd wordpress-theme
zip -r laravel-pos-theme.zip laravel-pos-theme/
# Upload via WordPress Admin → Appearance → Themes → Add New → Upload
```

### **Step 2: Activate Theme**

1. Login to WordPress Admin
2. Go to **Appearance → Themes**
3. Activate **Laravel POS Theme**

### **Step 3: Set Permalinks**

1. Go to **Settings → Permalinks**
2. Select **Post name**
3. Click **Save Changes**

### **Step 4: Create Pages**

Create these pages in **Pages → Add New**:
- Dashboard
- POS
- Products
- Orders
- Customers

### **Step 5: Set Up Menu**

1. Go to **Appearance → Menus**
2. Create a new menu called "Primary Menu"
3. Add pages: Dashboard, Products, Orders, Customers
4. Assign to "Primary Menu" location

### **Step 6: Run Migration**

```bash
# Upload migration script
php migrate-laravel-to-wp.php
```

---

## 🔧 Extending to Other Modules

### **Template for Any Module:**

```php
// 1. Register CPT in functions.php
function laravel_pos_register_[module]_cpt() {
    register_post_type('[module]', array(
        'labels' => array(/*...*/),
        'public' => true,
        'show_in_rest' => true,
        // ...
    ));
}
add_action('init', 'laravel_pos_register_[module]_cpt');

// 2. Create helper functions
function laravel_pos_get_[module]($id) { /* ... */ }
function laravel_pos_create_[module]($data) { /* ... */ }
function laravel_pos_update_[module]($id, $data) { /* ... */ }

// 3. Create REST API endpoints
register_rest_route('laravel-pos/v1', '/[module]', array(
    'methods' => 'GET',
    'callback' => 'laravel_pos_api_get_[module]s',
));

// 4. Create templates
// - archive-[module].php
// - single-[module].php
```

---

## ✅ Testing

### **Test REST API:**

```bash
# Get products
curl http://your-site.com/wp-json/laravel-pos/v1/products

# Get single product
curl http://your-site.com/wp-json/laravel-pos/v1/products/123

# Create product (requires authentication)
curl -X POST http://your-site.com/wp-json/laravel-pos/v1/products \
  -H "Content-Type: application/json" \
  -d '{"title":"New Product","price":99.99,"stock":10}'
```

### **Test Frontend:**

1. Visit `/products` - Should show product archive
2. Click on a product - Should show single product
3. Test add to cart functionality
4. Test search

---

## 📊 Summary

### **What's Included:**

✅ Complete WordPress theme structure
✅ Products module (fully functional)
✅ REST API endpoints
✅ Database migration scripts
✅ Template files (Blade equivalents)
✅ Helper functions (Model methods)
✅ Authentication integration

### **What You Need to Do:**

1. ⬜ Extend Orders module (follow Product example)
2. ⬜ Extend Customers module
3. ⬜ Extend Notifications module
4. ⬜ Add POS interface
5. ⬜ Customize CSS/JS
6. ⬜ Test all functionality

### **Resources:**

- WordPress Codex: https://codex.wordpress.org/
- REST API Handbook: https://developer.wordpress.org/rest-api/
- Custom Post Types: https://developer.wordpress.org/plugins/post-types/

---

**🎉 You now have a complete framework to convert your Laravel POS to WordPress!**

Use the Product module as a template for all other modules.

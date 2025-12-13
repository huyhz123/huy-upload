<?php
/**
 * Template Name: POS (Point of Sale)
 * Laravel equivalent: resources/views/pos/index.blade.php
 */

// Check if user has permission
if (!current_user_can('edit_posts')) {
    wp_redirect(home_url());
    exit;
}

get_header();

// Get stats for dashboard
$today_stats = laravel_pos_get_sales_stats('today');
$top_products = laravel_pos_get_top_products(5, 'today');
?>

<div class="pos-container">
    <div class="pos-header">
        <h1>Point of Sale</h1>
        <div class="pos-stats-mini">
            <div class="stat">
                <span class="stat-label">Today's Sales</span>
                <span class="stat-value">$<?php echo number_format($today_stats['total_sales'], 2); ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Orders</span>
                <span class="stat-value"><?php echo $today_stats['total_orders']; ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Avg Order</span>
                <span class="stat-value">$<?php echo number_format($today_stats['avg_order_value'], 2); ?></span>
            </div>
        </div>
    </div>

    <div class="pos-main">
        <!-- Left Panel: Product Search & Selection -->
        <div class="pos-products">
            <div class="product-search-bar">
                <input type="text" id="product-search" placeholder="Search products or scan barcode..." autocomplete="off">
                <button id="scan-barcode" class="btn btn-secondary">📷 Scan</button>
            </div>

            <div class="product-categories">
                <button class="category-btn active" data-category="all">All Products</button>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'product_category',
                    'hide_empty' => false,
                ));
                foreach ($categories as $category):
                    ?>
                    <button class="category-btn" data-category="<?php echo $category->slug; ?>">
                        <?php echo esc_html($category->name); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div id="products-grid" class="products-grid">
                <?php
                $products = laravel_pos_get_products(array('posts_per_page' => 50));
                foreach ($products as $product):
                    if ($product['stock'] <= 0) continue;
                    ?>
                    <div class="product-item" data-product='<?php echo json_encode($product); ?>'>
                        <?php if ($product['image']): ?>
                            <div class="product-img">
                                <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['title']); ?>">
                            </div>
                        <?php else: ?>
                            <div class="product-img no-image">
                                <span>📦</span>
                            </div>
                        <?php endif; ?>
                        <div class="product-info">
                            <h4><?php echo esc_html($product['title']); ?></h4>
                            <p class="product-sku"><?php echo esc_html($product['sku']); ?></p>
                            <div class="product-bottom">
                                <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
                                <span class="product-stock">Stock: <?php echo $product['stock']; ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Panel: Cart & Checkout -->
        <div class="pos-cart">
            <div class="cart-customer">
                <label for="cart-customer-select">Customer:</label>
                <select id="cart-customer-select">
                    <option value="">Walk-in Customer</option>
                    <?php
                    $customers = laravel_pos_get_customers();
                    foreach ($customers as $customer):
                        ?>
                        <option value="<?php echo $customer['id']; ?>">
                            <?php echo esc_html($customer['name']); ?>
                            <?php echo $customer['email'] ? ' - ' . esc_html($customer['email']) : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button id="add-customer" class="btn btn-sm">+</button>
            </div>

            <div class="cart-items" id="cart-items">
                <div class="empty-cart">
                    <p>Cart is empty</p>
                    <p>Scan or select products to add</p>
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="cart-subtotal">$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Tax (<input type="number" id="tax-rate" value="0" min="0" max="100" step="0.1" style="width: 50px;">%):</span>
                    <span id="cart-tax">$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Discount:</span>
                    <span>$<input type="number" id="discount-amount" value="0" min="0" step="0.01" style="width: 80px;"></span>
                </div>
                <div class="summary-row total-row">
                    <span>TOTAL:</span>
                    <span id="cart-total">$0.00</span>
                </div>
            </div>

            <div class="cart-payment">
                <label>Payment Method:</label>
                <div class="payment-methods">
                    <button class="payment-btn active" data-method="cash">💵 Cash</button>
                    <button class="payment-btn" data-method="card">💳 Card</button>
                    <button class="payment-btn" data-method="bank_transfer">🏦 Transfer</button>
                    <button class="payment-btn" data-method="online">🌐 Online</button>
                </div>
                <input type="hidden" id="payment-method" value="cash">
            </div>

            <div class="cart-actions">
                <button id="clear-cart" class="btn btn-secondary btn-lg">Clear Cart</button>
                <button id="process-sale" class="btn btn-success btn-lg">Process Sale</button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close">&times;</span>
        <div id="receipt-content"></div>
        <div class="modal-actions">
            <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
            <button id="new-sale" class="btn btn-secondary">New Sale</button>
        </div>
    </div>
</div>

<style>
    .pos-container {
        max-width: 100%;
        padding: 20px;
        background: #f5f5f5;
    }

    .pos-header {
        background: #fff;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pos-header h1 {
        margin: 0;
    }

    .pos-stats-mini {
        display: flex;
        gap: 30px;
    }

    .pos-stats-mini .stat {
        text-align: center;
    }

    .pos-stats-mini .stat-label {
        display: block;
        font-size: 12px;
        color: #666;
        margin-bottom: 5px;
    }

    .pos-stats-mini .stat-value {
        display: block;
        font-size: 20px;
        font-weight: bold;
        color: #28a745;
    }

    .pos-main {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 20px;
        min-height: calc(100vh - 200px);
    }

    .pos-products {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
    }

    .product-search-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    .product-search-bar input {
        flex: 1;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .product-categories {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .category-btn {
        padding: 8px 16px;
        border: 1px solid #ddd;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .category-btn.active,
    .category-btn:hover {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }

    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 15px;
        overflow-y: auto;
        max-height: calc(100vh - 400px);
    }

    .product-item {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }

    .product-item:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .product-img {
        width: 100%;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 4px;
        overflow: hidden;
    }

    .product-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
    }

    .product-img.no-image span {
        font-size: 40px;
    }

    .product-info h4 {
        margin: 0 0 5px 0;
        font-size: 14px;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-sku {
        font-size: 11px;
        color: #666;
        margin: 0 0 8px 0;
    }

    .product-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-price {
        font-weight: bold;
        color: #28a745;
        font-size: 14px;
    }

    .product-stock {
        font-size: 11px;
        color: #666;
    }

    .pos-cart {
        background: #fff;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
    }

    .cart-customer {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        align-items: center;
    }

    .cart-customer label {
        font-weight: 600;
    }

    .cart-customer select {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .cart-items {
        flex: 1;
        overflow-y: auto;
        max-height: 300px;
        margin-bottom: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px;
        min-height: 200px;
    }

    .empty-cart {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .cart-item {
        display: flex;
        gap: 10px;
        padding: 10px;
        border-bottom: 1px solid #f0f0f0;
        align-items: center;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-name {
        font-weight: 600;
        margin-bottom: 4px;
    }

    .cart-item-price {
        color: #666;
        font-size: 14px;
    }

    .cart-item-quantity {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .qty-btn {
        width: 30px;
        height: 30px;
        border: 1px solid #ddd;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }

    .qty-btn:hover {
        background: #f0f0f0;
    }

    .qty-input {
        width: 50px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 4px;
    }

    .cart-item-total {
        font-weight: bold;
        color: #28a745;
    }

    .cart-item-remove {
        color: #dc3545;
        cursor: pointer;
        font-size: 18px;
    }

    .cart-summary {
        border-top: 2px solid #ddd;
        padding-top: 15px;
        margin-bottom: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .summary-row.total-row {
        font-size: 20px;
        font-weight: bold;
        color: #28a745;
        border-top: 2px solid #ddd;
        padding-top: 15px;
        margin-top: 15px;
    }

    .cart-payment {
        margin-bottom: 20px;
    }

    .cart-payment label {
        display: block;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .payment-methods {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .payment-btn {
        padding: 15px;
        border: 2px solid #ddd;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
    }

    .payment-btn.active,
    .payment-btn:hover {
        border-color: #28a745;
        background: #28a745;
        color: white;
    }

    .cart-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .btn-lg {
        padding: 15px;
        font-size: 16px;
        font-weight: 600;
    }

    /* Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-content {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        max-width: 400px;
        position: relative;
    }

    .modal-close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 28px;
        cursor: pointer;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .modal-actions .btn {
        flex: 1;
    }

    @media (max-width: 1024px) {
        .pos-main {
            grid-template-columns: 1fr;
        }

        .pos-cart {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: 20px 20px 0 0;
            max-height: 60vh;
            z-index: 100;
        }
    }
</style>

<script>
let cart = [];
let paymentMethod = 'cash';

// Add product to cart
document.querySelectorAll('.product-item').forEach(item => {
    item.addEventListener('click', function() {
        const product = JSON.parse(this.dataset.product);
        addToCart(product);
    });
});

function addToCart(product) {
    const existingItem = cart.find(item => item.product_id === product.id);

    if (existingItem) {
        if (existingItem.quantity < product.stock) {
            existingItem.quantity++;
        } else {
            alert('Not enough stock!');
            return;
        }
    } else {
        cart.push({
            product_id: product.id,
            product_name: product.title,
            price: parseFloat(product.price),
            quantity: 1,
            stock: product.stock
        });
    }

    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateQuantity(index, quantity) {
    if (quantity <= 0) {
        removeFromCart(index);
        return;
    }

    if (quantity > cart[index].stock) {
        alert('Not enough stock!');
        return;
    }

    cart[index].quantity = quantity;
    renderCart();
}

function renderCart() {
    const cartItems = document.getElementById('cart-items');

    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="empty-cart"><p>Cart is empty</p><p>Scan or select products to add</p></div>';
        updateTotals();
        return;
    }

    let html = '';
    cart.forEach((item, index) => {
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.product_name}</div>
                    <div class="cart-item-price">$${item.price.toFixed(2)}</div>
                </div>
                <div class="cart-item-quantity">
                    <button class="qty-btn" onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                    <input type="number" class="qty-input" value="${item.quantity}" onchange="updateQuantity(${index}, parseInt(this.value))" min="1" max="${item.stock}">
                    <button class="qty-btn" onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                </div>
                <div class="cart-item-total">$${(item.price * item.quantity).toFixed(2)}</div>
                <div class="cart-item-remove" onclick="removeFromCart(${index})">×</div>
            </div>
        `;
    });

    cartItems.innerHTML = html;
    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const taxRate = parseFloat(document.getElementById('tax-rate').value) || 0;
    const discount = parseFloat(document.getElementById('discount-amount').value) || 0;
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount - discount;

    document.getElementById('cart-subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('cart-tax').textContent = '$' + taxAmount.toFixed(2);
    document.getElementById('cart-total').textContent = '$' + total.toFixed(2);
}

// Tax and discount change
document.getElementById('tax-rate').addEventListener('input', updateTotals);
document.getElementById('discount-amount').addEventListener('input', updateTotals);

// Payment method selection
document.querySelectorAll('.payment-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.payment-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        paymentMethod = this.dataset.method;
        document.getElementById('payment-method').value = paymentMethod;
    });
});

// Clear cart
document.getElementById('clear-cart').addEventListener('click', function() {
    if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        renderCart();
    }
});

// Process sale
document.getElementById('process-sale').addEventListener('click', function() {
    if (cart.length === 0) {
        alert('Cart is empty!');
        return;
    }

    const data = {
        items: cart,
        customer_id: document.getElementById('cart-customer-select').value,
        payment_method: paymentMethod,
        tax_rate: parseFloat(document.getElementById('tax-rate').value) || 0,
        discount: parseFloat(document.getElementById('discount-amount').value) || 0
    };

    fetch('/wp-json/laravel-pos/v1/pos/process-sale', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showReceipt(result.order_id);
            cart = [];
            renderCart();
        } else {
            alert('Error: ' + (result.message || 'Failed to process sale'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the sale');
    });
});

function showReceipt(orderId) {
    fetch('/wp-json/laravel-pos/v1/orders/' + orderId, {
        headers: {
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        }
    })
    .then(response => response.json())
    .then(order => {
        document.getElementById('receipt-content').innerHTML = generateReceipt(order);
        document.getElementById('receipt-modal').style.display = 'flex';
    });
}

function generateReceipt(order) {
    return '<?php echo addslashes(laravel_pos_print_receipt(0)); ?>'.replace(/Order #/g, 'Order #' + order.order_number);
}

// Close modal
document.querySelector('.modal-close').addEventListener('click', function() {
    document.getElementById('receipt-modal').style.display = 'none';
});

document.getElementById('new-sale').addEventListener('click', function() {
    document.getElementById('receipt-modal').style.display = 'none';
    location.reload();
});

// Product search
document.getElementById('product-search').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
        const product = JSON.parse(item.dataset.product);
        const matches = product.title.toLowerCase().includes(search) ||
                       product.sku.toLowerCase().includes(search);
        item.style.display = matches ? 'block' : 'none';
    });
});
</script>

<?php get_footer(); ?>

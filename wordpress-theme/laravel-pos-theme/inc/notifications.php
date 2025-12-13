<?php
/**
 * Notifications Module
 * Laravel equivalent: app/Models/Notification.php + NotificationController.php
 */

// Register Notification meta boxes
function laravel_pos_notification_meta_boxes() {
    add_meta_box(
        'notification_details',
        __('Notification Details', 'laravel-pos'),
        'laravel_pos_notification_details_callback',
        'notification',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'laravel_pos_notification_meta_boxes');

// Notification details meta box callback
function laravel_pos_notification_details_callback($post) {
    wp_nonce_field('laravel_pos_save_notification', 'laravel_pos_notification_nonce');

    $notification_type = get_post_meta($post->ID, '_notification_type', true);
    $priority = get_post_meta($post->ID, '_notification_priority', true);
    $status = get_post_meta($post->ID, '_notification_status', true);
    $user_id = get_post_meta($post->ID, '_notification_user_id', true);
    $link = get_post_meta($post->ID, '_notification_link', true);
    $icon = get_post_meta($post->ID, '_notification_icon', true);
    $action_text = get_post_meta($post->ID, '_notification_action_text', true);
    ?>

    <div class="notification-meta-fields">
        <div class="meta-row">
            <div class="meta-col">
                <p>
                    <label for="notification_type"><strong><?php _e('Type:', 'laravel-pos'); ?></strong></label><br>
                    <select id="notification_type" name="notification_type" style="width: 100%;">
                        <option value="info" <?php selected($notification_type, 'info'); ?>><?php _e('Info', 'laravel-pos'); ?></option>
                        <option value="success" <?php selected($notification_type, 'success'); ?>><?php _e('Success', 'laravel-pos'); ?></option>
                        <option value="warning" <?php selected($notification_type, 'warning'); ?>><?php _e('Warning', 'laravel-pos'); ?></option>
                        <option value="error" <?php selected($notification_type, 'error'); ?>><?php _e('Error', 'laravel-pos'); ?></option>
                        <option value="order" <?php selected($notification_type, 'order'); ?>><?php _e('Order Update', 'laravel-pos'); ?></option>
                        <option value="payment" <?php selected($notification_type, 'payment'); ?>><?php _e('Payment', 'laravel-pos'); ?></option>
                        <option value="system" <?php selected($notification_type, 'system'); ?>><?php _e('System', 'laravel-pos'); ?></option>
                    </select>
                </p>
            </div>
            <div class="meta-col">
                <p>
                    <label for="notification_priority"><strong><?php _e('Priority:', 'laravel-pos'); ?></strong></label><br>
                    <select id="notification_priority" name="notification_priority" style="width: 100%;">
                        <option value="low" <?php selected($priority, 'low'); ?>><?php _e('Low', 'laravel-pos'); ?></option>
                        <option value="normal" <?php selected($priority, 'normal'); ?>><?php _e('Normal', 'laravel-pos'); ?></option>
                        <option value="high" <?php selected($priority, 'high'); ?>><?php _e('High', 'laravel-pos'); ?></option>
                        <option value="urgent" <?php selected($priority, 'urgent'); ?>><?php _e('Urgent', 'laravel-pos'); ?></option>
                    </select>
                </p>
            </div>
        </div>

        <div class="meta-row">
            <div class="meta-col">
                <p>
                    <label for="notification_status"><strong><?php _e('Status:', 'laravel-pos'); ?></strong></label><br>
                    <select id="notification_status" name="notification_status" style="width: 100%;">
                        <option value="unread" <?php selected($status, 'unread'); ?>><?php _e('Unread', 'laravel-pos'); ?></option>
                        <option value="read" <?php selected($status, 'read'); ?>><?php _e('Read', 'laravel-pos'); ?></option>
                        <option value="archived" <?php selected($status, 'archived'); ?>><?php _e('Archived', 'laravel-pos'); ?></option>
                    </select>
                </p>
            </div>
            <div class="meta-col">
                <p>
                    <label for="notification_user_id"><strong><?php _e('User:', 'laravel-pos'); ?></strong></label><br>
                    <select id="notification_user_id" name="notification_user_id" style="width: 100%;">
                        <option value=""><?php _e('All Users (Broadcast)', 'laravel-pos'); ?></option>
                        <?php
                        $users = get_users();
                        foreach ($users as $user):
                            ?>
                            <option value="<?php echo $user->ID; ?>" <?php selected($user_id, $user->ID); ?>>
                                <?php echo esc_html($user->display_name . ' (' . $user->user_email . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
            </div>
        </div>

        <p>
            <label for="notification_icon"><strong><?php _e('Icon (Emoji or Font Awesome class):', 'laravel-pos'); ?></strong></label><br>
            <input type="text" id="notification_icon" name="notification_icon" value="<?php echo esc_attr($icon); ?>" style="width: 100%;" placeholder="🔔 or fa-bell">
        </p>

        <p>
            <label for="notification_link"><strong><?php _e('Link/URL:', 'laravel-pos'); ?></strong></label><br>
            <input type="url" id="notification_link" name="notification_link" value="<?php echo esc_attr($link); ?>" style="width: 100%;" placeholder="https://...">
        </p>

        <p>
            <label for="notification_action_text"><strong><?php _e('Action Button Text:', 'laravel-pos'); ?></strong></label><br>
            <input type="text" id="notification_action_text" name="notification_action_text" value="<?php echo esc_attr($action_text); ?>" style="width: 100%;" placeholder="View Details">
        </p>
    </div>

    <style>
        .notification-meta-fields p { margin-bottom: 15px; }
        .notification-meta-fields input[type="text"],
        .notification-meta-fields input[type="url"],
        .notification-meta-fields select {
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

// Save Notification meta
function laravel_pos_save_notification_meta($post_id) {
    if (!isset($_POST['laravel_pos_notification_nonce']) ||
        !wp_verify_nonce($_POST['laravel_pos_notification_nonce'], 'laravel_pos_save_notification')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'notification_type',
        'notification_priority',
        'notification_status',
        'notification_user_id',
        'notification_link',
        'notification_icon',
        'notification_action_text',
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post_notification', 'laravel_pos_save_notification_meta');

// CRUD Helper Functions

/**
 * Get Notification by ID
 * Laravel: Notification::find($id)
 */
function laravel_pos_get_notification($notification_id) {
    $notification = get_post($notification_id);

    if (!$notification || $notification->post_type !== 'notification') {
        return null;
    }

    return array(
        'id' => $notification->ID,
        'title' => $notification->post_title,
        'message' => $notification->post_content,
        'type' => get_post_meta($notification->ID, '_notification_type', true) ?: 'info',
        'priority' => get_post_meta($notification->ID, '_notification_priority', true) ?: 'normal',
        'status' => get_post_meta($notification->ID, '_notification_status', true) ?: 'unread',
        'user_id' => get_post_meta($notification->ID, '_notification_user_id', true),
        'link' => get_post_meta($notification->ID, '_notification_link', true),
        'icon' => get_post_meta($notification->ID, '_notification_icon', true),
        'action_text' => get_post_meta($notification->ID, '_notification_action_text', true),
        'created_at' => $notification->post_date,
        'updated_at' => $notification->post_modified,
    );
}

/**
 * Get all Notifications
 * Laravel: Notification::all()
 */
function laravel_pos_get_notifications($args = array()) {
    $default_args = array(
        'post_type' => 'notification',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    $args = wp_parse_args($args, $default_args);
    $notifications = get_posts($args);

    $result = array();
    foreach ($notifications as $notification) {
        $result[] = laravel_pos_get_notification($notification->ID);
    }

    return $result;
}

/**
 * Get notifications for specific user
 * Laravel: Notification::where('user_id', $userId)
 */
function laravel_pos_get_user_notifications($user_id, $status = null) {
    $args = array(
        'post_type' => 'notification',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => '_notification_user_id',
                'value' => $user_id,
            ),
            array(
                'key' => '_notification_user_id',
                'value' => '',
            ),
        ),
    );

    if ($status) {
        $args['meta_query'][] = array(
            'key' => '_notification_status',
            'value' => $status,
        );
    }

    return laravel_pos_get_notifications($args);
}

/**
 * Create Notification
 * Laravel: Notification::create($data)
 */
function laravel_pos_create_notification($data) {
    $notification_data = array(
        'post_title' => sanitize_text_field($data['title']),
        'post_content' => sanitize_textarea_field($data['message'] ?? ''),
        'post_type' => 'notification',
        'post_status' => 'publish',
    );

    $notification_id = wp_insert_post($notification_data);

    if (is_wp_error($notification_id)) {
        return false;
    }

    $meta_fields = array(
        'type', 'priority', 'status', 'user_id', 'link', 'icon', 'action_text',
    );

    foreach ($meta_fields as $field) {
        if (isset($data[$field])) {
            update_post_meta($notification_id, '_notification_' . $field, sanitize_text_field($data[$field]));
        }
    }

    return $notification_id;
}

/**
 * Update Notification
 * Laravel: $notification->update($data)
 */
function laravel_pos_update_notification($notification_id, $data) {
    $notification = get_post($notification_id);

    if (!$notification || $notification->post_type !== 'notification') {
        return false;
    }

    $update_data = array('ID' => $notification_id);

    if (isset($data['title'])) {
        $update_data['post_title'] = sanitize_text_field($data['title']);
    }

    if (isset($data['message'])) {
        $update_data['post_content'] = sanitize_textarea_field($data['message']);
    }

    if (count($update_data) > 1) {
        wp_update_post($update_data);
    }

    $meta_fields = array(
        'type', 'priority', 'status', 'user_id', 'link', 'icon', 'action_text',
    );

    foreach ($meta_fields as $field) {
        if (isset($data[$field])) {
            update_post_meta($notification_id, '_notification_' . $field, sanitize_text_field($data[$field]));
        }
    }

    return true;
}

/**
 * Mark notification as read
 * Laravel: $notification->markAsRead()
 */
function laravel_pos_mark_notification_read($notification_id) {
    return laravel_pos_update_notification($notification_id, array('status' => 'read'));
}

/**
 * Delete Notification
 * Laravel: $notification->delete()
 */
function laravel_pos_delete_notification($notification_id) {
    $notification = get_post($notification_id);

    if (!$notification || $notification->post_type !== 'notification') {
        return false;
    }

    return wp_delete_post($notification_id, true);
}

// REST API Endpoints

function laravel_pos_register_notification_rest_routes() {
    // GET /wp-json/laravel-pos/v1/notifications
    register_rest_route('laravel-pos/v1', '/notifications', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_notifications',
        'permission_callback' => '__return_true',
    ));

    // GET /wp-json/laravel-pos/v1/notifications/user/{user_id}
    register_rest_route('laravel-pos/v1', '/notifications/user/(?P<user_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_user_notifications',
        'permission_callback' => '__return_true',
    ));

    // GET /wp-json/laravel-pos/v1/notifications/{id}
    register_rest_route('laravel-pos/v1', '/notifications/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'laravel_pos_api_get_notification',
        'permission_callback' => '__return_true',
    ));

    // POST /wp-json/laravel-pos/v1/notifications
    register_rest_route('laravel-pos/v1', '/notifications', array(
        'methods' => 'POST',
        'callback' => 'laravel_pos_api_create_notification',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    // PUT /wp-json/laravel-pos/v1/notifications/{id}
    register_rest_route('laravel-pos/v1', '/notifications/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'laravel_pos_api_update_notification',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));

    // POST /wp-json/laravel-pos/v1/notifications/{id}/mark-read
    register_rest_route('laravel-pos/v1', '/notifications/(?P<id>\d+)/mark-read', array(
        'methods' => 'POST',
        'callback' => 'laravel_pos_api_mark_notification_read',
        'permission_callback' => '__return_true',
    ));

    // DELETE /wp-json/laravel-pos/v1/notifications/{id}
    register_rest_route('laravel-pos/v1', '/notifications/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'laravel_pos_api_delete_notification',
        'permission_callback' => 'laravel_pos_check_admin_permission',
    ));
}
add_action('rest_api_init', 'laravel_pos_register_notification_rest_routes');

// REST API Callbacks

function laravel_pos_api_get_notifications($request) {
    $notifications = laravel_pos_get_notifications();
    return rest_ensure_response($notifications);
}

function laravel_pos_api_get_user_notifications($request) {
    $user_id = $request['user_id'];
    $status = $request->get_param('status');
    $notifications = laravel_pos_get_user_notifications($user_id, $status);
    return rest_ensure_response($notifications);
}

function laravel_pos_api_get_notification($request) {
    $notification_id = $request['id'];
    $notification = laravel_pos_get_notification($notification_id);

    if (!$notification) {
        return new WP_Error('notification_not_found', 'Notification not found', array('status' => 404));
    }

    return rest_ensure_response($notification);
}

function laravel_pos_api_create_notification($request) {
    $data = $request->get_json_params();
    $notification_id = laravel_pos_create_notification($data);

    if (!$notification_id) {
        return new WP_Error('notification_creation_failed', 'Failed to create notification', array('status' => 500));
    }

    $notification = laravel_pos_get_notification($notification_id);
    return rest_ensure_response($notification);
}

function laravel_pos_api_update_notification($request) {
    $notification_id = $request['id'];
    $data = $request->get_json_params();

    $success = laravel_pos_update_notification($notification_id, $data);

    if (!$success) {
        return new WP_Error('notification_update_failed', 'Failed to update notification', array('status' => 500));
    }

    $notification = laravel_pos_get_notification($notification_id);
    return rest_ensure_response($notification);
}

function laravel_pos_api_mark_notification_read($request) {
    $notification_id = $request['id'];
    $success = laravel_pos_mark_notification_read($notification_id);

    if (!$success) {
        return new WP_Error('notification_update_failed', 'Failed to mark notification as read', array('status' => 500));
    }

    $notification = laravel_pos_get_notification($notification_id);
    return rest_ensure_response($notification);
}

function laravel_pos_api_delete_notification($request) {
    $notification_id = $request['id'];
    $success = laravel_pos_delete_notification($notification_id);

    if (!$success) {
        return new WP_Error('notification_deletion_failed', 'Failed to delete notification', array('status' => 500));
    }

    return rest_ensure_response(array('success' => true, 'message' => 'Notification deleted'));
}

// AJAX Handler - Get unread count
function laravel_pos_ajax_get_unread_count() {
    $user_id = get_current_user_id();
    $notifications = laravel_pos_get_user_notifications($user_id, 'unread');

    wp_send_json_success(array('count' => count($notifications)));
}
add_action('wp_ajax_laravel_pos_get_unread_count', 'laravel_pos_ajax_get_unread_count');

// Shortcode: [notifications_list]
function laravel_pos_notifications_list_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 10,
        'user_id' => get_current_user_id(),
        'status' => '',
    ), $atts);

    $notifications = $atts['user_id']
        ? laravel_pos_get_user_notifications($atts['user_id'], $atts['status'])
        : laravel_pos_get_notifications(array('posts_per_page' => intval($atts['limit'])));

    ob_start();
    ?>
    <div class="notifications-widget">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item notification-<?php echo esc_attr($notification['type']); ?> notification-<?php echo esc_attr($notification['status']); ?>">
                    <?php if ($notification['icon']): ?>
                        <div class="notification-icon"><?php echo esc_html($notification['icon']); ?></div>
                    <?php endif; ?>
                    <div class="notification-content">
                        <h4><?php echo esc_html($notification['title']); ?></h4>
                        <?php if ($notification['message']): ?>
                            <p><?php echo esc_html($notification['message']); ?></p>
                        <?php endif; ?>
                        <span class="notification-time"><?php echo human_time_diff(strtotime($notification['created_at']), current_time('timestamp')) . ' ago'; ?></span>
                    </div>
                    <?php if ($notification['link'] && $notification['action_text']): ?>
                        <div class="notification-action">
                            <a href="<?php echo esc_url($notification['link']); ?>" class="btn btn-sm btn-primary">
                                <?php echo esc_html($notification['action_text']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-notifications">No notifications.</p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('notifications_list', 'laravel_pos_notifications_list_shortcode');

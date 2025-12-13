<?php
/**
 * Template for displaying notification archive
 * Laravel equivalent: resources/views/notifications/index.blade.php
 */

get_header();

$current_user_id = get_current_user_id();
?>

<div class="container">
    <div class="main-content">
        <h1 class="page-title">Notifications</h1>

        <div class="notifications-toolbar">
            <div class="notification-filters">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="unread">Unread</button>
                <button class="filter-btn" data-filter="read">Read</button>
            </div>

            <div class="notification-actions">
                <button id="mark-all-read" class="btn btn-secondary">Mark All as Read</button>
                <?php if (current_user_can('edit_posts')): ?>
                    <a href="<?php echo admin_url('post-new.php?post_type=notification'); ?>" class="btn btn-success">Create Notification</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="notifications-stats">
            <?php
            $user_notifications = laravel_pos_get_user_notifications($current_user_id);
            $total = count($user_notifications);
            $unread = count(array_filter($user_notifications, fn($n) => $n['status'] === 'unread'));
            $read = count(array_filter($user_notifications, fn($n) => $n['status'] === 'read'));
            ?>
            <div class="stat-card">
                <div class="stat-label">Total</div>
                <div class="stat-value"><?php echo number_format($total); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Unread</div>
                <div class="stat-value unread"><?php echo number_format($unread); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Read</div>
                <div class="stat-value"><?php echo number_format($read); ?></div>
            </div>
        </div>

        <div class="notifications-list">
            <?php
            if (have_posts()):
                while (have_posts()): the_post();
                    $notification = laravel_pos_get_notification(get_the_ID());

                    // Check if notification is for current user or broadcast
                    if ($notification['user_id'] && $notification['user_id'] != $current_user_id) {
                        continue;
                    }

                    $type_class = 'notification-' . $notification['type'];
                    $status_class = 'notification-' . $notification['status'];
                    $priority_class = 'priority-' . $notification['priority'];
                    ?>
                    <div class="notification-item <?php echo esc_attr($type_class . ' ' . $status_class . ' ' . $priority_class); ?>" data-notification-id="<?php echo $notification['id']; ?>" data-status="<?php echo esc_attr($notification['status']); ?>">
                        <div class="notification-indicator">
                            <?php if ($notification['status'] === 'unread'): ?>
                                <span class="unread-dot"></span>
                            <?php endif; ?>
                        </div>

                        <div class="notification-icon">
                            <?php if ($notification['icon']): ?>
                                <?php echo esc_html($notification['icon']); ?>
                            <?php else: ?>
                                🔔
                            <?php endif; ?>
                        </div>

                        <div class="notification-content">
                            <div class="notification-header">
                                <h3><?php echo esc_html($notification['title']); ?></h3>
                                <span class="notification-type-badge type-<?php echo esc_attr($notification['type']); ?>">
                                    <?php echo esc_html(ucfirst($notification['type'])); ?>
                                </span>
                            </div>

                            <?php if ($notification['message']): ?>
                                <p class="notification-message"><?php echo esc_html($notification['message']); ?></p>
                            <?php endif; ?>

                            <div class="notification-meta">
                                <span class="notification-time">
                                    <?php echo human_time_diff(strtotime($notification['created_at']), current_time('timestamp')) . ' ago'; ?>
                                </span>

                                <?php if ($notification['priority'] !== 'normal'): ?>
                                    <span class="priority-badge priority-<?php echo esc_attr($notification['priority']); ?>">
                                        <?php echo esc_html(ucfirst($notification['priority'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="notification-actions">
                            <?php if ($notification['link'] && $notification['action_text']): ?>
                                <a href="<?php echo esc_url($notification['link']); ?>" class="btn btn-sm btn-primary action-btn">
                                    <?php echo esc_html($notification['action_text']); ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($notification['status'] === 'unread'): ?>
                                <button class="btn btn-sm btn-secondary mark-read-btn" onclick="markAsRead(<?php echo $notification['id']; ?>)">
                                    Mark as Read
                                </button>
                            <?php endif; ?>

                            <?php if (current_user_can('delete_post', get_the_ID())): ?>
                                <button class="btn btn-sm btn-danger delete-btn" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                                    Delete
                                </button>
                            <?php endif; ?>
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
                <div class="no-notifications">
                    <p>No notifications found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .notifications-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        gap: 20px;
        flex-wrap: wrap;
    }

    .notification-filters {
        display: flex;
        gap: 10px;
    }

    .filter-btn {
        padding: 8px 20px;
        border: 1px solid #ddd;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .filter-btn.active,
    .filter-btn:hover {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }

    .notification-actions {
        display: flex;
        gap: 10px;
    }

    .notifications-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
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

    .stat-value.unread {
        color: #dc3545;
    }

    .notifications-list {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
    }

    .notification-item {
        display: grid;
        grid-template-columns: auto auto 1fr auto;
        gap: 15px;
        padding: 20px;
        border-bottom: 1px solid #f0f0f0;
        align-items: start;
        transition: background 0.2s;
    }

    .notification-item:last-child {
        border-bottom: none;
    }

    .notification-item:hover {
        background: #f8f9fa;
    }

    .notification-item.notification-unread {
        background: #f0f8ff;
    }

    .notification-indicator {
        width: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .unread-dot {
        width: 8px;
        height: 8px;
        background: #007bff;
        border-radius: 50%;
    }

    .notification-icon {
        font-size: 32px;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 50%;
    }

    .notification-content {
        flex: 1;
    }

    .notification-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .notification-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .notification-type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .type-info { background: #d1ecf1; color: #0c5460; }
    .type-success { background: #d4edda; color: #155724; }
    .type-warning { background: #fff3cd; color: #856404; }
    .type-error { background: #f8d7da; color: #721c24; }
    .type-order { background: #cce5ff; color: #004085; }
    .type-payment { background: #d4edda; color: #155724; }
    .type-system { background: #e2e3e5; color: #383d41; }

    .notification-message {
        margin: 0 0 10px 0;
        color: #666;
        line-height: 1.6;
    }

    .notification-meta {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .notification-time {
        font-size: 12px;
        color: #999;
    }

    .priority-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .priority-low { background: #e2e3e5; color: #383d41; }
    .priority-normal { background: #cce5ff; color: #004085; }
    .priority-high { background: #fff3cd; color: #856404; }
    .priority-urgent { background: #f8d7da; color: #721c24; }

    .notification-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 120px;
    }

    .notification-actions .btn {
        white-space: nowrap;
    }

    .no-notifications {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    @media (max-width: 768px) {
        .notifications-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .notification-filters,
        .notification-actions {
            width: 100%;
        }

        .notifications-stats {
            grid-template-columns: 1fr;
        }

        .notification-item {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .notification-actions {
            flex-direction: row;
            width: 100%;
        }
    }
</style>

<script>
// Filter notifications by status
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filter = this.dataset.filter;
        const notifications = document.querySelectorAll('.notification-item');

        notifications.forEach(notification => {
            const status = notification.dataset.status;
            if (filter === 'all' || status === filter) {
                notification.style.display = 'grid';
            } else {
                notification.style.display = 'none';
            }
        });
    });
});

// Mark as read function
function markAsRead(notificationId) {
    fetch('/wp-json/laravel-pos/v1/notifications/' + notificationId + '/mark-read', {
        method: 'POST',
        headers: {
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'read') {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Mark all as read
document.getElementById('mark-all-read')?.addEventListener('click', function() {
    const unreadNotifications = document.querySelectorAll('.notification-item[data-status="unread"]');
    unreadNotifications.forEach(notification => {
        const id = notification.dataset.notificationId;
        markAsRead(id);
    });
});

// Delete notification
function deleteNotification(notificationId) {
    if (!confirm('Are you sure you want to delete this notification?')) {
        return;
    }

    fetch('/wp-json/laravel-pos/v1/notifications/' + notificationId, {
        method: 'DELETE',
        headers: {
            'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to delete notification');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the notification');
    });
}
</script>

<?php get_footer(); ?>

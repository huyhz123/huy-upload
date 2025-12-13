<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <?php if (has_custom_logo()): ?>
                    <?php the_custom_logo(); ?>
                <?php else: ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <h1><?php bloginfo('name'); ?></h1>
                    </a>
                <?php endif; ?>
            </div>

            <nav class="main-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'nav-menu',
                    'fallback_cb' => false,
                ));
                ?>
            </nav>

            <?php if (is_user_logged_in()): ?>
                <div class="user-menu">
                    <a href="<?php echo esc_url(home_url('/dashboard')); ?>" class="btn btn-primary">Dashboard</a>
                    <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn">Logout</a>
                </div>
            <?php else: ?>
                <div class="auth-links">
                    <a href="<?php echo wp_login_url(); ?>" class="btn">Login</a>
                    <a href="<?php echo wp_registration_url(); ?>" class="btn btn-primary">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

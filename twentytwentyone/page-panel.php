<?php

/**
 * Template Name: Panel
 * Template Post Type: page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/logowanie'));
    exit;
}

get_header();

$current_user = wp_get_current_user();
$current_user_id = get_current_user_id();
global $wpdb;

?>

<div id="panel-container">
    <nav class="panel-nav">
        <a href="<?php echo esc_url(home_url('/')); ?>"><button>Home</button></a>
        <a href="<?php echo esc_url(home_url('/kalkulator')); ?>"><button>Kalkulator</button></a>
        <a href="<?php echo esc_url(home_url('/edycjaprofilu')); ?>"><button>Edytuj profil</button></a>
        <a href="<?php echo esc_url(home_url('/zaproszenie')); ?>"><button>Wyślij zaproszenie</button></a>
        <a href="<?php echo esc_url(wp_logout_url(home_url('/logowanie'))); ?>"><button>Wyloguj się</button></a>
    </nav>

    <div class="userinfo">
        <h2>Witaj, <?php echo esc_html($current_user->display_name); ?>!</h2>
        <p>Twój e-mail: <?php echo esc_html($current_user->user_email); ?></p>
        <p>Tworzysz parę z: <?php echo get_user_partner_info($current_user->ID); ?></p>
        <p>Twoja rola: <?php echo esc_html(get_user_role_label($current_user->ID)); ?></p>
    </div>

    <div id="results-container">
        <div class="results-wrapper">
            <h3>Twoje wyniki</h3>
            <div class="user-results">
                <?php
                render_user_calculator_results($current_user_id);
                ?>
            </div>
        </div>

        <div class="results-wrapper">
            <h3>Wyniki partnera</h3>
            <div class="partner-results">
                <?php
                get_partner_results($current_user_id);
                ?>
            </div>
        </div>
    </div>
    <div id="result-details"></div>
    <!-- echo '<div id="result-details"></div>'; -->
</div>

<?php get_footer(); ?>
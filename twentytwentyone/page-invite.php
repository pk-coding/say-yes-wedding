<?php

/**
 * Template Name: Invite
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
?>
<nav class="panel-nav">
    <a href="<?php echo esc_url(home_url('/')); ?>"><button>Home</button></a>
    <a href="<?php echo esc_url(home_url('/panel')); ?>"><button>Panel</button></a>
    <a href="<?php echo esc_url(home_url('/kalkulator')); ?>"><button>Kalkulator</button></a>
    <a href="<?php echo esc_url(home_url('/edycjaprofilu')); ?>"><button>Edytuj profil</button></a>
    <a href="<?php echo esc_url(wp_logout_url(home_url('/logowanie'))); ?>"><button>Wyloguj się</button></a>
</nav>

<div id="invite-form">
    <input type="email" id="invite-email" placeholder="Wpisz email" required />
    <button id="invite-send-btn">Zaproś</button>
    <div id="invite-message"></div>
</div>

<?php get_footer(); ?>
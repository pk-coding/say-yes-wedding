<?php

/**
 * Template Name: RemovePartnerConfirm
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

$user_id = get_current_user_id();
$token = $_GET['remove_partner_token'] ?? '';

if (!$token) {
    echo '<p>Brak tokena usunięcia relacji.</p>';
    get_footer();
    exit;
}

$result = confirm_remove_partner($user_id, $token);

if (is_wp_error($result)) {
    echo '<p>Wystąpił błąd: ' . esc_html($result->get_error_message()) . '</p>';
} else {
    echo '<p>Relacja z partnerem została usunięta pomyślnie.</p>';
}

get_footer();

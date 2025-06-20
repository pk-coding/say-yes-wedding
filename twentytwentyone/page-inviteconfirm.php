<?php

/**
 * Template Name: InviteConfirm
 * Template Post Type: page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();

$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';

?>

<div id="inviteconfirm-container">
    <div id="invite-confirm-root" data-token="<?php echo esc_attr($token); ?>">
        <h1>Aktywacja zaproszenia</h1>
        <div id="invite-confirm-actions" style="margin-top:20px; display:none;">
            <button id="invite-accept-btn">Akceptuj zaproszenie</button>
            <button id="invite-reject-btn">Odrzuć zaproszenie</button>
            <?php if (!is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(home_url('/logowanie')); ?>" target="_blank"><button>Zaloguj się</button></a>
                <a href="<?php echo esc_url(home_url('/rejestracja')); ?>" target="_blank"><button>Zarejestruj się</button></a>
            <?php endif; ?>
        </div>
        <div id="invite-confirm-message"></div>
    </div>
</div>

<?php get_footer(); ?>
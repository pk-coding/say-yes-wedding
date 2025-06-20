<?php

/**
 * Template Name: PasswordReset
 * Template Post Type: page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

$reset_key = $_GET['key'] ?? '';
$login = $_GET['login'] ?? '';

if (!$reset_key && !$login) {
    wp_redirect(home_url('/logowanie'));
    exit;
}

get_header();

?>

<div id="passwordreset-container">
    <main class="password-reset-container">
        <?php

        if ($reset_key && $login): ?>
            <h2>Ustaw nowe hasło</h2>
            <form id="password-reset-form">
                <input type="hidden" id="reset-key" value="<?php echo esc_attr($reset_key); ?>">
                <input type="hidden" id="reset-login" value="<?php echo esc_attr($login); ?>">

                <label for="new-password">Nowe hasło</label>
                <input type="password" id="new-password" required>

                <label for="confirm-password">Powtórz hasło</label>
                <input type="password" id="confirm-password" required>

                <button type="submit">Zmień hasło</button>
                <div id="reset-message"></div>
            </form>
        <?php else: ?>
            <div>Strona nieaktywna.</div>
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>
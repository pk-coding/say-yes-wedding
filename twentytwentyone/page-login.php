<?php

/**
 * Template Name: Login
 * Template Post Type: page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();

?>

<div id="login-container">
    <nav class="panel-nav">
        <a href="<?php echo esc_url(home_url('/')); ?>"><button>Home</button></a>
        <a href="<?php echo esc_url(home_url('/kalkulator')); ?>"><button>Kalkulator</button></a>
    </nav>

    <div class="login-form-container">
        <form id="login-form">
            <h2>Zaloguj się</h2>

            <label for="username">Nazwa użytkownika lub email</label>
            <input type="text" id="login-username" name="username" required>

            <label for="password">Hasło</label>
            <input type="password" id="login-password" name="password" required>

            <div>Zapoznaj się z </br><a href="<?php echo esc_url(home_url('/rodo')); ?>" target="_blank">Polityką Prywatności</a></div>

            <button type="submit">Zaloguj</button>
            <div id="login-message"></div>
            <div>Nie masz jeszcze konta?</br><a href="<?php echo esc_url(home_url('/rejestracja')); ?>">Zarejestruj się</a></div>

            <button type="button" id="forgot-password-btn">Zapomniałeś hasło?</button>

        </form>
    </div>
</div>

<?php get_footer(); ?>
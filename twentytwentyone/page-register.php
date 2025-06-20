<?php

/**
 * Template Name: Register
 * Template Post Type: page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();

?>

<div id="register-container">
    <nav class="panel-nav">
        <a href="<?php echo esc_url(home_url('/')); ?>"><button>Home</button></a>
        <a href="<?php echo esc_url(home_url('/kalkulator')); ?>"><button>Kalkulator</button></a>
    </nav>

    <div class="register-form-container">
        <form id="register-form">
            <h2>Zarejestruj się</h2>

            <label for="username">Nazwa użytkownika</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Hasło</label>
            <input type="password" id="password" name="password" required>

            <label for="password_confirm">Powtórz hasło</label>
            <input type="password" id="password_confirm" name="password_confirm" required>

            <div class="terms-wrapper">
                <p>Wybierz rolę:</p>
                <label>
                    <input type="radio" name="role" value="pan_mlody" required> Pan młody
                </label>
                <label>
                    <input type="radio" name="role" value="panna_mloda"> Panna młoda
                </label>

                <p>Zaakceptuj regulamin:</p>
                <label>
                    <input type="checkbox" name="statute" value="1" required>
                    <span>Akceptuję <a href="<?php echo esc_url(home_url('/regulamin')); ?>" target="_blank">Regulamin</a></span>
                </label>

                <p>Zaakceptuj RODO:</p>
                <label>
                    <input type="checkbox" name="rodo" value="1" required>
                    <span>Akceptuję <a href="<?php echo esc_url(home_url('/rodo')); ?>" target="_blank">Politykę Prywatności</a></span>
                </label>
            </div>

            <button type="submit">Zarejestruj</button>
            <div id="form-message"></div>
            <div>Masz już konto?</br><a href="<?php echo esc_url(home_url('/logowanie')); ?>">Zaloguj się</a></div>
        </form>
    </div>
</div>

<?php
get_footer();

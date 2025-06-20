<?php

/**
 * Template Name: EditProfile
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

<div id="editprofile-container">
    <nav class="panel-nav">
        <a href="<?php echo esc_url(home_url('/')); ?>"><button>Home</button></a>
        <a href="<?php echo esc_url(home_url('/panel')); ?>"><button>Panel</button></a>
        <a href="<?php echo esc_url(home_url('/kalkulator')); ?>"><button>Kalkulator</button></a>
        <a href="<?php echo esc_url(home_url('/zaproszenie')); ?>"><button>Wyślij zaproszenie</button></a>
        <a href="<?php echo esc_url(wp_logout_url(home_url('/logowanie'))); ?>"><button>Wyloguj się</button></a>
    </nav>

    <div class="profile-form-container">
        <h2>Edytuj profil</h2>

        <form id="profile-email-form">
            <label for="new-email">Nowy e-mail</label>
            <input type="email" id="new-email" required>
            <button type="submit">Zaktualizuj e-mail</button>
            <div id="profile-email-message"></div>
        </form>

        <form id="profile-password-form">
            <label for="current-password">Aktualne hasło</label>
            <input type="password" id="current-password" required>

            <label for="new-password">Nowe hasło</label>
            <input type="password" id="new-password" required>

            <label for="confirm-password">Potwierdź hasło</label>
            <input type="password" id="confirm-password" required>

            <button type="submit">Zmień hasło</button>
            <div id="profile-password-message"></div>
        </form>

        <form id="remove-partner-form">
            <h3>Usuń relację z partnerem</h3>
            <p>Po potwierdzeniu na e-mail, relacja zostanie usunięta, a wy oboje będziecie mogli dodać partnera ponownie.</p>
            <button type="submit">Usuń relację</button>
            <div id="remove-partner-message"></div>
        </form>

        <form id="custom-user-role-form">
            <fieldset>
                <legend>Wybierz swoją rolę:</legend>

                <label><input type="radio" name="user_role" value="pan_mlody" required> Pan Młody</label><br>
                <label><input type="radio" name="user_role" value="panna_mloda"> Panna Młoda</label><br>

            </fieldset>
            <button type="submit">Zapisz rolę</button>
            <div id="custom-user-role-message"></div>
        </form>


    </div>
</div>

<?php get_footer(); ?>
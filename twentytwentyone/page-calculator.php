<?php

/**
 * Template Name: Calculator
 * Template Post Type: page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();

?>

<div id="calculator-container">
    <nav class="panel-nav">
        <a href="<?php echo esc_url(home_url('/')); ?>"><button>Home</button></a>

        <?php if (is_user_logged_in()) : ?>
            <a href="<?php echo esc_url(home_url('/panel')); ?>"><button>Panel</button></a>
            <a href="<?php echo esc_url(home_url('/edycjaprofilu')); ?>"><button>Edytuj profil</button></a>
            <a href="<?php echo esc_url(home_url('/zaproszenie')); ?>"><button>Wyślij zaproszenie</button></a>
            <a href="<?php echo esc_url(wp_logout_url(home_url('/logowanie'))); ?>"><button>Wyloguj się</button></a>
        <?php endif; ?>
    </nav>

    <div class="calculator">
        <h1>Kalkulator Ślubny</h1>

        <form id="calculator-form">
            <label>
                Liczba gości:
                <input type="number" id="guests" placeholder="np. 100" required>
            </label>

            <label>
                Koszt za osobę (PLN):
                <input type="number" id="costPerGuest" placeholder="np. 250" required>
            </label>

            <label>
                Koszt fotografa (PLN):
                <input type="number" id="photographer" placeholder="np. 3000" required>
            </label>

            <label>
                Koszt zespołu/DJ-a (PLN):
                <input type="number" id="music" placeholder="np. 4000" required>
            </label>

            <label>
                Inne koszty (PLN):
                <input type="number" id="other" placeholder="np. 2000">
            </label>

            <button type="submit">Oblicz koszt całkowity</button>
        </form>

        <div id="result"></div>
    </div>
</div>

<?php get_footer(); ?>
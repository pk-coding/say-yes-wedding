<?php

/**
 * Template Name: CalculatorTest
 * Template Post Type: page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */
get_header();
?>

<div class="container">
    <div><a href="<?php echo esc_url(home_url('/')); ?>"><button>Home</button></a></div>

    <?php if (is_user_logged_in()) : ?>
        <a href="<?php echo esc_url(home_url('/panel')); ?>"><button>Panel</button></a>
    <?php endif; ?>

    <!-- ------------------------------------------------------------------------------ -->

    <div class="calculator">
        <h1>CZĘŚĆ 1: Formularz - pytania i możliwe odpowiedzi</h1>
        <form action="#" method="post">
            <fieldset>
                <legend>Możesz </legend>

                <!-- 1. Budżet -->
                <label for="budgetTotal">1. Jaki jest planowany budżet? (PLN):</label>
                <input type="number" id="budgetTotal" name="budgetTotal" required><br><br>

                <!-- 2. Kiedy planujecie ślub? -->
                <p>2. Kiedy planujecie ślub?</p>
                <label><input type="radio" name="season" value="Wiosna" required> Wiosna</label><br>
                <label><input type="radio" name="season" value="Lato"> Lato</label><br>
                <label><input type="radio" name="season" value="Jesień"> Jesień</label><br>
                <label><input type="radio" name="season" value="Zima"> Zima</label><br><br>

                <!-- 3. Rodzaj ceremonii -->
                <p>3. Rodzaj ceremonii:</p>
                <label><input type="radio" name="ceremonyType" value="Kościelna" required> Kościelna</label><br>
                <label><input type="radio" name="ceremonyType" value="Cywilna"> Cywilna</label><br>
                <label><input type="radio" name="ceremonyType" value="Humanistyczna"> Humanistyczna</label><br><br>

                <!-- 4. Liczba gości -->
                <label for="guestCount">4. Liczba gości:</label>
                <input type="number" id="guestCount" name="guestCount" required><br><br>

                <!-- 5. Styl wesela -->
                <label for="weddingStyle">5. Styl wesela:</label>
                <select id="weddingStyle" name="weddingStyle" required>
                    <option value="">-- Wybierz --</option>
                    <option value="Rustykalny">Rustykalny</option>
                    <option value="Boho">Boho</option>
                    <option value="Glamour">Glamour</option>
                    <option value="Klasyczny">Klasyczny</option>
                    <option value="Inny">Inny</option>
                </select><br><br>

                <!-- 6. Noclegi -->
                <p>6. Czy planujecie noclegi dla gości?</p>
                <label><input type="radio" name="hasAccommodation" value="true" required> Tak</label><br>
                <label><input type="radio" name="hasAccommodation" value="false"> Nie</label><br><br>

                <!-- 7. Poprawiny -->
                <p>7. Czy planujecie poprawiny?</p>
                <label><input type="radio" name="hasAfterparty" value="true" required> Tak</label><br>
                <label><input type="radio" name="hasAfterparty" value="false"> Nie</label><br><br>

                <!-- 8. Wedding plannerka -->
                <label for="plannerOption">8. Czy planujecie skorzystać z usług wedding plannerki?</label><br>
                <select id="plannerOption" name="plannerOption" required>
                    <option value="">-- Wybierz --</option>
                    <option value="pełna">Tak – pełna organizacja</option>
                    <option value="dzień">Tak – obsługa dnia ślubu</option>
                    <option value="nie">Nie</option>
                </select><br><br>

                <!-- 9. Priorytety -->
                <p>9. Co jest dla Was najważniejsze? (maks. 3)</p>
                <label><input type="checkbox" name="priorities[]" value="Lokalizacja"> Lokalizacja</label><br>
                <label><input type="checkbox" name="priorities[]" value="Jedzenie"> Jedzenie</label><br>
                <label><input type="checkbox" name="priorities[]" value="Dekoracje"> Dekoracje</label><br>
                <label><input type="checkbox" name="priorities[]" value="Foto / wideo"> Foto / wideo</label><br>
                <label><input type="checkbox" name="priorities[]" value="Muzyka"> Muzyka</label><br>
                <label><input type="checkbox" name="priorities[]" value="Ubiór"> Ubiór (suknia/garnitur)</label><br>
                <label><input type="checkbox" name="priorities[]" value="Atrakcje dodatkowe"> Atrakcje dodatkowe</label><br>
                <small>Uwaga: wybierz maksymalnie 3 opcje.</small><br><br>

                <button type="submit">Wyślij</button>
            </fieldset>
        </form>
    </div>

    <!-- ------------------------------------------------------------------------------ -->
    <div class="calculator">
        <h1>CZĘŚĆ 1: Formularz - pytania i możliwe odpowiedzi </h1>
        <span>Pytania obowiązkowe (jeśli kwestionariusz jest wypełniany)</span>

        <form id="calculator-form">
            <label>
                Jaki jest planowany budżet?
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

<?php
get_footer();
?>
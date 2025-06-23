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

    <form id="register-form">

        <h2>Kalkulator Ślubny</h2>

        <div class="budget-input">
            <label for="guestCount">Liczba gości:</label>
            <input type="number" id="guestCount" value="100" min="1">
            <button id="validateGuestCountBtn" type="button">Sprawdź minimalne kwoty</button>
            <label for="budgetTotal">Podaj całkowity budżet? (PLN)</label>
            <input type="number" id="budgetTotal" value="100000" min="10000" />
            <p id="remainingBudget">Do rozdysponowania: 0 zł</p>
        </div>

        <!-- Lokal i catering -->
        <div class="category-box" id="box-lokal-catering" data-category="lokal-catering">

            <h3>Lokal i catering</h3>

            <div class="slider-row category-slider">

                <label for="lokal-catering-slider">Budżet dla kategorii: Lokal i catering</label>
                <input type="range" class="category-slider" id="lokal-catering-slider" min="0" value="0" />
                <input type="number" id="lokal-catering-slider_value" value="0" />
            </div>

            <div class="subcategory" id="lokal-catering">
                <p id="subcategoryRemaining-lokal-catering" class="subcategory-remaining"></p>
                <div class="slider-row">
                    <label for="lokal">Lokal:</label>
                    <div id="lokal-data">
                        <input type="range" id="lokal" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="lokal_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="lokal-catering" data-sub-id="lokal" />
                        </label>
                        <div class="warning" id="warning-lokal"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="catering">Catering:</label>
                    <div id="catering-data">
                        <input type="range" id="catering" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="catering_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="lokal-catering" data-sub-id="catering" />
                        </label>
                        <div class="warning" id="warning-catering"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="alkohol">Alkohol:</label>
                    <div id="alkohol-data">
                        <input type="range" id="alkohol" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="alkohol_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="lokal-catering" data-sub-id="alkohol" />
                        </label>
                        <div class="warning" id="warning-alkohol"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="tort">Tort:</label>
                    <div id="tort-data">
                        <input type="range" id="tort" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="tort_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="lokal-catering" data-sub-id="tort" />
                        </label>
                        <div class="warning" id="warning-tort"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="poprawiny">Poprawiny:</label>
                    <div id="poprawiny-data">
                        <input type="range" id="poprawiny" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="poprawiny_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="lokal-catering" data-sub-id="poprawiny" />
                        </label>
                        <div class="warning" id="warning-poprawiny"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Foto / wideo -->
        <div class="category-box" id="box-foto-video" data-category="foto-video">

            <h3>Foto / wideo</h3>

            <div class="slider-row category-slider">
                <label for="foto-video-slider">Budżet dla kategorii: FOTO i VIDEO</label>
                <input type="range" class="category-slider" id="foto-video-slider" min="0" value="0" />
                <input type="number" id="foto-video-slider_value" value="0" />
            </div>

            <div class="subcategory" id="foto-video">
                <p id="subcategoryRemaining-foto-video" class="subcategory-remaining"></p>
                <div class="slider-row">
                    <label for="fotograf">Fotograf:</label>
                    <div id="fotograf-data">
                        <input type="range" id="fotograf" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="fotograf_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="foto-video" data-sub-id="fotograf" />
                        </label>
                        <div class="warning" id="warning-fotograf"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="filmowiec">Filmowiec:</label>
                    <div id="filmowiec-data">
                        <input type="range" id="filmowiec" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="filmowiec_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="foto-video" data-sub-id="filmowiec" />
                        </label>
                        <div class="warning" id="warning-filmowiec"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="fotobudka">Fotobudka:</label>
                    <div id="fotobudka-data">
                        <input type="range" id="fotobudka" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="fotobudka_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="foto-video" data-sub-id="fotobudka" />
                        </label>
                        <div class="warning" id="warning-fotobudka"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dekoracje i kwiaty -->
        <div class="category-box" id="box-dekoracje" data-category="dekoracje-kwiaty">

            <h3>Dekoracje i kwiaty</h3>

            <div class="slider-row category-slider">
                <label for="dekoracje-kwiaty-slider">Budżet dla kategorii: Dekoracje i kwiaty</label>
                <input type="range" class="category-slider" id="dekoracje-kwiaty-slider" min="0" value="0" />
                <input type="number" id="dekoracje-kwiaty-slider_value" value="0" />
            </div>

            <div class="subcategory" id="dekoracje">
                <p id="subcategoryRemaining-dekoracje-kwiaty" class="subcategory-remaining"></p>
                <div class="slider-row">
                    <label for="dekoracje-sali">Dekoracje sali:</label>
                    <div id="dekoracje-sali-data">
                        <input type="range" id="dekoracje-sali" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="dekoracje-sali_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="dekoracje-kwiaty" data-sub-id="dekoracje-sali" />
                        </label>
                        <div class="warning" id="warning-dekoracje-sali"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="dekoracje-kosciola">Dekoracje kościoła:</label>
                    <div id="dekoracja-kosciola-data">
                        <input type="range" id="dekoracje-kosciola" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="dekoracje-kosciola_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="dekoracje-kwiaty" data-sub-id="dekoracje-kosciola" />
                        </label>
                        <div class="warning" id="warning-dekoracje-kosiola"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="bukiet">Bukiet:</label>
                    <div id="bukiet-data">
                        <input type="range" id="bukiet" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="bukiet_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="dekoracje-kwiaty" data-sub-id="bukiet" />
                        </label>
                        <div class="warning" id="warning-bukiet"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="scianka">Ścianka:</label>
                    <div id="scianka-data">
                        <input type="range" id="scianka" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="scianka_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="dekoracje-kwiaty" data-sub-id="scianka" />
                        </label>
                        <div class="warning" id="warning-scianka"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Oprawa muzyczna Zespół/DJ -->
        <div class="category-box" id="box-muzyka" data-category="muzyka">

            <h3>Oprawa muzyczna Zespół/DJ</h3>

            <div class="slider-row category-slider">
                <label for="muzyka-slider">Budżet dla kategorii: Muzyka</label>
                <input type="range" class="category-slider" id="muzyka-slider" min="0" value="0" />
                <input type="number" id="muzyka-slider_value" value="0" />
            </div>

            <div class="subcategory" id="muzyka">
                <p id="subcategoryRemaining-muzyka" class="subcategory-remaining"></p>
                <div class="slider-row">
                    <label for="zespol">Zespół / DJ:</label>
                    <div id="zespol-data">
                        <input type="range" id="zespol" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="zespol_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="muzyka" data-sub-id="zespol" />
                        </label>
                        <div class="warning" id="warning-zespol"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="naglosnienie">Nagłośnienie:</label>
                    <div id="naglosnienie-data">
                        <input type="range" id="naglosnienie" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="naglosnienie_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="muzyka" data-sub-id="naglosnienie" />
                        </label>
                        <div class="warning" id="warning-naglosnienie"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="muzyka-kosciol">Muzyka w kościele:</label>
                    <div id="muzyka-kosciol-data">
                        <input type="range" id="muzyka-kosciol" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="muzyka-kosciol_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="muzyka" data-sub-id="muzyka-kosciol" />
                        </label>
                        <div class="warning" id="warning-muzyka-kosciol"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ubiór -->
        <div class="category-box" id="box-ubior" data-category="ubior">

            <h3>Ubiór</h3>

            <div class="slider-row category-slider">
                <label for="ubior-slider">Budżet dla kategorii: Ubiór</label>
                <input type="range" class="category-slider" id="ubior-slider" min="0" value="0" />
                <input type="number" id="ubior-slider_value" value="0" />
            </div>

            <div class="subcategory" id="ubior">
                <p id="subcategoryRemaining-ubior" class="subcategory-remaining"></p>
                <div class="slider-row">
                    <label for="suknia">Suknia:</label>
                    <div id="suknia-data">
                        <input type="range" id="suknia" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="suknia_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="ubior" data-sub-id="suknia" />
                        </label>
                        <div class="warning" id="warning-suknia"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="garnitur">Garnitur:</label>
                    <div id="garnitur-data">
                        <input type="range" id="garnitur" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="garnitur_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="ubior" data-sub-id="garnitur" />
                        </label>
                        <div class="warning" id="warning-garnitur"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="dodatki">Dodatki:</label>
                    <div id="dodatki-data">
                        <input type="range" id="dodatki" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="dodatki_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="ubior" data-sub-id="dodatki" />
                        </label>
                        <div class="warning" id="warning-dodatki"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="fryzura">Fryzura:</label>
                    <div id="fryzura-data">
                        <input type="range" id="fryzura" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="fryzura_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="ubior" data-sub-id="fryzura" />
                        </label>
                        <div class="warning" id="warning-fryzura"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="makijaz">Makijaż:</label>
                    <div id="makijaz-data">
                        <input type="range" id="makijaz" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="makijaz_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="ubior" data-sub-id="makijaz" />
                        </label>
                        <div class="warning" id="warning-majijaz"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Papeteria -->
        <div class="category-box" id="box-papeteria" data-category="papeteria">

            <h3>Papeteria</h3>

            <div class="slider-row category-slider">
                <label for="papeteria-slider">Budżet dla kategorii: Papeteria</label>
                <input type="range" class="category-slider" id="papeteria-slider" min="0" value="0" />
                <input type="number" id="papeteria-slider_value" value="0" />
            </div>

            <div class="subcategory" id="papeteria">
                <p id="subcategoryRemaining-papeteria" class="subcategory-remaining"></p>
                <div class="slider-row">
                    <label for="zaproszenia">Zaproszenia:</label>
                    <div id="zaproszenia-data">
                        <input type="range" id="zaproszenia" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="zaproszenia_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="papeteria" data-sub-id="zaproszenia" />
                        </label>
                        <div class="warning" id="warning-zaproszenia"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="winietki">Winietki:</label>
                    <div id="winietki-data">
                        <input type="range" id="winietki" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="winietki_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="papeteria" data-sub-id="winietki" />
                        </label>
                        <div class="warning" id="warning-winietki"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="menu">Menu:</label>
                    <div id="menu-data">
                        <input type="range" id="menu" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="menu_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="papeteria" data-sub-id="menu" />
                        </label>
                        <div class="warning" id="warning-menu"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="plan-stolow">Plan stołów:</label>
                    <div id="plan-stolow-data">
                        <input type="range" id="plan-stolow" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="plan-stolow_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="papeteria" data-sub-id="plan-stolow" />
                        </label>
                        <div class="warning" id="warning-plan-stolow"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Atrakcje dodatkowe -->
        <div class="category-box" id="box-atrakcje" data-category="atrakcje">

            <h3>Atrakcje dodatkowe</h3>

            <div class="slider-row category-slider">
                <label for="atrakcje-slider">Budżet dla kategorii: Atrakcje</label>
                <input type="range" class="category-slider" id="atrakcje-slider" min="0" value="0" />
                <input type="number" id="atrakcje-slider_value" value="0" />
            </div>

            <div class="subcategory" id="atrakcje">
                <p id="subcategoryRemaining-atrakcje" class="subcategory-remaining"></p>
                <div class="slider-row">
                    <label for="pokaz-ognia">Pokaz ognia:</label>
                    <div id="pokaz-ognia--data">
                        <input type="range" id="pokaz-ognia" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="pokaz-ognia_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="atrakcje" data-sub-id="pokaz-ognia" />
                        </label>
                        <div class="warning" id="warning-pokaz-ognia"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="animatorzy">Animatorzy:</label>
                    <div id="animatorzy-data">
                        <input type="range" id="animatorzy" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="animatorzy_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="atrakcje" data-sub-id="animatorzy" />
                        </label>
                        <div class="warning" id="warning-animatorzy"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="chill">Strefy chill:</label>
                    <div id="chill-data">
                        <input type="range" id="chill" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="chill_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="atrakcje" data-sub-id="chill" />
                        </label>
                        <div class="warning" id="warning-chill"></div>
                    </div>
                </div>

                <div class="slider-row">
                    <label for="ksiega">Księga gości:</label>
                    <div id="ksiega-data">
                        <input type="range" id="ksiega" min="0" value="0" class="subcategory-slider" />
                        <input type="number" id="ksiega_value" value="0" />
                        <label>
                            Stała cena
                            <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="atrakcje" data-sub-id="ksiega" />
                        </label>
                        <div class="warning" id="warning-ksiega"></div>
                    </div>
                </div>
            </div>

            <!-- Transport -->
            <div class="category-box" id="box-transport" data-category="transport">

                <h3>Transport</h3>

                <div class="slider-row category-slider">
                    <label for="transport-slider">Budżet dla kategorii: Transport</label>
                    <input type="range" class="category-slider" id="transport-slider" min="0" value="0" />
                    <input type="number" id="transport-slider_value" value="0" />
                </div>

                <div class="subcategory" id="transport">
                    <p id="subcategoryRemaining-transport" class="subcategory-remaining"></p>
                    <div class="slider-row">
                        <label for="para-mloda">Para młoda:</label>
                        <div id="para-mloda--data">
                            <input type="range" id="para-mloda" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="para-mloda_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="transport" data-sub-id="para-mloda" />
                            </label>
                            <div class="warning" id="warning-para-mloda"></div>
                        </div>
                    </div>

                    <div class="slider-row">
                        <label for="goscie">Goście:</label>
                        <div id="goscie-data">
                            <input type="range" id="goscie" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="goscie_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="transport" data-sub-id="goscie" />
                            </label>
                            <div class="warning" id="warning-goscie"></div>
                        </div>
                    </div>

                    <div class="slider-row">
                        <label for="autokary">Autokary:</label>
                        <div id="autokary-data">
                            <input type="range" id="autokary" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="autokary_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="transport" data-sub-id="autokary" />
                            </label>
                            <div class="warning" id="warning-autokary"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Noclegi -->
            <div class="category-box" id="box-noclegi" data-category="noclegi">

                <h3>Noclegi</h3>

                <div class="slider-row category-slider">
                    <label for="noclegi-slider">Budżet dla kategorii: Noclegi</label>
                    <input type="range" class="category-slider" id="noclegi-slider" min="0" value="0" />
                    <input type="number" id="noclegi-slider_value" value="0" />
                </div>

                <div class="subcategory" id="noclegi">
                    <p id="subcategoryRemaining-noclegi" class="subcategory-remaining"></p>
                    <div class="slider-row">
                        <label for="noclegi-goscie">Noclegi dla gości:</label>
                        <div id="noclegi-goscie-data">
                            <input type="range" id="noclegi-goscie" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="noclegi-goscie_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="noclegi" data-sub-id="noclegi-goscie" />
                            </label>
                            <div class="warning" id="warning-noclegi-goscie"></div>
                        </div>
                    </div>

                    <div class="slider-row">
                        <label for="apartament">Apartament dla pary młodej:</label>
                        <div id="apartament-data">
                            <input type="range" id="apartament" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="apartament_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="noclegi" data-sub-id="apartament" />
                            </label>
                            <div class="warning" id="warning-apartament"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wedding planner -->
            <div class="category-box" id="box-wedding-planner" data-category="wedding-planner">

                <h3>Wedding planner</h3>

                <div class="slider-row category-slider">
                    <label for="wedding-planner-slider">Budżet dla kategorii: Wedding Planner</label>
                    <input type="range" class="category-slider" id="wedding-planner-slider" min="0" value="0" />
                    <input type="number" id="wedding-planner-slider_value" value="0" />
                </div>

                <div class="subcategory" id="wedding-planner">
                    <p id="subcategoryRemaining-wedding-planner" class="subcategory-remaining"></p>
                    <div class="slider-row">
                        <label for="organizacja">Organizacja:</label>
                        <div id="organizacja-data">
                            <input type="range" id="organizacja" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="organizacja_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="wedding-planner" data-sub-id="organizacja" />
                            </label>
                            <div class="warning" id="warning-organizacja"></div>
                        </div>
                    </div>

                    <div class="slider-row">
                        <label for="koordynacja">Koordynacja:</label>
                        <div id="koordynacja-data">
                            <input type="range" id="koordynacja" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="koordynacja_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="wedding-planner" data-sub-id="koordynacja" />
                            </label>
                            <div class="warning" id="warning-koordynacja"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rezerwa -->
            <div class="category-box" id="box-rezerwa" data-category="rezerwa">

                <h3>Rezerwa</h3>

                <div class="slider-row category-slider">
                    <label for="rezerwa-slider">Budżet dla kategorii: Rezerwa</label>
                    <input type="range" class="category-slider" id="rezerwa-slider" min="0" value="0" />
                    <input type="number" id="rezerwa-slider_value" value="0" />
                </div>

                <div class="subcategory" id="rezerwa">
                    <p id="subcategoryRemaining-rezerwa" class="subcategory-remaining"></p>
                    <div class="slider-row">
                        <label for="nieprzewidziane">Nieprzewidziane wydatki:</label>
                        <div id="nieprzewidziane-data">
                            <input type="range" id="nieprzewidziane" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="nieprzewidziane_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="rezerwa" data-sub-id="nieprzewidziane" />
                            </label>
                            <div class="warning" id="warning-nieprzewidziane"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inne -->
            <div class="category-box" id="box-inne" data-category="inne">

                <h3>Inne</h3>

                <div class="slider-row category-slider">
                    <label for="inne-slider">Budżet dla kategorii: Inne</label>
                    <input type="range" class="category-slider" id="inne-slider" min="0" value="0" />
                    <input type="number" id="inne-slider_value" value="0" />
                </div>

                <div class="subcategory" id="inne">
                    <p id="subcategoryRemaining-inne" class="subcategory-remaining"></p>
                    <div class="slider-row">
                        <label for="prezenty">Prezenty dla rodziców:</label>
                        <div id="prezenty-data">
                            <input type="range" id="prezenty" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="prezenty_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="inne" data-sub-id="prezenty" />
                            </label>
                            <div class="warning" id="warning-prezenty"></div>
                        </div>
                    </div>

                    <div class="slider-row">
                        <label for="napiwki">Napiwki:</label>
                        <div id="napiwki-data">
                            <input type="range" id="napiwki" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="napiwki_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="inne" data-sub-id="napiwki" />
                            </label>
                            <div class="warning" id="warning-napiwki"></div>
                        </div>
                    </div>

                    <div class="slider-row">
                        <label for="oplata-usc">Opłaty USC/kościół:</label>
                        <div id="oplata-usc-data">
                            <input type="range" id="oplata-usc" min="0" value="0" class="subcategory-slider" />
                            <input type="number" id="oplata-usc_value" value="0" />
                            <label>
                                Stała cena
                                <input type="checkbox" class="fixed-subcategory-checkbox" data-cat-id="inne" data-sub-id="oplata-usc" />
                            </label>
                            <div class="warning" id="warning-oplata-usc"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pomysły -->
            <div class="category-box" id="box-pomysly" data-category="pomysly">

                <h3>Pomysły</h3>

                <div class="slider-row category-slider">
                    <label for="pomysly-slider">Budżet dla kategorii: Własne pomysły</label>
                    <p id="pomyslyBudget">Dostępny budżet na pomysły: 0 zł</p>
                    <div id="pomyslyMessage" style="color: red; font-weight: bold;"></div>
                </div>

                <input type="text" id="ideaName" placeholder="Wpisz pomysł" />
                <input type="number" id="ideaPrice" placeholder="Podaj cenę" min="0" />
                <button id="addIdeaBtn" type="button">Dodaj</button>

                <div class="subcategory" id="pomysly">
                    <p id="subcategoryRemaining-pomysly" class="subcategory-remaining"></p>
                    <!-- tutaj dynamicznie dodamy pomysły -->
                </div>
            </div>

            <button id="saveBudgetBtn" type="button">Zapisz budżet</button>
            <div id="saveMessage"></div>
    </form>

</div>

<?php get_footer(); ?>
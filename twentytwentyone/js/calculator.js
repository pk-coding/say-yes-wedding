document.addEventListener('DOMContentLoaded', initBudgetTool);

document.addEventListener("DOMContentLoaded", () => {
    const boxes = document.querySelectorAll(".category-box");
    const animations = ["slide-in-left", "slide-in-right", "slide-in-top", "slide-in-bottom"];

    boxes.forEach((box, index) => {
        const delay = index * 100; // opóźnienie co 100ms
        const animation = animations[index % animations.length]; // naprzemiennie
        box.style.animationName = animation;
        box.style.animationDelay = `${delay}ms`;
    });
});

function getInitialState() {
    const guestInput = document.getElementById('guestCount');
    const budgetInput = document.getElementById('budgetTotal');
    const remainingBudgetEl = document.getElementById('remainingBudget');

    const maxGuests = parseInt(guestInput.max, 10) || 500;
    const rawGuestCount = parseInt(guestInput.value, 10) || 0;
    const guestCount = Math.min(rawGuestCount, maxGuests);

    // Nadpisz input, jeśli przekracza maksymalną wartość
    if (rawGuestCount > maxGuests) {
        guestInput.value = maxGuests;
    }

    return {
        totalBudget: parseInt(budgetInput.value, 10) || 0,
        remainingBudget: parseInt(remainingBudgetEl.value, 10) || 0,
        guestCount: guestCount,
        categoryValues: {},
        subcategoryValues: {},
        fixedSubcategory: {},
        validationMessages: {},
        nameInput: document.getElementById('ideaName'),
        priceInput: document.getElementById('ideaPrice'),
        pomyslyBox: document.getElementById('pomysly'),
        pomyslySliderValue: document.getElementById('pomysly-slider_value'),
        ideaPriceInput: document.getElementById('ideaPrice'),
        isPriceCheckVisible: false,
        tooltip: document.getElementById('tooltip'),
        isManualChange: false,
    };
}

document.getElementById('budgetTotal').addEventListener('input', function () {
    const max = parseInt(this.max, 10);
    if (parseInt(this.value, 10) > max) {
        this.value = max;
    }
});

document.getElementById('guestCount').addEventListener('input', function () {
    const max = parseInt(this.max, 10);
    if (parseInt(this.value, 10) > max) {
        this.value = max;
    }
});

document.getElementById('ideaPrice').addEventListener('input', function () {
    const max = parseInt(this.max, 10);
    if (parseInt(this.value, 10) > max) {
        this.value = max;
    }
});


const state = getInitialState();

const categoryShares = {
    "lokal-catering": 0.42,
    "foto-video": 0.10,
    "dekoracje-kwiaty": 0.08,
    "muzyka": 0.07,
    "ubior": 0.06,
    "papeteria": 0.02,
    "atrakcje": 0.03,
    "transport": 0.02,
    "noclegi": 0.00,
    "wedding-planner": 0.05,
    "rezerwa": 0.10,
    "inne": 0.05,
    "pomysly": 0,
};

const subcategoryShares = {
    "lokal-catering": { lokal: 0.3571, catering: 0.3571, alkohol: 0.1190, tort: 0.0952, poprawiny: 0.0714 },
    "foto-video": { fotograf: 0.6, filmowiec: 0.3, fotobudka: 0.1 },
    "dekoracje-kwiaty": { "dekoracje-sali": 0.375, "dekoracje-kosciola": 0.25, bukiet: 0.25, scianka: 0.125 },
    "muzyka": { zespol: 0.7143, naglosnienie: 0.1429, "muzyka-kosciol": 0.1429 },
    "ubior": { suknia: 0.5, garnitur: 0.25, dodatki: 0.0833, fryzura: 0.0833, makijaz: 0.0833 },
    "papeteria": { zaproszenia: 0.5, winietki: 0.15, menu: 0.15, "plan-stolow": 0.2 },
    "atrakcje": { "pokaz-ognia": 0.3333, animatorzy: 0.2667, chill: 0.2, ksiega: 0.2 },
    "transport": { "para-mloda": 0.5, goscie: 0.35, autokary: 0.15 },
    "noclegi": { "noclegi-goscie": 0.5, apartament: 0.5 },
    "wedding-planner": { organizacja: 0.6, koordynacja: 0.4 },
    "rezerwa": { nieprzewidziane: 1 },
    "inne": { prezenty: 0.4, napiwki: 0.4, "oplata-usc": 0.2 }
};

// Minimalne wartości na osobę dla podkategorii (na podstawie danych powyżej)
const minPerPerson = {
    lokal: 250,
    catering: 150,
    alkohol: 20,
    tort: 8,
    poprawiny: 40,
    fotograf: 40,
    filmowiec: 50,
    fotobudka: 25,
    "dekoracje-sali": 20,
    "dekoracje-kosciola": 10,
    bukiet: 5,
    scianka: 5,
    zespol: 50,
    naglosnienie: 5,
    "muzyka-kosciol": 5,
    suknia: 40,
    garnitur: 20,
    dodatki: 8,
    fryzura: 6,
    makijaz: 6,
    zaproszenia: 6,
    winietki: 1.5,
    menu: 1.5,
    "plan-stolow": 2,
    "pokaz-ognia": 15,
    animatorzy: 10,
    chill: 10,
    ksiega: 5,
    "para-mloda": 10,
    goscie: 20,
    autokary: 10,
    "noclegi-goscie": 120,
    apartament: 20,
    organizacja: 10,
    koordynacja: 6,
    nieprzewidziane: 30,
    prezenty: 10,
    napiwki: 10,
    "oplata-usc": 1,
};

// ----
function initBudgetTool() {
    setupFixedCheckboxHandlers();
    setupAddIdeaHandler();

    const input = document.getElementById('budgetTotal');
    if (input) {
        state.totalBudget = parseFloat(input.value) || 0;
        distributeInitialBudgetRespectingFixed();
    }

    setupSliderHandlers();

    state.remainingBudget = state.totalBudget - getCurrentAllocatedTotal();
    updateRemainingBudget();
}

// ---- budgetBox SERVICES

// 1. Zmiana liczby gości
document.getElementById('changeGuestsBtn').addEventListener('click', () => {
    const input = document.getElementById('guestCount');
    const val = parseInt(input.value, 10);
    if (isNaN(val) || val < 0) return;

    state.guestCount = val;
    state.tooltip.textContent = 'Zmieniono liczbę gości';
    state.tooltip.style.color = 'green'; state.tooltip.style.display = 'inline';
    setTimeout(() => state.tooltip.style.display = 'none', 2000);
});

// 2. Zmiana budżetu
function handleChangeBudget() {
    const input = document.getElementById('budgetTotal');
    const newBudget = parseFloat(input.value) || 0;
    const diff = newBudget - state.totalBudget;

    if (diff === 0) return;

    state.totalBudget = newBudget;

    // Zawsze rozkładamy sliderami automatycznie bez potwierdzenia
    distributeInitialBudgetRespectingFixed();

    state.tooltip.textContent = 'Zmieniono budżet';
    state.tooltip.style.color = 'green';
    state.tooltip.style.display = 'inline';
    setTimeout(() => state.tooltip.style.display = 'none', 2000);
}

// ----- Auto Budżet
function handleAutoBudget() {
    const guestInput = document.getElementById('guestCount');
    const guests = parseInt(guestInput.value, 10);

    if (isNaN(guests) || guests <= 0) {
        state.tooltip.textContent = 'Podaj poprawną liczbę gości';
        state.tooltip.style.color = 'red';
        state.tooltip.style.display = 'inline';
        setTimeout(() => state.tooltip.style.display = 'none', 2000);
        return;
    }

    let totalBudget = 0;
    const catSums = {}; // sumy dla kategorii

    for (const [subId, perPerson] of Object.entries(minPerPerson)) {
        const subTotal = Math.round(perPerson * guests);

        // znajdź kategorię, do której należy subkategoria
        for (const [catId, subs] of Object.entries(subcategoryShares)) {
            if (subs[subId] !== undefined) {
                if (!catSums[catId]) catSums[catId] = 0;
                catSums[catId] += subTotal;

                // ustaw subkategorię w stanie i w UI
                state.subcategoryValues[catId][subId] = subTotal;

                const subSlider = document.getElementById(subId);
                const subInput = document.getElementById(`${subId}_value`);
                if (subSlider) subSlider.value = subTotal;
                if (subInput) subInput.value = subTotal;

                break;
            }
        }

        totalBudget += subTotal;
    }

    // ustaw wartości kategorii
    for (const [catId, catVal] of Object.entries(catSums)) {
        state.categoryValues[catId] = catVal;

        const catSlider = document.getElementById(`${catId}-slider`);
        const catInput = document.getElementById(`${catId}-slider_value`);
        if (catSlider) catSlider.value = catVal;
        if (catInput) catInput.value = catVal;

        updateSubcategoryRemaining(catId);
    }

    // aktualizuj budżet i interfejs
    state.totalBudget = totalBudget;
    state.remainingBudget = 0;
    updateRemainingBudget();

    document.getElementById('budgetTotal').value = totalBudget;

    // Tooltip końcowy
    state.tooltip.textContent = 'Wygenerowano automatyczny kosztorys z minimalnymi cenami';
    state.tooltip.style.color = 'green';
    state.tooltip.style.display = 'inline';
    setTimeout(() => state.tooltip.style.display = 'none', 2500);
    document.querySelectorAll('input[type="range"], input[type="number"]').forEach(el => {
        el.disabled = true;
    });
}
document.getElementById('autoBudgetBtn').addEventListener('click', handleAutoBudget);

// Funkcja do dodawania środków do remainingBudget bez zmiany sliderów
function handleAddFunds() {
    const input = document.getElementById('budgetTotal');
    const budgetBefore = parseFloat(input.value) || 0;
    const diff = state.remainingBudget;

    if (diff === 0) return;

    const newBudget = state.remainingBudget < 0
        ? budgetBefore + Math.abs(diff)
        : budgetBefore - diff;

    input.value = newBudget;

    // wymuszam żeby inne funkcje zadziałały na nową wartość
    input.dispatchEvent(new Event('input', { bubbles: true }));

    state.remainingBudget = 0;

    state.tooltip.textContent = 'Budżet został skorygowany';
    state.tooltip.style.color = 'green';
    state.tooltip.style.display = 'inline';

    setTimeout(() => {
        state.tooltip.style.display = 'none';
    }, 2000);
}
document.getElementById('changeBudgetBtn').addEventListener('click', handleChangeBudget);
document.getElementById('addFundsBtn').addEventListener('click', handleAddFunds);

// 3. Sprawdź ceny
document.getElementById('checkPricesBtn').addEventListener('click', () => {
    checkPricesHandler();
    const tooltipText = document.getElementById('checkPricesBtn').textContent;
    if (tooltipText === "Sprawdź sugestie cen") {
        state.tooltip.textContent = "Sprawdzono sugestie cenowe dla wszystkich podkategorii";
    } else if (tooltipText === "Usuń obecne sugestie dla wszystkich podkategorii") {
        tooltip.textContent = "Usunięto obecne sugestie cenowe dla wszystkich podkategorii";
    } else if (tooltipText === "Sprawdź nowe sugestie") {
        state.tooltip.textContent = "Sprawdzono nowe sugestie cenowe dla wszystkich podkategorii";
    }

    state.tooltip.style.color = 'green'; state.tooltip.style.display = 'inline';
    setTimeout(() => state.tooltip.style.display = 'none', 2000);
});

// --------
// 1. Zmiana liczby gości - Enter
document.getElementById('guestCount').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        document.getElementById('changeGuestsBtn').click();
    }
});

// 2. Zmiana budżetu - Enter
document.getElementById('budgetTotal').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        document.getElementById('changeBudgetBtn').click();
    }
});

// 3. Sprawdź ceny - Enter (jeśli chcesz np. Enter w dowolnym miejscu – np. focus na przycisku)
function setupSliderHandlers() {
    // Kategorie
    document.querySelectorAll(".category-slider").forEach(slider => {
        const input = document.getElementById(`${slider.id}_value`);
        const catId = slider.id.replace("-slider", "");
        if (!slider || !input) return;

        // Ustawienia step/min na sliderze i input
        slider.step = 100;
        slider.min = 0;
        input.step = 100;
        input.min = 0;

        const update = () => {
            const oldVal = state.categoryValues[catId] || 100;
            let newVal = parseInt(slider.value, 10) || 100;
            newVal = Math.round(newVal / 100) * 100;
            if (newVal < 100) newVal = 100;

            const fixedSubsSum = getFixedSubcategoriesSum(catId);

            if (newVal < fixedSubsSum) {
                // Przywróć starą wartość jeśli nowa jest za mała
                slider.value = oldVal;
                input.value = oldVal;
                return;
            }

            state.categoryValues[catId] = newVal;
            slider.value = newVal;
            input.value = newVal;

            updateRemainingBudget();

            if (state.isManualChange) {
                // Ręczna zmiana - aktualizujemy tylko infoBox
                updateSubcategoryRemaining(catId);
            } else {
                // Automatyczna zmiana - pełna aktualizacja podkategorii
                updateSubcategoryRemaining(catId);

                if (catId === "pomysly") {
                    limitIdeaSliders();
                } else {
                    redistributeSubcategories(catId);
                }
            }

            updateRemainingBudget();
        };

        slider.addEventListener("input", () => {
            state.isManualChange = true; // ręczna zmiana
            update();
        });

        input.addEventListener("blur", () => {
            state.isManualChange = true; // ręczna zmiana

            // Walidacja wartości z inputa
            let val = input.value.replace(/\D/g, '');
            if (val.length === 0) val = '100';
            let numVal = parseInt(val, 10);
            if (isNaN(numVal) || numVal < 100) numVal = 100;
            numVal = Math.round(numVal / 100) * 100;

            slider.value = numVal;
            input.value = numVal;
            update();
        });
    });

    // Podkategorie
    document.querySelectorAll(".subcategory-slider").forEach(slider => {
        const input = document.getElementById(`${slider.id}_value`);
        const subId = slider.id;
        if (!slider || !input) return;

        slider.step = 10;
        slider.min = 0;
        input.step = 10;
        input.min = 0;

        const update = () => {
            let newVal = parseInt(slider.value, 10) || 100;
            newVal = Math.round(newVal / 100) * 100;
            if (newVal < 100) newVal = 100;

            slider.value = newVal;
            input.value = newVal;

            for (const [catId, subs] of Object.entries(subcategoryShares)) {
                if (subs[subId] !== undefined) {
                    if (state.fixedSubcategory[catId] && state.fixedSubcategory[catId][subId]) {
                        slider.value = state.subcategoryValues[catId][subId];
                        input.value = slider.value;
                        return;
                    }

                    const totalSubSum = Object.entries(state.subcategoryValues[catId])
                        .reduce((sum, [key, val]) => key === subId ? sum : sum + val, 0);

                    const catMax = state.categoryValues[catId] || 0;
                    const available = catMax - totalSubSum;

                    if (newVal > available) {
                        newVal = available;
                        slider.value = newVal;
                        input.value = newVal;
                    }

                    state.subcategoryValues[catId][subId] = newVal;
                    updateSubcategoryRemaining(catId);
                    if (state.isPriceCheckVisible) {
                        validateSubcategory(subId, newVal);
                    };
                    break;
                }
            }
        };

        slider.addEventListener("input", () => {
            update();
        });

        input.addEventListener("blur", () => {

            let val = input.value.replace(/\D/g, '');
            if (val.length === 0) val = '100';
            let numVal = parseInt(val, 10);
            if (isNaN(numVal) || numVal < 100) numVal = 100;
            numVal = Math.round(numVal / 100) * 100;

            slider.value = numVal;
            input.value = numVal;
            update();
        });
    });
}

// ----

// Funkcja do wywołania przy kliknięciu ustawiania budżetu/liczby gości
function onSetBudgetAndGuests() {
    state.isManualChange = false;  // tryb automatyczny
    distributeInitialBudgetRespectingFixed(); // pełna automatyczna dystrybucja sliderów i inputów
}



const validateBtn = document.getElementById("validateGuestCountBtn");
if (validateBtn) {
    validateBtn.addEventListener("click", () => {
        validateAllSubcategories();
    });
}



function hasAnyAllocation() {
    const total = Object.values(state.categoryValues || {}).reduce((sum, val) => sum + val, 0);
    return total > 0;
}


function distributeInitialBudget() {
    state.categoryValues = {};
    state.subcategoryValues = {};
    state.remainingBudget = state.totalBudget;

    for (const [catId, share] of Object.entries(categoryShares)) {
        const catBudget = Math.round(state.totalBudget * share);
        state.categoryValues[catId] = catBudget;
        state.remainingBudget -= catBudget;

        const catSlider = document.getElementById(`${catId}-slider`);
        const catInput = document.getElementById(`${catId}-slider_value`);
        if (catSlider && catInput) {
            catSlider.max = state.totalBudget;
            catSlider.min = 0;
            catSlider.step = 100;

            catSlider.value = catBudget;
            catInput.value = catBudget;

            // wymuś event input żeby odświeżyć powiązane handlery
            catSlider.dispatchEvent(new Event('input', { bubbles: true }));
        }

        const subs = subcategoryShares[catId];
        if (!subs) continue;

        state.subcategoryValues[catId] = {};
        for (const [subId, subShare] of Object.entries(subs)) {
            const subBudget = Math.round(catBudget * subShare);
            state.subcategoryValues[catId][subId] = subBudget;

            const subSlider = document.getElementById(subId);
            const subInput = document.getElementById(`${subId}_value`);
            if (subSlider && subInput) {
                subSlider.max = catBudget;
                subSlider.value = subBudget;
                subSlider.step = 100;
                subSlider.disabled = false;
                subInput.value = subBudget;
            }
        }

        updateSubcategoryRemaining(catId);
    }

    updateRemainingBudget();
    setupSliderHandlers();
}


function distributeInitialBudgetRespectingFixed() {
    state.remainingBudget = state.totalBudget;
    for (const [catId, share] of Object.entries(categoryShares)) {
        const newCatBudget = Math.round(state.totalBudget * share);
        state.categoryValues[catId] = newCatBudget;

        // ustawienia sliderów
        const catSlider = document.getElementById(`${catId}-slider`);
        const catInput = document.getElementById(`${catId}-slider_value`);
        if (catSlider && catInput) {
            catSlider.max = state.totalBudget;
            catSlider.value = newCatBudget;
            catSlider.step = 100;
            catSlider.disabled = false;
            catInput.value = newCatBudget;

            // Ręcznie wywołaj update
            catSlider.dispatchEvent(new Event('input'));
        }

        const subs = subcategoryShares[catId];
        if (!subs) continue;

        state.subcategoryValues[catId] = state.subcategoryValues[catId] || {};
        for (const [subId, subShare] of Object.entries(subs)) {
            const isFixed = state.fixedSubcategory[catId]?.[subId];
            if (isFixed) continue;

            const subBudget = Math.round(newCatBudget * subShare);
            state.subcategoryValues[catId][subId] = subBudget;

            const subSlider = document.getElementById(subId);
            const subInput = document.getElementById(`${subId}_value`);
            if (subSlider && subInput) {
                subSlider.max = newCatBudget;   // <--- tu zmiana
                subSlider.value = subBudget;
                subSlider.step = 100;
                subSlider.disabled = false;
                subInput.value = subBudget;

                subSlider.dispatchEvent(new Event('input'));
            }
        }

        updateSubcategoryRemaining(catId);
    }

    updateRemainingBudget();
    setupSliderHandlers();
    updateRemainingBudget();
}


// ----
function updateRemainingBudget() {
    let diff = state.totalBudget - getCurrentAllocatedTotal();

    // Uwzględnij tolerancję do ±200 zł dla błędów zaokrągleń
    if (Math.abs(diff) <= 200) {
        diff = 0;
    }

    state.remainingBudget = diff;

    const remainingBox = document.getElementById('remainingBudget');
    const pomyslyBudget = document.getElementById('pomyslyBudget');
    const messageBox = document.getElementById('pomyslyMessage');

    const isDeficit = diff < 0;
    const formatted = Math.abs(diff).toLocaleString();
    const color = isDeficit ? "red" : "green";
    const text = isDeficit
        ? `Brakuje: ${formatted} zł`
        : `Do rozdysponowania: ${formatted} zł`;

    if (remainingBox) {
        remainingBox.textContent = text;
        remainingBox.style.color = color;
    }

    if (pomyslyBudget) {
        pomyslyBudget.textContent = isDeficit
            ? `Brakuje na pomysły: ${formatted} zł`
            : `Dostępny budżet na pomysły: ${formatted} zł`;
        pomyslyBudget.style.color = color;
    }

    if (messageBox && !isDeficit) {
        messageBox.textContent = '';
    }
}



// ----
function updateSubcategoryRemaining(catId) {
    const catBudget = state.categoryValues[catId] || 0;
    const subBudgets = Object.values(state.subcategoryValues[catId] || {});
    const used = subBudgets.reduce((a, b) => a + b, 0);
    let remaining = catBudget - used;

    if (remaining >= 100) {
        // Zaokrąglaj w dół do setek, jeśli większe lub równe 100
        remaining = Math.floor(remaining / 100) * 100;
    } else if (remaining > 0 && remaining < 100) {
        // Jeśli jest między 1 a 99, pokaż 0
        remaining = 0;
    }
    // Jeśli jest zero lub ujemne, pokazuj dokładnie takie wartości, np. -100, -200 itd.

    const infoBox = document.getElementById(`subcategoryRemaining-${catId}`);
    if (infoBox) {
        infoBox.textContent = `Pozostało do podziału: ${remaining.toLocaleString()} zł`;
    }
}


// ----
function redistributeSubcategories(catId) {
    const catBudget = state.categoryValues[catId] || 0;
    const subs = subcategoryShares[catId];
    if (!subs) return;

    // Sumujemy wartości podkategorii ze stałą ceną
    const fixedSubs = state.fixedSubcategory[catId] || {};
    const fixedSum = Object.entries(fixedSubs)
        .filter(([subId, isFixed]) => isFixed)
        .reduce((sum, [subId]) => sum + (state.subcategoryValues[catId][subId] || 0), 0);

    const totalShare = Object.values(subs).reduce((a, b) => a + b, 0);

    // Wartość do podziału między pozostałe podkategorie
    const availableBudget = catBudget - fixedSum;

    // Sumujemy udziały tylko dla podkategorii bez stałej ceny
    const nonFixedShares = Object.entries(subs)
        .filter(([subId]) => !fixedSubs[subId])
        .reduce((sum, [, share]) => sum + share, 0);

    for (const [subId, subShare] of Object.entries(subs)) {
        if (fixedSubs[subId]) {
            // Podkategoria ze stałą ceną - pozostawiamy dotychczasową wartość
            continue;
        }
        const budget = Math.round(availableBudget * (subShare / nonFixedShares));
        state.subcategoryValues[catId][subId] = budget;

        const slider = document.getElementById(subId);
        const input = document.getElementById(`${subId}_value`);
        if (slider && input) {
            slider.max = catBudget;
            slider.value = budget;
            input.value = budget;
            slider.disabled = false;
        }
    }
    updateSubcategoryRemaining(catId);
}

// ----
function updateRemainingBudget() {
    const allocated = getCurrentAllocatedTotal();
    state.remainingBudget = state.totalBudget - allocated;

    const remainingBox = document.getElementById('remainingBudget');
    const pomyslyBudget = document.getElementById('pomyslyBudget');
    const messageBox = document.getElementById('pomyslyMessage');

    const formatted = Math.abs(state.remainingBudget).toLocaleString();

    const isDeficit = state.remainingBudget < 0;

    const textColor = isDeficit ? "red" : "green";
    const remainingText = isDeficit
        ? `Brakuje: ${formatted} zł`
        : `Pozostało: ${formatted} zł`;

    if (remainingBox) {
        remainingBox.textContent = remainingText;
        remainingBox.style.color = textColor;
    }

    if (pomyslyBudget) {
        pomyslyBudget.textContent = isDeficit
            ? `Brakuje na pomysły: ${formatted} zł`
            : `Dostępny budżet na pomysły: ${formatted} zł`;
        pomyslyBudget.style.color = textColor;
    }

    if (messageBox && !isDeficit) {
        messageBox.textContent = '';
    }
}

// ----
function setupFixedCheckboxHandlers() {
    // Kategorie
    document.querySelectorAll(".fixed-category-checkbox").forEach(checkbox => {
        checkbox.addEventListener("change", e => {
            const catId = e.target.dataset.catId;
            state.fixedCategory[catId] = e.target.checked;
            // Jeśli kategoria jest "stała", jej slider nie może się zmieniać
            const slider = document.getElementById(`${catId}-slider`);
            if (slider) {
                slider.disabled = e.target.checked;
            }
            // Jeśli ustawiono stałą kategorię, blokujemy zmiany i wymuszamy minimalną wartość
            enforceCategoryMin(catId);
            updateRemainingBudget();
        });
    });

    // Podkategorie
    document.querySelectorAll(".fixed-subcategory-checkbox").forEach(checkbox => {
        checkbox.addEventListener("change", e => {
            const subId = e.target.dataset.subId;
            const catId = e.target.dataset.catId;
            state.fixedSubcategory[catId] = state.fixedSubcategory[catId] || {};
            state.fixedSubcategory[catId][subId] = e.target.checked;

            const slider = document.getElementById(subId);
            if (slider) {
                slider.disabled = e.target.checked;
            }
            // Gdy podkategoria jest stała, jej wartość się nie zmienia
            enforceSubcategoryMin(catId, subId);
            updateRemainingBudget();
        });
    });
}


// ----
function enforceCategoryMin(catId) {
    const fixedSubsSum = getFixedSubcategoriesSum(catId);
    const catSlider = document.getElementById(`${catId}-slider`);
    const catInput = document.getElementById(`${catId}-slider_value`);
    if (!catSlider || !catInput) return;

    // Kategoria nie może zejść poniżej sumy stałych podkategorii
    const minVal = fixedSubsSum;
    if ((state.categoryValues[catId] || 0) < minVal) {
        state.categoryValues[catId] = minVal;
    }
    catSlider.min = minVal;
    if (catSlider.value < minVal) {
        catSlider.value = minVal;
        catInput.value = minVal;
    }
}


// ----
function enforceSubcategoryMin(catId, subId) {
    const subSlider = document.getElementById(subId);
    const subInput = document.getElementById(`${subId}_value`);
    if (!subSlider || !subInput) return;

    if (state.fixedSubcategory[catId] && state.fixedSubcategory[catId][subId]) {
        // podkategoria stała - blokujemy wartość na stałej
        subSlider.value = state.subcategoryValues[catId][subId];
        subSlider.disabled = true;
        subInput.value = state.subcategoryValues[catId][subId];
    } else {
        subSlider.disabled = false;
    }
}

function getFixedSubcategoriesSum(catId) {
    if (!state.fixedSubcategory[catId]) return 0;
    return Object.entries(state.fixedSubcategory[catId])
        .filter(([subId, fixed]) => fixed)
        .reduce((sum, [subId]) => sum + (state.subcategoryValues[catId][subId] || 0), 0);
}

function getCurrentAllocatedTotal() {
    let total = 0;

    // Kategorie (pomysły też są kategorią)
    for (const value of Object.values(state.categoryValues)) {
        total += value || 0;
    }

    // Pomysły (jeśli dodajesz je osobno — trzeba je też doliczyć)
    const ideaItems = state.pomyslyBox ? state.pomyslyBox.querySelectorAll('.idea-item') : [];
    ideaItems.forEach(item => {
        const text = item.textContent || "";
        const match = text.match(/- ([\d\s]+) zł/);
        if (match) {
            const amount = parseInt(match[1].replace(/\s/g, ''), 10);
            total += amount || 0;
        }
    });

    return total;
}

function validateSubcategory(subId, value) {
    const warningBox = document.getElementById(`warning-${subId}`);
    const minPerGuest = minPerPerson[subId] || 0;
    const minValue = minPerGuest * state.guestCount;

    if (!warningBox) return;

    if (value < minValue) {
        const message = `Kwota może być zbyt niska (minimum: ${minValue.toLocaleString("pl-PL")} zł dla ${state.guestCount} osób.`;
        warningBox.textContent = message;
        warningBox.classList.remove("success", "info");
        warningBox.classList.add("warning");
        state.validationMessages[subId] = message;
    } else {
        const message = `Kwota mieści si w średniej cenowej dla ${state.guestCount} osób).`;
        warningBox.textContent = message;
        warningBox.classList.remove("warning", "info");
        warningBox.classList.add("success");
        delete state.validationMessages[subId];
    }
}

// ---- Check if subcategory is check as Stała cena
function checkPricesHandler() {
    Object.entries(state.subcategoryValues).forEach(([catId, subs]) => {
        Object.entries(subs).forEach(([subId, value]) => {
            const isFixed = state.fixedSubcategory?.[catId]?.[subId];
            const warningBox = document.getElementById(`warning-${subId}`);
            if (!warningBox) return;

            if (isFixed) {
                const message = `Kwota jest zablokowana - nie podlega walidacji.`;
                warningBox.textContent = message;
                warningBox.classList.remove("warning", "success");
                warningBox.classList.add("info");
                state.validationMessages[subId] = message;
            } else {
                validateSubcategory(subId, value);
            }
        });
    });
}


// ----
function validateAllSubcategories() {
    for (const [catId, subs] of Object.entries(subcategoryShares)) {
        for (const subId of Object.keys(subs)) {
            const val = state.subcategoryValues[catId]?.[subId] || 0;
            validateSubcategory(subId, val);
        }
    }
}

// ---- Display / hidden checked prices
document.getElementById("checkPricesBtn").addEventListener("click", () => {
    state.isPriceCheckVisible = !state.isPriceCheckVisible;

    const btn = document.getElementById("checkPricesBtn");
    btn.textContent = state.isPriceCheckVisible ? "Usuń obecne sugestie" : "Sprawdź nowe sugestie";

    togglePriceValidationMessages(state.isPriceCheckVisible);
});


function togglePriceValidationMessages(show) {
    Object.entries(state.subcategoryValues).forEach(([catId, subs]) => {
        Object.entries(subs).forEach(([subId, value]) => {
            const warningBox = document.getElementById(`warning-${subId}`);
            if (!warningBox) return;

            if (show) {
                const isFixed = state.fixedSubcategory?.[catId]?.[subId];
                if (isFixed) {
                    const message = `Kwota jest zablokowana - nie podlega walidacji.`;
                    warningBox.textContent = message;
                    warningBox.classList.remove("warning", "success");
                    warningBox.classList.add("info");
                    state.validationMessages[subId] = message;
                } else {
                    validateSubcategory(subId, value);
                }
                warningBox.style.display = "block";
            } else {
                warningBox.style.display = "none";
            }
        });
    });
}


// ---- ADD OWN IDEAS ----
function setupAddIdeaHandler() {
    const addBtn = document.getElementById('addIdeaBtn');
    if (!addBtn) return;

    addBtn.addEventListener('click', () => {
        const name = state.nameInput.value.trim();
        const price = parseFloat(state.priceInput.value);
        const messageBox = document.getElementById('pomyslyMessage');
        messageBox.textContent = '';

        if (!name || isNaN(price) || price <= 0) {
            messageBox.textContent = 'Wprowadź poprawną nazwę i kwotę.';
            return;
        }

        // Tworzenie elementu pomysłu
        const ideaDiv = document.createElement('div');
        ideaDiv.classList.add('idea-item');

        // Element z nazwą i ceną
        const ideaText = document.createElement('span');
        ideaText.textContent = `${name} - ${price.toLocaleString()} zł`;
        ideaDiv.appendChild(ideaText);

        // Przycisk edycji
        const editBtn = document.createElement('button');
        editBtn.textContent = 'Edytuj';
        editBtn.className = 'edit-idea-btn';
        editBtn.type = 'button';
        editBtn.addEventListener('click', () => {
            const oldName = name; // or store in closure / data attribute
            const oldPrice = price;

            const newName = prompt('Nowa nazwa pomysłu:', oldName);
            const newPriceStr = prompt('Nowa kwota:', oldPrice);
            const newPrice = parseFloat(newPriceStr);

            if (!newName || isNaN(newPrice) || newPrice <= 0) {
                alert('Nieprawidłowe dane.');
                return;
            }

            ideaText.textContent = `${newName} - ${newPrice.toLocaleString()} zł`;
            updateCategoryCost();
            // Możesz zaktualizować state.remainingBudget jeśli to potrzebne
            updateRemainingBudget();
        });

        // Przycisk usuwania
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Usuń';
        deleteBtn.className = 'delete-idea-btn';
        deleteBtn.type = 'button';
        deleteBtn.addEventListener('click', () => {
            const confirmDelete = confirm(`Czy na pewno chcesz usunąć "${name}" za ${price.toLocaleString()} zł?`);
            if (!confirmDelete) return;

            ideaDiv.remove();
            updateCategoryCost();
            state.remainingBudget += price;
            updateRemainingBudget();
        });
        // Dodaj przyciski do pomysłu
        ideaDiv.appendChild(editBtn);
        ideaDiv.appendChild(deleteBtn);

        if (state.pomyslyBox) {
            state.pomyslyBox.appendChild(ideaDiv);
        }
        // Dodaj pomysł do listy
        state.pomyslyBox.appendChild(ideaDiv);
        updateCategoryCost();
        // Aktualizacja budżetu
        state.remainingBudget -= price;
        updateRemainingBudget();

        // Wyczyść pola
        state.nameInput.value = '';
        state.priceInput.value = '';
    });
}

// ----
function updateCategoryCost() {
    const ideaItems = document.querySelectorAll('.subcategory#pomysly .idea-item');
    let total = 0;

    ideaItems.forEach(item => {
        // Parsuj cenę z tekstu, np. "Nazwa - 123 zł"
        const text = item.querySelector('span').textContent;
        const match = text.match(/- ([\d\s]+) zł/);
        if (match) {
            // usuń spacje z liczby i zamień na int
            const price = parseInt(match[1].replace(/\s/g, ''), 10);
            if (!isNaN(price)) {
                total += price;
            }
        }
    });

    const costDisplay = document.getElementById('pomysly-costs');
    if (costDisplay) {
        costDisplay.textContent = `Zaplanowane wydatki w kategorii Pomysły: ${total.toLocaleString()} zł`;
    }
}


// ---- API ----
// ---- wysyłanie wyników ----
document.getElementById('saveBudgetBtn').addEventListener('click', () => {
    const msgBox = document.getElementById('saveMessage');
    const payload = {
        guestCount: state.guestCount,
        totalBudget: state.totalBudget,
        remainingBudget: state.remainingBudget,
        categoryValues: state.categoryValues,
        subcategoryValues: state.subcategoryValues,
        pomysly: getPomyslyArray(),
        fixedSubcategory: state.fixedSubcategory,
        validationMessages: state.validationMessages,
        // fixedCategory: state.fixedCategory,
    };

    const formData = new FormData();
    formData.append('action', 'save_budget');
    formData.append('state', JSON.stringify(payload));

    fetch(calculatorAjax.ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage(msgBox, data.message || 'Dane zostały zapisane', 'success');
            } else {
                showMessage(msgBox, data.message || 'Wystąpił błąd po stronie serwera', 'error');
            }
        })
        .catch((err) => {
            showMessage(msgBox, "Błąd zapisywania danych", "error");
        });
});

// ---- SHOW MESSAGE HELPERS ----
function showMessage(element, msg, type) {
    element.textContent = msg;
    element.classList.remove('success', 'error', 'loading');
    element.classList.add(type);
}


// ---- CLEAR MESSAGE HELPERS ----
function clearMessage(element) {
    element.textContent = '';
    element.classList.remove('success', 'error', 'loading');
}

function getPomyslyArray() {
    const ideas = [];
    document.querySelectorAll('.idea-item').forEach(el => {
        const text = el.textContent;
        const parts = text.split(' - ');
        if (parts.length === 2) {
            const name = parts[0].trim();
            const price = parseFloat(parts[1].replace(/[^\d\.]/g, ''));
            if (!isNaN(price)) {
                ideas.push({ name, price });
            }
        }
    });
    return ideas;
}

function renderDetailTable(detail, label = '') {
    const container = document.getElementById('result-details');
    if (!container) return;

    container.innerHTML = '';

    if (label) {
        const header = document.createElement('h3');
        header.textContent = label;
        header.style.marginBottom = '10px';
        container.appendChild(header);
    }

    const table = document.createElement('table');
    table.style.borderCollapse = 'collapse';
    table.style.width = '100%';

    table.appendChild(createTableRow('Liczba gości', detail.guestCount ?? 'brak'));
    table.appendChild(createTableRow('Całkowity budżet', formatCurrency(detail.totalBudget ?? 0)));
    table.appendChild(createTableRow('Pozostały budżet', formatCurrency(detail.remainingBudget ?? 0)));

    // Kategorie
    const categoryHeader = document.createElement('tr');
    categoryHeader.innerHTML = `<td colspan="2" style="font-weight:bold; padding-top: 10px;">Kategorie:</td>`;
    table.appendChild(categoryHeader);

    if (detail.categoryValues) {
        for (const [key, value] of Object.entries(detail.categoryValues)) {
            table.appendChild(createTableRow(formatLabel(key), formatCurrency(value)));
        }
    }

    // Podkategorie
    const subcategoryHeader = document.createElement('tr');
    subcategoryHeader.innerHTML = `<td colspan="2" style="font-weight:bold; padding-top: 10px;">Podkategorie:</td>`;
    table.appendChild(subcategoryHeader);

    if (detail.subcategoryValues) {
        for (const [catKey, subcats] of Object.entries(detail.subcategoryValues)) {
            const catLabel = formatLabel(catKey);

            const subcatTitle = document.createElement('tr');
            subcatTitle.innerHTML = `<td colspan="2" style="padding-top: 10px;">${catLabel}</td>`;
            table.appendChild(subcatTitle);

            for (const [subKey, subValue] of Object.entries(subcats)) {
                table.appendChild(createTableRow('— ' + formatLabel(subKey), formatCurrency(subValue)));
            }
        }
    }

    container.appendChild(table);
}

function createTableRow(label, value) {
    const row = document.createElement('tr');

    const labelCell = document.createElement('td');
    labelCell.textContent = label;
    labelCell.style.border = '1px solid #ccc';
    labelCell.style.padding = '5px';
    labelCell.style.fontWeight = 'bold';

    const valueCell = document.createElement('td');
    valueCell.textContent = value;
    valueCell.style.border = '1px solid #ccc';
    valueCell.style.padding = '5px';

    row.appendChild(labelCell);
    row.appendChild(valueCell);

    return row;
}

function formatCurrency(val) {
    if (typeof val !== 'number') return val;
    return val.toLocaleString('pl-PL', { style: 'currency', currency: 'PLN', minimumFractionDigits: 0 });
}

function formatLabel(label) {
    // Zamień camelCase/underscore na normalne nazwy
    if (!label) return '';
    return label.replace(/([A-Z])/g, ' $1').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}


// ---- Zwijanie i rozwijanie kategroii ----
document.querySelectorAll('.category-box h3').forEach(title => {
    title.addEventListener('click', () => {
        const box = title.parentElement;
        // Toggle klasy active na klikniętej kategorii
        box.classList.toggle('active');
    });
});


// --------
function showMessage(el, text, type) {
    el.innerHTML = '';
    el.classList.remove('success', 'error');
    el.classList.add(type);

    if (Array.isArray(text)) {
        const ul = document.createElement('ul');
        text.forEach(msg => {
            const li = document.createElement('li');
            li.textContent = msg;
            ul.appendChild(li);
        });
        el.appendChild(ul);
    } else {
        el.textContent = text;
    }
}
document.addEventListener('DOMContentLoaded', initCalculator);

function initCalculator() {
    const form = document.getElementById('calculator-form');
    const resultBox = document.getElementById('result');

    if (!form || !resultBox) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await handleCalculatorSubmit(form, resultBox);
    });
}

async function handleCalculatorSubmit(form, resultBox) {
    clearResult(resultBox);

    const data = getFormData(form);
    const total = calculateTotal(data);

    showResult(resultBox, total);

    if (calculator_ajax_obj.is_logged_in) {
        try {
            const response = await saveUserResult(total);
            if (!response.success) {
                console.warn('Nie udało się zapisać wyniku:', response.data?.message || 'Nieznany błąd');
            }
        } catch (error) {
            console.error('Błąd sieci przy zapisie wyniku:', error);
        }
    }
}

function getFormData(form) {
    return {
        guests: parseInt(form.querySelector('#guests').value) || 0,
        costPerGuest: parseFloat(form.querySelector('#costPerGuest').value) || 0,
        photographer: parseFloat(form.querySelector('#photographer').value) || 0,
        music: parseFloat(form.querySelector('#music').value) || 0,
        other: parseFloat(form.querySelector('#other').value) || 0,
    };
}

function calculateTotal({ guests, costPerGuest, photographer, music, other }) {
    return guests * costPerGuest + photographer + music + other;
}

function showResult(element, total) {
    element.textContent = `Całkowity koszt: ${total.toFixed(2)} PLN`;
}

function clearResult(element) {
    element.textContent = '';
}

async function saveUserResult(total) {
    const body = new URLSearchParams({
        action: 'save_user_result',
        result: total,
        security: calculator_ajax_obj.nonce,
    });

    const response = await fetch(calculator_ajax_obj.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body,
    });

    return response.json();
}

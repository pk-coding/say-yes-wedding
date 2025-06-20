document.addEventListener('DOMContentLoaded', initInviteForm);

// ---- INIT ----
function initInviteForm() {
    const inviteBtn = document.getElementById('invite-send-btn');
    const emailInput = document.getElementById('invite-email');
    const messageDiv = document.getElementById('invite-message');

    if (!inviteBtn || !emailInput || !messageDiv) return;

    inviteBtn.addEventListener('click', async () => {
        clearMessage(messageDiv);
        const email = emailInput.value.trim();

        const validationError = validateEmail(email);
        if (validationError) {
            showMessage(messageDiv, validationError, 'error');
            return;
        }

        disableButton(inviteBtn, 'Wysyłanie...');

        try {
            const response = await sendInviteRequest(email);
            handleResponse(response, messageDiv, emailInput);
        } catch (err) {
            showMessage(messageDiv, 'Błąd połączenia.', 'error');
        } finally {
            enableButton(inviteBtn, 'Zaproś');
        }
    });
}

// ---- VALIDATION ----
function validateEmail(email) {
    if (!email || email.trim() === '') {
        return 'Proszę wpisać email.';
    }

    email = email.trim();

    if (email.length < 6 || email.length > 254) {
        return 'Adres e-mail musi mieć od 6 do 254 znaków.';
    }

    // Prosty regex na podstawowy format emaila — możesz go dostosować, jeśli chcesz bardziej rozbudowany
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        return 'Nieprawidłowy format email.';
    }

    return null; // wszystko ok
}


// ---- AJAX ----
async function sendInviteRequest(email) {
    const formData = new FormData();
    formData.append('action', 'send_invite');
    formData.append('nonce', invite_ajax_obj.partner_invite_nonce);
    formData.append('email', email);

    const res = await fetch(invite_ajax_obj.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    });

    return res.json();
}

// ---- RESPONSE HANDLING ----
function handleResponse(response, messageDiv, emailInput) {
    if (response.success) {
        showMessage(messageDiv, response.data || 'Zaproszenie zostało wysłane.', 'success');
        emailInput.value = '';
    } else {
        showMessage(messageDiv, response.data || 'Wystąpił błąd.', 'error');
    }
}

// ---- MESSAGE UTILS ----
function showMessage(el, text, type) {
    el.textContent = text;
    el.className = '';
    el.classList.add(type);
}

function clearMessage(el) {
    el.textContent = '';
    el.className = '';
}

// ---- BUTTON STATE ----
function disableButton(btn, label) {
    btn.disabled = true;
    btn.textContent = label;
}

function enableButton(btn, label) {
    btn.disabled = false;
    btn.textContent = label;
}

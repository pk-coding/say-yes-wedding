// ---- INIT ----
document.addEventListener('DOMContentLoaded', initLoginPanel);


// ---- INIT FUNCTIONS ----
function initLoginPanel() {
    const ajaxUrl = login_ajax_obj.ajax_url;
    const nonce = login_ajax_obj.nonce;

    setupLoginForm(ajaxUrl, nonce);
    setupForgotPasswordButton(ajaxUrl, nonce);
}


// ---- SETUP FUNCTION FOR CONFIGURING THE LOGIN FORM ----
function setupLoginForm(ajaxUrl, nonce) {
    const form = document.getElementById('login-form');
    const messageBox = document.getElementById('login-message');
    if (!form || !messageBox) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearMessage(messageBox);
        const { username, password } = collectLoginFormData(form);

        const validationError = validateLoginData(username, password);
        if (validationError) {
            showMessage(messageBox, validationError, 'error');
            return;
        }

        disableForm(form);
        showMessage(messageBox, 'Logowanie...', 'loading');

        try {
            const response = await sendLoginRequest(ajaxUrl, nonce, username, password);

            let message = 'Zalogowano pomyślnie.';
            if (Array.isArray(response.data)) {
                message = response.data.join('<br>');
            } else if (typeof response.data === 'string') {
                message = response.data;
            } else if (typeof response.data?.message === 'string') {
                message = response.data.message;
            }

            showMessage(messageBox, message, response.success ? 'success' : 'error');

            if (response.success) {
                setTimeout(() => {
                    window.location.href = '/say-yes/panel'; // <- zmień na właściwy URL
                }, 1000);
            }
        } catch (error) {
            console.error('Błąd sieci:', error);
            showMessage(messageBox, 'Błąd serwera podczas logowania.', 'error');
        } finally {
            enableForm(form);
        }
    });
}


// ---- HANDLE THE "ZAPOMNIAŁEŚ HASŁA?" BUTTON ----
function setupForgotPasswordButton(ajaxUrl, nonce) {
    const btn = document.getElementById('forgot-password-btn');
    const messageBox = document.getElementById('login-message');
    if (!btn || !messageBox) return;

    btn.addEventListener('click', async () => {
        const usernameOrEmail = document.querySelector('#login-username').value.trim();
        clearMessage(messageBox);

        if (!usernameOrEmail) {
            showMessage(messageBox, 'Podaj nazwę użytkownika lub email.', 'error');
            return;
        }

        showMessage(messageBox, 'Wysyłanie wiadomości resetującej...', 'loading');

        const formData = new FormData();
        formData.append('action', 'custom_forgot_password');
        formData.append('security', nonce);
        formData.append('username_or_email', usernameOrEmail);

        try {
            const res = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await res.json();
            showMessage(messageBox, data.data, data.success ? 'success' : 'error');
        } catch (error) {
            console.error('Błąd sieci:', error);
            showMessage(messageBox, 'Błąd serwera podczas resetowania hasła.', 'error');
        }
    });
}


// ---- COLLECT LOGIN FORM DATA ----
function collectLoginFormData(form) {
    return {
        username: form.querySelector('#login-username')?.value.trim(),
        password: form.querySelector('#login-password')?.value
    };
}


// ---- VALIDATE LOGIN FORM DATA ----
function validateLoginData(username, password) {
    if (!username || !password) {
        return 'Podaj nazwę użytkownika i hasło.';
    }
    return null;
}


// ---- VALIDATE FORM DATA ----
async function sendLoginRequest(ajaxUrl, nonce, username, password) {
    const formData = new FormData();
    formData.append('action', 'custom_user_login');
    formData.append('security', nonce);
    formData.append('username', username);
    formData.append('password', password);

    const res = await fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    });

    return res.json();
}


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


// LOCKING THE LOGIN FORM
function disableForm(form) {
    form.querySelectorAll('input, button').forEach(el => el.disabled = true);
}


// UNLOCKING THE LOGIN FORM
function enableForm(form) {
    form.querySelectorAll('input, button').forEach(el => el.disabled = false);
}

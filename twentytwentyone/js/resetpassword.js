// ---- INIT ----
document.addEventListener('DOMContentLoaded', initResetPasswordForm);


// ---- INIT FUNCTIONS ----
function initResetPasswordForm() {
    setupResetPasswordForm();
}


// ---- SETUP FUNCTION FOR CONFIGURING THE RESET FORM ----
function setupResetPasswordForm() {
    const form = document.getElementById('password-reset-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await handleResetPasswordFormSubmit(form);
    });
}


// ---- HANDLE RESET PASSWORD FORM SUBMIT ----
async function handleResetPasswordFormSubmit(form) {
    const messageEl = document.getElementById('reset-message');
    clearMessage(messageEl);

    const data = collectResetFormData(form);

    const validationError = validateResetFormData(data);
    if (validationError) {
        showMessage(messageEl, validationError, 'error');
        return;
    }

    try {
        const response = await sendResetPasswordRequest(data);

        // Obsługa treści wiadomości z response.data
        let message = 'Hasło zostało zresetowane!';
        if (Array.isArray(response.data)) {
            message = response.data.join('<br>');
        } else if (typeof response.data === 'string') {
            message = response.data;
        } else if (typeof response.data?.message === 'string') {
            message = response.data.message;
        }

        showMessage(messageEl, message, response.success ? 'success' : 'error');

        if (response.success) {
            setTimeout(() => {
                window.location.href = '/say-yes/panel'; // dostosuj URL
            }, 2000);
        }
    } catch (err) {
        showMessage(messageEl, 'Błąd połączenia z serwerem.', 'error');
    }

}


// ---- COLLECT RESET PASSWORD FORM DATA ----
function collectResetFormData(form) {
    return {
        reset_key: form.querySelector('#reset-key')?.value,
        login: form.querySelector('#reset-login')?.value,
        new_password: form.querySelector('#new-password')?.value,
        confirm_password: form.querySelector('#confirm-password')?.value
    };
}


// ---- VALIDATE RESET PASSWORD FORM DATA ----
function validateResetFormData(data) {
    const errors = [];

    if (!data.new_password || !data.confirm_password) {
        errors.push('Wszystkie pola są wymagane.');
    }
    if (data.new_password.length < 12 || data.new_password.length > 80) {
        errors.push('Hasło musi mieć od 12 do 80 znaków.');
    }
    if (!/[A-Z]/.test(data.new_password)) {
        errors.push('Hasło musi zawierać co najmniej jedną dużą literę.');
    }

    if (!/\d/.test(data.new_password)) {
        errors.push('Hasło musi zawierać co najmniej jedną cyfrę.');
    }

    if (!/^[a-zA-Z0-9_.-]+$/.test(data.new_password)) {
        errors.push('Hasło może zawierać tylko litery, cyfry, kropki, podkreślenia i myślniki.');
    }
    if (data.new_password !== data.confirm_password) {
        errors.push('Hasła nie są takie same.');
    }

    return errors.length > 0 ? errors : null;

}


// ---- SEND AJAX REQUEST TO BACKEND ----
async function sendResetPasswordRequest(data) {
    const formData = new FormData();
    formData.append('action', 'custom_reset_password');
    formData.append('security', login_ajax_obj.nonce);
    formData.append('reset_key', data.reset_key);
    formData.append('login', data.login);
    formData.append('new_password', data.new_password);
    formData.append('confirm_password', data.confirm_password);

    const res = await fetch(login_ajax_obj.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    });

    return res.json();
}


// ---- SHOW MESSAGE HELPERS ----
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


// ---- CLEAR MESSAGE HELPERS ----
function clearMessage(el) {
    el.innerHTML = '';
    el.classList.remove('success', 'error');
}


// LOCKING THE RESET PASSWORD FORM
function disableForm(form) {
    form.querySelectorAll('input, button').forEach(el => el.disabled = true);
}


// UNLOCKING THE RESET PASSWORD FORM
function enableForm(form) {
    form.querySelectorAll('input, button').forEach(el => el.disabled = false);
}

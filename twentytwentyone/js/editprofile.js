// ---- INIT ----
document.addEventListener('DOMContentLoaded', initProfileForms);


// ---- INIT FUNCTIONS ----
function initProfileForms() {
    setupEmailForm();
    setupPasswordForm();
    setupRemovePartnerForm();
    setupCustomUserRoleForm();
}


// ---- SETUP EMAIL FORM ----
function setupEmailForm() {
    const form = document.getElementById('profile-email-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await handleEmailFormSubmit(form);
    });
}


// ---- SETUP PASSWORD FORM ----
function setupPasswordForm() {
    const form = document.getElementById('profile-password-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await handlePasswordFormSubmit(form);
    });
}


// ---- HANDLE EMAIL FORM SUBMIT ----
async function handleEmailFormSubmit(form) {
    const data = collectEmailFormData(form);
    const msgBox = document.getElementById('profile-email-message');
    clearMessage(msgBox);

    const error = validateEmailData(data);
    if (error) {
        showMessage(msgBox, error, 'error');
        return;
    }

    disableForm(form);
    showMessage(msgBox, 'Aktualizuję adres e-mail...', 'loading');

    try {
        const response = await sendEmailUpdateRequest(data);

        let message = 'Operacja zakończona.';
        if (Array.isArray(response.data)) {
            message = response.data.join('<br>');
        } else if (typeof response.data === 'string') {
            message = response.data;
        } else if (typeof response.data?.message === 'string') {
            message = response.data.message;
        }

        if (response.success) {
            showMessage(msgBox, message, 'success');

            // jeśli w odpowiedzi jest redirect — przekieruj
            const redirectUrl = response.data?.redirect;
            if (redirectUrl) {
                setTimeout(() => window.location.href = redirectUrl, 1500);
            }

            console.log(response.data);
        } else {
            showMessage(msgBox, message, 'error');
        }
    } catch {
        showMessage(msgBox, 'Błąd sieci lub serwera.', 'error');
    } finally {
        enableForm(form);
    }

}


// ---- HANDLE PASSWORD FORM SUBMIT ----
async function handlePasswordFormSubmit(form) {
    const data = collectPasswordFormData(form);

    const msgBox = document.getElementById('profile-password-message');
    clearMessage(msgBox);

    const error = validatePasswordData(data);
    if (error) {
        showMessage(msgBox, error, 'error');
        return;
    }

    disableForm(form);
    showMessage(msgBox, 'Zmieniam hasło...', 'loading');

    try {
        const response = await sendPasswordUpdateRequest(data);

        let message = 'Zmieniono hasło. Zaloguj się ponownie na nowe dane.';
        if (Array.isArray(response.data)) {
            message = response.data.join('<br>');
        } else if (typeof response.data === 'string') {
            message = response.data;
        } else if (typeof response.data?.message === 'string') {
            message = response.data.message;
        }

        showMessage(msgBox, message, response.success ? 'success' : 'error');

        if (response.success && response.data?.redirect) {
            setTimeout(() => window.location.href = response.data.redirect, 1500);
        }
    } catch {
        showMessage(msgBox, 'Błąd sieci lub serwera.', 'error');
    } finally {
        enableForm(form);
    }

}


// ---- COLLECT EMAIL DATA ----
function collectEmailFormData(form) {
    return {
        new_email: form.querySelector('#new-email')?.value.trim(),
        action: 'custom_update_email',
        security: profile_ajax_obj.nonce_email
    };
}


// ---- COLLECT PASSWORD DATA ----
function collectPasswordFormData(form) {
    return {
        current_password: form.querySelector('#current-password')?.value,
        new_password: form.querySelector('#new-password')?.value,
        confirm_password: form.querySelector('#confirm-password')?.value,
        action: 'custom_update_password',
        security: profile_ajax_obj.nonce_password
    };
}


// ---- VALIDATE EMAIL ----
function validateEmailData(data) {
    const errors = [];

    if (data.new_email.length < 6 || data.new_email.length > 254) {
        errors.push('Adres e-mail musi mieć od 6 do 254 znaków.');
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(data.new_email)) {
        errors.push('Podaj poprawny adres e-mail.');
    }

    return errors.length > 0 ? errors : null;
}


// ---- VALIDATE PASSWORD ----
function validatePasswordData(data) {
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


// ---- SEND AJAX REQUEST: EMAIL ----
async function sendEmailUpdateRequest(data) {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => formData.append(key, value));

    const res = await fetch(profile_ajax_obj.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    });

    return res.json();
}


// ---- SEND AJAX REQUEST: PASSWORD ----
async function sendPasswordUpdateRequest(data) {
    const formData = new FormData();
    Object.entries(data).forEach(([key, value]) => formData.append(key, value));

    const res = await fetch(profile_ajax_obj.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    });

    return res.json();
}


// DELETE RELATIONSHIP
function setupRemovePartnerForm() {
    const form = document.getElementById('remove-partner-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const msgBox = document.getElementById('remove-partner-message');
        clearMessage(msgBox);

        disableForm(form);
        showMessage(msgBox, 'Wysyłam e-mail z linkiem potwierdzającym...', 'loading');

        try {
            const formData = new FormData();
            formData.append('action', 'custom_remove_partner_request');
            formData.append('security', profile_ajax_obj.nonce_remove_partner);

            const res = await fetch(profile_ajax_obj.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });

            const response = await res.json();

            if (response.success) {
                showMessage(msgBox, response.data.message || 'Link z potwierdzeniem został wysłany.', 'success');
            } else {
                showMessage(msgBox, response.data || 'Coś poszło nie tak.', 'error');
            }
        } catch {
            showMessage(msgBox, 'Błąd sieci lub serwera.', 'error');
        } finally {
            enableForm(form);
        }
    });
}


// ---- CHANGE USER ROLE ----
function setupCustomUserRoleForm() {
    const form = document.getElementById('custom-user-role-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const msgBox = document.getElementById('custom-user-role-message');
        clearMessage(msgBox);

        const role = form.querySelector('input[name="user_role"]:checked')?.value;

        const formData = new FormData();
        formData.append('action', 'custom_update_custom_user_role');
        formData.append('security', profile_ajax_obj.nonce_user_role_custom);
        formData.append('user_role', role);

        disableForm(form);
        showMessage(msgBox, 'Aktualizuję rolę...', 'loading');

        try {
            const res = await fetch(profile_ajax_obj.ajax_url, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });

            const response = await res.json();

            if (response.success) {
                showMessage(msgBox, response.data.message, 'success');
            } else {
                showMessage(msgBox, response.data || 'Błąd.', 'error');
            }
        } catch {
            showMessage(msgBox, 'Błąd sieci lub serwera.', 'error');
        } finally {
            enableForm(form);
        }
    });
}


// ---- UI HELPERS ----
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

function clearMessage(el) {
    el.innerHTML = '';
    el.classList.remove('success', 'error', 'loading');
}

function disableForm(form) {
    form.querySelectorAll('input, button').forEach(el => el.disabled = true);
}

function enableForm(form) {
    form.querySelectorAll('input, button').forEach(el => el.disabled = false);
}

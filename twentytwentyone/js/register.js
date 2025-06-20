// ---- INIT ----
document.addEventListener('DOMContentLoaded', initRegisterForm);


// ---- INIT FUNCTIONS ----
function initRegisterForm() {
    setupRegisterForm();
}


// ---- SETUP FUNCTION FOR CONFIGURING THE REGISTRATION FORM ----
function setupRegisterForm() {
    const form = document.getElementById('register-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        await handleRegisterFormSubmit(form);
    });
}


// ---- HANDLE REGISTRATION FORM SUBMIT ----
async function handleRegisterFormSubmit(form) {
    const formData = collectFormData(form);
    const messageEl = document.getElementById('form-message');
    clearMessage(messageEl);

    const validationError = validateFormData(formData);
    if (validationError) {
        showMessage(messageEl, validationError, 'error');
        return;
    }

    try {
        const response = await sendRegistrationRequest(formData);
        if (response.success) {
            const message = typeof response.data === 'string'
                ? response.data
                : 'Rejestracja powiodła się!';
            showMessage(messageEl, message, 'success');
            setTimeout(() => window.location.href = register_ajax_obj.redirect_url, 1500);
        } else {
            const message =
                Array.isArray(response.data) ? response.data.join('<br>') :
                    (typeof response.data === 'string' ? response.data : 'Błąd rejestracji.');
            showMessage(messageEl, message, 'error');
        }
    } catch (err) {
        showMessage(messageEl, 'Błąd sieci lub serwera.', 'error');
    }
}


// ---- COLLECT REGISTRATION FORM DATA ----
function collectFormData(form) {
    return {
        username: form.querySelector('#username')?.value.trim(),
        email: form.querySelector('#email')?.value.trim(),
        password: form.querySelector('#password')?.value,
        password_confirm: form.querySelector('#password_confirm')?.value,
        role: form.querySelector('input[name="role"]:checked')?.value || '',
        statute: form.querySelector('input[name="statute"]:checked')?.value || '',
        rodo: form.querySelector('input[name="rodo"]:checked')?.value || '',
    };
}


// ---- VALIDATE REGISTRATION FORM DATA ----
function validateFormData(data) {
    const errors = [];

    // Required fields
    if (!data.username || !data.email || !data.password) {
        errors.push('Wszystkie pola są wymagane.');
    }

    // Email
    if (data.email.length < 6 || data.email.length > 254) {
        errors.push('Adres e-mail musi mieć od 6 do 254 znaków.');
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(data.email)) {
        errors.push('Podaj poprawny adres e-mail.');
    }

    // Username
    if (data.username.length < 3 || data.username.length > 32) {
        errors.push('Nazwa użytkownika musi mieć od 3 do 32 znaków.');
    }

    if (!/^[a-zA-Z0-9_.-]+$/.test(data.username)) {
        errors.push('Nazwa użytkownika może zawierać tylko litery, cyfry, kropki, podkreślenia i myślniki.');
    }

    // Password
    if (data.password.length < 12 || data.password.length > 80) {
        errors.push('Hasło musi mieć od 12 do 80 znaków.');
    }

    if (!/[A-Z]/.test(data.password)) {
        errors.push('Hasło musi zawierać co najmniej jedną dużą literę.');
    }

    if (!/\d/.test(data.password)) {
        errors.push('Hasło musi zawierać co najmniej jedną cyfrę.');
    }

    if (!/^[a-zA-Z0-9_.-]+$/.test(data.password)) {
        errors.push('Hasło może zawierać tylko litery, cyfry, kropki, podkreślenia i myślniki.');
    }

    // Confirm email
    if (data.password !== data.password_confirm) {
        errors.push('Hasła nie są takie same.');
    }

    // Role
    if (!data.role || (data.role !== 'pan_mlody' && data.role !== 'panna_mloda')) {
        errors.push('Musisz wybrać prawidłową rolę.');
    }

    // Statute and rodo
    if (data.statute !== '1') {
        errors.push('Musisz zaakceptować regulamin.');
    }

    if (data.rodo !== '1') {
        errors.push('Musisz zaakceptować politykę prywatności.');
    }

    return errors.length > 0 ? errors : null;
}


// ---- SEND AJAX REQUEST TO BACKEND ----
async function sendRegistrationRequest(data) {
    const formData = new FormData();
    formData.append('action', 'custom_user_register');
    formData.append('security', register_ajax_obj.nonce);
    formData.append('username', data.username);
    formData.append('email', data.email);
    formData.append('password', data.password);
    formData.append('password_confirm', data.password_confirm);
    formData.append('role', data.role);
    formData.append('statute', data.statute);
    formData.append('rodo', data.rodo);

    const res = await fetch(register_ajax_obj.ajax_url, {
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


// LOCKING THE REGISTRATION FORM
function disableForm(form) {
    form.querySelectorAll('input, button').forEach(el => el.disabled = true);
}


// UNLOCKING THE REGISTRATION FORM
function enableForm(form) {
    form.querySelectorAll('input, button').forEach(el => el.disabled = false);
}

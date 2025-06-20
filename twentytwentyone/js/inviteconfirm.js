// ---- INIT ----
document.addEventListener('DOMContentLoaded', initInviteConfirm);


// ---- INIT FUNCTION ----
function initInviteConfirm() {
    const root = document.getElementById('invite-confirm-root');
    if (!root || !root.dataset.token) {
        showInviteMessage('Brak tokena w adresie URL.', 'error');
        return;
    }

    setupInviteConfirmUI(root.dataset.token);
}


// ---- SETUP FUNCTION ----
function setupInviteConfirmUI(token) {
    const acceptBtn = document.getElementById('invite-accept-btn');
    const rejectBtn = document.getElementById('invite-reject-btn');
    const actionsDiv = document.getElementById('invite-confirm-actions');

    if (!acceptBtn || !rejectBtn || !actionsDiv) return;

    actionsDiv.style.display = 'block';

    acceptBtn.addEventListener('click', () => handleInviteConfirmAction('accept', token));
    rejectBtn.addEventListener('click', () => handleInviteConfirmAction('reject', token));
}


// ---- HANDLE INVITE ACTION ----
async function handleInviteConfirmAction(actionType, token) {

    disableInviteButtons(true);
    clearInviteMessage();

    try {
        const formData = new FormData();
        formData.append('action', 'invite_confirm_action');
        formData.append('nonce', inviteconfirm_ajax_obj.partner_inviteconfirm_nonce);
        formData.append('token', token);
        formData.append('action_type', actionType);

        const response = await fetch(inviteconfirm_ajax_obj.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showInviteMessage(result.data || 'Operacja zakończona sukcesem.', 'success');
            document.getElementById('invite-confirm-actions').style.display = 'none';
        } else {
            showInviteMessage(result.data || 'Wystąpił błąd.', 'error');
        }
    } catch (err) {
        console.error('Błąd sieci lub serwera:', err);
        showInviteMessage('Błąd sieci lub serwera.', 'error');
    } finally {
        disableInviteButtons(false);
    }
}


// ---- UI HELPERS ----
function showInviteMessage(text, type = 'info') {
    const messageEl = document.getElementById('invite-confirm-message');
    if (!messageEl) return;

    messageEl.textContent = text;
    messageEl.classList.remove('success', 'error', 'info');
    messageEl.classList.add(type);
}

function clearInviteMessage() {
    const messageEl = document.getElementById('invite-confirm-message');
    if (!messageEl) return;

    messageEl.textContent = '';
    messageEl.classList.remove('success', 'error', 'info');
}

function disableInviteButtons(disabled) {
    const acceptBtn = document.getElementById('invite-accept-btn');
    const rejectBtn = document.getElementById('invite-reject-btn');

    if (acceptBtn) acceptBtn.disabled = disabled;
    if (rejectBtn) rejectBtn.disabled = disabled;
}

function showInviteMessage(message, type = 'success') {
    const messageBox = document.getElementById('invite-confirm-message');

    if (!messageBox) return;

    messageBox.innerHTML = message;

    // Usuń stare klasy i dodaj nową
    messageBox.classList.remove('success', 'error', 'hidden');
    messageBox.classList.add(type);
}

function clearInviteMessage() {
    const messageBox = document.getElementById('invite-confirm-message');

    if (!messageBox) return;

    messageBox.innerHTML = '';
    messageBox.classList.add('hidden');
    messageBox.classList.remove('success', 'error');
}

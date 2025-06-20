// ---- INIT ----
document.addEventListener('DOMContentLoaded', initPanel);


// ---- INIT FUNCTIONS ----
function initPanel() {
    const ajaxUrl = panel_ajax_obj.ajax_url;
    const userDeleteResultNonce = panel_ajax_obj.user_delete_result_nonce;

    setupUserPDFButtons(ajaxUrl);
    setupPartnerPDFButtons(ajaxUrl);
    setupDeleteResultButtons(ajaxUrl, userDeleteResultNonce);
}


// ---- GENERATE PDF FILE WITH USER'S CALCULATOR RESULTS ----
function setupUserPDFButtons(ajaxUrl) {
    const pdfBtns = document.querySelectorAll('.generate-pdf-btn');
    if (!pdfBtns.length) return;

    pdfBtns.forEach((btn) => {
        btn.addEventListener('click', async (e) => {
            const parent = e.target.closest('.result-item');
            if (!parent) return;

            const resultId = parent.dataset.id;
            if (!resultId) {
                alert('Brak ID wyniku.');
                return;
            }

            try {
                const data = await sendAjaxRequest(
                    ajaxUrl,
                    'get_user_result',
                    { result_id: resultId },
                    panel_ajax_obj.user_results_nonce
                );

                if (!data.success || !data.data) {
                    alert('Nie udało się pobrać wyniku.');
                    return;
                }

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                doc.setFontSize(16);
                doc.text('Twój wynik kalkulatora:', 10, 20);
                doc.setFontSize(12);
                doc.text(data.data.result, 10, 30);

                const filename = createNameOfPdfFile();
                doc.save(filename);
            } catch (e) {
                alert('Błąd generowania PDF.');
            }
        });
    });
}


// ---- GENERATE PDF FILE WITH PARTNER'S CALCULATOR RESULTS ----
function setupPartnerPDFButtons(ajaxUrl) {
    const partnerPdfBtns = document.querySelectorAll('.generate-partner-pdf-btn');
    if (!partnerPdfBtns.length) return;

    partnerPdfBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const parent = e.target.closest('.result-item');
            if (!parent) return;

            const resultId = parent.dataset.id;
            if (!resultId) {
                alert('Brak ID wyniku.');
                return;
            }

            try {
                const data = await sendAjaxRequest(
                    ajaxUrl,
                    'get_partner_result',
                    { result_id: resultId },
                    panel_ajax_obj.partner_results_nonce
                );

                if (!data.success || !data.data) {
                    alert('Nie udało się pobrać wyniku partnera.');
                    return;
                }

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                doc.setFontSize(16);
                doc.text('Twój wynik kalkulatora:', 10, 20);
                doc.setFontSize(12);
                doc.text(data.data.result, 10, 30);

                const filename = createNameOfPdfFile();
                doc.save(filename);
            } catch (e) {
                alert('Błąd generowania PDF partnera.');
            }
        });
    });
}


// DELETE USER CALCULATOR RESULTS
async function setupDeleteResultButtons(ajaxUrl, userDeleteResultNonce) {
    const deleteBtns = document.querySelectorAll('.delete-btn');
    if (!deleteBtns.length) return;

    deleteBtns.forEach((btn) => {
        btn.addEventListener('click', async (e) => {
            const parent = e.target.closest('.result-item');
            if (!parent) return;

            const resultId = parent.dataset.id;
            if (!resultId) {
                alert('Brak ID wyniku.');
                return;
            }

            if (!confirm('Czy na pewno chcesz usunąć ten wynik?')) {
                return;
            }

            try {
                const data = await sendAjaxRequest(
                    ajaxUrl,
                    'user_delete_result',
                    { result_id: resultId },
                    userDeleteResultNonce
                );

                if (!data.success) {
                    alert('Błąd podczas usuwania: ' + (data.data || 'nieznany'));
                    return;
                }

                parent.remove();
            } catch (err) {
                handleAjaxError();
            }
        });
    });
}


// ---- UTILS ----
function createNameOfPdfFile() {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');

    return `say-yes-kalkulator_${year}-${month}-${day}__${hours}-${minutes}-${seconds}.pdf`;
}


// ----
async function sendAjaxRequest(ajaxUrl, action, data, nonce) {
    const body = new URLSearchParams({
        ...data,
        action,
        security: nonce
    });

    const response = await fetch(ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return response.json();
}


// ----
function handleAjaxError() {
    alert('Błąd komunikacji z serwerem.');
}

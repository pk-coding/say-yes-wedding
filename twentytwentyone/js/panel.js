// ---- INIT ----
document.addEventListener('DOMContentLoaded', initPanel);


// ---- INIT FUNCTIONS ----
function initPanel() {
    const ajaxUrl = panel_ajax_obj.ajax_url;
    const userDeleteResultNonce = panel_ajax_obj.user_delete_result_nonce;

    // setupDeleteResultButtons(ajaxUrl, userDeleteResultNonce);
}


// DELETE USER CALCULATOR RESULTS
function handleDeleteResult(id, element) {
    if (!confirm('Na pewno chcesz usunąć ten wynik?')) return;

    fetch(panel_ajax_obj.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: new URLSearchParams({
            action: 'user_delete_result',
            result_id: id,
            security: panel_ajax_obj.user_delete_result_nonce,
        }),
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Wynik został usunięty.');
                element.remove();
            } else {
                alert('Błąd: ' + data.data);
            }
        })
        .catch(() => alert('Błąd połączenia z serwerem'));
}


// ---- AJAX HELPER ----
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


// ---- HANDLER TO GENERATE TABLE WITH RESULTS AND PDF FILE FOR USER ----
document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        const target = e.target;
        const resultItem = target.closest('.result-item');
        if (!resultItem) return;
        const id = resultItem.getAttribute('data-id');
        if (!id) return;

        if (target.classList.contains('show-details-btn')) {
            // Pokaz szczegóły — ajax get_result_detail
            fetch(panel_ajax_obj.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: new URLSearchParams({
                    action: 'get_result_details',
                    id: id,
                    _ajax_nonce: panel_ajax_obj.user_results_nonce,
                }),
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderDetailTable(data.data, 'Szczegóły wyniku ID: ' + id);
                    } else {
                        alert('Błąd: ' + data.data);
                    }
                })
                .catch(() => alert('Błąd połączenia z serwerem'));

        } else if (target.classList.contains('generate-pdf-btn')) {
            alert('Wywołano generowanie PDF dla partnera. Wynik ID: ' + id);

        } else if (target.classList.contains('delete-btn')) {
            handleDeleteResult(id, resultItem);
        }
    });
});


// ---- HANDLER TO GENERATE TABLE WITH RESULTS AND PDF FILE FOR PARTNER ----
document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        const target = e.target;
        const resultItem = target.closest('.partner-result-item');
        if (!resultItem) return;
        const id = resultItem.getAttribute('data-id');
        if (!id) return;

        // 🔹 Pokaz szczegóły partnera
        if (target.classList.contains('show-partner-details-btn')) {
            fetch(panel_ajax_obj.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: new URLSearchParams({
                    action: 'get_result_details',
                    id: id,
                    _ajax_nonce: panel_ajax_obj.partner_results_nonce,
                }),
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderDetailTable(data.data, 'Szczegóły wyniku ID: ' + id);
                    } else {
                        alert('Błąd: ' + data.data);
                    }
                })
                .catch(() => alert('Błąd połączenia z serwerem'));
        }

        // 🔹 Generowanie PDF dla partnera
        else if (target.classList.contains('generate-partner-pdf-btn')) {
            alert('Wywołano generowanie PDF dla partnera. Wynik ID: ' + id);
            // Tu możesz dodać własną logikę
        }
    });
});


// ---- HELPERS FOR GENERATE TABLE WITH RESULTS ----
function renderDetailTable(detail, label = '') {
    const container = document.getElementById('result-details');
    if (!container) return;

    container.innerHTML = ''; // Czyść poprzednie dane

    if (label) {
        const header = document.createElement('h3');
        header.textContent = label;
        header.style.marginBottom = '10px';
        container.appendChild(header);
    }

    const table = document.createElement('table');
    table.style.borderCollapse = 'collapse';
    table.style.width = '100%';

    table.appendChild(createTableRow('Liczba gości', detail.guestCount ?? 'brak', '', false));
    table.appendChild(createTableRow('Całkowity budżet', formatCurrency(detail.totalBudget ?? 0)));
    table.appendChild(createTableRow('Pozostały budżet', formatCurrency(detail.remainingBudget ?? 0)));

    const categoryHeader = document.createElement('tr');
    categoryHeader.innerHTML = `<td colspan="2" style="font-weight:bold; padding-top: 10px;">Kategorie:</td>`;
    table.appendChild(categoryHeader);

    if (detail.categoryValues) {
        for (const [key, value] of Object.entries(detail.categoryValues)) {
            table.appendChild(createTableRow(formatLabel(key), formatCurrency(value)));
        }
    }

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
                // Pobierz komunikat walidacji jeśli jest
                const validationMsg = detail.validationMessages ? detail.validationMessages[subKey] : '';
                table.appendChild(createTableRow('— ' + formatLabel(subKey), subValue, validationMsg));
            }
        }
    }

    if (detail.pomysly && detail.pomysly.length > 0) {
        const ideasHeader = document.createElement('tr');
        ideasHeader.innerHTML = `<td colspan="2" style="font-weight:bold; padding-top: 10px;">Pomysły:</td>`;
        table.appendChild(ideasHeader);

        detail.pomysly.forEach(idea => {
            table.appendChild(createTableRow(
                '— ' + idea.idea_name,
                formatCurrency(Number(idea.idea_price))
            ));
        });
    }

    container.appendChild(table);

    // Add button Download PDF under the table
    const pdfBtn = document.createElement('button');
    pdfBtn.textContent = 'Pobierz PDF';
    pdfBtn.className = 'download-pdf-btn';
    pdfBtn.style.marginTop = '20px';
    container.appendChild(pdfBtn);

    // Add addEventListener for button
    pdfBtn.addEventListener('click', function () {
        generatePdfFromTable(label, table.outerHTML);
    });
}


// Geberate design of table
function createTableRow(label, value, validationMessage = '', isCurrency = true) {
    const row = document.createElement('tr');

    const labelCell = document.createElement('td');
    labelCell.textContent = label;
    labelCell.style.border = '1px solid #ccc';
    labelCell.style.padding = '5px';
    labelCell.style.fontWeight = 'bold';

    const valueCell = document.createElement('td');
    valueCell.style.border = '1px solid #ccc';
    valueCell.style.padding = '5px';

    // Formatowanie wartości tylko jeśli isCurrency == true
    valueCell.innerHTML = isCurrency ? formatCurrency(value) : value;

    if (validationMessage) {
        const msg = document.createElement('div');
        msg.textContent = validationMessage;
        msg.style.color = 'red';
        msg.style.fontSize = '0.85em';
        msg.style.marginTop = '4px';
        valueCell.appendChild(msg);
    }

    row.appendChild(labelCell);
    row.appendChild(valueCell);

    return row;
}


// Set currency format for PLN
function formatCurrency(val) {
    if (typeof val !== 'number') return val;
    return val.toLocaleString('pl-PL', { style: 'currency', currency: 'PLN', minimumFractionDigits: 0 });
}

// Zamień camelCase/underscore na normalne nazwy
function formatLabel(label) {
    if (!label) return '';
    return label.replace(/([A-Z])/g, ' $1').replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}


// ---- GENERATE PDF FROM TABLE ----
function generatePdfFromTable(title, tableHtml) {
    const printWindow = window.open('', '', 'width=800,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>${title}</title>
            <style>
                table { width: 100%; border-collapse: collapse; }
                td, th { border: 1px solid #ccc; padding: 5px; }
                h3 { margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <h3>${title}</h3>
            ${tableHtml}
        </body>
        </html>
    `);
    printWindow.document.close();

    // Poczekaj na załadowanie
    printWindow.onload = function () {
        printWindow.focus();
        printWindow.print(); // Można też użyć html2pdf.js jeśli chcesz automatyczny download
    };
}

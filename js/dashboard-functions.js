/**
 * Dashboard Functions - Studio Legale BCS
 * Funzioni comuni per dashboard Admin e Cliente
 */

// ==================== UTILITY FUNCTIONS ====================

/**
 * Mostra toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };

    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all transform translate-x-0 opacity-100`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Mostra loader
 */
function showLoader(text = 'Caricamento...') {
    const loader = document.createElement('div');
    loader.id = 'global-loader';
    loader.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50';
    loader.innerHTML = `
        <div class="bg-white rounded-2xl p-8 flex flex-col items-center gap-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
            <p class="text-gray-700 font-medium">${text}</p>
        </div>
    `;
    document.body.appendChild(loader);
}

function hideLoader() {
    const loader = document.getElementById('global-loader');
    if (loader) loader.remove();
}

/**
 * Formatta data in italiano
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Formatta data e ora
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// ==================== ADMIN DASHBOARD FUNCTIONS ====================

/**
 * Segna tutte le notifiche come lette
 */
async function markAllNotificationsRead() {
    try {
        const response = await fetch('api/notifications/mark_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mark_all: true })
        });

        const result = await response.json();
        if (result.success) {
            showToast('Notifiche segnate come lette', 'success');
            // Rimuovi badge rosso
            const badge = document.querySelector('.bg-red-500.rounded-full');
            if (badge) badge.remove();
        }
    } catch (error) {
        console.error('Errore:', error);
        showToast('Errore durante l\'operazione', 'error');
    }
}

/**
 * Filtra notifiche per tipo
 */
function filterNotifications(type) {
    const notifications = document.querySelectorAll('#notifications-list > div');
    const tabs = document.querySelectorAll('#notifications-panel button');

    // Aggiorna tab attivi
    tabs.forEach(tab => {
        tab.classList.remove('text-primary', 'border-b-2', 'border-primary');
        tab.classList.add('text-gray-500');
    });

    event.target.classList.remove('text-gray-500');
    event.target.classList.add('text-primary', 'border-b-2', 'border-primary');

    // Filtra notifiche
    notifications.forEach(notif => {
        if (type === 'all') {
            notif.style.display = 'block';
        } else {
            const notifType = notif.dataset.type || 'message';
            notif.style.display = notifType === type ? 'block' : 'none';
        }
    });
}

/**
 * Toggle pannello notifiche
 */
function toggleNotifications() {
    const panel = document.getElementById('notifications-panel');
    if (panel) {
        panel.classList.toggle('hidden');
    }
}

/**
 * Apre modal nuovo utente
 */
function openNewUserModal() {
    const modal = document.getElementById('modal-new-user');
    if (!modal) {
        createNewUserModal();
    } else {
        modal.classList.remove('hidden');
    }
}

/**
 * Crea modal nuovo utente
 */
function createNewUserModal() {
    const modal = document.createElement('div');
    modal.id = 'modal-new-user';
    modal.className = 'fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="font-bold text-xl text-gray-800 mb-4">Nuovo Utente</h3>
            <form onsubmit="event.preventDefault(); submitNewUser();">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nome *</label>
                        <input type="text" id="new-user-name" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cognome *</label>
                        <input type="text" id="new-user-surname" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email *</label>
                        <input type="email" id="new-user-email" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ruolo *</label>
                        <select id="new-user-role" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm">
                            <option value="client">Cliente</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Password *</label>
                        <input type="password" id="new-user-password" required class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('modal-new-user')" class="flex-1 py-3 rounded-xl border border-gray-200 font-bold text-gray-600 hover:bg-gray-50">Annulla</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-primary text-white font-bold hover:bg-primary-dark">Crea Utente</button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(modal);
}

/**
 * Invia form nuovo utente
 */
async function submitNewUser() {
    const data = {
        first_name: document.getElementById('new-user-name').value,
        last_name: document.getElementById('new-user-surname').value,
        email: document.getElementById('new-user-email').value,
        role: document.getElementById('new-user-role').value,
        password: document.getElementById('new-user-password').value
    };

    try {
        showLoader('Creazione utente...');
        const response = await fetch('api/users/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        hideLoader();

        if (result.success) {
            showToast('Utente creato con successo!', 'success');
            closeModal('modal-new-user');
            // Ricarica lista utenti se siamo nella vista clienti
            if (typeof loadDashboardData === 'function') loadDashboardData();
        } else {
            showToast(result.message || 'Errore durante la creazione', 'error');
        }
    } catch (error) {
        hideLoader();
        console.error('Errore:', error);
        showToast('Errore di connessione', 'error');
    }
}

/**
 * Genera report
 */
function generateReport() {
    const modal = document.createElement('div');
    modal.id = 'modal-generate-report';
    modal.className = 'fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="font-bold text-xl text-gray-800 mb-4">Genera Report</h3>
            <form onsubmit="event.preventDefault(); submitGenerateReport();">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipo Report *</label>
                        <select id="report-type" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm">
                            <option value="cases">Pratiche</option>
                            <option value="clients">Clienti</option>
                            <option value="appointments">Appuntamenti</option>
                            <option value="financial">Finanziario</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Formato *</label>
                        <select id="report-format" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Periodo</label>
                        <select id="report-period" class="w-full rounded-xl border-gray-200 bg-gray-50 text-sm">
                            <option value="month">Ultimo Mese</option>
                            <option value="quarter">Ultimo Trimestre</option>
                            <option value="year">Ultimo Anno</option>
                            <option value="all">Tutto</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('modal-generate-report')" class="flex-1 py-3 rounded-xl border border-gray-200 font-bold text-gray-600 hover:bg-gray-50">Annulla</button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-primary text-white font-bold hover:bg-primary-dark">Genera</button>
                </div>
            </form>
        </div>
    `;
    document.body.appendChild(modal);
}

/**
 * Invia richiesta generazione report
 */
async function submitGenerateReport() {
    const data = {
        type: document.getElementById('report-type').value,
        format: document.getElementById('report-format').value,
        period: document.getElementById('report-period').value
    };

    try {
        showLoader('Generazione report in corso...');
        const response = await fetch('api/reports/generate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        hideLoader();

        if (result.success && result.file_url) {
            showToast('Report generato!', 'success');
            closeModal('modal-generate-report');
            // Download automatico
            window.open(result.file_url, '_blank');
        } else {
            showToast(result.message || 'Errore durante la generazione', 'error');
        }
    } catch (error) {
        hideLoader();
        console.error('Errore:', error);
        showToast('Errore di connessione', 'error');
    }
}

/**
 * Filtra dashboard
 */
async function filterDashboard() {
    const role = document.getElementById('dash-filter-role')?.value || '';
    const status = document.getElementById('dash-filter-status')?.value || '';
    const date = document.getElementById('dash-filter-date')?.value || '';
    const search = document.getElementById('dash-search')?.value || '';

    const params = new URLSearchParams();
    if (role && role !== '') params.append('role', role);
    if (status && status !== '') params.append('status', status);
    if (date && date !== '') params.append('date', date);
    if (search) params.append('search', search);

    try {
        showLoader('Ricerca in corso...');
        const response = await fetch(`api/cases/search.php?${params.toString()}`);
        const result = await response.json();
        hideLoader();

        if (result.success) {
            renderCasesTable(result.cases);
            showToast(`Trovati ${result.cases.length} risultati`, 'success');
        }
    } catch (error) {
        hideLoader();
        console.error('Errore:', error);
        showToast('Errore durante la ricerca', 'error');
    }
}

/**
 * Carica più pratiche (paginazione)
 */
let currentOffset = 0;
async function loadMoreCases() {
    currentOffset += 10;

    try {
        const response = await fetch(`api/cases/list.php?offset=${currentOffset}&limit=10`);
        const result = await response.json();

        if (result.success && result.cases.length > 0) {
            appendCasesToTable(result.cases);
            if (result.cases.length < 10) {
                document.getElementById('btn-load-more').style.display = 'none';
            }
        } else {
            document.getElementById('btn-load-more').style.display = 'none';
            showToast('Non ci sono altre pratiche', 'info');
        }
    } catch (error) {
        console.error('Errore:', error);
        showToast('Errore durante il caricamento', 'error');
    }
}

/**
 * Carica statistiche dashboard
 */
async function loadDashboardStats() {
    try {
        const response = await fetch('api/stats/dashboard.php');
        const result = await response.json();

        if (result.success) {
            // Anima contatori
            animateCounter('stat-active-cases', result.stats.active_cases || 0);
            animateCounter('stat-appointments', result.stats.appointments_today || 0);
            animateCounter('stat-messages', result.stats.new_messages || 0);
            animateCounter('stat-pending', result.stats.pending || 0);
        }
    } catch (error) {
        console.error('Errore caricamento stats:', error);
    }
}

/**
 * Anima contatore numerico
 */
function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;

    let current = 0;
    const increment = targetValue / 30;
    const timer = setInterval(() => {
        current += increment;
        if (current >= targetValue) {
            element.textContent = targetValue;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 30);
}

/**
 * Carica stato sistema
 */
async function loadSystemStats() {
    try {
        const response = await fetch('api/system/status.php');
        const result = await response.json();

        if (result.success) {
            const diskBar = document.getElementById('sys-disk-bar');
            const diskVal = document.getElementById('sys-disk-val');
            const loadBar = document.getElementById('sys-load-bar');
            const loadVal = document.getElementById('sys-load-val');

            if (diskBar && diskVal) {
                diskBar.style.width = result.disk_usage + '%';
                diskVal.textContent = result.disk_usage + '%';
            }

            if (loadBar && loadVal) {
                loadBar.style.width = result.server_load + '%';
                loadVal.textContent = result.server_load + '%';
            }
        }
    } catch (error) {
        console.error('Errore caricamento system stats:', error);
    }
}

/**
 * Carica log attività
 */
async function loadActivityLog() {
    try {
        const response = await fetch('api/activity/recent.php?limit=20');
        const result = await response.json();

        if (result.success) {
            renderActivityLog(result.activities);
        }
    } catch (error) {
        console.error('Errore caricamento activity log:', error);
    }
}

/**
 * Renderizza log attività
 */
function renderActivityLog(activities) {
    const container = document.getElementById('activity-log');
    if (!container) return;

    if (activities.length === 0) {
        container.innerHTML = '<div class="p-6 text-center text-gray-500 text-sm">Nessuna attività recente</div>';
        return;
    }

    container.innerHTML = activities.map(activity => `
        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                    <span class="material-icons-outlined text-primary text-sm">${activity.icon || 'info'}</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-800 font-medium">${activity.description}</p>
                    <p class="text-xs text-gray-400 mt-1">${formatDateTime(activity.created_at)}</p>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Visualizza dettaglio pratica
 */
async function viewCase(caseId) {
    try {
        showLoader('Caricamento pratica...');
        const response = await fetch(`api/cases/${caseId}.php`);
        const result = await response.json();
        hideLoader();

        if (result.success) {
            showCaseDetailModal(result.case);
        } else {
            showToast('Pratica non trovata', 'error');
        }
    } catch (error) {
        hideLoader();
        console.error('Errore:', error);
        showToast('Errore durante il caricamento', 'error');
    }
}

/**
 * Mostra modal dettaglio pratica
 */
function showCaseDetailModal(caseData) {
    const modal = document.createElement('div');
    modal.id = 'modal-case-detail';
    modal.className = 'fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center overflow-y-auto';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl w-full max-w-2xl p-6 shadow-2xl m-4">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-bold text-xl text-gray-800">Dettaglio Pratica #${caseData.id}</h3>
                <button onclick="closeModal('modal-case-detail')" class="text-gray-400 hover:text-gray-600">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Cliente</label>
                        <p class="text-sm text-gray-800 font-medium">${caseData.client_name || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Tipologia</label>
                        <p class="text-sm text-gray-800 font-medium">${caseData.type || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Stato</label>
                        <p class="text-sm text-gray-800 font-medium">${caseData.status || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase">Data Apertura</label>
                        <p class="text-sm text-gray-800 font-medium">${formatDate(caseData.created_at)}</p>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-500 uppercase">Descrizione</label>
                    <p class="text-sm text-gray-700 mt-1">${caseData.description || 'Nessuna descrizione'}</p>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="editCase(${caseData.id})" class="flex-1 py-3 rounded-xl bg-primary text-white font-bold hover:bg-primary-dark">Modifica</button>
                <button onclick="closeModal('modal-case-detail')" class="flex-1 py-3 rounded-xl border border-gray-200 font-bold text-gray-600 hover:bg-gray-50">Chiudi</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

/**
 * Modifica pratica
 */
function editCase(caseId) {
    closeModal('modal-case-detail');
    showToast('Funzione modifica in sviluppo', 'info');
    // TODO: Implementare modal di modifica
}

/**
 * Elimina pratica
 */
async function deleteCase(caseId) {
    if (!confirm('Sei sicuro di voler eliminare questa pratica?')) return;

    try {
        showLoader('Eliminazione in corso...');
        const response = await fetch(`api/cases/${caseId}.php`, {
            method: 'DELETE'
        });

        const result = await response.json();
        hideLoader();

        if (result.success) {
            showToast('Pratica eliminata', 'success');
            if (typeof loadDashboardData === 'function') loadDashboardData();
        } else {
            showToast(result.message || 'Errore durante l\'eliminazione', 'error');
        }
    } catch (error) {
        hideLoader();
        console.error('Errore:', error);
        showToast('Errore di connessione', 'error');
    }
}

/**
 * Toggle sidebar mobile
 */
function toggleMobileSidebar() {
    const sidebar = document.querySelector('aside');
    const overlay = document.getElementById('mobile-overlay');

    if (!overlay) {
        const newOverlay = document.createElement('div');
        newOverlay.id = 'mobile-overlay';
        newOverlay.className = 'fixed inset-0 bg-black/50 z-40 md:hidden';
        newOverlay.onclick = toggleMobileSidebar;
        document.body.appendChild(newOverlay);
    }

    if (sidebar) {
        sidebar.classList.toggle('hidden');
        sidebar.classList.toggle('flex');
        sidebar.classList.toggle('fixed');
        sidebar.classList.toggle('z-50');
    }

    if (overlay) {
        overlay.remove();
    }
}

/**
 * Renderizza tabella pratiche
 */
function renderCasesTable(cases) {
    const tbody = document.getElementById('leads-table-body');
    if (!tbody) return;

    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Nessuna pratica trovata</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(c => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.client_name || 'User')}&size=32" class="w-8 h-8 rounded-full">
                    <span class="font-medium text-gray-800">${c.client_name || 'N/A'}</span>
                </div>
            </td>
            <td class="px-6 py-4 text-gray-600">${c.type || 'N/A'}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 rounded text-xs font-bold ${getStatusColor(c.status)}">${c.status || 'N/A'}</span>
            </td>
            <td class="px-6 py-4 text-gray-600">${formatDate(c.created_at)}</td>
            <td class="px-6 py-4 text-right">
                <button onclick="viewCase(${c.id})" class="text-primary hover:text-primary-dark font-medium text-sm">Visualizza</button>
            </td>
        </tr>
    `).join('');
}

/**
 * Aggiungi pratiche alla tabella (paginazione)
 */
function appendCasesToTable(cases) {
    const tbody = document.getElementById('leads-table-body');
    if (!tbody) return;

    const rows = cases.map(c => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(c.client_name || 'User')}&size=32" class="w-8 h-8 rounded-full">
                    <span class="font-medium text-gray-800">${c.client_name || 'N/A'}</span>
                </div>
            </td>
            <td class="px-6 py-4 text-gray-600">${c.type || 'N/A'}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 rounded text-xs font-bold ${getStatusColor(c.status)}">${c.status || 'N/A'}</span>
            </td>
            <td class="px-6 py-4 text-gray-600">${formatDate(c.created_at)}</td>
            <td class="px-6 py-4 text-right">
                <button onclick="viewCase(${c.id})" class="text-primary hover:text-primary-dark font-medium text-sm">Visualizza</button>
            </td>
        </tr>
    `).join('');

    tbody.insertAdjacentHTML('beforeend', rows);
}

/**
 * Ottieni colore stato
 */
function getStatusColor(status) {
    const colors = {
        'Nuovo': 'bg-blue-100 text-blue-700',
        'In Corso': 'bg-yellow-100 text-yellow-700',
        'In Attesa': 'bg-orange-100 text-orange-700',
        'Chiuso': 'bg-green-100 text-green-700'
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
}

// ==================== CLIENT DASHBOARD FUNCTIONS ====================

/**
 * Invia messaggio (apre vista messaggi)
 */
function sendMessage() {
    switchView('messages');
    setTimeout(() => {
        const input = document.getElementById('chat-input');
        if (input) input.focus();
    }, 100);
}

/**
 * Upload file
 */
async function uploadFile() {
    const input = document.getElementById('doc-upload-input');
    const file = input.files[0];

    if (!file) return;

    // Validazione tipo file
    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        showToast('Tipo file non supportato. Usa PDF, DOC, DOCX, JPG o PNG', 'error');
        return;
    }

    // Validazione dimensione (max 10MB)
    if (file.size > 10 * 1024 * 1024) {
        showToast('File troppo grande. Massimo 10MB', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('client_id', currentUser.id);

    try {
        showLoader('Upload in corso...');
        const response = await fetch('api/documents/upload.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoader();

        if (result.success) {
            showToast('Documento caricato con successo!', 'success');
            input.value = '';
            // Ricarica lista documenti
            if (currentUser) loadClientDocuments(currentUser.id);
        } else {
            showToast(result.message || 'Errore durante l\'upload', 'error');
        }
    } catch (error) {
        hideLoader();
        console.error('Errore:', error);
        showToast('Errore di connessione', 'error');
    }
}

/**
 * Aggiorna profilo cliente
 */
async function submitProfileUpdate() {
    const data = {
        first_name: document.getElementById('set-name').value,
        last_name: document.getElementById('set-surname').value,
        password: document.getElementById('set-password').value
    };

    // Rimuovi password se vuota
    if (!data.password) delete data.password;

    try {
        showLoader('Salvataggio...');
        const response = await fetch(`api/clients/${currentUser.id}/profile.php`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        hideLoader();

        if (result.success) {
            showToast('Profilo aggiornato!', 'success');
            // Aggiorna sessione
            if (result.user) {
                AuthService.saveSession(result.user);
                currentUser = result.user;
                updateProfileUI(currentUser);
            }
        } else {
            showToast(result.message || 'Errore durante l\'aggiornamento', 'error');
        }
    } catch (error) {
        hideLoader();
        console.error('Errore:', error);
        showToast('Errore di connessione', 'error');
    }
}

/**
 * Invia messaggio chat
 */
async function sendChatMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();

    if (!message) return;

    const data = {
        sender_id: currentUser.id,
        message: message
    };

    try {
        const response = await fetch('api/messages/send.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            input.value = '';
            // Aggiungi messaggio alla UI
            addMessageToUI(message, 'sent');
            // Emetti evento socket se disponibile
            if (typeof socket !== 'undefined' && socket) {
                socket.emit('new_message', {
                    sender_id: currentUser.id,
                    message: message
                });
            }
        } else {
            showToast(result.message || 'Errore durante l\'invio', 'error');
        }
    } catch (error) {
        console.error('Errore:', error);
        showToast('Errore di connessione', 'error');
    }
}

/**
 * Aggiungi messaggio alla UI
 */
function addMessageToUI(message, type = 'sent') {
    const container = document.getElementById('messages-container');
    if (!container) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${type === 'sent' ? 'justify-end' : 'justify-start'}`;

    const now = new Date();
    const time = now.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });

    messageDiv.innerHTML = `
        <div class="${type === 'sent' ? 'bg-primary text-white' : 'bg-white border border-gray-100'} p-3 rounded-2xl ${type === 'sent' ? 'rounded-tr-none' : 'rounded-tl-none'} shadow-sm max-w-[80%]">
            <p class="text-sm">${message}</p>
            <span class="text-[10px] ${type === 'sent' ? 'text-white/70' : 'text-gray-400'} mt-1 block">${type === 'sent' ? 'Tu' : 'Admin'} • ${time}</span>
        </div>
    `;

    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
}

/**
 * Carica documenti cliente
 */
async function loadClientDocuments(userId) {
    try {
        const response = await fetch(`api/clients/${userId}/documents.php`);
        const result = await response.json();

        if (result.success) {
            renderClientDocuments(result.documents);
        }
    } catch (error) {
        console.error('Errore caricamento documenti:', error);
    }
}

/**
 * Renderizza documenti cliente
 */
function renderClientDocuments(documents) {
    const container = document.getElementById('docs-list');
    if (!container) return;

    if (documents.length === 0) {
        container.innerHTML = '<div class="col-span-2 text-center text-gray-400 text-xs py-4">Nessun documento</div>';
        return;
    }

    container.innerHTML = documents.slice(0, 4).map(doc => {
        const icon = getFileIcon(doc.file_type);
        return `
            <a href="api/documents/${doc.id}/download.php" target="_blank" class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                <span class="material-symbols-outlined text-primary">${icon}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-gray-800 truncate">${doc.filename}</p>
                    <p class="text-[10px] text-gray-400">${formatDate(doc.uploaded_at)}</p>
                </div>
            </a>
        `;
    }).join('');
}

/**
 * Ottieni icona file
 */
function getFileIcon(fileType) {
    if (fileType.includes('pdf')) return 'picture_as_pdf';
    if (fileType.includes('word') || fileType.includes('document')) return 'description';
    if (fileType.includes('image')) return 'image';
    return 'insert_drive_file';
}

/**
 * Carica attività cliente
 */
async function loadClientActivity(userId) {
    try {
        const response = await fetch(`api/clients/${userId}/activity.php`);
        const result = await response.json();

        if (result.success) {
            renderClientActivity(result.activities);
        }
    } catch (error) {
        console.error('Errore caricamento attività:', error);
    }
}

/**
 * Renderizza attività cliente
 */
function renderClientActivity(activities) {
    const container = document.getElementById('client-activity-history');
    if (!container) return;

    if (activities.length === 0) {
        container.innerHTML = '<p class="text-gray-400 text-sm text-center py-4">Nessuna attività recente</p>';
        return;
    }

    container.innerHTML = activities.map(activity => `
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary text-sm">${activity.icon || 'info'}</span>
            </div>
            <div class="flex-1">
                <p class="text-sm text-gray-800">${activity.description}</p>
                <p class="text-xs text-gray-400 mt-1">${formatDateTime(activity.created_at)}</p>
            </div>
        </div>
    `).join('');
}

// ==================== MODAL UTILITIES ====================

/**
 * Chiudi modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.remove();
    }
}

/**
 * Carica impostazioni nel form
 */
function loadSettings(user) {
    const nameInput = document.getElementById('set-name');
    const surnameInput = document.getElementById('set-surname');
    const emailInput = document.getElementById('set-email');

    if (nameInput) nameInput.value = user.first_name || '';
    if (surnameInput) surnameInput.value = user.last_name || '';
    if (emailInput) emailInput.value = user.email || '';
}

// ==================== INITIALIZATION ====================

// Carica dati iniziali quando il DOM è pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    initDashboard();
}

function initDashboard() {
    // Determina quale dashboard siamo
    const isAdmin = window.location.pathname.includes('admin');

    if (isAdmin) {
        // Carica dati admin dashboard
        loadDashboardStats();
        loadSystemStats();
        loadActivityLog();

        // Aggiorna stats ogni 30 secondi
        setInterval(() => {
            loadSystemStats();
        }, 30000);
    }

    // Aggiungi listener per chiusura modal con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('[id^="modal-"]');
            modals.forEach(modal => modal.remove());
        }
    });
}

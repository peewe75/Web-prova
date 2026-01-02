// ============================================
// FIX DASHBOARD CLIENTE - MESSAGGI, APPUNTAMENTI, CALENDARIO, IMPOSTAZIONI
// ============================================

// ==================== FIX 1: MESSAGGI ====================
async function loadMessages() {
  try {
    const clientId = localStorage.getItem('client_id') || localStorage.getItem('userId');
    if (!clientId) {
      console.error('Client ID not found');
      document.querySelector('[data-section="messaggi"]').innerHTML = '<p>Errore: Client ID mancante</p>';
      return;
    }
    
    const response = await fetch(`/api/messages.php?client_id=${clientId}`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });
    
    if (!response.ok) {
      console.error(`API error: ${response.status}`);
      return;
    }
    
    const messages = await response.json();
    const container = document.querySelector('[data-section="messaggi"]');
    
    if (!container) return;
    
    if (!messages || messages.length === 0) {
      container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Nessun messaggio ancora</div>';
      return;
    }
    
    container.innerHTML = messages.map(msg => `
      <div style="padding:15px; border-bottom:1px solid #eee; ${msg.from === 'admin' ? 'background:#f0f8ff;' : ''}">
        <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
          <strong>${msg.from === 'admin' ? '📞 Studio Legale' : '👤 Tu'}</strong>
          <small style="color:#999;">${new Date(msg.timestamp).toLocaleString('it-IT')}</small>
        </div>
        <div style="color:#333;">${msg.content}</div>
      </div>
    `).join('');
    
  } catch (error) {
    console.error('Error loading messages:', error);
  }
}

// ==================== FIX 2: APPUNTAMENTI ====================
async function submitAppointment(e) {
  if (e) e.preventDefault();
  
  try {
    const clientId = localStorage.getItem('client_id') || localStorage.getItem('userId');
    const data = {
      client_id: clientId,
      data: document.getElementById('req-date')?.value || document.querySelector('[name="data"]')?.value,
      ora: document.getElementById('req-time')?.value || document.querySelector('[name="ora"]')?.value,
      tipo: document.getElementById('req-type')?.value || document.querySelector('[name="tipo"]')?.value || 'Videocall Conoscitiva',
      note: document.getElementById('req-notes')?.value || document.querySelector('[name="note"]')?.value || ''
    };
    
    if (!data.data || !data.ora) {
      showToast('⚠️ Per favore compila DATA e ORA', 'error');
      return;
    }
    
    const response = await fetch('/api/appointments.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    
    const result = await response.json();
    
    if (result.success) {
      showToast('✅ Appuntamento richiesto con successo!', 'success');
      
      // Chiudi modal
      const modal = document.querySelector('[role="dialog"]');
      if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('active', 'show');
      }
      
      // Reset form
      document.getElementById('req-date').value = '';
      document.getElementById('req-time').value = '';
      document.getElementById('req-notes').value = '';
    }
    
  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Errore: ' + error.message, 'error');
  }
}

// ==================== FIX 3: CALENDARIO ====================
async function loadCalendar() {
  try {
    const clientId = localStorage.getItem('client_id') || localStorage.getItem('userId');
    
    const response = await fetch(`/api/calendar.php?client_id=${clientId}`, {
      method: 'GET'
    });
    
    if (!response.ok) throw new Error(`API error: ${response.status}`);
    
    const appointments = await response.json();
    const container = document.querySelector('[data-section="calendario"]');
    
    if (!container) return;
    
    if (!appointments || appointments.length === 0) {
      container.innerHTML = '<div style="padding:20px; text-align:center; color:#999;">Nessun appuntamento</div>';
      return;
    }
    
    container.innerHTML = appointments.map(app => `
      <div style="padding:15px; border-left:4px solid #00bcd4; margin-bottom:10px; background:#f9f9f9; border-radius:4px;">
        <div style="font-weight:bold; color:#00bcd4; margin-bottom:5px;">📅 ${app.data} alle ${app.ora}</div>
        <div style="margin-bottom:5px;"><strong>${app.tipo}</strong></div>
        <div style="color:#666; font-size:0.9em;">${app.note || ''}</div>
        <div style="margin-top:5px; color:#999; font-size:0.85em;">Stato: <strong>${app.status}</strong></div>
      </div>
    `).join('');
    
  } catch (error) {
    console.error('Error loading calendar:', error);
  }
}

// ==================== FIX 4: IMPOSTAZIONI ====================
async function loadSettings() {
  try {
    const clientId = localStorage.getItem('client_id') || localStorage.getItem('userId');
    
    const response = await fetch(`/api/settings.php?client_id=${clientId}`, {
      method: 'GET'
    });
    
    if (!response.ok) throw new Error(`API error: ${response.status}`);
    
    const settings = await response.json();
    const container = document.querySelector('[data-section="impostazioni"]');
    
    if (!container) return;
    
    container.innerHTML = `
      <form id="settings-form" style="max-width:600px;">
        <div style="margin-bottom:20px;">
          <label style="display:block; margin-bottom:5px; font-weight:bold;">Nome</label>
          <input type="text" name="nome" value="${settings.nome || ''}" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
        </div>
        
        <div style="margin-bottom:20px;">
          <label style="display:block; margin-bottom:5px; font-weight:bold;">Email</label>
          <input type="email" name="email" value="${settings.email || ''}" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
        </div>
        
        <div style="margin-bottom:20px;">
          <label style="display:block; margin-bottom:5px; font-weight:bold;">Telefono</label>
          <input type="tel" name="telefono" value="${settings.telefono || ''}" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
        </div>
        
        <div style="margin-bottom:20px;">
          <label style="display:block; margin-bottom:5px; font-weight:bold;">Città</label>
          <input type="text" name="citta" value="${settings.citta || ''}" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
        </div>
        
        <button type="submit" style="padding:10px 20px; background:#00bcd4; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:bold;">💾 Salva Impostazioni</button>
      </form>
    `;
    
    document.getElementById('settings-form').addEventListener('submit', saveSettings);
    
  } catch (error) {
    console.error('Error loading settings:', error);
  }
}

async function saveSettings(e) {
  e.preventDefault();
  
  try {
    const formData = new FormData(document.getElementById('settings-form'));
    const data = Object.fromEntries(formData);
    data.client_id = localStorage.getItem('client_id') || localStorage.getItem('userId');
    
    const response = await fetch('/api/settings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    
    if (!response.ok) throw new Error(`HTTP ${response.status}`);
    
    const result = await response.json();
    
    if (result.success) {
      showToast('✅ Impostazioni salvate!', 'success');
    }
    
  } catch (error) {
    console.error('Error:', error);
    showToast('❌ Errore nel salvataggio', 'error');
  }
}

// ==================== UTILITY ====================
function showToast(message, type = 'info') {
  const toast = document.createElement('div');
  const bgColor = type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3';
  
  toast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    background: ${bgColor};
    color: white;
    border-radius: 4px;
    z-index: 10000;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    animation: slideIn 0.3s ease-out;
  `;
  toast.textContent = message;
  
  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}

// ==================== INIT ====================
document.addEventListener('DOMContentLoaded', () => {
  // Messaggi
  const messaggiLink = document.querySelector('a[href*="#messaggi"], [data-tab="messaggi"], [data-section="messaggi"]');
  if (messaggiLink) {
    messaggiLink.addEventListener('click', loadMessages);
    // Load on page load if messaggi is active
    if (messaggiLink.classList.contains('active') || window.location.hash.includes('messaggi')) {
      loadMessages();
    }
  }
  
  // Appuntamenti
  const submitBtn = document.getElementById('invia-richiesta-btn') || 
                   document.querySelector('button[type="submit"][data-form="appuntamento"]');
  if (submitBtn) {
    submitBtn.addEventListener('click', submitAppointment);
  }
  
  // Calendario
  const calendarioLink = document.querySelector('a[href*="#calendario"], [data-tab="calendario"]');
  if (calendarioLink) {
    calendarioLink.addEventListener('click', loadCalendar);
  }
  
  // Impostazioni
  const impostLink = document.querySelector('a[href*="#impostazioni"], [data-tab="impostazioni"]');
  if (impostLink) {
    impostLink.addEventListener('click', loadSettings);
  }
  
  // Polling per messaggi ogni 5 secondi
  setInterval(() => {
    if (document.querySelector('[data-section="messaggi"]:visible') ||
        window.location.hash.includes('messaggi')) {
      loadMessages();
    }
  }, 5000);
});

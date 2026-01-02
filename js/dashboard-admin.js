// ============================================
// FIX DASHBOARD ADMIN - STATISTICHE
// ============================================

async function loadStatistics() {
  try {
    const response = await fetch('/api/statistics.php', {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' }
    });
    
    if (!response.ok) throw new Error(`API error: ${response.status}`);
    
    const stats = await response.json();
    
    // Mappa degli elementi e dei loro valori
    const updates = {
      'stat-active-cases': stats.pratiche_attive,
      'stat-appointments': stats.appuntamenti_oggi,
      'stat-messages': stats.nuovi_messaggi,
      'stat-pending': stats.in_attesa
    };
    
    // Aggiorna ogni elemento
    for (const [id, value] of Object.entries(updates)) {
      const element = document.getElementById(id) || 
                     document.querySelector(`[data-stat="${id}"]`);
      if (element) {
        element.textContent = value || '0';
        element.style.color = value > 0 ? '#00bcd4' : '#999';
      }
    }
    
    // System status
    const storageEl = document.querySelector('[data-stat="storage"]');
    if (storageEl) {
      storageEl.textContent = stats.storage_usage || '-';
    }
    
    const cpuEl = document.querySelector('[data-stat="cpu"]');
    if (cpuEl) {
      cpuEl.textContent = stats.server_load || '-';
    }
    
    console.log('✅ Statistics loaded:', stats);
    
  } catch (error) {
    console.error('❌ Error loading statistics:', error);
  }
}

// Carica statistiche al caricamento pagina
document.addEventListener('DOMContentLoaded', () => {
  loadStatistics();
  
  // Refresh ogni 30 secondi
  setInterval(loadStatistics, 30000);
});

# Guida Configurazione Webhook GitHub Auto-Deploy

## Panoramica
Questo sistema permette al sito Hostinger di aggiornarsi automaticamente quando fai un push su GitHub.

## Passo 1: Carica i File su Hostinger

1. Accedi al **File Manager** di Hostinger
2. Carica questi file nella root del sito:
   - `deploy.php`
   - `.htaccess`

## Passo 2: Configura il Token Segreto

1. Apri `deploy.php` su Hostinger
2. Alla riga 11, troverai:
   ```php
   define('SECRET_TOKEN', 'CAMBIA_QUESTO_TOKEN_CON_UNO_SICURO_...');
   ```
3. Genera un token sicuro (puoi usare questo):
   ```
   <?php echo bin2hex(random_bytes(32)); ?>
   ```
4. **COPIA E SALVA** questo token, ti servirà per GitHub!

## Passo 3: Verifica Permessi Git su Hostinger

1. Accedi via **SSH** a Hostinger (se disponibile)
2. Vai nella directory del sito:
   ```bash
   cd public_html
   ```
3. Verifica che sia un repository Git:
   ```bash
   git status
   ```
4. Se non è un repository, inizializzalo:
   ```bash
   git init
   git remote add origin https://github.com/peewe75/Web-prova.git
   git fetch origin
   git checkout -b main origin/main
   ```

## Passo 4: Configura il Webhook su GitHub

1. Vai su **GitHub.com** → Il tuo repository **peewe75/Web-prova**
2. Clicca su **Settings** (Impostazioni)
3. Nel menu laterale, clicca su **Webhooks**
4. Clicca su **Add webhook** (Aggiungi webhook)
5. Compila i campi:
   - **Payload URL**: `https://lavenderblush-giraffe-745354.hostingersite.com/deploy.php`
   - **Content type**: `application/json`
   - **Secret**: Incolla il token che hai salvato al Passo 2
   - **Which events**: Seleziona "Just the push event"
   - **Active**: ✓ Spunta questa casella
6. Clicca su **Add webhook**

## Passo 5: Test del Webhook

1. Fai una piccola modifica al repository locale
2. Esegui:
   ```bash
   git add .
   git commit -m "Test webhook auto-deploy"
   git push
   ```
3. Vai su GitHub → Settings → Webhooks
4. Clicca sul webhook appena creato
5. Scorri in basso fino a **Recent Deliveries**
6. Dovresti vedere una richiesta con stato **200** (successo)

## Passo 6: Verifica il Deploy

1. Accedi al File Manager di Hostinger
2. Controlla se esiste il file `deploy.log`
3. Aprilo per vedere i log del deploy
4. Dovresti vedere messaggi come:
   ```
   [2026-01-04 00:15:00] INFO: Received push event from GitHub
   [2026-01-04 00:15:01] SUCCESS: Git fetch completed
   [2026-01-04 00:15:02] SUCCESS: Git reset completed
   [2026-01-04 00:15:03] SUCCESS: Deploy completed successfully
   ```

## Risoluzione Problemi

### Errore 403 - Forbidden
- Verifica che il token segreto in `deploy.php` corrisponda a quello su GitHub

### Errore 500 - Internal Server Error
- Controlla i permessi del file `deploy.php` (dovrebbe essere 644)
- Verifica che Git sia installato su Hostinger
- Controlla il file `deploy.log` per dettagli

### Il sito non si aggiorna
- Verifica che il webhook sia attivo su GitHub
- Controlla i "Recent Deliveries" su GitHub per vedere se ci sono errori
- Verifica il file `deploy.log` su Hostinger

### Permessi Git
Se ricevi errori di permessi Git:
```bash
cd public_html
chmod -R 755 .git
```

## Note di Sicurezza

- **NON condividere** il token segreto
- Il file `.htaccess` protegge `deploy.log` da accessi esterni
- Cambia il token periodicamente per maggiore sicurezza

## Backup Manuale (se il webhook non funziona)

Se il webhook non funziona, puoi sempre fare il deploy manualmente via SSH:
```bash
cd public_html
git fetch origin main
git reset --hard origin/main
git clean -fd
```

## Prossimi Passi

Una volta configurato il webhook, ogni volta che fai `git push`:
1. GitHub invia una notifica a `deploy.php`
2. Lo script esegue automaticamente `git pull`
3. Il sito si aggiorna in pochi secondi!

---

**Hai bisogno di aiuto?** Controlla sempre il file `deploy.log` per vedere cosa sta succedendo.

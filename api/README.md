# BCS API (pulito) — Hostinger shared (PHP + MySQL)

Questa cartella va caricata come document root del sottodominio `api` (es. `public_html/api/`).

## Requisiti
- PHP 8.1+ (consigliato)
- Estensioni: pdo_mysql, openssl, json
- MySQL (Hostinger)

## Setup rapido
1. Copia `config.sample.php` in `config.php` e inserisci le credenziali MySQL.
2. Crea le tabelle eseguendo `migrate.sql` (phpMyAdmin Hostinger).
3. (Opzionale) crea un admin con `seed_admin.php` (poi cancellalo o proteggilo).

## Endpoint pronti
- `GET  /health.php`
- `POST /login.php`  body JSON: { "email": "...", "password": "..." }
- `POST /logout.php` header: Authorization: Bearer <token>
- `GET  /me.php`     header: Authorization: Bearer <token>

## CORS
Sono ammessi gli origin:
- https://app.<dominio>
- https://admin.<dominio>

Il codice deriva automaticamente il dominio base dall'Origin.

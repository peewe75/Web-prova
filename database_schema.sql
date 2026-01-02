-- Tabella Utenti (Per Admin e Clienti)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client') DEFAULT 'client',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Tabella Richieste/Leads (Dal form del sito)
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(50),
    topic VARCHAR(100), -- Area di competenza (es. Penale, Tech)
    message TEXT,
    status ENUM('new', 'contacted', 'converted', 'archived') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabella Pratiche (Per la Dashboard Cliente)
CREATE TABLE IF NOT EXISTS cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT, -- Collegamento all'utente (cliente)
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'Aperta', -- Aperta, In Attesa, Chiusa
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Inserimento Utente Admin di Default (OPZIONALE)
-- Password: 'admin' (Da cambiare subito! Hash generato per 'admin')
INSERT INTO users (email, password_hash, role, first_name, last_name) 
VALUES ('info@studiodigitale.eu', '$2y$10$YourHashedPasswordHere', 'admin', 'Admin', 'Studio');

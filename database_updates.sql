-- Script SQL per aggiungere tabelle mancanti
-- Database: Studio Legale BCS

-- Tabella Cases (se non esiste già)
CREATE TABLE IF NOT EXISTS `cases` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `type` VARCHAR(100),
  `status` ENUM('Nuovo', 'In Corso', 'In Attesa', 'Chiuso') DEFAULT 'Nuovo',
  `description` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella Documents
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_id` INT NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(100),
  `file_size` INT,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `uploaded_by` INT,
  FOREIGN KEY (`client_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella Activity Log
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `action_type` VARCHAR(50) NOT NULL,
  `description` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indici per performance
CREATE INDEX idx_cases_client ON cases(client_id);
CREATE INDEX idx_cases_status ON cases(status);
CREATE INDEX idx_cases_created ON cases(created_at);
CREATE INDEX idx_documents_client ON documents(client_id);
CREATE INDEX idx_activity_user ON activity_log(user_id);
CREATE INDEX idx_activity_created ON activity_log(created_at);

-- Inserisci dati di esempio per testing
INSERT INTO cases (client_id, type, status, description, created_at) VALUES
((SELECT id FROM users WHERE role = 'client' LIMIT 1), 'Consulenza Legale', 'In Corso', 'Consulenza per pratica immobiliare', DATE_SUB(NOW(), INTERVAL 5 DAY)),
((SELECT id FROM users WHERE role = 'client' LIMIT 1), 'Contratto', 'Nuovo', 'Revisione contratto di lavoro', DATE_SUB(NOW(), INTERVAL 2 DAY)),
((SELECT id FROM users WHERE role = 'client' LIMIT 1), 'Contenzioso', 'In Attesa', 'Causa civile in attesa di udienza', DATE_SUB(NOW(), INTERVAL 10 DAY));

-- Log attività di esempio
INSERT INTO activity_log (user_id, action_type, description, created_at) VALUES
((SELECT id FROM users WHERE role = 'admin' LIMIT 1), 'login', 'Accesso al sistema', NOW()),
((SELECT id FROM users WHERE role = 'client' LIMIT 1), 'upload_document', 'Caricato documento: contratto.pdf', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
((SELECT id FROM users WHERE role = 'admin' LIMIT 1), 'create_case', 'Creata nuova pratica', DATE_SUB(NOW(), INTERVAL 2 HOUR));

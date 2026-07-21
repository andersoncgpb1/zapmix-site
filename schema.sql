-- schema.sql - Database schema for ZapMix

-- Tabela de Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    empresa VARCHAR(255),
    ativa TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ativa (ativa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Licenças
CREATE TABLE IF NOT EXISTS licencas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    chave_licenca VARCHAR(255) UNIQUE NOT NULL,
    tipo VARCHAR(50), -- 'trial', 'mensal', 'anual'
    data_inicio DATE NOT NULL,
    data_expiracao DATE NOT NULL,
    ativa TINYINT(1) DEFAULT 1,
    funcoes JSON, -- Funcionalidades habilitadas
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_ativa (ativa),
    INDEX idx_expiracao (data_expiracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Mensagens (do broadcast)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    name VARCHAR(255) NOT NULL,
    text LONGTEXT NOT NULL,
    media VARCHAR(500) NULL,
    approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_approved (approved),
    INDEX idx_cliente (cliente_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Enquetes
CREATE TABLE IF NOT EXISTS polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    question VARCHAR(500) NOT NULL,
    options JSON NOT NULL, -- ["Opção A", "Opção B", ...]
    results JSON NOT NULL, -- [votos_A, votos_B, ...]
    ativa TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_ativa (ativa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Sorteios
CREATE TABLE IF NOT EXISTS sorteios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    participantes JSON, -- {nome, email}[]
    vencedor_id INT,
    ativa TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_ativa (ativa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Configurações por Cliente
CREATE TABLE IF NOT EXISTS configuracoes_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT UNIQUE NOT NULL,
    whatsapp_webhook_url VARCHAR(500),
    overlay_cor_primaria VARCHAR(7) DEFAULT '#90d105',
    overlay_tamanho_fonte INT DEFAULT 24,
    logs_enabled TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Logs
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    tipo VARCHAR(50), -- 'login', 'acesso', 'erro', 'config'
    descricao TEXT,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_tipo (tipo),
    INDEX idx_data (data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir enquete padrão
INSERT IGNORE INTO polls (cliente_id, question, options, results) 
VALUES (NULL, 'Qual é sua opinião?', '["Opção A", "Opção B", "Opção C"]', '[0, 0, 0]');


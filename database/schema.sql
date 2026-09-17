-- Catálogo de Caminhões — Script de criação do banco de dados

CREATE DATABASE IF NOT EXISTS catalogo_caminhoes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE catalogo_caminhoes;

CREATE TABLE IF NOT EXISTS caminhoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    ano INT NOT NULL,
    tipo ENUM('trator','rigido','basculante','cegonha','tanque','bau','outro') NOT NULL,
    cor VARCHAR(50) NOT NULL,
    placa VARCHAR(7) NOT NULL UNIQUE,
    chassi VARCHAR(17) NOT NULL UNIQUE,
    quilometragem BIGINT NOT NULL DEFAULT 0,
    valor DECIMAL(12,2) NOT NULL,
    status ENUM('disponivel','vendido','reservado','em_negociacao') NOT NULL DEFAULT 'disponivel',
    observacoes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_marca (marca),
    INDEX idx_status (status),
    INDEX idx_placa (placa)
) ENGINE=InnoDB;

-- Registros de exemplo
INSERT INTO caminhoes
    (marca, modelo, ano, tipo, cor, placa, chassi, quilometragem, valor, status, observacoes)
VALUES
    ('Volvo', 'FH 540', 2023, 'trator', 'Branco', 'ABC1D23', '9BVHS30X0PC123456', 0, 650000.00, 'disponivel', 'Caminhão zero km, garantia de fábrica'),
    ('Scania', 'R 450', 2022, 'trator', 'Vermelho', 'DEF4G56', 'YS2R4X200N1234567', 45000, 520000.00, 'disponivel', 'Revisão em dia, pneus novos'),
    ('Mercedes-Benz', 'Actros 2651', 2021, 'trator', 'Prata', 'GHI7J89', '9BMZZZ907M1234567', 120000, 480000.00, 'vendido', 'Vendido para transportadora XYZ'),
    ('MAN', 'TGX 28.510', 2023, 'trator', 'Preto', 'JKL0M12', 'WMA06SZZ0PM123456', 0, 620000.00, 'reservado', 'Reservado para cliente até 30/09'),
    ('Iveco', 'S-Way 480', 2022, 'trator', 'Azul', 'NOP3Q45', 'ZCFC4540001234567', 30000, 550000.00, 'em_negociacao', 'Negociação avançada com frotista'),
    ('DAF', 'XF 530', 2021, 'trator', 'Branco', 'RST6U78', 'XLR4XF1000M123456', 85000, 495000.00, 'disponivel', 'Baixa quilometragem, único dono'),
    ('Volvo', 'VM 270', 2020, 'rigido', 'Vermelho', 'VWX9Y01', '9BVJS30X0LC123456', 180000, 280000.00, 'disponivel', 'Ideal para distribuição urbana'),
    ('Scania', 'P 320', 2019, 'rigido', 'Branco', 'ZAB2C34', 'YS2P4X200K1234567', 250000, 220000.00, 'disponivel', 'Caminhão de entrada, muito econômico');
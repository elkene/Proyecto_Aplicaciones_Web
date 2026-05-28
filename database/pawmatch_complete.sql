-- ==========================================
-- PawMatch Database Schema - Complete Version
-- MySQL Database for Pet Adoption Platform
-- ==========================================

-- Create Database
CREATE DATABASE IF NOT EXISTS pawmatch;
USE pawmatch;

-- Drop existing tables if they exist
DROP TABLE IF EXISTS animal_report_updates;
DROP TABLE IF EXISTS reported_animals;
DROP TABLE IF EXISTS donations;
DROP TABLE IF EXISTS adoption_requests;
DROP TABLE IF EXISTS pets;
DROP TABLE IF EXISTS users;

-- ==========================================
-- USERS TABLE
-- ==========================================
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255),
    phone VARCHAR(20),
    bio TEXT,
    avatar VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- ==========================================
-- PETS TABLE
-- ==========================================
CREATE TABLE pets (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    species ENUM('Perro', 'Gato', 'Conejo', 'Ave') NOT NULL,
    breed VARCHAR(255) NOT NULL,
    age VARCHAR(50) NOT NULL,
    size ENUM('Pequeño', 'Mediano', 'Grande') NOT NULL,
    energy ENUM('Bajo', 'Medio', 'Alto') NOT NULL,
    location VARCHAR(255) NOT NULL,
    image VARCHAR(255),
    images JSON,
    description TEXT,
    compatibility JSON,
    vaccinated BOOLEAN DEFAULT false,
    sterilized BOOLEAN DEFAULT false,
    microchip BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_species (species),
    INDEX idx_size (size),
    INDEX idx_energy (energy),
    FULLTEXT INDEX ft_search (name, breed, description)
);

-- ==========================================
-- ADOPTION REQUESTS TABLE
-- ==========================================
CREATE TABLE adoption_requests (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    pet_id VARCHAR(36) NOT NULL,
    message TEXT,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_pet_id (pet_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_adoption (user_id, pet_id)
);

-- ==========================================
-- DONATIONS TABLE
-- ==========================================
CREATE TABLE donations (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36),
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'MXN',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_date (created_at)
);

-- ==========================================
-- REPORTED ANIMALS TABLE
-- ==========================================
CREATE TABLE reported_animals (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    type VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    phone VARCHAR(20),
    image LONGTEXT,
    status ENUM('pending', 'in_rescue', 'rescued', 'unknown') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_location (latitude, longitude)
);

-- ==========================================
-- ANIMAL REPORT UPDATES TABLE
-- ==========================================
CREATE TABLE animal_report_updates (
    id VARCHAR(36) PRIMARY KEY,
    report_id VARCHAR(36) NOT NULL,
    user_id VARCHAR(36) NOT NULL,
    update_type ENUM('status_change', 'comment') DEFAULT 'comment',
    content TEXT NOT NULL,
    new_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reported_animals(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_report_id (report_id),
    INDEX idx_user_id (user_id)
);

-- ==========================================
-- INSERT SAMPLE DATA - USERS
-- ==========================================
INSERT INTO users VALUES
('550e8400-e29b-41d4-a716-446655440001', 'admin@pawmatch.com', '$2y$10$bVPS3Zd/FWtvkaH8E4OUYubqt2YxeE7wt5gUWWLsDC54I1MEtNmQm', 'Admin PawMatch', 'Tijuana, BC', '+52664123456', 'Administrator de PawMatch', NULL, 'admin', NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440002', 'juan@pawmatch.com', '$2y$10$bVPS3Zd/FWtvkaH8E4OUYubqt2YxeE7wt5gUWWLsDC54I1MEtNmQm', 'Juan García', 'Tijuana, BC', '+52664987654', 'Amante de los animales', NULL, 'user', NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440003', 'maria@pawmatch.com', '$2y$10$bVPS3Zd/FWtvkaH8E4OUYubqt2YxeE7wt5gUWWLsDC54I1MEtNmQm', 'María López', 'Playas de Rosarito, BC', '+52614556789', 'Voluntaria de rescate animal', NULL, 'user', NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440004', 'carlos@pawmatch.com', '$2y$10$bVPS3Zd/FWtvkaH8E4OUYubqt2YxeE7wt5gUWWLsDC54I1MEtNmQm', 'Carlos Rodríguez', 'Ensenada, BC', '+52616234567', 'Adoptante responsable', NULL, 'user', NOW(), NOW());

-- ==========================================
-- INSERT SAMPLE DATA - PETS
-- ==========================================
INSERT INTO pets VALUES
('550e8400-e29b-41d4-a716-446655440101', 'Luna', 'Perro', 'Golden Retriever', '2 años', 'Grande', 'Medio', 'Tijuana, Baja California', 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=600&h=450&fit=crop', '["https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=600&h=450&fit=crop"]', 'Luna es una perrita muy cariñosa y juguetona que adora los paseos largos. Perfecta para familias activas.', '["Familias con niños","Hogares activos","Jardín amplio"]', true, true, true, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440102', 'Max', 'Gato', 'Siamés', '3 años', 'Mediano', 'Medio', 'Playas de Rosarito, BC', 'https://images.unsplash.com/photo-1513360371669-4adf3dd7dff8?w=600&h=450&fit=crop', '["https://images.unsplash.com/photo-1513360371669-4adf3dd7dff8?w=600&h=450&fit=crop"]', 'Max es un gato elegante y conversador. Le encanta estar cerca de sus humanos y es muy curioso.', '["Hogares tranquilos","Adultos solteros","Parejas"]', true, true, true, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440103', 'Bella', 'Perro', 'Labrador', '1 año', 'Grande', 'Alto', 'Ensenada, BC', 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=600&h=450&fit=crop', '["https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=600&h=450&fit=crop"]', 'Bella es una cachorra llena de energía. Necesita una familia que pueda seguirle el ritmo.', '["Familias activas","Hogares con jardín","Personas deportistas"]', true, false, true, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440104', 'Milo', 'Gato', 'Persa', '4 años', 'Mediano', 'Bajo', 'Tijuana, BC', 'https://premierpet.com.br/es/wp-content/uploads/sites/4/2025/08/model-banner-persa-mobile-v1.png', '["https://images.unsplash.com/photo-1506440773649-6e0eb8cfb237?w=600&h=450&fit=crop"]', 'Milo es un gato tranquilo y elegante que disfruta tomar siestas. Perfecto para hogares sin niños pequeños.', '["Hogares tranquilos","Adultos mayores","Personas con paciencia"]', true, true, true, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440105', 'Rocky', 'Perro', 'Pastor Alemán', '3 años', 'Grande', 'Medio', 'Mexicali, BC', 'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=600&h=450&fit=crop', '["https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=600&h=450&fit=crop"]', 'Rocky es un perro inteligente y leal. Excelente guardián y compañero para dueños experimentados.', '["Dueños experimentados","Hogares con espacio","Personas activas"]', true, true, true, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440106', 'Nieve', 'Conejo', 'Holandés', '1 año', 'Pequeño', 'Bajo', 'Tecate, BC', 'https://i.pinimg.com/474x/d9/fe/d0/d9fed07ad1c9dd2a02c8b5655df61c68.jpg', '["https://images.unsplash.com/photo-1585110396000-c9ffd4d4b3f1?w=600&h=450&fit=crop"]', 'Nieve es un conejo dulce y sociable. Requiere espacio para saltar y jugar.', '["Niños responsables","Hogares tranquilos"]', false, false, false, NOW(), NOW());

-- ==========================================
-- INSERT SAMPLE DATA - ADOPTION REQUESTS
-- ==========================================
INSERT INTO adoption_requests VALUES
('650e8400-e29b-41d4-a716-446655440201', '550e8400-e29b-41d4-a716-446655440002', '550e8400-e29b-41d4-a716-446655440101', 'Me encantaría adoptar a Luna', 'pending', NOW(), NOW()),
('650e8400-e29b-41d4-a716-446655440202', '550e8400-e29b-41d4-a716-446655440003', '550e8400-e29b-41d4-a716-446655440102', 'Max es perfecto para mi hogar', 'approved', NOW(), NOW()),
('650e8400-e29b-41d4-a716-446655440203', '550e8400-e29b-41d4-a716-446655440004', '550e8400-e29b-41d4-a716-446655440103', 'Quiero adoptar a Bella', 'pending', NOW(), NOW());

-- ==========================================
-- INSERT SAMPLE DATA - DONATIONS
-- ==========================================
INSERT INTO donations VALUES
('750e8400-e29b-41d4-a716-446655440301', '550e8400-e29b-41d4-a716-446655440002', 500.00, 'MXN', 'Apoyo para alimento de animales', NOW()),
('750e8400-e29b-41d4-a716-446655440302', '550e8400-e29b-41d4-a716-446655440003', 1000.00, 'MXN', 'Para medicinas veterinarias', NOW()),
('750e8400-e29b-41d4-a716-446655440303', '550e8400-e29b-41d4-a716-446655440004', 250.00, 'MXN', 'Donación voluntaria', NOW());

-- ==========================================
-- INSERT SAMPLE DATA - REPORTED ANIMALS
-- ==========================================
INSERT INTO reported_animals VALUES
('850e8400-e29b-41d4-a716-446655440401', '550e8400-e29b-41d4-a716-446655440002', 'Perro', 'Perro pequeño café oscuro, collar rojo, parecía perdido en la zona del Centro', 32.5149, -116.9718, '+52664123456', NULL, 'pending', 'Visto hace 2 días en la zona', NOW(), NOW()),
('850e8400-e29b-41d4-a716-446655440402', '550e8400-e29b-41d4-a716-446655440003', 'Gato', 'Gato naranja/anaranjado, muy delgado, se acercaba a los patios', 32.5200, -116.9600, '+52614556789', NULL, 'in_rescue', 'En proceso de captura responsable', NOW(), NOW()),
('850e8400-e29b-41d4-a716-446655440403', '550e8400-e29b-41d4-a716-446655440004', 'Perro', 'Perro grande blanco y negro, se vio desorientado en la carretera', 32.5250, -116.9800, '+52616234567', NULL, 'rescued', 'Rescatado y en proceso de adopción', NOW(), NOW());

-- ==========================================
-- INSERT SAMPLE DATA - REPORT UPDATES
-- ==========================================
INSERT INTO animal_report_updates VALUES
('950e8400-e29b-41d4-a716-446655440501', '850e8400-e29b-41d4-a716-446655440401', '550e8400-e29b-41d4-a716-446655440001', 'status_change', 'Se cambió el estado a revisión', 'pending', NOW()),
('950e8400-e29b-41d4-a716-446655440502', '850e8400-e29b-41d4-a716-446655440402', '550e8400-e29b-41d4-a716-446655440003', 'comment', 'Se vio al gato en la mañana, intentaremos capturarlo hoy', NULL, NOW()),
('950e8400-e29b-41d4-a716-446655440503', '850e8400-e29b-41d4-a716-446655440403', '550e8400-e29b-41d4-a716-446655440001', 'status_change', 'Rescate completado exitosamente', 'rescued', NOW());

-- ==========================================
-- TEST CREDENTIALS
-- ==========================================
-- Email: admin@pawmatch.com
-- Password: Admin@123
--
-- Email: juan@pawmatch.com
-- Password: Admin@123
--
-- Email: maria@pawmatch.com
-- Password: Admin@123
--
-- Email: carlos@pawmatch.com
-- Password: Admin@123

-- ==========================================
-- DATABASE SETUP COMPLETE
-- ==========================================

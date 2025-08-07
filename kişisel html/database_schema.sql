-- PostgreSQL Veritabanı Şeması
-- Proje Paylaşım Platformu

-- Tabloları oluşturmadan önce mevcut olanları siler (isteğe bağlı)
DROP TABLE IF EXISTS project_categories;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Kullanıcılar tablosu
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, -- Şifreleri her zaman hash'leyerek saklayın
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Kategoriler tablosu
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL -- URL dostu kimlik
);

-- Projeler tablosu
CREATE TABLE projects (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL, -- URL için benzersiz kimlik
    status VARCHAR(20) NOT NULL DEFAULT 'active', -- Örn: active, pending, deleted
    views INTEGER DEFAULT 0,
    github_url VARCHAR(255),
    live_url VARCHAR(255),
    image_url VARCHAR(255), -- Proje için resim yolu
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Proje ve Kategorileri birleştiren tablo (Çoka-çok ilişki)
CREATE TABLE project_categories (
    project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    category_id INTEGER NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
    PRIMARY KEY (project_id, category_id)
);

-- İndeksler, sorgu performansını artırmak için eklenir
CREATE INDEX idx_projects_user_id ON projects(user_id);
CREATE INDEX idx_projects_slug ON projects(slug);
CREATE INDEX idx_categories_slug ON categories(slug);

-- Örnek Veriler (Uygulamayı test etmek için)

-- Örnek kullanıcı
-- Not: 'password123' yerine gerçek bir hash kullanmalısınız.
-- PHP'de password_hash('password123', PASSWORD_BCRYPT) ile oluşturabilirsiniz.
INSERT INTO users (username, email, password_hash) VALUES
('samet', 'samet@example.com', '$2y$10$...hash_buraya_gelecek...');

-- Örnek kategoriler
INSERT INTO categories (name, slug) VALUES
('Web Geliştirme', 'web-gelistirme'),
('Mobil Uygulama', 'mobil-uygulama'),
('Veri Bilimi', 'veri-bilimi'),
('Oyun Geliştirme', 'oyun-gelistirme');

-- Örnek proje (Kullanıcı ID'sinin (1) mevcut olduğunu varsayar)
INSERT INTO projects (user_id, title, description, slug, github_url)
VALUES (1, 'Kişisel Portfolyo Sitesi', 'HTML, CSS ve PHP kullanarak oluşturduğum kişisel portfolyo web sitem. Projelerimi ve yeteneklerimi sergiliyor.', 'kisisel-portfolyo-sitesi', 'https://github.com/kullanici/portfolyo');

-- Örnek proje için kategori ataması
INSERT INTO project_categories (project_id, category_id) VALUES
(1, 1); -- Portfolyo projesini 'Web Geliştirme' kategorisine ekle

-- Bildirim: Şema ve örnek veriler başarıyla eklendi.
SELECT 'Veritabanı şeması ve örnek veriler başarıyla oluşturuldu.' as status;

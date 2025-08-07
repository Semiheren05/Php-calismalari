-- KodForum PostgreSQL Şeması

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    avatar VARCHAR(255),
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin', 'moderator')),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE codes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    code TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    views INTEGER DEFAULT 0
);

CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    code_id INTEGER REFERENCES codes(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE code_tags (
    code_id INTEGER REFERENCES codes(id) ON DELETE CASCADE,
    tag_id INTEGER REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (code_id, tag_id)
);

CREATE TABLE votes (
    id SERIAL PRIMARY KEY,
    code_id INTEGER REFERENCES codes(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    vote SMALLINT NOT NULL CHECK (vote IN (-1, 1)),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog tablosu
CREATE TABLE blog_posts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt TEXT,
    featured_image VARCHAR(255),
    status VARCHAR(20) DEFAULT 'published' CHECK (status IN ('draft', 'published', 'archived')),
    views INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog kategorileri
CREATE TABLE blog_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog post kategorileri
CREATE TABLE blog_post_categories (
    post_id INTEGER REFERENCES blog_posts(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES blog_categories(id) ON DELETE CASCADE,
    PRIMARY KEY (post_id, category_id)
);

-- Projeler tablosu
CREATE TABLE projects (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    content TEXT,
    featured_image VARCHAR(255),
    github_url VARCHAR(255),
    live_url VARCHAR(255),
    technologies TEXT[], -- PostgreSQL array
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'completed', 'archived')),
    views INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Proje kategorileri
CREATE TABLE project_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Proje kategorileri
CREATE TABLE project_categories_rel (
    project_id INTEGER REFERENCES projects(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES project_categories(id) ON DELETE CASCADE,
    PRIMARY KEY (project_id, category_id)
);

-- Site ayarları
CREATE TABLE site_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'text' CHECK (setting_type IN ('text', 'number', 'boolean', 'json')),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- İstatistikler tablosu
CREATE TABLE statistics (
    id SERIAL PRIMARY KEY,
    stat_key VARCHAR(100) NOT NULL,
    stat_value INTEGER DEFAULT 0,
    stat_date DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(stat_key, stat_date)
);

-- Admin kullanıcısı
INSERT INTO users (username, email, password, bio, role) VALUES
('admin', 'admin@kodforum.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KodForum yöneticisi', 'admin'),
('semih', 'semih@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KodForum kurucusu ve yazılım geliştirici.', 'admin');

-- Örnek kodlar
INSERT INTO codes (user_id, title, description, code) VALUES
(2, 'PHP ile Dizideki En Büyük Sayıyı Bulma', 'Bir dizideki en büyük sayıyı bulan basit bir PHP fonksiyonu.', E'function maxInArray($arr) {\n    return max($arr);\n}\n$nums = [1, 5, 9, 3];\necho maxInArray($nums); // 9'),
(2, 'JavaScript ile Ters Çevirme', 'Bir stringi ters çeviren JavaScript fonksiyonu.', E'function reverseString(str) {\n    return str.split(\'\').reverse().join(\'\');\n}\nconsole.log(reverseString(\'KodForum\')); // \'muroFdoK\''),
(2, 'Python: Fibonacci Dizisi', 'Fibonacci dizisini üreten Python fonksiyonu.', E'def fibonacci(n):\n    a, b = 0, 1\n    for _ in range(n):\n        print(a, end=\' \')\n        a, b = b, a + b\nfibonacci(7) # 0 1 1 2 3 5 8');

-- Örnek etiketler
INSERT INTO tags (name) VALUES ('PHP'), ('Algoritma'), ('JavaScript'), ('String'), ('Python'), ('Döngü');

-- Kodlara etiket bağla
INSERT INTO code_tags (code_id, tag_id) VALUES
(1, 1), (1, 2),
(2, 3), (2, 4),
(3, 5), (3, 6);

-- Blog kategorileri
INSERT INTO blog_categories (name, slug, description) VALUES
('PHP', 'php', 'PHP ile ilgili yazılar ve öğreticiler'),
('JavaScript', 'javascript', 'JavaScript ve modern web geliştirme'),
('Python', 'python', 'Python programlama dili'),
('Web Geliştirme', 'web-development', 'Genel web geliştirme konuları'),
('Veritabanı', 'database', 'Veritabanı yönetimi ve optimizasyon'),
('DevOps', 'devops', 'DevOps ve deployment konuları');

-- Blog yazıları
INSERT INTO blog_posts (user_id, title, slug, content, excerpt, status) VALUES
(2, 'PHP ile Modern Web Geliştirme', 'php-ile-modern-web-gelistirme', E'# PHP ile Modern Web Geliştirme\n\nPHP, web geliştirme dünyasında hala en popüler dillerden biri. Bu yazıda PHP\'nin en son özelliklerini kullanarak modern web uygulamaları geliştirme tekniklerini ele alacağız.\n\n## PHP 8.x Özellikleri\n\nPHP 8.x ile gelen yeni özellikler:\n- Named Arguments\n- Attributes\n- Constructor Property Promotion\n- Match Expression\n- Nullsafe Operator\n\n## Modern PHP Uygulaması Örneği\n\n```php\n<?php\ndeclare(strict_types=1);\n\nclass UserController\n{\n    public function __construct(\n        private UserService $userService,\n        private LoggerInterface $logger\n    ) {}\n    \n    public function createUser(CreateUserRequest $request): JsonResponse\n    {\n        try {\n            $user = $this->userService->create($request->validated());\n            return response()->json($user, 201);\n        } catch (Exception $e) {\n            $this->logger->error(\'User creation failed\', [\'error\' => $e->getMessage()]);\n            return response()->json([\'error\' => \'User creation failed\'], 500);\n        }\n    }\n}\n```\n\nBu modern yaklaşım ile daha temiz, okunabilir ve sürdürülebilir kod yazabilirsiniz.', 'PHP\'nin en son özelliklerini kullanarak modern web uygulamaları geliştirme teknikleri.', 'published'),
(2, 'JavaScript ES2023 Özellikleri', 'javascript-es2023-ozellikleri', E'# JavaScript ES2023 Özellikleri\n\nJavaScript sürekli gelişiyor ve her yıl yeni özellikler ekleniyor. ES2023 ile gelen yeni özellikleri keşfedelim.\n\n## Yeni Array Metodları\n\n### Array.prototype.toSorted()\n\n```javascript\nconst numbers = [3, 1, 4, 1, 5, 9];\nconst sortedNumbers = numbers.toSorted(); // [1, 1, 3, 4, 5, 9]\nconsole.log(numbers); // [3, 1, 4, 1, 5, 9] - orijinal dizi değişmedi\n```\n\n### Array.prototype.toReversed()\n\n```javascript\nconst letters = [\'a\', \'b\', \'c\', \'d\'];\nconst reversedLetters = letters.toReversed(); // [\'d\', \'c\', \'b\', \'a\']\nconsole.log(letters); // [\'a\', \'b\', \'c\', \'d\'] - orijinal dizi değişmedi\n```\n\n## WeakMap ve WeakSet İyileştirmeleri\n\nES2023 ile WeakMap ve WeakSet\'e yeni metodlar eklendi:\n\n```javascript\nconst weakMap = new WeakMap();\nconst obj = {};\n\nweakMap.set(obj, \'value\');\nconsole.log(weakMap.has(obj)); // true\nconsole.log(weakMap.get(obj)); // \'value\'\n```\n\nBu yeni özellikler ile JavaScript kodunuzu daha temiz ve güvenli hale getirebilirsiniz.', 'JavaScript\'in en yeni özelliklerini keşfedin ve kodunuzu daha temiz hale getirin.', 'published'),
(2, 'Python ile Veri Analizi', 'python-ile-veri-analizi', E'# Python ile Veri Analizi\n\nPython, veri analizi ve bilimsel hesaplama için mükemmel bir dil. Bu yazıda Python kullanarak büyük veri setlerini analiz etme ve görselleştirme tekniklerini ele alacağız.\n\n## Gerekli Kütüphaneler\n\n```python\nimport pandas as pd\nimport numpy as np\nimport matplotlib.pyplot as plt\nimport seaborn as sns\nfrom sklearn.preprocessing import StandardScaler\nfrom sklearn.cluster import KMeans\n```\n\n## Veri Yükleme ve Temizleme\n\n```python\n# CSV dosyasından veri yükleme\ndf = pd.read_csv(\'data.csv\')\n\n# Eksik verileri kontrol etme\nprint(df.isnull().sum())\n\n# Eksik verileri doldurma\ndf[\'age\'].fillna(df[\'age\'].mean(), inplace=True)\n\n# Veri tiplerini kontrol etme\nprint(df.dtypes)\n```\n\n## Veri Görselleştirme\n\n```python\n# Histogram\nplt.figure(figsize=(10, 6))\nplt.hist(df[\'age\'], bins=30, alpha=0.7, color=\'skyblue\')\nplt.title(\'Yaş Dağılımı\')\nplt.xlabel(\'Yaş\')\nplt.ylabel(\'Frekans\')\nplt.show()\n\n# Korelasyon matrisi\ncorrelation_matrix = df.corr()\nplt.figure(figsize=(12, 8))\nsns.heatmap(correlation_matrix, annot=True, cmap=\'coolwarm\')\nplt.title(\'Korelasyon Matrisi\')\nplt.show()\n```\n\n## Makine Öğrenmesi\n\n```python\n# Veri ön işleme\nscaler = StandardScaler()\nX_scaled = scaler.fit_transform(df[[\'feature1\', \'feature2\']])\n\n# K-means clustering\nkmeans = KMeans(n_clusters=3, random_state=42)\ndf[\'cluster\'] = kmeans.fit_predict(X_scaled)\n\n# Sonuçları görselleştirme\nplt.scatter(df[\'feature1\'], df[\'feature2\'], c=df[\'cluster\'], cmap=\'viridis\')\nplt.title(\'K-means Clustering Sonuçları\')\nplt.show()\n```\n\nBu teknikler ile Python kullanarak güçlü veri analizi uygulamaları geliştirebilirsiniz.', 'Python kullanarak büyük veri setlerini analiz etme ve görselleştirme teknikleri.', 'published');

-- Blog yazılarını kategorilere bağla
INSERT INTO blog_post_categories (post_id, category_id) VALUES
(1, 1), -- PHP yazısı -> PHP kategorisi
(2, 2), -- JavaScript yazısı -> JavaScript kategorisi
(3, 3); -- Python yazısı -> Python kategorisi

-- Proje kategorileri
INSERT INTO project_categories (name, slug, description) VALUES
('Web Uygulaması', 'web-app', 'Web tabanlı uygulamalar'),
('Mobil Uygulama', 'mobile-app', 'Mobil uygulamalar'),
('API', 'api', 'REST API ve mikroservisler'),
('E-ticaret', 'ecommerce', 'E-ticaret platformları'),
('Blog', 'blog', 'Blog ve CMS sistemleri'),
('Araç', 'tool', 'Geliştirici araçları');

-- Projeler
INSERT INTO projects (user_id, title, slug, description, content, github_url, live_url, technologies, status) VALUES
(2, 'KodForum', 'kodforum', 'Yazılım geliştiricilerin kodlarını paylaştığı platform', E'# KodForum\n\nKodForum, yazılım geliştiricilerin kodlarını paylaştığı, tartıştığı ve öğrendiği modern bir platformdur.\n\n## Özellikler\n\n- Kod paylaşımı ve yorum sistemi\n- Etiketleme ve arama\n- Kullanıcı profilleri\n- Blog sistemi\n- Proje portföyü\n\n## Teknolojiler\n\n- PHP 8.x\n- PostgreSQL\n- Bootstrap 5\n- JavaScript ES6+\n- PDO\n\n## Kurulum\n\n```bash\ngit clone https://github.com/semih/kodforum.git\ncd kodforum\ncomposer install\n```\n\nBu proje modern web geliştirme tekniklerini kullanarak geliştirilmiştir.', 'https://github.com/semih/kodforum', 'https://kodforum.com', ARRAY['PHP', 'PostgreSQL', 'Bootstrap', 'JavaScript'], 'active'),
(2, 'E-ticaret API', 'e-ticaret-api', 'Modern e-ticaret platformu için REST API', E'# E-ticaret API\n\nModern e-ticaret platformları için geliştirilmiş REST API. Laravel framework kullanılarak geliştirilmiştir.\n\n## API Endpoints\n\n- `GET /api/products` - Ürün listesi\n- `POST /api/products` - Yeni ürün ekleme\n- `GET /api/products/{id}` - Ürün detayı\n- `POST /api/orders` - Sipariş oluşturma\n\n## Özellikler\n\n- JWT Authentication\n- Rate Limiting\n- API Documentation\n- Caching\n- Logging\n\nBu API, mikroservis mimarisi için tasarlanmıştır.', 'https://github.com/semih/ecommerce-api', 'https://api.ecommerce.com', ARRAY['Laravel', 'MySQL', 'Redis', 'Docker'], 'active'),
(2, 'Task Manager', 'task-manager', 'Takım yönetimi için görev takip uygulaması', E'# Task Manager\n\nTakımlar için geliştirilmiş modern görev takip uygulaması. React ve Node.js kullanılarak geliştirilmiştir.\n\n## Özellikler\n\n- Görev oluşturma ve atama\n- Proje yönetimi\n- Zaman takibi\n- Raporlama\n- Gerçek zamanlı bildirimler\n\n## Teknolojiler\n\n- React 18\n- Node.js\n- Express.js\n- MongoDB\n- Socket.io\n\nBu uygulama modern web teknolojilerini kullanarak geliştirilmiştir.', 'https://github.com/semih/task-manager', 'https://taskmanager.com', ARRAY['React', 'Node.js', 'MongoDB', 'Socket.io'], 'completed');

-- Projeleri kategorilere bağla
INSERT INTO project_categories_rel (project_id, category_id) VALUES
(1, 1), -- KodForum -> Web Uygulaması
(2, 3), -- E-ticaret API -> API
(3, 1); -- Task Manager -> Web Uygulaması

-- Site ayarları
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_title', 'KodForum - Kod Paylaşım ve Tartışma Platformu', 'text', 'Site başlığı'),
('site_description', 'Yazılım geliştiricilerin kodlarını paylaştığı, tartıştığı ve öğrendiği modern platform', 'text', 'Site açıklaması'),
('site_keywords', 'kod, programlama, yazılım, geliştirici, php, javascript, python', 'text', 'Site anahtar kelimeleri'),
('posts_per_page', '10', 'number', 'Sayfa başına gösterilecek yazı sayısı'),
('enable_comments', 'true', 'boolean', 'Yorum sistemi aktif mi?'),
('enable_registration', 'true', 'boolean', 'Kayıt sistemi aktif mi?'),
('maintenance_mode', 'false', 'boolean', 'Bakım modu aktif mi?'),
('contact_email', 'info@kodforum.com', 'text', 'İletişim e-posta adresi'),
('social_links', '{"github": "https://github.com/semih", "twitter": "https://twitter.com/semih", "linkedin": "https://linkedin.com/in/semih"}', 'json', 'Sosyal medya linkleri');

-- İstatistikler
INSERT INTO statistics (stat_key, stat_value, stat_date) VALUES
('total_users', 500, CURRENT_DATE),
('total_codes', 1000, CURRENT_DATE),
('total_posts', 50, CURRENT_DATE),
('total_projects', 25, CURRENT_DATE),
('total_views', 15000, CURRENT_DATE); 
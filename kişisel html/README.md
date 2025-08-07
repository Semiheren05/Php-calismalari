# 🚀 KodForum - Modern Kod Paylaşım Platformu

KodForum, yazılım geliştiricilerin kodlarını paylaştığı, tartıştığı ve öğrendiği modern bir web platformudur. PHP, PostgreSQL ve modern web teknolojileri kullanılarak geliştirilmiştir.

## ✨ Özellikler

### 🎨 **Modern Tasarım**
- Glassmorphism efektleri
- Responsive tasarım
- Gradient renkler
- Smooth animasyonlar
- Modern UI/UX

### 👥 **Kullanıcı Sistemi**
- Kayıt olma ve giriş yapma
- Kullanıcı profilleri
- Rol tabanlı yetkilendirme (User, Moderator, Admin)
- Güvenli şifre hashleme

### 💻 **Kod Paylaşımı**
- Kod yükleme ve düzenleme
- Syntax highlighting
- Etiketleme sistemi
- Arama ve filtreleme
- Kod kopyalama

### 📝 **Blog Sistemi**
- Blog yazısı oluşturma
- Kategori sistemi
- Markdown desteği
- SEO dostu URL'ler

### 🛠️ **Proje Portföyü**
- Proje ekleme ve düzenleme
- Teknoloji etiketleri
- GitHub ve demo linkleri
- Proje kategorileri

### 🔧 **Admin Paneli**
- Kullanıcı yönetimi
- Kod yönetimi
- Blog yazısı yönetimi
- Proje yönetimi
- Site ayarları
- İstatistikler

## 🛠️ Teknolojiler

### **Backend**
- **PHP 7.4+** - Ana programlama dili
- **PostgreSQL** - Veritabanı
- **PDO** - Veritabanı bağlantısı
- **Session Management** - Kullanıcı oturumu

### **Frontend**
- **HTML5** - Semantik markup
- **CSS3** - Modern styling
- **JavaScript ES6+** - İnteraktif özellikler
- **Bootstrap 5** - Responsive framework
- **Font Awesome 6** - İkonlar
- **Inter Font** - Modern tipografi

### **Güvenlik**
- **Password Hashing** - Güvenli şifre saklama
- **SQL Injection Protection** - PDO prepared statements
- **XSS Protection** - Input sanitization
- **CSRF Protection** - Form güvenliği

## 📦 Kurulum

### **Gereksinimler**
- PHP 7.4 veya üzeri
- PostgreSQL 12 veya üzeri
- Web sunucusu (Apache/Nginx)
- Composer (opsiyonel)

### **1. Projeyi İndirin**
```bash
git clone https://github.com/username/kodforum.git
cd kodforum
```

### **2. Veritabanını Kurun**
```bash
# PostgreSQL'e bağlanın
psql -U postgres

# Veritabanı oluşturun
CREATE DATABASE kodforum;

# Veritabanına bağlanın
\c kodforum

# SQL dosyasını çalıştırın
\i database.sql
```

### **3. Veritabanı Bağlantısını Yapılandırın**
`includes/db.php` dosyasını düzenleyin:
```php
$host = 'localhost';
$dbname = 'kodforum';
$username = 'your_username';
$password = 'your_password';
```

### **4. Web Sunucusunu Yapılandırın**

#### **Apache (.htaccess)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### **Nginx**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### **5. Dosya İzinlerini Ayarlayın**
```bash
chmod 755 -R .
chmod 777 -R uploads/  # Eğer dosya yükleme varsa
```

## 🔐 Admin Hesabı

Kurulum sonrası otomatik olarak oluşturulan admin hesapları:

### **Ana Admin**
- **E-posta**: `admin@kodforum.com`
- **Şifre**: `password`
- **Rol**: Admin

### **Test Kullanıcısı**
- **E-posta**: `semih@example.com`
- **Şifre**: `password`
- **Rol**: Admin

## 🎯 Kullanım

### **Kullanıcı İşlemleri**
1. **Kayıt Ol**: `/pages/register.php`
2. **Giriş Yap**: `/pages/login.php`
3. **Profil**: `/pages/profile.php`

### **Kod Paylaşımı**
1. **Kod Ekle**: `/pages/code_add.php`
2. **Kodları Görüntüle**: `/pages/codes.php`
3. **Kod Detayı**: `/pages/code.php?id=1`

### **Blog**
1. **Blog Yazıları**: `/pages/blog.php`
2. **Blog Detayı**: `/pages/blog.php?slug=yazi-slug`

### **Projeler**
1. **Proje Listesi**: `/pages/projects.php`
2. **Proje Detayı**: `/pages/projects.php?slug=proje-slug`

### **Admin Paneli**
1. **Admin Girişi**: Admin hesabıyla giriş yapın
2. **Dashboard**: `/admin/index.php`
3. **Kullanıcı Yönetimi**: `/admin/users.php`
4. **Kod Yönetimi**: `/admin/codes.php`
5. **Blog Yönetimi**: `/admin/blog.php`
6. **Proje Yönetimi**: `/admin/projects.php`
7. **Site Ayarları**: `/admin/settings.php`

## 📁 Proje Yapısı

```
kodforum/
├── admin/                 # Admin paneli
│   ├── index.php         # Dashboard
│   ├── users.php         # Kullanıcı yönetimi
│   ├── codes.php         # Kod yönetimi
│   ├── blog.php          # Blog yönetimi
│   ├── projects.php      # Proje yönetimi
│   └── settings.php      # Site ayarları
├── assets/               # Statik dosyalar
│   ├── css/
│   │   └── modern-style.css
│   └── js/
├── includes/             # PHP include dosyaları
│   ├── db.php           # Veritabanı bağlantısı
│   ├── header.php       # Ortak header
│   └── footer.php       # Ortak footer
├── pages/               # Ana sayfalar
│   ├── index.php        # Ana sayfa
│   ├── login.php        # Giriş
│   ├── register.php     # Kayıt
│   ├── codes.php        # Kod listesi
│   ├── code.php         # Kod detayı
│   ├── code_add.php     # Kod ekleme
│   ├── blog.php         # Blog
│   ├── projects.php     # Projeler
│   ├── about.php        # Hakkımda
│   ├── profile.php      # Profil
│   ├── logout.php       # Çıkış
│   └── 404.php          # 404 sayfası
├── templates/           # Şablon dosyaları
├── .htaccess           # Apache yapılandırması
├── index.php           # Ana giriş noktası
├── database.sql        # Veritabanı şeması
└── README.md           # Bu dosya
```

## 🔧 Yapılandırma

### **Site Ayarları**
Admin panelinden aşağıdaki ayarları yapabilirsiniz:
- Site başlığı ve açıklaması
- Sayfa başına gösterilecek içerik sayısı
- Yorum sistemi aktif/pasif
- Kayıt sistemi aktif/pasif
- Bakım modu
- İletişim bilgileri
- Sosyal medya linkleri

### **Tema Özelleştirme**
`assets/css/modern-style.css` dosyasından:
- Renk şeması
- Font ayarları
- Animasyonlar
- Responsive breakpoint'ler

## 🚀 Geliştirme

### **Yeni Özellik Ekleme**
1. Veritabanı şemasını güncelleyin
2. PHP dosyalarını oluşturun
3. CSS stillerini ekleyin
4. Admin panelini güncelleyin

### **Güvenlik Kontrol Listesi**
- [ ] Input validation
- [ ] SQL injection protection
- [ ] XSS protection
- [ ] CSRF protection
- [ ] File upload security
- [ ] Session security

## 📊 Veritabanı Şeması

### **Ana Tablolar**
- `users` - Kullanıcı bilgileri
- `codes` - Kod paylaşımları
- `blog_posts` - Blog yazıları
- `projects` - Projeler
- `comments` - Yorumlar
- `tags` - Etiketler

### **İlişki Tabloları**
- `code_tags` - Kod-etiket ilişkileri
- `blog_post_categories` - Blog kategori ilişkileri
- `project_categories_rel` - Proje kategori ilişkileri

### **Sistem Tabloları**
- `site_settings` - Site ayarları
- `statistics` - İstatistikler

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'Add amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## 📝 Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için `LICENSE` dosyasına bakın.

## 📞 İletişim

- **Proje Linki**: [https://github.com/username/kodforum](https://github.com/username/kodforum)
- **E-posta**: info@kodforum.com
- **Website**: [https://kodforum.com](https://kodforum.com)

## 🙏 Teşekkürler

- [Bootstrap](https://getbootstrap.com/) - CSS Framework
- [Font Awesome](https://fontawesome.com/) - İkonlar
- [Inter Font](https://rsms.me/inter/) - Tipografi
- [PostgreSQL](https://www.postgresql.org/) - Veritabanı

---

⭐ Bu projeyi beğendiyseniz yıldız vermeyi unutmayın! 
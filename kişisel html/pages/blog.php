<?php
session_start();

// Veritabanı bağlantısı ve hata yönetimi
try {
    require_once '../includes/db.php';
    
    // Blog yazılarını veritabanından çek
    $stmt = $pdo->query("
        SELECT bp.*, u.username, 
               COUNT(DISTINCT bpc.category_id) as category_count,
               STRING_AGG(bc.name, ', ') as categories
        FROM blog_posts bp 
        LEFT JOIN users u ON bp.user_id = u.id 
        LEFT JOIN blog_post_categories bpc ON bp.id = bpc.post_id
        LEFT JOIN blog_categories bc ON bpc.category_id = bc.id
        WHERE bp.status = 'published'
        GROUP BY bp.id, u.username
        ORDER BY bp.created_at DESC
    ");
    $blog_posts = $stmt->fetchAll();
    
    // Blog kategorilerini çek
    $stmt = $pdo->query("
        SELECT bc.*, COUNT(bpc.post_id) as post_count
        FROM blog_categories bc
        LEFT JOIN blog_post_categories bpc ON bc.id = bpc.category_id
        LEFT JOIN blog_posts bp ON bpc.post_id = bp.id AND bp.status = 'published'
        GROUP BY bc.id
        ORDER BY post_count DESC
    ");
    $categories = $stmt->fetchAll();
    
    // Popüler etiketleri çek (kodlardan)
    $stmt = $pdo->query("
        SELECT t.name, COUNT(ct.code_id) as usage_count
        FROM tags t
        LEFT JOIN code_tags ct ON t.id = ct.tag_id
        GROUP BY t.id, t.name
        ORDER BY usage_count DESC
        LIMIT 10
    ");
    $popular_tags = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Veritabanı hatası durumunda örnek veriler
    $blog_posts = [
        [
            'id' => 1,
            'title' => 'PHP ile Modern Web Geliştirme',
            'slug' => 'php-ile-modern-web-gelistirme',
            'excerpt' => 'PHP\'nin en son özelliklerini kullanarak modern web uygulamaları geliştirme teknikleri.',
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s'),
            'views' => 150,
            'categories' => 'PHP, Web Geliştirme'
        ],
        [
            'id' => 2,
            'title' => 'JavaScript ES2023 Özellikleri',
            'slug' => 'javascript-es2023-ozellikleri',
            'excerpt' => 'JavaScript\'in en yeni özelliklerini keşfedin ve kodunuzu daha temiz hale getirin.',
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'views' => 89,
            'categories' => 'JavaScript'
        ],
        [
            'id' => 3,
            'title' => 'Python ile Veri Analizi',
            'slug' => 'python-ile-veri-analizi',
            'excerpt' => 'Python kullanarak büyük veri setlerini analiz etme ve görselleştirme teknikleri.',
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'views' => 234,
            'categories' => 'Python, Veri Analizi'
        ]
    ];
    
    $categories = [
        ['name' => 'PHP', 'slug' => 'php', 'post_count' => 1],
        ['name' => 'JavaScript', 'slug' => 'javascript', 'post_count' => 1],
        ['name' => 'Python', 'slug' => 'python', 'post_count' => 1],
        ['name' => 'Web Geliştirme', 'slug' => 'web-development', 'post_count' => 1]
    ];
    
    $popular_tags = [
        ['name' => 'PHP', 'usage_count' => 5],
        ['name' => 'JavaScript', 'usage_count' => 3],
        ['name' => 'Python', 'usage_count' => 2],
        ['name' => 'Algoritma', 'usage_count' => 1]
    ];
}

$page_title = 'Blog - SemihHub';
$current_page = 'blog';

$additional_css = '
.hero-section .display-4 {
  background: linear-gradient(90deg, #fff 30%, #e0e0ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 800;
  text-shadow: 0 2px 8px rgba(102,126,234,0.10);
}
.hero-section .lead {
  color: #f8f9fa;
  text-shadow: 0 2px 8px rgba(102,126,234,0.10);
}
.hero-section .btn-light {
  color: #667eea;
  background: #fff;
  border: none;
  font-weight: 600;
}
.hero-section .btn-light:hover {
  background: #f8f9fa;
  color: #764ba2;
}
.hero-section .btn-outline-light {
  color: #fff;
  border-color: #fff;
  font-weight: 600;
}
.hero-section .btn-outline-light:hover {
  background: #fff;
  color: #667eea;
}

    .blog-hero {
        background: var(--primary-gradient);
        padding: 8rem 0 4rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .blog-hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><defs><pattern id=\'blog-pattern\' width=\'40\' height=\'40\' patternUnits=\'userSpaceOnUse\'><circle cx=\'20\' cy=\'20\' r=\'2\' fill=\'%23ffffff\' opacity=\'0.1\'/></pattern></defs><rect width=\'100\' height=\'100\' fill=\'url(%23blog-pattern)\'/></svg>");
        animation: float 25s ease-in-out infinite;
    }

    .blog-content {
        padding: 4rem 0;
        background: var(--light-gradient);
        min-height: 100vh;
    }

    .blog-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }

    .blog-card:hover {
        box-shadow: var(--box-shadow-hover);
        transform: translateY(-10px);
    }

    .blog-card .card-body {
        padding: 2rem;
    }

    .blog-card .card-img-top {
        height: 200px;
        object-fit: cover;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }

    .blog-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: var(--text-secondary);
        flex-wrap: wrap;
    }

    .blog-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .blog-category {
        background: var(--primary-gradient);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .sidebar-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .sidebar-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 0.5rem;
    }

    .category-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .category-item:hover {
        color: var(--primary-color);
        transform: translateX(5px);
    }

    .category-item:last-child {
        border-bottom: none;
    }

    .tag-item {
        display: inline-block;
        background: rgba(102, 126, 234, 0.1);
        color: var(--primary-color);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        margin: 0.25rem;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .tag-item:hover {
        background: var(--primary-color);
        color: white;
        transform: translateY(-2px);
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .blog-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .sidebar-card {
            margin-top: 2rem;
        }
    }
';

include '../includes/header.php';
?>
<section class="hero-section d-flex align-items-center" style="min-height: 60vh; background: var(--primary-gradient); color: #fff;">
  <div class="container text-center">
    <h1 class="display-4 fw-bold mb-3">Blog</h1>
    <p class="lead mb-4">SemihHub blogunda yazılım, teknoloji ve projeler hakkında güncel yazılar bulabilirsiniz.</p>
    <a href="index.php" class="btn btn-light btn-lg"><i class="fas fa-home me-2"></i>Ana Sayfa</a>
  </div>
</section>

<!-- Blog Content -->
<section class="blog-content">
    <div class="container">
        <div class="row">
            <!-- Blog Posts -->
            <div class="col-lg-8">
                <div class="row g-4">
                    <?php if (empty($blog_posts)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-blog fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Henüz blog yazısı yok</h4>
                        <p class="text-muted">İlk blog yazısını yazmak için giriş yapın.</p>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                        <?php foreach ($blog_posts as $post): ?>
                        <div class="col-md-6">
                            <div class="blog-card fade-in">
                                <div class="card-img-top">
                                    <i class="fas fa-blog"></i>
                                </div>
                                <div class="card-body">
                                    <div class="blog-meta">
                                        <span><i class="fas fa-user me-1"></i><?= htmlspecialchars($post['username'] ?? 'Anonim') ?></span>
                                        <span><i class="fas fa-calendar me-1"></i><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                                        <span><i class="fas fa-eye me-1"></i><?= $post['views'] ?> görüntüleme</span>
                                    </div>
                                    
                                    <h3 class="h5 fw-bold mb-3">
                                        <a href="blog-post.php?slug=<?= $post['slug'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h3>
                                    
                                    <p class="text-muted mb-3">
                                        <?= htmlspecialchars($post['excerpt']) ?>
                                    </p>

                                    <?php if (!empty($post['categories'])): ?>
                                    <div class="blog-categories">
                                        <?php foreach (explode(', ', $post['categories']) as $category): ?>
                                        <span class="blog-category"><?= htmlspecialchars(trim($category)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="mt-3">
                                        <a href="blog-post.php?slug=<?= $post['slug'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-readme me-1"></i>Devamını Oku
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Categories -->
                <div class="sidebar-card fade-in">
                    <h3 class="sidebar-title">
                        <i class="fas fa-folder me-2"></i>Kategoriler
                    </h3>
                    <?php foreach ($categories as $category): ?>
                    <div class="category-item">
                        <span><?= htmlspecialchars($category['name']) ?></span>
                        <span class="badge bg-primary"><?= $category['post_count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Popular Tags -->
                <div class="sidebar-card fade-in">
                    <h3 class="sidebar-title">
                        <i class="fas fa-tags me-2"></i>Popüler Etiketler
                    </h3>
                    <div class="text-center">
                        <?php foreach ($popular_tags as $tag): ?>
                        <a href="codes.php?tag=<?= urlencode($tag['name']) ?>" class="tag-item">
                            <?= htmlspecialchars($tag['name']) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- About Blog -->
                <div class="sidebar-card fade-in">
                    <h3 class="sidebar-title">
                        <i class="fas fa-info-circle me-2"></i>Blog Hakkında
                    </h3>
                    <p class="text-muted">
                        Yazılım geliştirme, programlama dilleri, web teknolojileri ve daha fazlası hakkında 
                        güncel yazılar ve öğreticiler bulabilirsiniz.
                    </p>
                    <div class="d-grid">
                        <a href="about.php" class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i>Hakkımda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?> 
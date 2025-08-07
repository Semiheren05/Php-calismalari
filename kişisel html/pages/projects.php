<?php
session_start();

// Veritabanı bağlantısı ve hata yönetimi
try {
    require_once '../includes/db.php';
    
    $postMessage = ''; // Hata mesajları için değişkeni başlat

// Proje ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    if (isset($_SESSION['user_id'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $user_id = $_SESSION['user_id'];
        $image_url = null;

        // Resim yükleme işlemi
        if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] == 0) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['project_image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $file_name = time() . '_' . uniqid() . '_' . basename($_FILES['project_image']['name']);
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['project_image']['tmp_name'], $target_file)) {
                    $image_url = 'uploads/' . $file_name; // Veritabanına kaydedilecek göreli yol
                } else {
                    $postMessage = '<div class="alert alert-danger">Hata: Resim yüklenemedi.</div>';
                }
            } else {
                $postMessage = '<div class="alert alert-danger">Geçersiz dosya türü. Sadece JPG, PNG, GIF kabul edilir.</div>';
            }
        }

        if (!empty($title) && !empty($description) && empty($postMessage)) {
            try {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . uniqid();

                $stmt = $pdo->prepare("INSERT INTO projects (title, description, user_id, slug, image_url, status, created_at) VALUES (:title, :description, :user_id, :slug, :image_url, 'active', NOW())");
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':user_id' => $user_id,
                    ':slug' => $slug,
                    ':image_url' => $image_url
                ]);
                $postMessage = '<div class="alert alert-success">Proje başarıyla paylaşıldı! Sayfa yenileniyor... <meta http-equiv="refresh" content="2"></div>';
            } catch (PDOException $e) {
                $postMessage = '<div class="alert alert-danger">Veritabanı hatası: ' . $e->getMessage() . '</div>';
            }
        } elseif (empty($postMessage)) {
            $postMessage = '<div class="alert alert-danger">Başlık ve açıklama alanları zorunludur.</div>';
        }
    } else {
        $postMessage = '<div class="alert alert-danger">Proje eklemek için giriş yapmalısınız.</div>';
    }
}

    // Projeleri veritabanından çek
    // Projeleri ve kategori bilgilerini veritabanından çek
    $stmt = $pdo->prepare(
        "SELECT 
            p.*, 
            u.username,
            string_agg(c.slug, ',') as category_slugs
         FROM 
            projects p 
         JOIN 
            users u ON p.user_id = u.id
         LEFT JOIN 
            project_categories pc ON p.id = pc.project_id
         LEFT JOIN 
            categories c ON pc.category_id = c.id
         WHERE 
            p.status = 'active'
         GROUP BY
            p.id, u.username
         ORDER BY 
            p.created_at DESC"
    );
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Proje ekleme/mesaj için değişkeni baştan tanımla
    $postMessage = '';
    
    // Proje kategorilerini çek
    $stmt = $pdo->query("
        SELECT pc.*, COUNT(pcr.project_id) as project_count
        FROM project_categories pc
        LEFT JOIN project_categories_rel pcr ON pc.id = pcr.category_id
        LEFT JOIN projects p ON pcr.project_id = p.id AND p.status = 'active'
        GROUP BY pc.id
        ORDER BY project_count DESC
    ");
    $categories = $stmt->fetchAll();

} catch (PDOException $e) {
    // Veritabanı hatası durumunda örnek veriler
    $projects = [
        [
            'id' => 1,
            'title' => 'KodForum',
            'slug' => 'kodforum',
            'description' => 'Yazılım geliştiricilerin kodlarını paylaştığı platform',
            'github_url' => 'https://github.com/semih/kodforum',
            'live_url' => 'https://kodforum.com',
            'technologies' => ['PHP', 'PostgreSQL', 'Bootstrap', 'JavaScript'],
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s'),
            'views' => 150,
            'categories' => 'Web Uygulaması'
        ],
        [
            'id' => 2,
            'title' => 'E-ticaret API',
            'slug' => 'e-ticaret-api',
            'description' => 'Modern e-ticaret platformu için REST API',
            'github_url' => 'https://github.com/semih/ecommerce-api',
            'live_url' => 'https://api.ecommerce.com',
            'technologies' => ['Laravel', 'MySQL', 'Redis', 'Docker'],
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'views' => 89,
            'categories' => 'API'
        ],
        [
            'id' => 3,
            'title' => 'Task Manager',
            'slug' => 'task-manager',
            'description' => 'Takım yönetimi için görev takip uygulaması',
            'github_url' => 'https://github.com/semih/task-manager',
            'live_url' => 'https://taskmanager.com',
            'technologies' => ['React', 'Node.js', 'MongoDB', 'Socket.io'],
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'views' => 234,
            'categories' => 'Web Uygulaması'
        ]
    ];
    
    $categories = [
        ['name' => 'Web Uygulaması', 'slug' => 'web-app', 'project_count' => 2],
        ['name' => 'API', 'slug' => 'api', 'project_count' => 1],
        ['name' => 'Mobil Uygulama', 'slug' => 'mobile-app', 'project_count' => 0],
        ['name' => 'E-ticaret', 'slug' => 'ecommerce', 'project_count' => 0]
    ];
    
    $stats = [
        'total_projects' => 3,
        'active_projects' => 3,
        'completed_projects' => 0,
        'total_views' => 473
    ];
}

$page_title = 'Projeler - SemihHub';
$current_page = 'projects';

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
    .projects-hero {
        background: var(--primary-gradient);
        padding: 8rem 0 4rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .projects-hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><defs><pattern id=\'projects-pattern\' width=\'45\' height=\'45\' patternUnits=\'userSpaceOnUse\'><circle cx=\'22.5\' cy=\'22.5\' r=\'2.5\' fill=\'%23ffffff\' opacity=\'0.1\'/></pattern></defs><rect width=\'100\' height=\'100\' fill=\'url(%23projects-pattern)\'/></svg>");
        animation: float 30s ease-in-out infinite;
    }

    .projects-content {
        padding: 4rem 0;
        background: var(--light-gradient);
        min-height: 100vh;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: var(--box-shadow-hover);
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 1rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .filter-buttons {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 3rem;
        flex-wrap: wrap;
    }

    .filter-btn {
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid transparent;
        color: var(--text-primary);
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .filter-btn:hover,
    .filter-btn.active {
        background: var(--primary-gradient);
        color: white;
        transform: translateY(-2px);
        box-shadow: var(--box-shadow);
        text-decoration: none;
    }

    .project-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }

    .project-card:hover {
        box-shadow: var(--box-shadow-hover);
        transform: translateY(-10px);
    }

    .project-card .card-body {
        padding: 2rem;
    }

    .project-card .card-img-top {
        height: 200px;
        object-fit: cover;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }

    .project-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: var(--text-secondary);
        flex-wrap: wrap;
    }

    .project-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .project-category {
        background: var(--primary-gradient);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .tech-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin: 1rem 0;
    }

    .tech-badge {
        background: rgba(102, 126, 234, 0.1);
        color: var(--primary-color);
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .project-links {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .project-links a {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .project-links .btn-github {
        background: #333;
        color: white;
    }

    .project-links .btn-github:hover {
        background: #555;
        color: white;
    }

    .project-links .btn-live {
        background: var(--success-color);
        color: white;
    }

    .project-links .btn-live:hover {
        background: #00b894;
        color: white;
    }

    @media (max-width: 768px) {
        .project-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .filter-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .project-links {
            flex-direction: column;
        }
    }
';

include '../includes/header.php'; ?>

<div class="container my-4" style="max-width: 700px;">
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Gönderi Başlatma Tetikleyicisi -->
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex align-items-center">
                <i class="fas fa-user-circle fa-2x text-muted me-3"></i>
                <div class="flex-grow-1 text-muted" data-bs-toggle="modal" data-bs-target="#createProjectModal" style="cursor: pointer; border-radius: 20px; background-color: #f0f2f5; padding: 8px 15px;">
                    Bir proje paylaş...
                </div>
                <button class="btn btn-light ms-3" data-bs-toggle="modal" data-bs-target="#createProjectModal" title="Proje Oluştur">
                    <i class="fas fa-image"></i> Fotoğraf
                </button>
            </div>
        </div>

        <!-- Başarı/Hata Mesajı Alanı -->
        <?php if ($postMessage): ?>
            <?= $postMessage ?>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning text-center my-5">Proje göndermek için giriş yapmalısınız. <a href="login.php">Giriş Yap</a></div>
    <?php endif; ?>

    <!-- Proje Akışı -->
        <!-- Kategori Filtreleme -->
    <div class="text-center my-4">
        <button class="btn btn-primary filter-btn" data-category="all">Tümü</button>
        <?php 
            $stmt_cat = $pdo->query("SELECT * FROM categories ORDER BY name");
            while ($category = $stmt_cat->fetch(PDO::FETCH_ASSOC)) {
                echo '<button class="btn btn-outline-primary filter-btn" data-category="' . htmlspecialchars($category['slug']) . '">' . htmlspecialchars($category['name']) . '</button>';
            }
        ?>
    </div>

    <div class="mt-5">
        <h4 class="mb-4">Son Paylaşılan Projeler</h4>
        <?php if (empty($projects)): ?>
        <div class="card card-body text-center text-muted">
            <p class="mb-0">Henüz paylaşılmış bir proje yok. İlk projeyi sen paylaş!</p>
        </div>
        <?php else: ?>
            <?php foreach ($projects as $project): ?>
            <div class="card mb-4 shadow-sm project-card" data-project-categories="<?= htmlspecialchars($project['category_slugs'] ?? '') ?>">
                <?php 
                    if (!empty($project['image_url'])) {
                        echo '<img src="../' . htmlspecialchars($project['image_url']) . '" class="card-img-top" alt="' . htmlspecialchars($project['title']) . '">';
                    }
                ?>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-user-circle fa-2x text-muted me-3"></i>
                        <div>
                            <h6 class="mb-0"><a href="profile.php?username=<?= htmlspecialchars($project['username']) ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($project['username']) ?></a></h6>
                            <small class="text-muted"><?= date('d F Y, H:i', strtotime($project['created_at'])) ?></small>
                        </div>
                    </div>
                    <h5 class="card-title mt-3">
                        <a href="project_detail.php?slug=<?= htmlspecialchars($project['slug']) ?>" class="text-decoration-none text-dark">
                            <?= htmlspecialchars($project['title']) ?>
                        </a>
                    </h5>
                    <p class="card-text"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                    <!-- Proje detay linki veya diğer butonlar buraya eklenebilir -->
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
<!-- Proje Oluşturma Modalı -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="createProjectModalLabel">Yeni Proje Oluştur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Proje Başlığı</label>
                        <input type="text" class="form-control" name="title" id="title" required maxlength="100" placeholder="Projenize ilgi çekici bir başlık verin">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control" name="description" id="description" rows="5" required placeholder="Projenizin detaylarını, hedeflerini ve kullanılan teknolojileri anlatın..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="project_image" class="form-label">Proje Resmi</label>
                        <input class="form-control" type="file" name="project_image" id="project_image" accept="image/png, image/jpeg, image/gif">
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-light me-1" title="Resim Ekle" onclick="document.getElementById('project_image').click();"><i class="fas fa-image"></i></button>
                        <button type="button" id="emoji-button" class="btn btn-light" title="Emoji Ekle"><i class="fas fa-smile"></i></button>
                    </div>
                    <button type="submit" name="add_project" class="btn btn-primary">Paylaş</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/vanilla-emoji-picker@0.2.2/dist/vanilla-emoji-picker.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (document.querySelector('#emoji-button')) {
            new EmojiPicker({
                trigger: [
                    {
                        selector: '#emoji-button',
                        position: 'top-end'
                    }
                ],
                target: document.querySelector('#description')
            });
        }
    });
</script>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Aktif buton stilini ayarla
            filterButtons.forEach(btn => btn.classList.remove('btn-primary'));
            filterButtons.forEach(btn => btn.classList.add('btn-outline-primary'));
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');

            const selectedCategory = this.getAttribute('data-category');

            projectCards.forEach(card => {
                const projectCategories = card.getAttribute('data-project-categories');
                
                if (selectedCategory === 'all' || (projectCategories && projectCategories.includes(selectedCategory))) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
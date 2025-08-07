<?php
session_start();

// Admin yetkisi kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/db.php';

$message = '';

// Ayarları kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['settings'] as $key => $value) {
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
        $message = 'Ayarlar başarıyla güncellendi.';
    } catch (PDOException $e) {
        $message = 'Hata: ' . $e->getMessage();
    }
}

// Mevcut ayarları çek
try {
    $stmt = $pdo->query("SELECT * FROM site_settings ORDER BY setting_key");
    $settings = $stmt->fetchAll();
} catch (PDOException $e) {
    $settings = [];
}

$page_title = 'Site Ayarları - Admin Panel';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/modern-style.css" rel="stylesheet">
    <style>
        .admin-sidebar {
            background: var(--dark-gradient);
            min-height: 100vh;
            color: white;
        }
        
        .admin-content {
            background: var(--light-gradient);
            min-height: 100vh;
            padding: 2rem;
        }
        
        .admin-nav-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .admin-nav-link:hover,
        .admin-nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            text-decoration: none;
        }
        
        .setting-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .setting-card:hover {
            box-shadow: var(--box-shadow-hover);
        }
        
        .setting-type-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar p-0">
                <div class="p-3">
                    <h4 class="mb-4">
                        <i class="fas fa-cog me-2"></i>Admin Panel
                    </h4>
                    
                    <nav>
                        <a href="index.php" class="admin-nav-link">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                        <a href="users.php" class="admin-nav-link">
                            <i class="fas fa-users me-2"></i>Kullanıcılar
                        </a>
                        <a href="codes.php" class="admin-nav-link">
                            <i class="fas fa-code me-2"></i>Kodlar
                        </a>
                        <a href="blog.php" class="admin-nav-link">
                            <i class="fas fa-blog me-2"></i>Blog
                        </a>
                        <a href="projects.php" class="admin-nav-link">
                            <i class="fas fa-project-diagram me-2"></i>Projeler
                        </a>
                        <a href="settings.php" class="admin-nav-link active">
                            <i class="fas fa-cogs me-2"></i>Ayarlar
                        </a>
                        <hr class="my-3">
                        <a href="../index.php" class="admin-nav-link">
                            <i class="fas fa-home me-2"></i>Siteye Dön
                        </a>
                        <a href="../logout.php" class="admin-nav-link">
                            <i class="fas fa-sign-out-alt me-2"></i>Çıkış
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Site Ayarları</h2>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row g-4">
                        <?php foreach ($settings as $setting): ?>
                        <div class="col-md-6">
                            <div class="setting-card p-3">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($setting['setting_key']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($setting['description']) ?></small>
                                    </div>
                                    <span class="badge bg-secondary setting-type-badge">
                                        <?= ucfirst($setting['setting_type']) ?>
                                    </span>
                                </div>
                                
                                <?php if ($setting['setting_type'] === 'boolean'): ?>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           name="settings[<?= $setting['setting_key'] ?>]" 
                                           value="true" 
                                           id="setting_<?= $setting['id'] ?>"
                                           <?= $setting['setting_value'] === 'true' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="setting_<?= $setting['id'] ?>">
                                        Aktif
                                    </label>
                                </div>
                                <?php elseif ($setting['setting_type'] === 'number'): ?>
                                <input type="number" class="form-control" 
                                       name="settings[<?= $setting['setting_key'] ?>]" 
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>">
                                <?php elseif ($setting['setting_type'] === 'json'): ?>
                                <textarea class="form-control" rows="3" 
                                          name="settings[<?= $setting['setting_key'] ?>]"><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                                <small class="text-muted">JSON formatında girin</small>
                                <?php else: ?>
                                <input type="text" class="form-control" 
                                       name="settings[<?= $setting['setting_key'] ?>]" 
                                       value="<?= htmlspecialchars($setting['setting_value']) ?>">
                                <?php endif; ?>
                                
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-clock me-1"></i>
                                    Son güncelleme: <?= date('d.m.Y H:i', strtotime($setting['updated_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Ayarları Kaydet
                        </button>
                    </div>
                </form>
                
                <!-- İstatistikler -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Site İstatistikleri
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <?php
                                    try {
                                        $stats = [
                                            'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                                            'codes' => $pdo->query("SELECT COUNT(*) FROM codes")->fetchColumn(),
                                            'posts' => $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn(),
                                            'projects' => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
                                            'comments' => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
                                            'views' => $pdo->query("SELECT SUM(views) FROM blog_posts")->fetchColumn() + 
                                                      $pdo->query("SELECT SUM(views) FROM projects")->fetchColumn() + 
                                                      $pdo->query("SELECT SUM(views) FROM codes")->fetchColumn()
                                        ];
                                    } catch (PDOException $e) {
                                        $stats = ['users' => 0, 'codes' => 0, 'posts' => 0, 'projects' => 0, 'comments' => 0, 'views' => 0];
                                    }
                                    ?>
                                    
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h3 text-primary"><?= $stats['users'] ?></div>
                                            <small class="text-muted">Kullanıcı</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h3 text-success"><?= $stats['codes'] ?></div>
                                            <small class="text-muted">Kod</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h3 text-info"><?= $stats['posts'] ?></div>
                                            <small class="text-muted">Blog Yazısı</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h3 text-warning"><?= $stats['projects'] ?></div>
                                            <small class="text-muted">Proje</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h3 text-secondary"><?= $stats['comments'] ?></div>
                                            <small class="text-muted">Yorum</small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="text-center">
                                            <div class="h3 text-danger"><?= number_format($stats['views']) ?></div>
                                            <small class="text-muted">Görüntüleme</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
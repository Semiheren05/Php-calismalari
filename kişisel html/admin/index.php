<?php
session_start();

// Admin yetkisi kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/db.php';

// İstatistikleri çek
try {
    // Kullanıcı sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $userCount = $stmt->fetch()['total'];
    
    // Kod sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM codes");
    $codeCount = $stmt->fetch()['total'];
    
    // Blog yazısı sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM blog_posts");
    $postCount = $stmt->fetch()['total'];
    
    // Proje sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM projects");
    $projectCount = $stmt->fetch()['total'];
    
    // Son aktiviteler
    $stmt = $pdo->query("
        SELECT 'user' as type, username as title, created_at as date 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recentUsers = $stmt->fetchAll();
    
    $stmt = $pdo->query("
        SELECT 'code' as type, title, created_at as date 
        FROM codes 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recentCodes = $stmt->fetchAll();
    
    $stmt = $pdo->query("
        SELECT 'post' as type, title, created_at as date 
        FROM blog_posts 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recentPosts = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $userCount = $codeCount = $postCount = $projectCount = 0;
    $recentUsers = $recentCodes = $recentPosts = [];
}

$page_title = 'Admin Panel - KodForum';
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
        
        .activity-item {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .activity-icon.user { background: var(--primary-gradient); }
        .activity-icon.code { background: var(--secondary-gradient); }
        .activity-icon.post { background: var(--success-color); }
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
                        <a href="index.php" class="admin-nav-link active">
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
                        <a href="settings.php" class="admin-nav-link">
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
                    <h2>Dashboard</h2>
                    <div class="text-muted">
                        Hoş geldin, <?= htmlspecialchars($_SESSION['username']) ?>!
                    </div>
                </div>
                
                <!-- Stats Grid -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= $userCount ?></div>
                            <div class="stat-label">Toplam Kullanıcı</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= $codeCount ?></div>
                            <div class="stat-label">Toplam Kod</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= $postCount ?></div>
                            <div class="stat-label">Blog Yazısı</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= $projectCount ?></div>
                            <div class="stat-label">Proje</div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2"></i>Son Kullanıcılar
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentUsers)): ?>
                                    <p class="text-muted">Henüz kullanıcı yok.</p>
                                <?php else: ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                    <div class="activity-item">
                                        <div class="d-flex align-items-center">
                                            <div class="activity-icon user">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-bold"><?= htmlspecialchars($user['title']) ?></div>
                                                <small class="text-muted"><?= date('d.m.Y H:i', strtotime($user['date'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-code me-2"></i>Son Kodlar
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentCodes)): ?>
                                    <p class="text-muted">Henüz kod yok.</p>
                                <?php else: ?>
                                    <?php foreach ($recentCodes as $code): ?>
                                    <div class="activity-item">
                                        <div class="d-flex align-items-center">
                                            <div class="activity-icon code">
                                                <i class="fas fa-code"></i>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-bold"><?= htmlspecialchars($code['title']) ?></div>
                                                <small class="text-muted"><?= date('d.m.Y H:i', strtotime($code['date'])) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-bolt me-2"></i>Hızlı İşlemler
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <a href="users.php?action=add" class="btn btn-primary w-100">
                                            <i class="fas fa-user-plus me-2"></i>Kullanıcı Ekle
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="codes.php?action=add" class="btn btn-success w-100">
                                            <i class="fas fa-plus me-2"></i>Kod Ekle
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="blog.php?action=add" class="btn btn-info w-100">
                                            <i class="fas fa-edit me-2"></i>Blog Yazısı
                                        </a>
                                    </div>
                                    <div class="col-md-3">
                                        <a href="projects.php?action=add" class="btn btn-warning w-100">
                                            <i class="fas fa-project-diagram me-2"></i>Proje Ekle
                                        </a>
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
<?php
session_start();

// Admin yetkisi kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/db.php';

$action = $_GET['action'] ?? 'list';
$message = '';

// Proje işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $slug = createSlug($title);
                $description = $_POST['description'];
                $content = $_POST['content'];
                $github_url = $_POST['github_url'];
                $live_url = $_POST['live_url'];
                $status = $_POST['status'];
                $user_id = $_POST['user_id'];
                $technologies = explode(',', $_POST['technologies']);
                $categories = $_POST['categories'] ?? [];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, slug, description, content, github_url, live_url, technologies, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $title, $slug, $description, $content, $github_url, $live_url, $technologies, $status]);
                    $project_id = $pdo->lastInsertId();
                    
                    // Kategorileri ekle
                    foreach ($categories as $category_id) {
                        $stmt = $pdo->prepare("INSERT INTO project_categories_rel (project_id, category_id) VALUES (?, ?)");
                        $stmt->execute([$project_id, $category_id]);
                    }
                    
                    $message = 'Proje başarıyla eklendi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $slug = createSlug($title);
                $description = $_POST['description'];
                $content = $_POST['content'];
                $github_url = $_POST['github_url'];
                $live_url = $_POST['live_url'];
                $status = $_POST['status'];
                $user_id = $_POST['user_id'];
                $technologies = explode(',', $_POST['technologies']);
                $categories = $_POST['categories'] ?? [];
                
                try {
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, slug = ?, description = ?, content = ?, github_url = ?, live_url = ?, technologies = ?, status = ?, user_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$title, $slug, $description, $content, $github_url, $live_url, $technologies, $status, $user_id, $id]);
                    
                    // Mevcut kategorileri sil
                    $stmt = $pdo->prepare("DELETE FROM project_categories_rel WHERE project_id = ?");
                    $stmt->execute([$id]);
                    
                    // Yeni kategorileri ekle
                    foreach ($categories as $category_id) {
                        $stmt = $pdo->prepare("INSERT INTO project_categories_rel (project_id, category_id) VALUES (?, ?)");
                        $stmt->execute([$id, $category_id]);
                    }
                    
                    $message = 'Proje başarıyla güncellendi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                try {
                    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Proje başarıyla silindi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Slug oluşturma fonksiyonu
function createSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    $string = trim($string, '-');
    return $string;
}

// Projeleri listele
try {
    $stmt = $pdo->query("
        SELECT p.*, u.username, 
               STRING_AGG(pc.name, ', ') as categories
        FROM projects p 
        LEFT JOIN users u ON p.user_id = u.id 
        LEFT JOIN project_categories_rel pcr ON p.id = pcr.project_id
        LEFT JOIN project_categories pc ON pcr.category_id = pc.id
        GROUP BY p.id, u.username
        ORDER BY p.created_at DESC
    ");
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {
    $projects = [];
}

// Kullanıcıları listele (form için)
try {
    $stmt = $pdo->query("SELECT id, username FROM users ORDER BY username");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

// Kategorileri listele (form için)
try {
    $stmt = $pdo->query("SELECT id, name FROM project_categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Düzenlenecek proje
$editProject = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.username, 
                   STRING_AGG(pc.name, ', ') as categories,
                   ARRAY_AGG(pcr.category_id) as category_ids
            FROM projects p 
            LEFT JOIN users u ON p.user_id = u.id 
            LEFT JOIN project_categories_rel pcr ON p.id = pcr.project_id
            LEFT JOIN project_categories pc ON pcr.category_id = pc.id
            WHERE p.id = ?
            GROUP BY p.id, u.username
        ");
        $stmt->execute([$_GET['id']]);
        $editProject = $stmt->fetch();
    } catch (PDOException $e) {
        $message = 'Proje bulunamadı.';
    }
}

$page_title = 'Proje Yönetimi - Admin Panel';
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
        
        .project-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .project-card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        
        .status-active { background: var(--success-color); color: white; }
        .status-completed { background: var(--info-color); color: white; }
        .status-archived { background: var(--secondary-color); color: white; }
        
        .category-badge {
            background: var(--primary-gradient);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin: 0.25rem;
            display: inline-block;
        }
        
        .tech-badge {
            background: var(--secondary-gradient);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin: 0.25rem;
            display: inline-block;
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
                        <a href="projects.php" class="admin-nav-link active">
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
                    <h2>Proje Yönetimi</h2>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Yeni Proje
                    </a>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Proje Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?= $action === 'add' ? 'plus' : 'edit' ?> me-2"></i>
                            <?= $action === 'add' ? 'Yeni Proje' : 'Proje Düzenle' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $action ?>">
                            <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $editProject['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Proje Adı</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= $editProject['title'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">Geliştirici</label>
                                        <select class="form-control" id="user_id" name="user_id" required>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" 
                                                    <?= ($editProject['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($user['username']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Kısa Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= $editProject['description'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Detaylı Açıklama</label>
                                <textarea class="form-control" id="content" name="content" rows="10"><?= $editProject['content'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="github_url" class="form-label">GitHub URL</label>
                                        <input type="url" class="form-control" id="github_url" name="github_url" 
                                               value="<?= $editProject['github_url'] ?? '' ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="live_url" class="form-label">Canlı Demo URL</label>
                                        <input type="url" class="form-control" id="live_url" name="live_url" 
                                               value="<?= $editProject['live_url'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Durum</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="active" <?= ($editProject['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="completed" <?= ($editProject['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Tamamlandı</option>
                                            <option value="archived" <?= ($editProject['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arşivlendi</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="technologies" class="form-label">Teknolojiler (virgülle ayırın)</label>
                                        <input type="text" class="form-control" id="technologies" name="technologies" 
                                               value="<?= $editProject['technologies'] ? implode(', ', $editProject['technologies']) : '' ?>" 
                                               placeholder="PHP, JavaScript, PostgreSQL">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Kategoriler</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <?php 
                                    $selected_categories = [];
                                    if ($editProject && $editProject['category_ids']) {
                                        $selected_categories = explode(',', str_replace(['{', '}'], '', $editProject['category_ids']));
                                    }
                                    ?>
                                    <?php foreach ($categories as $category): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]" 
                                               value="<?= $category['id'] ?>" id="cat_<?= $category['id'] ?>"
                                               <?= in_array($category['id'], $selected_categories) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cat_<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['name']) ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Kaydet
                                </button>
                                <a href="projects.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <!-- Proje Listesi -->
                <div class="row g-4">
                    <?php foreach ($projects as $project): ?>
                    <div class="col-lg-6">
                        <div class="project-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($project['title']) ?></h5>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($project['username'] ?? 'Anonim') ?>
                                    </p>
                                </div>
                                <span class="badge status-<?= $project['status'] ?> status-badge">
                                    <?= ucfirst($project['status']) ?>
                                </span>
                            </div>
                            
                            <?php if ($project['description']): ?>
                            <p class="text-muted mb-3"><?= htmlspecialchars(substr($project['description'], 0, 150)) ?>...</p>
                            <?php endif; ?>
                            
                            <?php if ($project['technologies']): ?>
                            <div class="mb-3">
                                <?php foreach ($project['technologies'] as $tech): ?>
                                <span class="tech-badge"><?= htmlspecialchars(trim($tech)) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($project['categories']): ?>
                            <div class="mb-3">
                                <?php foreach (explode(', ', $project['categories']) as $category): ?>
                                <span class="category-badge"><?= htmlspecialchars(trim($category)) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d.m.Y', strtotime($project['created_at'])) ?>
                                </small>
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $project['id'] ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteProject(<?= $project['id'] ?>, '<?= htmlspecialchars($project['title']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteProject(id, title) {
            if (confirm(`"${title}" projesini silmek istediğinizden emin misiniz?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 
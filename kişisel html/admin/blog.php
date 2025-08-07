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

// Blog işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $slug = createSlug($title);
                $content = $_POST['content'];
                $excerpt = $_POST['excerpt'];
                $status = $_POST['status'];
                $user_id = $_POST['user_id'];
                $categories = $_POST['categories'] ?? [];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO blog_posts (user_id, title, slug, content, excerpt, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $title, $slug, $content, $excerpt, $status]);
                    $post_id = $pdo->lastInsertId();
                    
                    // Kategorileri ekle
                    foreach ($categories as $category_id) {
                        $stmt = $pdo->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
                        $stmt->execute([$post_id, $category_id]);
                    }
                    
                    $message = 'Blog yazısı başarıyla eklendi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $slug = createSlug($title);
                $content = $_POST['content'];
                $excerpt = $_POST['excerpt'];
                $status = $_POST['status'];
                $user_id = $_POST['user_id'];
                $categories = $_POST['categories'] ?? [];
                
                try {
                    $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, slug = ?, content = ?, excerpt = ?, status = ?, user_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$title, $slug, $content, $excerpt, $status, $user_id, $id]);
                    
                    // Mevcut kategorileri sil
                    $stmt = $pdo->prepare("DELETE FROM blog_post_categories WHERE post_id = ?");
                    $stmt->execute([$id]);
                    
                    // Yeni kategorileri ekle
                    foreach ($categories as $category_id) {
                        $stmt = $pdo->prepare("INSERT INTO blog_post_categories (post_id, category_id) VALUES (?, ?)");
                        $stmt->execute([$id, $category_id]);
                    }
                    
                    $message = 'Blog yazısı başarıyla güncellendi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                try {
                    $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Blog yazısı başarıyla silindi.';
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

// Blog yazılarını listele
try {
    $stmt = $pdo->query("
        SELECT bp.*, u.username, 
               STRING_AGG(bc.name, ', ') as categories
        FROM blog_posts bp 
        LEFT JOIN users u ON bp.user_id = u.id 
        LEFT JOIN blog_post_categories bpc ON bp.id = bpc.post_id
        LEFT JOIN blog_categories bc ON bpc.category_id = bc.id
        GROUP BY bp.id, u.username
        ORDER BY bp.created_at DESC
    ");
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
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
    $stmt = $pdo->query("SELECT id, name FROM blog_categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Düzenlenecek yazı
$editPost = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT bp.*, u.username, 
                   STRING_AGG(bc.name, ', ') as categories,
                   ARRAY_AGG(bpc.category_id) as category_ids
            FROM blog_posts bp 
            LEFT JOIN users u ON bp.user_id = u.id 
            LEFT JOIN blog_post_categories bpc ON bp.id = bpc.post_id
            LEFT JOIN blog_categories bc ON bpc.category_id = bc.id
            WHERE bp.id = ?
            GROUP BY bp.id, u.username
        ");
        $stmt->execute([$_GET['id']]);
        $editPost = $stmt->fetch();
    } catch (PDOException $e) {
        $message = 'Blog yazısı bulunamadı.';
    }
}

$page_title = 'Blog Yönetimi - Admin Panel';
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
        
        .post-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .post-card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        
        .status-published { background: var(--success-color); color: white; }
        .status-draft { background: var(--warning-color); color: white; }
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
                        <a href="blog.php" class="admin-nav-link active">
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
                    <h2>Blog Yönetimi</h2>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Yeni Yazı
                    </a>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Blog Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?= $action === 'add' ? 'plus' : 'edit' ?> me-2"></i>
                            <?= $action === 'add' ? 'Yeni Blog Yazısı' : 'Blog Yazısı Düzenle' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $action ?>">
                            <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $editPost['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Başlık</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= $editPost['title'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">Yazar</label>
                                        <select class="form-control" id="user_id" name="user_id" required>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" 
                                                    <?= ($editPost['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($user['username']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Özet</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= $editPost['excerpt'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">İçerik</label>
                                <textarea class="form-control" id="content" name="content" rows="15" required><?= $editPost['content'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Durum</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="draft" <?= ($editPost['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Taslak</option>
                                            <option value="published" <?= ($editPost['status'] ?? '') === 'published' ? 'selected' : '' ?>>Yayınlandı</option>
                                            <option value="archived" <?= ($editPost['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arşivlendi</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Kategoriler</label>
                                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                            <?php 
                                            $selected_categories = [];
                                            if ($editPost && $editPost['category_ids']) {
                                                $selected_categories = explode(',', str_replace(['{', '}'], '', $editPost['category_ids']));
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
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Kaydet
                                </button>
                                <a href="blog.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <!-- Blog Listesi -->
                <div class="row g-4">
                    <?php foreach ($posts as $post): ?>
                    <div class="col-lg-6">
                        <div class="post-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($post['title']) ?></h5>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($post['username'] ?? 'Anonim') ?>
                                    </p>
                                </div>
                                <span class="badge status-<?= $post['status'] ?> status-badge">
                                    <?= ucfirst($post['status']) ?>
                                </span>
                            </div>
                            
                            <?php if ($post['excerpt']): ?>
                            <p class="text-muted mb-3"><?= htmlspecialchars(substr($post['excerpt'], 0, 150)) ?>...</p>
                            <?php endif; ?>
                            
                            <?php if ($post['categories']): ?>
                            <div class="mb-3">
                                <?php foreach (explode(', ', $post['categories']) as $category): ?>
                                <span class="category-badge"><?= htmlspecialchars(trim($category)) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                                </small>
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $post['id'] ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deletePost(<?= $post['id'] ?>, '<?= htmlspecialchars($post['title']) ?>')">
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
        function deletePost(id, title) {
            if (confirm(`"${title}" yazısını silmek istediğinizden emin misiniz?`)) {
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
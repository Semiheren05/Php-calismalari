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

// Kod işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $code = $_POST['code'];
                $user_id = $_POST['user_id'];
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO codes (user_id, title, description, code) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user_id, $title, $description, $code]);
                    $code_id = $pdo->lastInsertId();
                    
                    // Etiketleri ekle
                    if (!empty($_POST['tags'])) {
                        $tags = explode(',', $_POST['tags']);
                        foreach ($tags as $tag_name) {
                            $tag_name = trim($tag_name);
                            if (!empty($tag_name)) {
                                // Etiketi ekle veya mevcut olanı al
                                $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?) ON CONFLICT (name) DO NOTHING");
                                $stmt->execute([$tag_name]);
                                
                                $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                                $stmt->execute([$tag_name]);
                                $tag_id = $stmt->fetch()['id'];
                                
                                // Kod-etiket ilişkisini ekle
                                $stmt = $pdo->prepare("INSERT INTO code_tags (code_id, tag_id) VALUES (?, ?)");
                                $stmt->execute([$code_id, $tag_id]);
                            }
                        }
                    }
                    
                    $message = 'Kod başarıyla eklendi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $title = $_POST['title'];
                $description = $_POST['description'];
                $code = $_POST['code'];
                $user_id = $_POST['user_id'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE codes SET title = ?, description = ?, code = ?, user_id = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $code, $user_id, $id]);
                    
                    // Mevcut etiketleri sil
                    $stmt = $pdo->prepare("DELETE FROM code_tags WHERE code_id = ?");
                    $stmt->execute([$id]);
                    
                    // Yeni etiketleri ekle
                    if (!empty($_POST['tags'])) {
                        $tags = explode(',', $_POST['tags']);
                        foreach ($tags as $tag_name) {
                            $tag_name = trim($tag_name);
                            if (!empty($tag_name)) {
                                $stmt = $pdo->prepare("INSERT INTO tags (name) VALUES (?) ON CONFLICT (name) DO NOTHING");
                                $stmt->execute([$tag_name]);
                                
                                $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                                $stmt->execute([$tag_name]);
                                $tag_id = $stmt->fetch()['id'];
                                
                                $stmt = $pdo->prepare("INSERT INTO code_tags (code_id, tag_id) VALUES (?, ?)");
                                $stmt->execute([$id, $tag_id]);
                            }
                        }
                    }
                    
                    $message = 'Kod başarıyla güncellendi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                try {
                    $stmt = $pdo->prepare("DELETE FROM codes WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Kod başarıyla silindi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Kodları listele
try {
    $stmt = $pdo->query("
        SELECT c.*, u.username, 
               STRING_AGG(t.name, ', ') as tags
        FROM codes c 
        LEFT JOIN users u ON c.user_id = u.id 
        LEFT JOIN code_tags ct ON c.id = ct.code_id
        LEFT JOIN tags t ON ct.tag_id = t.id
        GROUP BY c.id, u.username
        ORDER BY c.created_at DESC
    ");
    $codes = $stmt->fetchAll();
} catch (PDOException $e) {
    $codes = [];
}

// Kullanıcıları listele (form için)
try {
    $stmt = $pdo->query("SELECT id, username FROM users ORDER BY username");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

// Düzenlenecek kod
$editCode = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, u.username, 
                   STRING_AGG(t.name, ', ') as tags
            FROM codes c 
            LEFT JOIN users u ON c.user_id = u.id 
            LEFT JOIN code_tags ct ON c.id = ct.code_id
            LEFT JOIN tags t ON ct.tag_id = t.id
            WHERE c.id = ?
            GROUP BY c.id, u.username
        ");
        $stmt->execute([$_GET['id']]);
        $editCode = $stmt->fetch();
    } catch (PDOException $e) {
        $message = 'Kod bulunamadı.';
    }
}

$page_title = 'Kod Yönetimi - Admin Panel';
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
        
        .code-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .code-card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }
        
        .code-preview {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 1rem;
            border-radius: 8px;
            font-family: "Consolas", "Monaco", "Courier New", monospace;
            font-size: 0.9rem;
            max-height: 150px;
            overflow: hidden;
        }
        
        .tag-badge {
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
                        <a href="codes.php" class="admin-nav-link active">
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
                    <h2>Kod Yönetimi</h2>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Yeni Kod
                    </a>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Kod Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?= $action === 'add' ? 'plus' : 'edit' ?> me-2"></i>
                            <?= $action === 'add' ? 'Yeni Kod Ekle' : 'Kod Düzenle' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $action ?>">
                            <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $editCode['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Başlık</label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= $editCode['title'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">Kullanıcı</label>
                                        <select class="form-control" id="user_id" name="user_id" required>
                                            <?php foreach ($users as $user): ?>
                                            <option value="<?= $user['id'] ?>" 
                                                    <?= ($editCode['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($user['username']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= $editCode['description'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="code" class="form-label">Kod</label>
                                <textarea class="form-control" id="code" name="code" rows="10" required><?= $editCode['code'] ?? '' ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tags" class="form-label">Etiketler (virgülle ayırın)</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                       value="<?= $editCode['tags'] ?? '' ?>" 
                                       placeholder="PHP, JavaScript, Algoritma">
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Kaydet
                                </button>
                                <a href="codes.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <!-- Kod Listesi -->
                <div class="row g-4">
                    <?php foreach ($codes as $code): ?>
                    <div class="col-lg-6">
                        <div class="code-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($code['title']) ?></h5>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($code['username'] ?? 'Anonim') ?>
                                    </p>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $code['id'] ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteCode(<?= $code['id'] ?>, '<?= htmlspecialchars($code['title']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <?php if ($code['description']): ?>
                            <p class="text-muted mb-3"><?= htmlspecialchars(substr($code['description'], 0, 100)) ?>...</p>
                            <?php endif; ?>
                            
                            <div class="code-preview mb-3">
                                <pre><code><?= htmlspecialchars(substr($code['code'], 0, 200)) ?>...</code></pre>
                            </div>
                            
                            <?php if ($code['tags']): ?>
                            <div class="mb-3">
                                <?php foreach (explode(', ', $code['tags']) as $tag): ?>
                                <span class="tag-badge"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d.m.Y', strtotime($code['created_at'])) ?>
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i>
                                    <?= $code['views'] ?> görüntüleme
                                </small>
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
        function deleteCode(id, title) {
            if (confirm(`"${title}" kodunu silmek istediğinizden emin misiniz?`)) {
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
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

// Kullanıcı işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = $_POST['username'];
                $email = $_POST['email'];
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $role = $_POST['role'];
                $bio = $_POST['bio'] ?? '';
                
                try {
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, bio) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $password, $role, $bio]);
                    $message = 'Kullanıcı başarıyla eklendi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $username = $_POST['username'];
                $email = $_POST['email'];
                $role = $_POST['role'];
                $bio = $_POST['bio'] ?? '';
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                try {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, bio = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $role, $bio, $is_active, $id]);
                    
                    if (!empty($_POST['password'])) {
                        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$password, $id]);
                    }
                    
                    $message = 'Kullanıcı başarıyla güncellendi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Kullanıcı başarıyla silindi.';
                } catch (PDOException $e) {
                    $message = 'Hata: ' . $e->getMessage();
                }
                break;
        }
    }
}

// Kullanıcıları listele
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

// Düzenlenecek kullanıcı
$editUser = null;
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $editUser = $stmt->fetch();
    } catch (PDOException $e) {
        $message = 'Kullanıcı bulunamadı.';
    }
}

$page_title = 'Kullanıcı Yönetimi - Admin Panel';
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
        
        .user-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .user-card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }
        
        .role-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        
        .role-admin { background: var(--danger-color); color: white; }
        .role-moderator { background: var(--warning-color); color: white; }
        .role-user { background: var(--success-color); color: white; }
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
                        <a href="users.php" class="admin-nav-link active">
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
                    <h2>Kullanıcı Yönetimi</h2>
                    <a href="?action=add" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Yeni Kullanıcı
                    </a>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if ($action === 'add' || $action === 'edit'): ?>
                <!-- Kullanıcı Formu -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?= $action === 'add' ? 'user-plus' : 'user-edit' ?> me-2"></i>
                            <?= $action === 'add' ? 'Yeni Kullanıcı Ekle' : 'Kullanıcı Düzenle' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?= $action ?>">
                            <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Kullanıcı Adı</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= $editUser['username'] ?? '' ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">E-posta</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= $editUser['email'] ?? '' ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            Şifre <?= $action === 'edit' ? '(Boş bırakın değiştirmek istemiyorsanız)' : '' ?>
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               <?= $action === 'add' ? 'required' : '' ?>>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Rol</label>
                                        <select class="form-control" id="role" name="role" required>
                                            <option value="user" <?= ($editUser['role'] ?? '') === 'user' ? 'selected' : '' ?>>Kullanıcı</option>
                                            <option value="moderator" <?= ($editUser['role'] ?? '') === 'moderator' ? 'selected' : '' ?>>Moderatör</option>
                                            <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label">Hakkında</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3"><?= $editUser['bio'] ?? '' ?></textarea>
                            </div>
                            
                            <?php if ($action === 'edit'): ?>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           <?= ($editUser['is_active'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        Aktif Kullanıcı
                                    </label>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Kaydet
                                </button>
                                <a href="users.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>İptal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <!-- Kullanıcı Listesi -->
                <div class="row g-4">
                    <?php foreach ($users as $user): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="user-card p-3">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($user['username']) ?></h5>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                                <span class="badge role-<?= $user['role'] ?> role-badge">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </div>
                            
                            <?php if ($user['bio']): ?>
                            <p class="text-muted small mb-3"><?= htmlspecialchars(substr($user['bio'], 0, 100)) ?>...</p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                                </small>
                                <div class="btn-group btn-group-sm">
                                    <a href="?action=edit&id=<?= $user['id'] ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
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
        function deleteUser(id, username) {
            if (confirm(`"${username}" kullanıcısını silmek istediğinizden emin misiniz?`)) {
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
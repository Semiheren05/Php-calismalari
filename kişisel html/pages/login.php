<?php
session_start();

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Basit validasyon
    if (empty($email) || empty($password)) {
        $error = 'E-posta ve şifre alanları zorunludur!';
    } else {
        try {
            require_once '../includes/db.php';
            
            // Kullanıcıyı bul (is_active kontrolü olmadan)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Şifre kontrolü
                if (password_verify($password, $user['password'])) {
                    // Kullanıcı aktif mi kontrol et
                    if ($user['is_active'] !== false) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];
                        
                        // Admin ise admin paneline yönlendir
                        if ($user['role'] === 'admin') {
                            header('Location: ../admin/index.php');
                        } else {
                            header('Location: index.php');
                        }
                        exit();
                    } else {
                        $error = 'Hesabınız aktif değil. Lütfen yönetici ile iletişime geçin.';
                    }
                } else {
                    $error = 'E-posta veya şifre hatalı!';
                }
            } else {
                $error = 'Bu e-posta adresi ile kayıtlı kullanıcı bulunamadı.';
            }
        } catch (PDOException $e) {
            $error = 'Veritabanı bağlantı hatası: ' . $e->getMessage();
        } catch (Exception $e) {
            $error = 'Beklenmeyen bir hata oluştu: ' . $e->getMessage();
        }
    }
}

$page_title = 'Giriş Yap - SemihHub';
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
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="row g-0">
                <!-- Sol Taraf -->
                <div class="col-lg-6 auth-card-left">
                    <div class="auth-icon">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <h2 class="auth-title">Tekrar Hoş Geldiniz!</h2>
                    <p class="auth-subtitle">
                        Hesabınıza giriş yapın ve kodlarınızı paylaşmaya devam edin.
                    </p>
                    <ul class="auth-features">
                        <li>Kodlarınızı paylaşın ve tartışın</li>
                        <li>Diğer geliştiricilerle etkileşime geçin</li>
                        <li>Blog yazılarını okuyun ve yazın</li>
                        <li>Projelerinizi sergileyin</li>
                    </ul>
                    <a href="register.php" class="btn btn-outline-light">
                        <i class="fas fa-user-plus me-2"></i>Hesap Oluştur
                    </a>
                </div>
                
                <!-- Sağ Taraf -->
                <div class="col-lg-6 auth-card-right">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Giriş Yap</h3>
                        <p class="text-muted">Hesabınıza erişim sağlayın</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="fade-in">
                        <div class="form-group">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Hesabınız yok mu? 
                            <a href="register.php" class="text-primary text-decoration-none">Kayıt olun</a>
                        </p>
                    </div>
                    
                    <!-- Demo Giriş Bilgileri -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-2">Demo Giriş Bilgileri:</h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Admin:</small><br>
                                <small>admin@kodforum.com</small><br>
                                <small>password</small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Kullanıcı:</small><br>
                                <small>semih@example.com</small><br>
                                <small>password</small>
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
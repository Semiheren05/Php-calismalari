<?php
session_start();

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/db.php';
    $username = trim($_POST['username'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $errors = [];

    // Validasyon
    if (strlen($username) < 3) $errors[] = 'Kullanıcı adı en az 3 karakter olmalı.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli bir e-posta girin!';
    if (strlen($password) < 6) $errors[] = 'Şifre en az 6 karakter olmalı!';
    if ($password !== $password2) $errors[] = 'Şifreler eşleşmiyor!';

    // E-posta ve kullanıcı adı benzersiz mi?
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) $errors[] = 'Bu e-posta adresi veya kullanıcı adı zaten kullanılıyor!';

    if ($errors) {
        $error = implode('<br>', $errors);
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // 'role' alanı varsa ekle, yoksa sadece username, email, password ile kayıt yap
            $columns = "username, email, password";
            $placeholders = "?, ?, ?";
            $values = [$username, $email, $hashed_password];
            if (isset($pdo) && $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='users' AND column_name='role'")->fetch()) {
                $columns .= ", role";
                $placeholders .= ", ?";
                $values[] = 'user';
            }
            $stmt = $pdo->prepare("INSERT INTO users ($columns) VALUES ($placeholders)");
            $stmt->execute($values);
            $success = 'Hesabınız başarıyla oluşturuldu! Şimdi giriş yapabilirsiniz.';
        } catch (PDOException $e) {
            $error = 'Kayıt sırasında bir hata oluştu: ' . $e->getMessage();
        }
    }
}

$page_title = 'Kayıt Ol - SemihHub';
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
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2 class="auth-title">Hesap Oluşturun!</h2>
                    <p class="auth-subtitle">
                        Kişisel web siteme katılarak projelerimi ve blog yazılarımı takip edebilirsiniz.
                    </p>
                    <ul class="auth-features">
                        <li>Ücretsiz hesap oluşturun</li>
                        <li>Projelerimi ve yazılarımı takip edin</li>
                        <li>Güncellemelerden haberdar olun</li>
                    </ul>
                    <a href="login.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-in-alt me-2"></i>Zaten Hesabım Var
                    </a>
                </div>
                <!-- Sağ Taraf -->
                <div class="col-lg-6 auth-card-right">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">Hesap Oluştur</h3>
                        <p class="text-muted">Hemen ücretsiz hesap oluşturun ve başlayın</p>
                    </div>
                    <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?= $success ?>
                        <br><br>
                        <a href="login.php" class="btn btn-success">
                            <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if (!$success): ?>
                    <form method="POST" class="fade-in">
                        <div class="form-group">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                            <small class="form-text text-muted">Benzersiz bir kullanıcı adı seçin</small>
                        </div>
                        <div class="form-group">
                            <label for="email" class="form-label">E-posta Adresi</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" class="form-control" id="password" name="password" required oninput="checkPasswordStrength()">
                            <small class="form-text text-muted">En az 6 karakter olmalıdır</small>
                            <div id="password-strength" class="mt-2"></div>
                        </div>
                        <div class="form-group">
                            <label for="password2" class="form-label">Şifre Tekrar</label>
                            <input type="password" class="form-control" id="password2" name="password2" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus me-2"></i>Hesap Oluştur
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Zaten hesabınız var mı? 
                            <a href="login.php" class="text-primary text-decoration-none">Giriş yapın</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthDiv = document.getElementById('password-strength');
    let strength = 0;
    let msg = '';
    let color = '';
    if (password.length === 0) {
        strengthDiv.innerHTML = '';
        return;
    }
    if (password.length >= 6) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    if (strength <= 1) {
        msg = 'Güvensiz şifre';
        color = '#e74c3c';
    } else if (strength === 2) {
        msg = 'Zayıf şifre';
        color = '#f39c12';
    } else if (strength >= 3) {
        msg = 'Güçlü şifre';
        color = '#27ae60';
    }
    strengthDiv.innerHTML = `<span style="color:${color}; font-weight:600;">${msg}</span>`;
}
</script>
</body>
</html> 
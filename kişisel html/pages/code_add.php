<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
if (!is_logged_in()) {
    header('Location: login.php?redirect=code_add');
    exit;
}
require_once 'db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $user_id = 1; // Demo: Giriş yapan kullanıcı id'si (geliştirilebilir)

    if (!$title || !$code) {
        $error = 'Başlık ve kod alanı zorunludur!';
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO codes (user_id, title, description, code) VALUES (?, ?, ?, ?) RETURNING id");
            $stmt->execute([$user_id, $title, $description, $code]);
            $code_id = $stmt->fetchColumn();

            // Etiket işlemleri
            if ($tags) {
                $tagArr = array_filter(array_map('trim', explode(',', $tags)));
                foreach ($tagArr as $tag) {
                    // Etiket var mı kontrol et, yoksa ekle
                    $tagStmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                    $tagStmt->execute([$tag]);
                    $tag_id = $tagStmt->fetchColumn();
                    if (!$tag_id) {
                        $pdo->prepare("INSERT INTO tags (name) VALUES (?)")->execute([$tag]);
                        $tag_id = $pdo->lastInsertId('tags_id_seq');
                    }
                    // code_tags tablosuna ekle
                    $pdo->prepare("INSERT INTO code_tags (code_id, tag_id) VALUES (?, ?)")->execute([$code_id, $tag_id]);
                }
            }
            $pdo->commit();
            $success = 'Kod başarıyla eklendi!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kod Ekle - SemihHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container py-5 mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="h4 fw-bold mb-4"><i class="fas fa-plus me-2"></i>Kod Ekle</h2>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="title" class="form-label">Başlık <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="code" class="form-label">Kod <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="code" name="code" rows="7" required><?= htmlspecialchars($_POST['code'] ?? '') ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="tags" class="form-label">Etiketler <small class="text-muted">(virgülle ayırın)</small></label>
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="PHP, Algoritma, JavaScript" value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>">
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="codes.php" class="btn btn-outline-secondary">İptal</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Ekle</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
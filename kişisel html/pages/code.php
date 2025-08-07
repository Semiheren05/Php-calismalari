<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once 'db.php';

$code_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$code_id) {
    header('Location: codes.php');
    exit;
}

// Kod detayını çek
$stmt = $pdo->prepare("SELECT c.*, u.username as author FROM codes c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$stmt->execute([$code_id]);
$code = $stmt->fetch();
if (!$code) {
    echo '<div class="container py-5"><div class="alert alert-danger">Kod bulunamadı.</div></div>';
    exit;
}

// Etiketleri çek
$tagStmt = $pdo->prepare("SELECT t.name FROM code_tags ct JOIN tags t ON ct.tag_id = t.id WHERE ct.code_id = ?");
$tagStmt->execute([$code_id]);
$tags = $tagStmt->fetchAll(PDO::FETCH_COLUMN);

// Oyları çek
$voteStmt = $pdo->prepare("SELECT SUM(vote) as total FROM votes WHERE code_id = ?");
$voteStmt->execute([$code_id]);
$total_votes = (int)($voteStmt->fetchColumn() ?? 0);

// Yorumları çek
$commentStmt = $pdo->prepare("SELECT c.*, u.username FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.code_id = ? ORDER BY c.created_at ASC");
$commentStmt->execute([$code_id]);
$comments = $commentStmt->fetchAll();

// Yorum ekleme işlemi
$comment_success = '';
$comment_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=code.php?id=' . $code_id);
        exit;
    }
    $content = trim($_POST['comment_content']);
    $user_id = $_SESSION['user_id']; // Giriş yapan kullanıcı id'si
    if ($content) {
        $stmt = $pdo->prepare("INSERT INTO comments (code_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$code_id, $user_id, $content]);
        header('Location: code.php?id=' . $code_id . '#comments');
        exit;
    } else {
        $comment_error = 'Yorum boş olamaz!';
    }
}

// Oylama işlemi
if (isset($_GET['vote']) && in_array($_GET['vote'], ['up', 'down'])) {
    if (!is_logged_in()) {
        header('Location: login.php?redirect=code.php?id=' . $code_id);
        exit;
    }
    $vote = $_GET['vote'] === 'up' ? 1 : -1;
    $user_id = $_SESSION['user_id']; // Giriş yapan kullanıcı id'si
    // Kullanıcı daha önce oy verdiyse güncelle, yoksa ekle
    $voteCheck = $pdo->prepare("SELECT id FROM votes WHERE code_id = ? AND user_id = ?");
    $voteCheck->execute([$code_id, $user_id]);
    if ($voteCheck->fetch()) {
        $pdo->prepare("UPDATE votes SET vote = ? WHERE code_id = ? AND user_id = ?")->execute([$vote, $code_id, $user_id]);
    } else {
        $pdo->prepare("INSERT INTO votes (code_id, user_id, vote) VALUES (?, ?, ?)")->execute([$code_id, $user_id, $vote]);
    }
    header('Location: code.php?id=' . $code_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($code['title']) ?> - SemihHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h4 fw-bold mb-0"><?= htmlspecialchars($code['title']) ?></h2>
                        <span class="badge bg-secondary">#<?= htmlspecialchars($code['id']) ?></span>
                    </div>
                    <p class="mb-2 text-muted small">
                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($code['author'] ?? 'Anonim') ?> ·
                        <i class="fas fa-clock me-1"></i><?= date('d M Y H:i', strtotime($code['created_at'])) ?>
                    </p>
                    <p class="mb-2"><?= nl2br(htmlspecialchars($code['description'])) ?></p>
                    <pre><code><?= htmlspecialchars($code['code']) ?></code></pre>
                    <div class="mb-2">
                        <?php foreach ($tags as $tag): ?>
                            <span class="badge bg-info text-dark me-1">#<?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-3">
                        <form method="get" action="" class="d-inline">
                            <input type="hidden" name="id" value="<?= $code_id ?>">
                            <button type="submit" name="vote" value="up" class="btn btn-outline-success btn-sm"><i class="fas fa-arrow-up"></i></button>
                            <button type="submit" name="vote" value="down" class="btn btn-outline-danger btn-sm"><i class="fas fa-arrow-down"></i></button>
                        </form>
                        <span class="fw-bold">Toplam Oy: <?= $total_votes ?></span>
                    </div>
                </div>
            </div>
            <!-- Yorumlar -->
            <div class="card mb-4" id="comments">
                <div class="card-header bg-light fw-bold"><i class="fas fa-comments me-2"></i>Yorumlar</div>
                <div class="card-body">
                    <?php if ($comments): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="fw-bold me-2"><i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($comment['username'] ?? 'Anonim') ?></span>
                                    <span class="text-muted small ms-2"><i class="fas fa-clock me-1"></i><?= date('d M Y H:i', strtotime($comment['created_at'])) ?></span>
                                </div>
                                <div class="bg-light rounded p-2">
                                    <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Henüz yorum yok. İlk yorumu sen yap!</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Yorum Ekle -->
            <div class="card mb-4">
                <div class="card-header bg-light fw-bold"><i class="fas fa-plus me-2"></i>Yorum Ekle</div>
                <div class="card-body">
                    <?php if ($comment_error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($comment_error) ?></div>
                    <?php endif; ?>
                    <form method="post" action="#comments">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment_content" rows="3" required placeholder="Yorumunuzu yazın..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Gönder</button>
                        </div>
                    </form>
                </div>
            </div>
            <a href="codes.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Tüm Kodlara Dön</a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
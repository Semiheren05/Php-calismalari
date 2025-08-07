<?php
session_start();
require_once '../includes/db.php';

// URL'den proje slug'ını al
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: projects.php");
    exit();
}

// Proje detaylarını veritabanından çek
try {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username
        FROM projects p
        JOIN users u ON p.user_id = u.id
        WHERE p.slug = :slug AND p.status = 'active'
    ");
    $stmt->execute([':slug' => $slug]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Proje bulunamazsa
if (!$project) {
    http_response_code(404);
    $page_title = "Proje Bulunamadı";
    include '../includes/header.php';
    echo '<div class="container my-5 text-center"><div class="alert alert-danger">Aradığınız proje bulunamadı veya yayında değil.</div></div>';
    include '../includes/footer.php';
    exit();
}

$page_title = htmlspecialchars($project['title']);
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <?php if (!empty($project['image_url'])): ?>
                    <img src="../<?= htmlspecialchars($project['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($project['title']) ?>">
                <?php endif; ?>
                <div class="card-body p-4">
                    <h1 class="card-title h2 mb-3"><?= htmlspecialchars($project['title']) ?></h1>
                    
                    <div class="d-flex align-items-center text-muted mb-4">
                        <i class="fas fa-user-circle fa-fw me-2"></i>
                        <span><a href="profile.php?username=<?= htmlspecialchars($project['username']) ?>" class="text-decoration-none text-muted"><?= htmlspecialchars($project['username']) ?></a></span>
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar-alt fa-fw me-2"></i>
                        <span><?= date('d F Y', strtotime($project['created_at'])) ?></span>
                    </div>

                    <div class="project-content">
                        <p class="lead"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="projects.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Tüm Projelere Dön</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

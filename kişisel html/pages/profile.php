<?php
session_start();
require_once '../includes/db.php';

// URL'den kullanıcı adını al
$username = $_GET['username'] ?? '';

if (empty($username)) {
    header("Location: projects.php");
    exit();
}

// Kullanıcı bilgilerini ve projelerini veritabanından çek
try {
    // Kullanıcıyı bul
    $stmt_user = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt_user->execute([':username' => $username]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Kullanıcının projelerini çek
        $stmt_projects = $pdo->prepare("SELECT * FROM projects WHERE user_id = :user_id AND status = 'active' ORDER BY created_at DESC");
        $stmt_projects->execute([':user_id' => $user['id']]);
        $projects = $stmt_projects->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $projects = [];
    }
} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// Kullanıcı bulunamazsa
if (!$user) {
    http_response_code(404);
    $page_title = "Kullanıcı Bulunamadı";
    include '../includes/header.php';
    echo '<div class="container my-5 text-center"><div class="alert alert-danger">Aradığınız kullanıcı bulunamadı.</div></div>';
    include '../includes/footer.php';
    exit();
}

$page_title = htmlspecialchars($user['username']) . " Profili";
include '../includes/header.php';
?>

<div class="container my-5">
    <!-- Profil Başlığı -->
    <div class="row mb-4">
        <div class="col-md-8 mx-auto text-center">
            <i class="fas fa-user-circle fa-4x text-muted mb-3"></i>
            <h1 class="display-5"><?= htmlspecialchars($user['username']) ?></h1>
            <p class="text-muted">Topluluğa Katılım: <?= date('F Y', strtotime($user['created_at'])) ?></p>
        </div>
    </div>

    <!-- Kullanıcının Projeleri -->
    <div class="row">
        <div class="col-md-10 mx-auto">
            <h3 class="mb-4">Paylaşılan Projeler (<?= count($projects) ?>)</h3>
            <hr>
            <?php if (empty($projects)): ?>
                <div class="card card-body text-center text-muted">
                    <p class="mb-0"><?= htmlspecialchars($user['username']) ?> henüz bir proje paylaşmadı.</p>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="card mb-3 shadow-sm">
                        <?php if (!empty($project['image_url'])): ?>
                            <img src="../<?= htmlspecialchars($project['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($project['title']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="project_detail.php?slug=<?= htmlspecialchars($project['slug']) ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($project['title']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted"><?= nl2br(htmlspecialchars(substr($project['description'], 0, 150))) . (strlen($project['description']) > 150 ? '...' : '') ?></p>
                            <small class="text-muted">Paylaşım Tarihi: <?= date('d F Y', strtotime($project['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
                    <img src="https://via.placeholder.com/120x120/007bff/ffffff?text=<?= htmlspecialchars(substr($_SESSION['username'] ?? '', 0, 1)) ?>" 
                         class="rounded-circle mb-3" alt="Profile">
                    <h5 class="card-title"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></h5>
                    <p class="text-muted"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></p>
                    <div class="d-grid">
                        <a href="logout.php" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap
                        </a>
                    </div>
                </div>
            </div>
            <!-- Navigation Tabs -->
            <div class="card mt-3">
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="profile.php" class="list-group-item list-group-item-action <?= $active_tab === 'profile' ? 'active' : '' ?>">
                            <i class="fas fa-user me-2"></i>Profil Bilgileri
                        </a>
                        <a href="profile.php?tab=settings" class="list-group-item list-group-item-action <?= $active_tab === 'settings' ? 'active' : '' ?>">
                            <i class="fas fa-cog me-2"></i>Hesap Ayarları
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main Content -->
        <div class="col-lg-9">
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <!-- Profile Information Tab -->
            <?php if ($active_tab === 'profile'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Profil Bilgileri</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Ad Soyad</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($_SESSION['full_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="birthdate" class="form-label">Doğum Tarihi</label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?= htmlspecialchars($_SESSION['birthdate'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">İl</label>
                                <input type="text" class="form-control" id="city" name="city" list="iller" value="<?= htmlspecialchars($_SESSION['city'] ?? '') ?>" autocomplete="off" required>
                                <datalist id="iller"></datalist>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="district" class="form-label">İlçe</label>
                                <input type="text" class="form-control" id="district" name="district" list="ilceler" value="<?= htmlspecialchars($_SESSION['district'] ?? '') ?>" autocomplete="off" required>
                                <datalist id="ilceler"></datalist>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Web Sitesi</label>
                                <input type="url" class="form-control" id="website" name="website" value="<?= htmlspecialchars($_SESSION['website'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profile_image" class="form-label">Profil Fotoğrafı (URL)</label>
                                <input type="url" class="form-control" id="profile_image" name="profile_image" value="<?= htmlspecialchars($_SESSION['profile_image'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="twitter" class="form-label">Twitter</label>
                                <input type="text" class="form-control" id="twitter" name="twitter" value="<?= htmlspecialchars($_SESSION['twitter'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="instagram" class="form-label">Instagram</label>
                                <input type="text" class="form-control" id="instagram" name="instagram" value="<?= htmlspecialchars($_SESSION['instagram'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="linkedin" class="form-label">LinkedIn</label>
                                <input type="text" class="form-control" id="linkedin" name="linkedin" value="<?= htmlspecialchars($_SESSION['linkedin'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required disabled readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">Hakkımda</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Kendiniz hakkında kısa bir açıklama yazın..."><?= htmlspecialchars($_SESSION['bio'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Değişiklikleri Kaydet
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <!-- Settings Tab -->
            <?php if ($active_tab === 'settings'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Hesap Ayarları</h5>
                </div>
                <div class="card-body">
                    <!-- Change Password Form -->
                    <form method="POST" action="" class="mb-4">
                        <input type="hidden" name="action" value="change_password">
                        <h6 class="mb-3">Şifre Değiştir</h6>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Mevcut Şifre</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Yeni Şifre</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">En az 6 karakter olmalıdır</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Yeni Şifre Tekrar</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key me-2"></i>Şifreyi Değiştir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Türkiye il ve ilçe verisi (kısa örnek, tam liste aşağıda eklenecek)
const cityDistricts = {
    'Adana': ['Seyhan', 'Çukurova', 'Yüreğir', 'Ceyhan', 'Sarıçam', 'Kozan', 'İmamoğlu', 'Karaisalı', 'Karataş', 'Pozantı', 'Saimbeyli', 'Tufanbeyli', 'Feke', 'Aladağ'],
    'Adıyaman': ['Merkez', 'Besni', 'Kahta', 'Gölbaşı', 'Gerger', 'Samsat', 'Sincik', 'Tut', 'Çelikhan'],
    'Afyonkarahisar': ['Merkez', 'Bolvadin', 'Dinar', 'Emirdağ', 'Sandıklı', 'Sinanpaşa', 'Şuhut', 'Çay', 'İscehisar', 'İhsaniye', 'Bayat', 'Başmakçı', 'Çobanlar', 'Evciler', 'Hocalar', 'Kızılören', 'Sultandağı', 'Dazkırı'],
    'Ağrı': ['Merkez', 'Doğubayazıt', 'Patnos', 'Diyadin', 'Eleşkirt', 'Taşlıçay', 'Tutak', 'Hamur'],
    'Amasya': ['Merkez', 'Merzifon', 'Suluova', 'Taşova', 'Göynücek', 'Gümüşhacıköy', 'Hamamözü'],
    'Ankara': ['Çankaya', 'Keçiören', 'Yenimahalle', 'Mamak', 'Etimesgut', 'Sincan', 'Altındağ', 'Pursaklar', 'Gölbaşı', 'Polatlı', 'Kahramankazan', 'Beypazarı', 'Elmadağ', 'Ayaş', 'Akyurt', 'Çubuk', 'Evren', 'Haymana', 'Kalecik', 'Kızılcahamam', 'Nallıhan', 'Şereflikoçhisar'],
    'Antalya': ['Muratpaşa', 'Kepez', 'Alanya', 'Manavgat', 'Konyaaltı', 'Aksu', 'Döşemealtı', 'Kemer', 'Serik', 'Kumluca', 'Finike', 'Gazipaşa', 'Elmalı', 'Demre', 'Kaş', 'Korkuteli', 'İbradı', 'Akseki', 'Gündoğmuş'],
    'İstanbul': ['Kadıköy', 'Beşiktaş', 'Üsküdar', 'Bakırköy', 'Şişli', 'Fatih', 'Beyoğlu', 'Ataşehir', 'Maltepe', 'Kartal', 'Pendik', 'Tuzla', 'Sancaktepe', 'Sultanbeyli', 'Çekmeköy', 'Ümraniye', 'Beykoz', 'Sarıyer', 'Eyüpsultan', 'Kağıthane', 'Bayrampaşa', 'Gaziosmanpaşa', 'Esenler', 'Bağcılar', 'Bahçelievler', 'Güngören', 'Zeytinburnu', 'Avcılar', 'Beylikdüzü', 'Esenyurt', 'Küçükçekmece', 'Başakşehir', 'Arnavutköy', 'Silivri', 'Çatalca', 'Şile', 'Adalar'],
    'İzmir': ['Konak', 'Bornova', 'Karşıyaka', 'Buca', 'Bayraklı', 'Balçova', 'Çiğli', 'Gaziemir', 'Güzelbahçe', 'Karabağlar', 'Narlıdere', 'Menemen', 'Aliağa', 'Bergama', 'Dikili', 'Foça', 'Kınık', 'Menderes', 'Ödemiş', 'Seferihisar', 'Selçuk', 'Tire', 'Torbalı', 'Urla', 'Beydağ', 'Bayındır', 'Kemalpaşa', 'Kiraz'],
    // ... TÜM İLLER VE İLÇELERİNİ BURAYA EKLEYİN ...
};
// Tüm illeri datalist'e ekle
const illerDatalist = document.getElementById('iller');
Object.keys(cityDistricts).forEach(function(city) {
    const opt = document.createElement('option');
    opt.value = city;
    illerDatalist.appendChild(opt);
});
// İl seçilince ilçeleri güncelle
const cityInput = document.getElementById('city');
const districtInput = document.getElementById('district');
const ilcelerDatalist = document.getElementById('ilceler');
function updateDistricts() {
    const city = cityInput.value;
    ilcelerDatalist.innerHTML = '';
    if (cityDistricts[city]) {
        cityDistricts[city].forEach(function(d) {
            const opt = document.createElement('option');
            opt.value = d;
            ilcelerDatalist.appendChild(opt);
        });
    }
}
cityInput.addEventListener('input', updateDistricts);
// Sayfa yüklendiğinde de ilçeleri güncelle (varsa)
window.addEventListener('DOMContentLoaded', updateDistricts);
</script>

<?php
include '../includes/footer.php';
?> 
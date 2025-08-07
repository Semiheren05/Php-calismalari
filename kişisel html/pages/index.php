<?php
$page_title = 'SemihHub - Kişisel Web Sitesi';
$additional_css = '
.hero-section .display-4 {
  background: linear-gradient(90deg, #fff 30%, #e0e0ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 800;
  text-shadow: 0 2px 8px rgba(102,126,234,0.10);
}
.hero-section .lead {
  color: #f8f9fa;
  text-shadow: 0 2px 8px rgba(102,126,234,0.10);
}
.hero-section .btn-light {
  color: #667eea;
  background: #fff;
  border: none;
  font-weight: 600;
}
.hero-section .btn-light:hover {
  background: #f8f9fa;
  color: #764ba2;
}
.hero-section .btn-outline-light {
  color: #fff;
  border-color: #fff;
  font-weight: 600;
}
.hero-section .btn-outline-light:hover {
  background: #fff;
  color: #667eea;
}
';
include '../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center" style="min-height: 80vh; background: var(--primary-gradient); color: #fff;">
  <div class="container text-center">
    <img src="https://www.gravatar.com/avatar/?d=mp" alt="Profil" class="rounded-circle mb-4" width="120" height="120">
    <h1 class="display-4 fw-bold mb-3">SemihHub</h1>
    <p class="lead mb-4">Web Geliştirici & Yazılım Tutkunu<br>Kişisel projelerimi ve blog yazılarımı burada paylaşıyorum.</p>
    <div class="d-flex justify-content-center gap-3 mb-4">
      <a href="https://github.com/" class="btn btn-light btn-lg" target="_blank"><i class="fab fa-github me-2"></i>GitHub</a>
      <a href="https://linkedin.com/" class="btn btn-outline-light btn-lg" target="_blank"><i class="fab fa-linkedin me-2"></i>LinkedIn</a>
      <a href="mailto:ornek@mail.com" class="btn btn-outline-light btn-lg"><i class="fas fa-envelope me-2"></i>E-posta</a>
    </div>
  </div>
</section>

<!-- Projeler Bölümü -->
<section class="py-5" style="background: #fff;">
  <div class="container">
    <h2 class="h2 fw-bold text-center mb-5">Öne Çıkan Projeler</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Proje 1</h5>
            <p class="card-text">Kısa proje açıklaması. Modern web teknolojileriyle geliştirilmiştir.</p>
            <a href="#" class="btn btn-primary">Detaylar</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Proje 2</h5>
            <p class="card-text">Kısa proje açıklaması. Mobil uyumlu ve hızlıdır.</p>
            <a href="#" class="btn btn-primary">Detaylar</a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Proje 3</h5>
            <p class="card-text">Kısa proje açıklaması. Açık kaynak kodludur.</p>
            <a href="#" class="btn btn-primary">Detaylar</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- İletişim Bölümü -->
<section class="py-5" style="background: var(--bg-secondary);">
  <div class="container text-center">
    <h2 class="h2 fw-bold mb-4">İletişim</h2>
    <p class="mb-4">Benimle iletişime geçmek için aşağıdaki e-posta adresini kullanabilirsiniz:</p>
    <a href="mailto:ornek@mail.com" class="btn btn-primary btn-lg"><i class="fas fa-envelope me-2"></i>ornek@mail.com</a>
  </div>
</section>

<?php include '../includes/footer.php'; ?> 
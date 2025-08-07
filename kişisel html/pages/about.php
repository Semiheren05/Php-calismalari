<?php
session_start();

$page_title = 'Hakkımda - SemihHub';
$current_page = 'about';

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
<section class="hero-section d-flex align-items-center" style="min-height: 60vh; background: var(--primary-gradient); color: #fff;">
  <div class="container text-center">
    <h1 class="display-4 fw-bold mb-3">Hakkımda</h1>
    <p class="lead mb-4">Ben Semih, SemihHub'ın kurucusu ve yazılım geliştiricisiyim.<br>Modern web teknolojileriyle projeler geliştiriyor ve paylaşıyorum.</p>
    <a href="index.php" class="btn btn-light btn-lg"><i class="fas fa-home me-2"></i>Ana Sayfa</a>
  </div>
</section>
<!-- Diğer içerikler buraya eklenebilir -->

<!-- About Content -->
<section class="about-content">
    <div class="container">
        <!-- Profile Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <div class="about-card fade-in">
                    <img src="https://via.placeholder.com/200x200/007bff/ffffff?text=S" alt="Semih" class="profile-image">
                    <h2 class="h3 fw-bold mb-3">Semih</h2>
                    <p class="lead text-muted mb-4">
                        5+ yıllık deneyimimle web teknolojileri konusunda uzmanlaşmış bir yazılım geliştiricisiyim. 
                        Modern teknolojileri kullanarak kullanıcı dostu ve performanslı uygulamalar geliştiriyorum.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-github"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Skills Section -->
            <div class="col-lg-6">
                <div class="about-card fade-in">
                    <h3 class="h4 fw-bold mb-4">
                        <i class="fas fa-code me-2"></i>Teknolojiler & Beceriler
                    </h3>
                    <div class="skill-item">
                        <div class="skill-name">PHP & Laravel</div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 90%"></div>
                        </div>
                    </div>
                    <div class="skill-item">
                        <div class="skill-name">JavaScript & React</div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 85%"></div>
                        </div>
                    </div>
                    <div class="skill-item">
                        <div class="skill-name">HTML5 & CSS3</div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 95%"></div>
                        </div>
                    </div>
                    <div class="skill-item">
                        <div class="skill-name">MySQL & PostgreSQL</div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 80%"></div>
                        </div>
                    </div>
                    <div class="skill-item">
                        <div class="skill-name">Node.js & Express</div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 75%"></div>
                        </div>
                    </div>
                    <div class="skill-item">
                        <div class="skill-name">Docker & Git</div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: 70%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Experience Section -->
            <div class="col-lg-6">
                <div class="about-card fade-in">
                    <h3 class="h4 fw-bold mb-4">
                        <i class="fas fa-briefcase me-2"></i>Deneyim & Eğitim
                    </h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-date">2023 - Günümüz</div>
                            <h5 class="fw-bold">Senior Web Developer</h5>
                            <p class="text-muted">TechCorp - Modern web uygulamaları geliştirme ve ekip liderliği</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">2021 - 2023</div>
                            <h5 class="fw-bold">Full Stack Developer</h5>
                            <p class="text-muted">WebSolutions - E-ticaret ve CMS projeleri geliştirme</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">2019 - 2021</div>
                            <h5 class="fw-bold">Frontend Developer</h5>
                            <p class="text-muted">DigitalAgency - Responsive web tasarımı ve UI/UX geliştirme</p>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-date">2015 - 2019</div>
                            <h5 class="fw-bold">Bilgisayar Mühendisliği</h5>
                            <p class="text-muted">İstanbul Teknik Üniversitesi - Yazılım geliştirme odaklı eğitim</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="about-card fade-in">
                    <h3 class="h4 fw-bold mb-4 text-center">
                        <i class="fas fa-cogs me-2"></i>Hizmetlerim
                    </h3>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center p-4">
                                <i class="fas fa-laptop-code fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Web Geliştirme</h5>
                                <p class="text-muted">Modern ve responsive web uygulamaları geliştirme</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4">
                                <i class="fas fa-mobile-alt fa-3x text-success mb-3"></i>
                                <h5 class="fw-bold">Mobil Uygulama</h5>
                                <p class="text-muted">Cross-platform mobil uygulama geliştirme</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4">
                                <i class="fas fa-database fa-3x text-info mb-3"></i>
                                <h5 class="fw-bold">Backend Sistemler</h5>
                                <p class="text-muted">API geliştirme ve veritabanı yönetimi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="about-card fade-in text-center">
                    <h3 class="h4 fw-bold mb-4">
                        <i class="fas fa-envelope me-2"></i>İletişim
                    </h3>
                    <p class="lead mb-4">
                        Projeleriniz için benimle iletişime geçin. Size en uygun çözümü birlikte geliştirelim.
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="mailto:semih@example.com" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>E-posta Gönder
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fab fa-linkedin me-2"></i>LinkedIn
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            <i class="fab fa-github me-2"></i>GitHub
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?> 
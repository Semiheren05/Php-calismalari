<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Aktif sayfa tespiti
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">SemihHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>" href="/">Anasayfa</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page === 'about' ? 'active' : '' ?>" href="/pages/about.php">Hakkımda</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page === 'blog' ? 'active' : '' ?>" href="/pages/blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page === 'projects' ? 'active' : '' ?>" href="/pages/projects.php">Projeler</a></li>
        <li class="nav-item"><a class="nav-link <?= $current_page === 'contact' ? 'active' : '' ?>" href="/pages/contact.php">İletişim</a></li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($_SESSION['username']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/pages/profile.php?username=<?= htmlspecialchars($_SESSION['username']) ?>">Profilim</a></li>
              <li><a class="dropdown-item" href="/pages/logout.php">Çıkış</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/pages/login.php">Giriş</a></li>
          <li class="nav-item"><a class="btn btn-primary ms-2" href="/pages/register.php">Kayıt Ol</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="https://github.com/" target="_blank"><i class="fab fa-github"></i></a></li>
        <li class="nav-item"><a class="nav-link" href="https://linkedin.com/" target="_blank"><i class="fab fa-linkedin"></i></a></li>
        <li class="nav-item"><a class="nav-link" href="mailto:ornek@mail.com"><i class="fas fa-envelope"></i></a></li>
      </ul>
    </div>
  </div>
</nav>
<div style="height: 70px;"></div> <!-- Navbar boşluğu --> 
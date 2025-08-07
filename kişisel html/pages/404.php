<?php
$page_title = '404 - Sayfa Bulunamadı | SemihHub';
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern-style.css" rel="stylesheet">
    <?php if (isset($additional_css)): ?>
        <style><?= $additional_css ?></style>
    <?php endif; ?>
</head>
<body>
<section class="hero-section d-flex align-items-center justify-content-center" style="min-height: 80vh; background: var(--primary-gradient); color: #fff;">
  <div class="container text-center">
    <h1 class="display-4 fw-bold mb-3">404</h1>
    <p class="lead mb-4">Aradığınız sayfa bulunamadı.<br>SemihHub ana sayfasına dönebilirsiniz.</p>
    <a href="index.php" class="btn btn-light btn-lg"><i class="fas fa-home me-2"></i>Ana Sayfa</a>
  </div>
</section>
</body>
</html> 
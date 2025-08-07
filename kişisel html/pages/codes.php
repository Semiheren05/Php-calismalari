<?php
session_start();

// Veritabanı bağlantısı ve hata yönetimi
try {
    require_once '../includes/db.php';
    
    // Kodları veritabanından çek
    $stmt = $pdo->query("
        SELECT c.*, u.username, 
               COUNT(DISTINCT cm.id) as comment_count,
               COUNT(DISTINCT v.id) as vote_count
        FROM codes c 
        LEFT JOIN users u ON c.user_id = u.id 
        LEFT JOIN comments cm ON c.id = cm.code_id
        LEFT JOIN votes v ON c.id = v.code_id
        GROUP BY c.id, u.username
        ORDER BY c.created_at DESC
    ");
    $codes = $stmt->fetchAll();
} catch (PDOException $e) {
    // Veritabanı hatası durumunda örnek veriler
    $codes = [
        [
            'id' => 1,
            'title' => 'PHP ile Dizideki En Büyük Sayıyı Bulma',
            'description' => 'Bir dizideki en büyük sayıyı bulan basit bir PHP fonksiyonu.',
            'code' => 'function maxInArray($arr) { return max($arr); }',
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s'),
            'views' => 150,
            'comment_count' => 5,
            'vote_count' => 12
        ],
        [
            'id' => 2,
            'title' => 'JavaScript ile Ters Çevirme',
            'description' => 'Bir stringi ters çeviren JavaScript fonksiyonu.',
            'code' => 'function reverseString(str) { return str.split("").reverse().join(""); }',
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'views' => 89,
            'comment_count' => 3,
            'vote_count' => 8
        ],
        [
            'id' => 3,
            'title' => 'Python: Fibonacci Dizisi',
            'description' => 'Fibonacci dizisini üreten Python fonksiyonu.',
            'code' => 'def fibonacci(n): a, b = 0, 1; for _ in range(n): print(a, end=" "); a, b = b, a + b',
            'username' => 'semih',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'views' => 234,
            'comment_count' => 7,
            'vote_count' => 15
        ]
    ];
}

$page_title = 'Kodlar - SemihHub';
$current_page = 'codes';

$additional_css = '
    .codes-hero {
        background: var(--primary-gradient);
        padding: 8rem 0 4rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .codes-hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><defs><pattern id=\'codes-pattern\' width=\'35\' height=\'35\' patternUnits=\'userSpaceOnUse\'><circle cx=\'17.5\' cy=\'17.5\' r=\'1.5\' fill=\'%23ffffff\' opacity=\'0.1\'/></pattern></defs><rect width=\'100\' height=\'100\' fill=\'url(%23codes-pattern)\'/></svg>");
        animation: float 28s ease-in-out infinite;
    }

    .codes-content {
        padding: 4rem 0;
        background: var(--light-gradient);
        min-height: 100vh;
    }

    .code-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
        transition: all 0.3s ease;
        height: 100%;
    }

    .code-card:hover {
        box-shadow: var(--box-shadow-hover);
        transform: translateY(-10px);
    }

    .code-card .card-body {
        padding: 2rem;
    }

    .code-preview {
        background: #1e1e1e;
        color: #d4d4d4;
        padding: 1.5rem;
        border-radius: 12px;
        font-family: "Consolas", "Monaco", "Courier New", monospace;
        font-size: 0.9rem;
        line-height: 1.5;
        max-height: 200px;
        overflow: hidden;
        position: relative;
    }

    .code-preview::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 40px;
        background: linear-gradient(transparent, #1e1e1e);
        pointer-events: none;
    }

    .code-preview pre {
        margin: 0;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .code-preview code {
        background: none;
        padding: 0;
        color: inherit;
    }

    .code-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: var(--text-secondary);
        flex-wrap: wrap;
    }

    .code-stats {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0,0,0,0.1);
        flex-wrap: wrap;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .search-box {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 3rem;
        box-shadow: var(--box-shadow);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .filter-tag {
        background: var(--primary-gradient);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        outline: none;
    }

    .filter-tag:hover {
        transform: translateY(-2px);
        box-shadow: var(--box-shadow);
    }

    .filter-tag.active {
        background: var(--secondary-gradient);
    }

    .btn-success {
        background: var(--success-color);
        border-color: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #00b894;
        border-color: #00b894;
        color: white;
    }

    @media (max-width: 768px) {
        .code-meta {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .code-stats {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .search-box {
            padding: 1.5rem;
        }
    }
';

include '../includes/header.php';
?>

<!-- Codes Hero Section -->
<section class="codes-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-4 slide-in-left">Kod Paylaşımı</h1>
                <p class="lead mb-0 slide-in-right">
                    Yazılım geliştiricilerin kodlarını paylaştığı, tartıştığı ve öğrendiği platform
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Codes Content -->
<section class="codes-content">
    <div class="container">
        <!-- Search and Filter -->
        <div class="search-box fade-in">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="search" class="form-label">
                            <i class="fas fa-search me-2"></i>Kodlarda Ara
                        </label>
                        <input type="text" class="form-control" id="search" placeholder="Kod, başlık veya açıklama ara...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sort" class="form-label">
                            <i class="fas fa-sort me-2"></i>Sırala
                        </label>
                        <select class="form-control" id="sort">
                            <option value="newest">En Yeni</option>
                            <option value="oldest">En Eski</option>
                            <option value="popular">En Popüler</option>
                            <option value="views">En Çok Görüntülenen</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-tags">
                <span class="filter-tag active" data-filter="all">Tümü</span>
                <span class="filter-tag" data-filter="php">PHP</span>
                <span class="filter-tag" data-filter="javascript">JavaScript</span>
                <span class="filter-tag" data-filter="python">Python</span>
                <span class="filter-tag" data-filter="css">CSS</span>
                <span class="filter-tag" data-filter="html">HTML</span>
            </div>
        </div>

        <!-- Add Code Button -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="text-center mb-4 fade-in">
            <a href="code_add.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus me-2"></i>Yeni Kod Paylaş
            </a>
        </div>
        <?php endif; ?>

        <!-- Codes Grid -->
        <div class="row g-4">
            <?php if (empty($codes)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-code fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Henüz kod paylaşılmamış</h4>
                <p class="text-muted">İlk kod paylaşımını yapmak için giriş yapın.</p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Giriş Yap
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <?php foreach ($codes as $code): ?>
                <div class="col-lg-6 col-xl-4 code-item" data-tags="<?= strtolower($code['title'] . ' ' . $code['description']) ?>">
                    <div class="code-card fade-in">
                        <div class="card-body">
                            <div class="code-meta">
                                <span><i class="fas fa-user me-1"></i><?= htmlspecialchars($code['username'] ?? 'Anonim') ?></span>
                                <span><i class="fas fa-calendar me-1"></i><?= date('d.m.Y', strtotime($code['created_at'])) ?></span>
                                <span><i class="fas fa-eye me-1"></i><?= $code['views'] ?> görüntüleme</span>
                            </div>
                            
                            <h3 class="h5 fw-bold mb-3">
                                <a href="code.php?id=<?= $code['id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($code['title']) ?>
                                </a>
                            </h3>
                            
                            <p class="text-muted mb-3">
                                <?= htmlspecialchars(substr($code['description'], 0, 100)) ?>...
                            </p>

                            <div class="code-preview">
                                <pre><code><?= htmlspecialchars(substr($code['code'], 0, 300)) ?>...</code></pre>
                            </div>

                            <div class="code-stats">
                                <div class="stat-item">
                                    <i class="fas fa-comment"></i>
                                    <span><?= $code['comment_count'] ?> yorum</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span><?= $code['vote_count'] ?> beğeni</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-code"></i>
                                    <span><?= strlen($code['code']) ?> karakter</span>
                                </div>
                            </div>

                            <div class="mt-3">
                                <a href="code.php?id=<?= $code['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye me-1"></i>Görüntüle
                                </a>
                                <button class="btn btn-outline-secondary btn-sm ms-2" onclick="copyCode(<?= $code['id'] ?>, this)">
                                    <i class="fas fa-copy me-1"></i>Kopyala
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- No Codes Message -->
        <div id="no-codes" class="text-center py-5" style="display: none;">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">Kod bulunamadı</h4>
            <p class="text-muted">Arama kriterlerinizi değiştirmeyi deneyin.</p>
        </div>
    </div>
</section>

<?php 
$additional_js = '
    // Search and filter functionality
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById("search");
        const sortSelect = document.getElementById("sort");
        const filterTags = document.querySelectorAll(".filter-tag");
        const codeItems = document.querySelectorAll(".code-item");
        const noCodes = document.getElementById("no-codes");

        function filterCodes() {
            const searchTerm = searchInput.value.toLowerCase();
            const activeFilter = document.querySelector(".filter-tag.active").getAttribute("data-filter");
            let visibleCount = 0;

            codeItems.forEach(item => {
                const title = item.querySelector("h3").textContent.toLowerCase();
                const description = item.querySelector("p").textContent.toLowerCase();
                const tags = item.getAttribute("data-tags").toLowerCase();
                
                const matchesSearch = title.includes(searchTerm) || 
                                    description.includes(searchTerm) || 
                                    tags.includes(searchTerm);
                
                const matchesFilter = activeFilter === "all" || tags.includes(activeFilter);
                
                if (matchesSearch && matchesFilter) {
                    item.style.display = "block";
                    visibleCount++;
                } else {
                    item.style.display = "none";
                }
            });

            noCodes.style.display = visibleCount === 0 ? "block" : "none";
        }

        if (searchInput) {
            searchInput.addEventListener("input", filterCodes);
        }
        if (sortSelect) {
            sortSelect.addEventListener("change", filterCodes);
        }

        filterTags.forEach(tag => {
            tag.addEventListener("click", function() {
                filterTags.forEach(t => t.classList.remove("active"));
                this.classList.add("active");
                filterCodes();
            });
        });
    });

    // Copy code functionality
    function copyCode(codeId, button) {
        // Bu fonksiyon gerçek kod kopyalama işlemi için geliştirilebilir
        const codeText = "Kod kopyalandı! (ID: " + codeId + ")";
        
        navigator.clipboard.writeText(codeText).then(() => {
            // Başarılı kopyalama mesajı göster
            const originalText = button.innerHTML;
            button.innerHTML = \'<i class="fas fa-check me-1"></i>Kopyalandı\';
            button.classList.remove("btn-outline-secondary");
            button.classList.add("btn-success");
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove("btn-success");
                button.classList.add("btn-outline-secondary");
            }, 2000);
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement("textarea");
            textArea.value = codeText;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
            
            // Show success message
            const originalText = button.innerHTML;
            button.innerHTML = \'<i class="fas fa-check me-1"></i>Kopyalandı\';
            button.classList.remove("btn-outline-secondary");
            button.classList.add("btn-success");
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove("btn-success");
                button.classList.add("btn-outline-secondary");
            }, 2000);
        });
    }
';

include '../includes/footer.php'; 
?> 
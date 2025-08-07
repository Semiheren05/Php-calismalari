// Kullanıcı giriş kontrolü (login-register.html hariç)
(function() {
  function checkLogin() {
    const url = window.location.href;
    const path = window.location.pathname;
    const isLoginPage = url.includes('login-register.html') || path.includes('login-register.html');
    if (!isLoginPage) {
      if (localStorage.getItem('isLoggedIn') !== 'true') {
        window.location.href = 'login-register.html';
      }
    }
  }
  checkLogin();
  document.addEventListener('DOMContentLoaded', checkLogin);
})();

// === Karanlık Mod ===
(function() {
  // Temayı uygula ve tüm switchleri güncelle
  function setDarkMode(on) {
    document.body.classList.toggle('dark-mode', on);
    localStorage.setItem('darkMode', on ? 'on' : 'off');
    // Navbar'daki switch
    var mainSwitch = document.getElementById('darkModeSwitchBtn');
    if (mainSwitch) {
      mainSwitch.classList.toggle('dark', on);
      var ball = mainSwitch.querySelector('.switch-ball');
      if (ball) ball.innerHTML = on ? '☀️' : '🌙';
    }
  }
  function isDarkMode() {
    return document.body.classList.contains('dark-mode');
  }
  // Navbar dark mode switch
  function addMainSwitchToNavbar() {
    if (document.getElementById('darkModeSwitchBtn')) return;
    var navbar = document.querySelector('.navbar .container, .navbar .container-fluid');
    if (!navbar) return;
    const btn = document.createElement('button');
    btn.id = 'darkModeSwitchBtn';
    btn.className = 'dark-mode-switch';
    btn.setAttribute('aria-label', 'Karanlık Mod');
    btn.innerHTML = '<span class="switch-ball">🌙</span>';
    btn.style.position = 'static';
    btn.style.marginLeft = 'auto';
    btn.style.marginRight = '0';
    btn.style.top = 'unset';
    btn.style.right = 'unset';
    btn.style.transform = 'none';
    // Responsive için bir wrapper div
    const wrapper = document.createElement('div');
    wrapper.style.display = 'flex';
    wrapper.style.alignItems = 'center';
    wrapper.appendChild(btn);
    navbar.appendChild(wrapper);
    btn.addEventListener('click', function() {
      setDarkMode(!isDarkMode());
    });
  }
  // Sayfa yüklendiğinde başlat
  document.addEventListener('DOMContentLoaded', function() {
    addMainSwitchToNavbar();
    setDarkMode(localStorage.getItem('darkMode') === 'on');
  });
})();
// INDEX.HTML: E-posta kopyalama 
(function() {
  var emailLink = document.getElementById('copyEmailBtn');
  var timeoutId;
  if (emailLink) {
    var original = emailLink.innerHTML;
    emailLink.addEventListener('click', function(e) {
      e.preventDefault();
      var email = emailLink.getAttribute('href') ? emailLink.getAttribute('href').replace('mailto:', '') : emailLink.textContent.trim();
      navigator.clipboard.writeText(email).then(() => {
        emailLink.innerHTML = '<i class="bi bi-clipboard-check me-1"></i> Kopyalandı!';
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
          emailLink.innerHTML = original;
        }, 1500);
      });
    });
  }
})();

// DEVICE-LIST.HTML: Cihaz Listeleme ve Arama
if (document.getElementById('cihaz-listesi')) {
  const cihazlar = [
    { ad: "Sıcaklık Sensörü", tip: "Sensör", durum: "Aktif", aciklama: "Ofis sıcaklık takibi" },
    { ad: "Güvenlik Kamerası", tip: "Kamera", durum: "Pasif", aciklama: "Giriş kapısı" },
    { ad: "Nem Sensörü", tip: "Sensör", durum: "Aktif", aciklama: "Depo nem takibi" },
    { ad: "Akıllı Priz", tip: "Priz", durum: "Aktif", aciklama: "Sunucu odası enerji yönetimi" },
    { ad: "Duman Dedektörü", tip: "Sensör", durum: "Pasif", aciklama: "Yangın güvenliği" },
    { ad: "Kapı Kontrol Paneli", tip: "Panel", durum: "Aktif", aciklama: "Ana giriş kapısı kontrolü" },
    { ad: "Işık Sensörü", tip: "Sensör", durum: "Aktif", aciklama: "Aydınlatma otomasyonu" },
    { ad: "Akıllı Termostat", tip: "Termostat", durum: "Pasif", aciklama: "Klima kontrolü" },
    { ad: "Su Kaçağı Sensörü", tip: "Sensör", durum: "Aktif", aciklama: "Bodrum su baskını önleme" },
    { ad: "Alarm Sireni", tip: "Alarm", durum: "Aktif", aciklama: "Güvenlik alarmı" }
  ];
  const tbody = document.getElementById('cihaz-listesi');
  function tabloyuGuncelle(liste, highlightName = null) {
    tbody.innerHTML = '';
    liste.forEach((c, i) => {
      const isHighlight = highlightName && c.ad.toLowerCase() === highlightName.toLowerCase();
      const tr = document.createElement('tr');
      if (isHighlight) {
        tr.id = 'highlight-row';
        tr.classList.add('table-warning');
      }
      tr.innerHTML = `<td>${i+1}</td><td>${c.ad}</td><td>${c.tip}</td><td>${c.durum === 'Aktif' ? "<span class='badge bg-success'>Aktif</span>" : "<span class='badge bg-secondary'>Pasif</span>"}</td><td>${c.aciklama}</td>`;
      tbody.appendChild(tr);
    });
  }
  tabloyuGuncelle(cihazlar);
  // Arama kutusu ile filtreleme
  const searchInput = document.getElementById('device-search');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = this.value.toLowerCase();
      const filtreli = cihazlar.filter(c =>
        c.ad.toLowerCase().includes(query) ||
        c.tip.toLowerCase().includes(query) ||
        c.durum.toLowerCase().includes(query) ||
        c.aciklama.toLowerCase().includes(query)
      );
      tabloyuGuncelle(filtreli, this.value);
    });
  }
  // Buton ile arama
  const searchBtn = document.getElementById('device-search-btn');
  if (searchBtn && searchInput) {
    searchBtn.addEventListener('click', function() {
      const query = searchInput.value.toLowerCase();
      const filtreli = cihazlar.filter(c =>
        c.ad.toLowerCase().includes(query) ||
        c.tip.toLowerCase().includes(query) ||
        c.durum.toLowerCase().includes(query) ||
        c.aciklama.toLowerCase().includes(query)
      );
      tabloyuGuncelle(filtreli, searchInput.value);
    });
  }
  // Sayfa yüklendiğinde search parametresi varsa uygula
  function getQueryParam(name) {
    const url = new URL(window.location.href);
    return url.searchParams.get(name) || '';
  }
  const initialSearch = getQueryParam('search');
  if (initialSearch && searchInput) {
    searchInput.value = initialSearch;
    const filtreli = cihazlar.filter(c =>
      c.ad.toLowerCase().includes(initialSearch.toLowerCase()) ||
      c.tip.toLowerCase().includes(initialSearch.toLowerCase()) ||
      c.durum.toLowerCase().includes(initialSearch.toLowerCase()) ||
      c.aciklama.toLowerCase().includes(initialSearch.toLowerCase())
    );
    tabloyuGuncelle(filtreli, initialSearch);
    // Tam eşleşen satıra scroll ve animasyon
    const highlightRow = document.getElementById('highlight-row');
    if (highlightRow) {
      highlightRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
      highlightRow.classList.add('animate__animated', 'animate__flash');
      setTimeout(() => {
        highlightRow.classList.remove('animate__animated', 'animate__flash');
      }, 1500);
    }
  }
}

// DASHBOARD.HTML: Demo veriler ve kartlar
if (document.getElementById('toplam-cihaz')) {
  const cihazlar = [
    { ad: "Sıcaklık Sensörü", tip: "Sensör", durum: "Aktif", aciklama: "Ofis sıcaklık takibi" },
    { ad: "Güvenlik Kamerası", tip: "Kamera", durum: "Pasif", aciklama: "Giriş kapısı" },
    { ad: "Nem Sensörü", tip: "Sensör", durum: "Aktif", aciklama: "Depo nem takibi" },
    { ad: "Akıllı Priz", tip: "Priz", durum: "Aktif", aciklama: "Sunucu odası enerji yönetimi" },
    { ad: "Duman Dedektörü", tip: "Sensör", durum: "Pasif", aciklama: "Yangın güvenliği" },
    { ad: "Kapı Kontrol Paneli", tip: "Panel", durum: "Aktif", aciklama: "Ana giriş kapısı kontrolü" },
    { ad: "Işık Sensörü", tip: "Sensör", durum: "Aktif", aciklama: "Aydınlatma otomasyonu" },
    { ad: "Akıllı Termostat", tip: "Termostat", durum: "Pasif", aciklama: "Klima kontrolü" },
    { ad: "Su Kaçağı Sensörü", tip: "Sensör", durum: "Aktif", aciklama: "Bodrum su baskını önleme" },
    { ad: "Alarm Sireni", tip: "Alarm", durum: "Aktif", aciklama: "Güvenlik alarmı" }
  ];
  document.getElementById('toplam-cihaz').textContent = cihazlar.length;
  document.getElementById('aktif-cihaz').textContent = cihazlar.filter(c => c.durum === "Aktif").length;
  document.getElementById('uyari-sayisi').textContent = cihazlar.filter(c => c.durum !== "Aktif").length;
  const tbody = document.getElementById('cihaz-listesi');
  tbody.innerHTML = '';
  cihazlar.forEach((c, i) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${i+1}</td><td>${c.ad}</td><td>${c.tip}</td><td>${c.durum === 'Aktif' ? "<span class='badge bg-success'>Aktif</span>" : "<span class='badge bg-secondary'>Pasif</span>"}</td><td>${c.aciklama}</td>`;
    tbody.appendChild(tr);
  });
}

// LOGIN-REGISTER.HTML: Form geçişi
if (document.getElementById('showLogin') && document.getElementById('showRegister')) {
  const showLogin = document.getElementById('showLogin');
  const showRegister = document.getElementById('showRegister');
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  function clearActive() {
    showLogin.classList.remove('active');
    showRegister.classList.remove('active');
  }
  showLogin.addEventListener('click', function() {
    clearActive();
    showLogin.classList.add('active');
    loginForm.classList.remove('d-none');
    registerForm.classList.add('d-none');
  });
  showRegister.addEventListener('click', function() {
    clearActive();
    showRegister.classList.add('active');
    registerForm.classList.remove('d-none');
    loginForm.classList.add('d-none');
  });
  // Sayfa #kayit-ol hash ile açılırsa otomatik kayıt formunu göster
  if (window.location.hash === '#kayit-ol') {
    showRegister.click();
  }
}
// Navbar veya başka bir yerdeki Kayıt Ol linklerine tıklanınca yönlendir
// Hatalı tekrar tanımlamayı engellemek için DOMContentLoaded içinde ve let ile tanımla
(function() {
  document.addEventListener('DOMContentLoaded', function() {
    let navbarRegisterLinks = document.querySelectorAll('a[href$="login-register.html#kayit-ol"]');
    navbarRegisterLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        window.location.href = 'login-register.html#kayit-ol';
      });
    });
  });
})();

// Giriş yapınca localStorage'a isLoggedIn kaydet (login formunu submit eden butona eklenmeli)
if (document.getElementById('loginForm')) {
  document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Burada gerçek doğrulama yapılabilir, şimdilik demo olarak giriş kabul ediliyor
    localStorage.setItem('isLoggedIn', 'true');
    window.location.href = 'index.html';
  });
} 

// Giriş yapılmadan sadece login-register.html açılabilsin, diğer sayfalara yönlendirme olmasın
(function() {
  const path = window.location.pathname;
  const isLoginPage = path.includes('login-register.html');
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
  if (!isLoggedIn && !isLoginPage) {
    window.location.href = 'login-register.html';
  }
})(); 
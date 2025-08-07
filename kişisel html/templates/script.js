const btn = document.querySelector('.menu-btn');
const menu = document.querySelector('nav ul');

if (btn && menu) {
btn.addEventListener('click', () => {
  menu.classList.toggle('active');
});
}

document.addEventListener('DOMContentLoaded', function () {
  // Navbar arama formu submit
  const searchForm = document.querySelector('.search-form');
  const searchInput = document.getElementById('navbar-search');
  if (searchForm && searchInput) {
    searchForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const query = searchInput.value.trim();
      if (query) {
        window.location.href = `device-list.html?search=${encodeURIComponent(query)}`;
      } else {
        window.location.href = 'device-list.html';
      }
    });
  }
  const form = document.getElementById('cihaz-ekle-form');
  const sonucDiv = document.getElementById('ekle-sonuc');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      const cihazAdi = document.getElementById('cihaz-adi').value.trim();
      const cihazTipi = document.getElementById('cihaz-tipi').value;
      const aciklama = document.getElementById('aciklama').value.trim();
      if (!cihazAdi || !cihazTipi) {
        sonucDiv.innerHTML = '<div class="alert alert-danger" role="alert">Lütfen tüm zorunlu alanları doldurun.</div>';
        return;
      }
      // Burada backend'e gönderme işlemi yapılabilir
      sonucDiv.innerHTML = '<div class="alert alert-success" role="alert">Cihaz başarıyla eklendi!</div>';
      form.reset();
    });
  }
});

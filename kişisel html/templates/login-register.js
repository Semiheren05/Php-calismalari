// Giriş ve Kayıt işlemleri için tek dosya

function showFormAlert(message, isSuccess) {
  const alertDiv = document.getElementById('formAlert');
  if (!alertDiv) return;
  alertDiv.innerHTML = `<div class='alert alert-${isSuccess ? 'success' : 'custom'}'>${message}</div>`;
  setTimeout(() => { alertDiv.innerHTML = ''; }, 2000);
}

// Kayıt Formu
const registerFormEl = document.getElementById("registerForm");
if (registerFormEl) {
  registerFormEl.addEventListener("submit", function (e) {
    e.preventDefault();

    const name = document.getElementById("registerName").value.trim();
    const email = document.getElementById("registerEmail").value.trim();
    const password = document.getElementById("registerPassword").value;
    const confirm = document.getElementById("registerConfirm").value;

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!name || !email || !password || !confirm) {
      showFormAlert("Lütfen tüm alanları doldurun.");
      return;
    }

    if (!emailPattern.test(email)) {
      showFormAlert("Geçerli bir email adresi girin.");
      return;
    }

    if (password !== confirm) {
      showFormAlert("Şifreler eşleşmiyor.");
      return;
    }

    // Kullanıcı bilgilerini localStorage'a kaydet
    localStorage.setItem("registeredUser", JSON.stringify({
      name: name,
      email: email,
      password: password
    }));

    showFormAlert("<b>Kayıt başarılı!</b> Giriş sekmesine yönlendiriliyorsunuz...", true);

    // Giriş sekmesine geçişi gecikmeli yap
    setTimeout(function() {
      const loginTabBtn = document.getElementById('showLogin');
      const registerTabBtn = document.getElementById('showRegister');
      const loginForm = document.getElementById('loginForm');
      const registerForm = document.getElementById('registerForm');
      if (loginTabBtn && registerTabBtn && loginForm && registerForm) {
        loginTabBtn.classList.add('active');
        registerTabBtn.classList.remove('active');
        loginForm.classList.remove('d-none');
        registerForm.classList.add('d-none');
      }
    }, 1500);
  });
}

// Giriş Formu
const loginFormEl = document.getElementById("loginForm");
if (loginFormEl) {
  loginFormEl.addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("loginEmail").value.trim();
    const password = document.getElementById("loginPassword").value;

    const storedUser = JSON.parse(localStorage.getItem("registeredUser"));

    if (!storedUser) {
      showFormAlert("Kayıtlı kullanıcı bulunamadı.");
      return;
    }

    if (email === storedUser.email && password === storedUser.password) {
      showFormAlert("<b>Hoş geldiniz!</b> Başarıyla giriş yaptınız. Ana sayfaya yönlendiriliyorsunuz...", true);
      localStorage.setItem('isLoggedIn', 'true');
      setTimeout(() => {
        window.location.href = "index.html";
      }, 1500);
    } else {
      showFormAlert("E-posta veya şifre hatalı.");
    }
  });
} 
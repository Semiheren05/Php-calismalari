// Mor yıldızlar efektini oluştur
function createFallingStar() {
  const container = document.querySelector('.falling-stars');
  if (!container) return;
  const star = document.createElement('div');
  star.className = 'falling-star';
  // Rastgele yatay konum
  star.style.left = Math.random() * 100 + 'vw';
  // Rastgele boyut
  const size = 7 + Math.random() * 10;
  star.style.width = size + 'px';
  star.style.height = size + 'px';
  // Rastgele animasyon süresi
  const duration = 2.5 + Math.random() * 1.5;
  star.style.animationDuration = duration + 's';
  // Hafif yatay kayma için rotate
  star.style.transform = `rotate(${Math.random()*360}deg)`;
  container.appendChild(star);
  // Animasyon bitince DOM'dan sil
  star.addEventListener('animationend', () => {
    star.remove();
  });
}
setInterval(createFallingStar, 200); 
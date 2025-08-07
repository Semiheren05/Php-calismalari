// Highlight active page in navbar
function setActiveNavItem() {
  // Get current URL hash or default to dashboard
  const hash = window.location.hash || '#dashboard';
  
  // Remove active class from all nav links
  document.querySelectorAll('.nav-link').forEach(link => {
    link.classList.remove('active');
  });
  
  // Add active class to current page link
  const activeLink = document.querySelector(`.nav-link[href="${hash}"]`);
  if (activeLink) {
    activeLink.classList.add('active');
  }
}

// Run on page load
document.addEventListener('DOMContentLoaded', setActiveNavItem);

// Run when URL hash changes
window.addEventListener('hashchange', setActiveNavItem);

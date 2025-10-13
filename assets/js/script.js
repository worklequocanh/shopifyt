
// Back to top button
const backToTopBtn = document.getElementById('backToTop');

window.addEventListener('scroll', () => {
  if (window.pageYOffset > 300) {
    backToTopBtn.classList.add('visible');
  } else {
    backToTopBtn.classList.remove('visible');
  }
});

backToTopBtn.addEventListener('click', () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
});

// Search functionality
document.querySelector('.search-btn').addEventListener('click', function () {
  const searchInput = document.querySelector('.search-input');
  const searchTerm = searchInput.value.trim();

  if (searchTerm) {
    window.location.href = `products.php?search=${encodeURIComponent(searchTerm)}`;
  }
});

document.querySelector('.search-input').addEventListener('keypress', function (e) {
  if (e.key === 'Enter') {
    document.querySelector('.search-btn').click();
  }
});

// Animation on scroll
const observerOptions = {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
    }
  });
}, observerOptions);

// Observe all animated elements
document.querySelectorAll('.animate-fadeInUp').forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(30px)';
  el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
  observer.observe(el);
});

// Common functions
function addToCart(productId) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'shopping-cart.php';
  form.innerHTML = `
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="${productId}">
        <input type="hidden" name="quantity" value="1">
    `;
  document.body.appendChild(form);
  form.submit();
}

function toggleWishlist(productId) {
  const btn = event.target.closest('.product-action-btn');
  const icon = btn.querySelector('i');

  if (icon.classList.contains('fas')) {
    icon.classList.remove('fas');
    icon.classList.add('far');
    btn.style.backgroundColor = 'var(--white)';
    btn.style.color = 'var(--gray-600)';
  } else {
    icon.classList.remove('far');
    icon.classList.add('fas');
    btn.style.backgroundColor = 'var(--danger-color)';
    btn.style.color = 'var(--white)';
  }
}

// ============================================
// FOODIE EXPRESS - MAIN JAVASCRIPT
// ============================================

// Navbar scroll effect
window.addEventListener('scroll', () => {
    const navbar = document.getElementById('navbar');
    if (navbar) {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    }
});

// Mobile menu toggle
function toggleMenu() {
    const menu = document.getElementById('mobileMenu');
    if (menu) menu.classList.toggle('open');
}

// Toast notification system
function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const icon = type === 'success' ? '✅' : '❌';
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span class="toast-icon">${icon}</span><span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add to cart
function addToCart(dishId, dishName, price) {
    fetch('includes/php/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&dish_id=${dishId}&dish_name=${encodeURIComponent(dishName)}&price=${price}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(`${dishName} added to cart!`, 'success');
            updateCartBadge(data.cart_count);
        } else {
            showToast(data.message || 'Error adding item', 'error');
        }
    })
    .catch(() => showToast('Connection error. Please try again.', 'error'));
}

// Update cart badge in navbar
function updateCartBadge(count) {
    let badge = document.querySelector('.cart-badge');
    const cartBtn = document.querySelector('.cart-btn');
    if (!cartBtn) return;

    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'cart-badge';
            cartBtn.appendChild(badge);
        }
        badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

// Update quantity in cart
function updateQty(dishId, delta) {
    fetch('includes/php/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&dish_id=${dishId}&delta=${delta}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showToast(data.message || 'Error updating cart', 'error');
        }
    });
}

// Remove from cart
function removeFromCart(dishId) {
    fetch('includes/php/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&dish_id=${dishId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Item removed from cart', 'success');
            location.reload();
        }
    });
}

// Filter dishes by category
function filterCategory(categoryId, btn) {
    // Update active button
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Show/hide dishes
    document.querySelectorAll('.dish-card').forEach(card => {
        if (categoryId === 'all' || card.dataset.category == categoryId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Modal open/close
function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('open');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('open');
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// Animate elements on scroll
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.restaurant-card, .step, .dish-card, .stat-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});

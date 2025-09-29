// Newsroom Training Platform - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize content type tabs for create content page
    initContentTypeTabs();
    
    // Initialize form handling
    initFormHandling();
    
    // Initialize auto-refresh for admin panel
    initAutoRefresh();
    
    // Initialize responsive behaviors
    initResponsiveBehaviors();
});

/**
 * Initialize content type tabs functionality
 */
function initContentTypeTabs() {
    const tabs = document.querySelectorAll('.content-type-tab');
    const forms = document.querySelectorAll('.content-form');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            
            // Update active tab
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Show corresponding form
            forms.forEach(form => {
                form.classList.remove('active');
                if (form.id === type + '-form' || (type !== 'news' && form.id === 'social-form')) {
                    form.classList.add('active');
                }
            });
            
            // Update social form for specific platform
            if (type !== 'news') {
                updateSocialForm(type);
            }
        });
    });
}

/**
 * Update social media form based on selected platform
 */
function updateSocialForm(type) {
    const socialTypeInput = document.getElementById('social-type');
    const socialTitle = document.getElementById('social-title');
    const retweetsField = document.getElementById('retweets-field');
    
    if (socialTypeInput) {
        socialTypeInput.value = type;
    }
    
    if (socialTitle) {
        socialTitle.textContent = type.charAt(0).toUpperCase() + type.slice(1);
    }
    
    // Show/hide retweets field based on platform
    if (retweetsField) {
        if (type === 'twitter') {
            retweetsField.style.display = 'block';
        } else {
            retweetsField.style.display = 'none';
        }
    }
}

/**
 * Initialize form handling
 */
function initFormHandling() {
    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
                
                // Re-enable after 3 seconds as fallback
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Submit';
                }, 3000);
            }
        });
    });
    
    // Character counter for social media posts
    const textAreas = document.querySelectorAll('#text');
    textAreas.forEach(textarea => {
        const counter = document.createElement('small');
        counter.className = 'form-text text-muted char-counter';
        textarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = 280 - textarea.value.length;
            counter.textContent = remaining + ' characters remaining';
            
            if (remaining < 0) {
                counter.classList.add('text-danger');
                counter.classList.remove('text-muted');
            } else {
                counter.classList.remove('text-danger');
                counter.classList.add('text-muted');
            }
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
}

/**
 * Initialize auto-refresh for admin panel
 */
function initAutoRefresh() {
    if (window.location.pathname.includes('admin.php')) {
        // Auto-refresh pending users every 30 seconds
        setInterval(() => {
            const pendingBadge = document.querySelector('.badge.bg-warning');
            if (pendingBadge) {
                fetch('api/users.php?action=pending')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const count = data.users.length;
                            pendingBadge.textContent = count;
                            pendingBadge.style.display = count > 0 ? 'inline' : 'none';
                        }
                    })
                    .catch(error => console.error('Error fetching pending users:', error));
            }
        }, 30000);
    }
}

/**
 * Initialize responsive behaviors
 */
function initResponsiveBehaviors() {
    // Mobile navigation handling
    const navToggler = document.querySelector('.navbar-toggler');
    const navCollapse = document.querySelector('.navbar-collapse');
    
    if (navToggler && navCollapse) {
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navToggler.contains(event.target) && !navCollapse.contains(event.target)) {
                if (navCollapse.classList.contains('show')) {
                    navToggler.click();
                }
            }
        });
    }
    
    // Responsive image handling
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.src = 'assets/images/default-avatar.svg';
        });
    });
    
    // Touch-friendly interactions for mobile
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
        
        // Add touch feedback to cards
        const cards = document.querySelectorAll('.content-card');
        cards.forEach(card => {
            card.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            card.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
    }
}

/**
 * Utility function to show toast notifications
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 5000);
}

/**
 * Utility function to format numbers with commas
 */
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

/**
 * Utility function to debounce function calls
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Initialize lazy loading for images
 */
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

/**
 * Handle keyboard navigation
 */
document.addEventListener('keydown', function(event) {
    // Close modals with Escape key
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    }
    
    // Navigate filters with arrow keys
    if (event.target.classList.contains('filter-tab')) {
        const tabs = Array.from(document.querySelectorAll('.filter-tab'));
        const currentIndex = tabs.indexOf(event.target);
        
        if (event.key === 'ArrowLeft' && currentIndex > 0) {
            event.preventDefault();
            tabs[currentIndex - 1].focus();
        } else if (event.key === 'ArrowRight' && currentIndex < tabs.length - 1) {
            event.preventDefault();
            tabs[currentIndex + 1].focus();
        }
    }
});

/**
 * Initialize performance monitoring
 */
function initPerformanceMonitoring() {
    // Monitor page load time
    window.addEventListener('load', function() {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        console.log('Page load time:', loadTime + 'ms');
    });
    
    // Monitor content rendering
    if ('PerformanceObserver' in window) {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (entry.entryType === 'measure') {
                    console.log(entry.name + ':', entry.duration + 'ms');
                }
            }
        });
        
        observer.observe({ entryTypes: ['measure'] });
    }
}

// Initialize performance monitoring
initPerformanceMonitoring();

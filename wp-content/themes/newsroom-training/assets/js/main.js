// Newsroom Training Platform - Main JavaScript

/**
 * Global functions for inline onclick handlers - Define immediately
 * These need to be available before DOMContentLoaded
 */
function handleReplyClick(postId) {
    console.log('🎯 Global reply handler called for post:', postId);
    if (typeof toggleInlineCommentForm === 'function') {
        toggleInlineCommentForm(postId);
    } else {
        console.error('❌ toggleInlineCommentForm function not found');
        // Fallback: try to show elements directly
        showCommentsSection(postId, true);
    }
}

function handleCommentsClick(postId) {
    console.log('🎯 Global comments handler called for post:', postId);

    // Debug: List all elements with this post ID
    const allElements = document.querySelectorAll(`[id*="${postId}"]`);
    console.log('🔍 All elements with post ID', postId, ':', allElements);

    if (typeof toggleCommentsSection === 'function') {
        toggleCommentsSection(postId);
    } else {
        console.error('❌ toggleCommentsSection function not found');
        // Fallback: try to show elements directly
        showCommentsSection(postId, false);
    }
}

/**
 * Fallback function to show comments section directly
 */
function showCommentsSection(postId, showForm) {
    console.log('🔧 Fallback: Showing comments section for post:', postId);

    const commentsSection = document.getElementById(`comments-section-${postId}`);
    const commentForm = document.getElementById(`comment-form-${postId}`);

    if (commentsSection) {
        commentsSection.style.display = 'block';
        commentsSection.style.visibility = 'visible';
        commentsSection.style.opacity = '1';
        console.log('✅ Comments section shown');
    }

    if (showForm && commentForm) {
        commentForm.style.display = 'block';
        commentForm.style.visibility = 'visible';
        commentForm.style.opacity = '1';
        console.log('✅ Comment form shown');

        const textarea = commentForm.querySelector('textarea');
        if (textarea) {
            setTimeout(() => textarea.focus(), 100);
        }
    }
}

// Also assign to window for extra safety
window.handleReplyClick = handleReplyClick;
window.handleCommentsClick = handleCommentsClick;
window.showCommentsSection = showCommentsSection;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize content type tabs for create content page
    initContentTypeTabs();

    // Initialize form handling
    initFormHandling();

    // Initialize auto-refresh for admin panel
    initAutoRefresh();

    // Initialize responsive behaviors
    initResponsiveBehaviors();

    // Initialize inline commenting
    initInlineCommenting();
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

/**
 * Initialize inline commenting functionality
 */
function initInlineCommenting() {
    console.log('🔧 Initializing inline commenting...');

    // Check if newsroom_ajax is available
    if (typeof newsroom_ajax === 'undefined') {
        console.error('❌ newsroom_ajax object not found! Scripts may not be loaded properly.');
        return;
    }

    console.log('✅ newsroom_ajax object found:', newsroom_ajax);

    // Handle reply button clicks
    document.addEventListener('click', function(event) {
        console.log('👆 Click detected on:', event.target);

        if (event.target.closest('.inline-reply-btn')) {
            console.log('✅ Reply button clicked!');
            event.preventDefault();
            const button = event.target.closest('.inline-reply-btn');
            const postId = button.getAttribute('data-post-id');
            console.log('📝 Post ID:', postId);
            toggleInlineCommentForm(postId);
        }

        // Handle view comments button clicks
        if (event.target.closest('.view-comments-btn')) {
            console.log('✅ Comments button clicked!');
            event.preventDefault();
            const button = event.target.closest('.view-comments-btn');
            const postId = button.getAttribute('data-post-id');
            console.log('📝 Post ID:', postId);
            toggleCommentsSection(postId);
        }

        // Handle cancel comment button clicks
        if (event.target.closest('.cancel-comment-btn')) {
            console.log('✅ Cancel button clicked!');
            event.preventDefault();
            const button = event.target.closest('.cancel-comment-btn');
            const form = button.closest('.inline-comment-form');
            hideCommentForm(form);
        }
    });

    // Handle comment form submissions (AJAX only for specific forms)
    document.addEventListener('submit', function(event) {
        if (event.target.classList.contains('add-comment-form-inline')) {
            event.preventDefault();
            submitInlineComment(event.target);
        }
    });

    // Load initial comment counts
    loadCommentCounts();

    // Debug: Check if buttons exist
    setTimeout(() => {
        const replyButtons = document.querySelectorAll('.inline-reply-btn');
        const commentButtons = document.querySelectorAll('.view-comments-btn');
        console.log('🔍 Found reply buttons:', replyButtons.length);
        console.log('🔍 Found comment buttons:', commentButtons.length);

        // Add direct event listeners as backup
        replyButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                console.log('🎯 Direct reply button click!');
                e.preventDefault();
                const postId = this.getAttribute('data-post-id');
                toggleInlineCommentForm(postId);
            });
        });

        commentButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                console.log('🎯 Direct comment button click!');
                e.preventDefault();
                const postId = this.getAttribute('data-post-id');
                toggleCommentsSection(postId);
            });
        });
    }, 1000);
}

/**
 * Toggle inline comment form visibility
 */
function toggleInlineCommentForm(postId) {
    console.log('🔍 Looking for elements for post:', postId);
    const commentsSection = document.getElementById(`comments-section-${postId}`);
    const commentForm = document.getElementById(`comment-form-${postId}`);

    console.log('📦 Comments section:', commentsSection);
    console.log('📝 Comment form:', commentForm);

    if (!commentsSection) {
        console.error('❌ Comments section not found for post:', postId);
        return;
    }

    if (!commentForm) {
        console.error('❌ Comment form not found for post:', postId);
        return;
    }

    // Show comments section if hidden
    console.log('📊 Comments section current display:', commentsSection.style.display);
    if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
        console.log('👁️ Showing comments section');
        commentsSection.style.display = 'block';
        commentsSection.style.visibility = 'visible';
        commentsSection.style.opacity = '1';
        loadPostComments(postId);
    }

    // Toggle comment form
    console.log('📊 Comment form current display:', commentForm.style.display);
    if (commentForm.style.display === 'none' || commentForm.style.display === '') {
        console.log('📝 Showing comment form');
        commentForm.style.display = 'block';
        commentForm.style.visibility = 'visible';
        commentForm.style.opacity = '1';

        // Force a reflow
        commentForm.offsetHeight;

        const textarea = commentForm.querySelector('textarea');
        if (textarea) {
            console.log('🎯 Focusing on textarea');
            setTimeout(() => textarea.focus(), 100);
        }
    } else {
        console.log('🙈 Hiding comment form');
        commentForm.style.display = 'none';
    }
}

/**
 * Toggle comments section visibility
 */
function toggleCommentsSection(postId) {
    console.log('🔍 Looking for comments section:', `comments-section-${postId}`);
    const commentsSection = document.getElementById(`comments-section-${postId}`);

    if (!commentsSection) {
        console.error('❌ Comments section not found for post:', postId);
        return;
    }

    console.log('✅ Comments section found:', commentsSection);
    console.log('📊 Current display style:', commentsSection.style.display);

    if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
        console.log('👁️ Showing comments section');
        commentsSection.style.display = 'block';
        commentsSection.style.visibility = 'visible';
        commentsSection.style.opacity = '1';

        // Force a reflow
        commentsSection.offsetHeight;

        loadPostComments(postId);
    } else {
        console.log('🙈 Hiding comments section');
        commentsSection.style.display = 'none';
    }
}

/**
 * Hide comment form
 */
function hideCommentForm(form) {
    form.style.display = 'none';
    const textarea = form.querySelector('textarea');
    if (textarea) {
        textarea.value = '';
    }
}

/**
 * Submit inline comment via AJAX
 */
function submitInlineComment(form) {
    const postId = form.getAttribute('data-post-id');
    const textarea = form.querySelector('textarea[name="comment_content"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    const commentContent = textarea.value.trim();

    if (!commentContent) {
        showToast('Please enter a comment', 'warning');
        return;
    }

    // Disable submit button and show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Posting...';

    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'add_comment_inline');
    formData.append('post_id', postId);
    formData.append('comment_content', commentContent);
    formData.append('nonce', getInlineCommentNonce());

    // Submit via AJAX
    const ajaxUrl = (typeof newsroom_ajax !== 'undefined' && newsroom_ajax.ajax_url)
        ? newsroom_ajax.ajax_url
        : '/wp-admin/admin-ajax.php';

    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new comment to the list
            const commentsList = document.getElementById(`comments-list-${postId}`);
            if (commentsList) {
                // Check if there's a "No comments yet" message and remove it
                const noCommentsMsg = commentsList.querySelector('p.text-muted');
                if (noCommentsMsg && noCommentsMsg.textContent.includes('No comments yet')) {
                    noCommentsMsg.remove();
                }
                commentsList.insertAdjacentHTML('beforeend', data.data.comment_html);
            }

            // Update comment count
            updateCommentCount(postId, 1);

            // Clear form
            textarea.value = '';

            // Show success message
            showToast('Comment added successfully!', 'success');

            // Scroll to new comment
            const newComment = document.getElementById(`comment-${data.data.comment_id}`);
            if (newComment) {
                newComment.scrollIntoView({ behavior: 'smooth', block: 'center' });
                newComment.style.backgroundColor = '#e8f5e8';
                setTimeout(() => {
                    newComment.style.backgroundColor = '';
                }, 2000);
            }
        } else {
            showToast(data.data || 'Failed to add comment', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding comment:', error);
        showToast('Error adding comment', 'error');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

/**
 * Load comments for a specific post
 */
function loadPostComments(postId) {
    const commentsList = document.getElementById(`comments-list-${postId}`);
    const commentsCountElement = document.querySelector(`#comments-section-${postId} .comments-count-number`);

    // Show loading state
    commentsList.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> Loading comments...</div>';

    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'get_post_comments');
    formData.append('post_id', postId);
    formData.append('nonce', getCommentsNonce());

    // Load comments via AJAX
    const ajaxUrl = (typeof newsroom_ajax !== 'undefined' && newsroom_ajax.ajax_url)
        ? newsroom_ajax.ajax_url
        : '/wp-admin/admin-ajax.php';

    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            commentsList.innerHTML = data.data.comments_html || '<p class="text-muted text-center py-3">No comments yet. Be the first to comment!</p>';

            // Update comment count
            if (commentsCountElement) {
                commentsCountElement.textContent = data.data.comments_count;
            }
            updateCommentCount(postId, 0, data.data.comments_count);
        } else {
            commentsList.innerHTML = '<p class="text-danger text-center py-3">Failed to load comments</p>';
        }
    })
    .catch(error => {
        console.error('Error loading comments:', error);
        commentsList.innerHTML = '<p class="text-danger text-center py-3">Error loading comments</p>';
    });
}

/**
 * Update comment count display
 */
function updateCommentCount(postId, increment = 0, absoluteCount = null) {
    const countElements = document.querySelectorAll(`[data-post-id="${postId}"] .comments-count`);
    const numberElements = document.querySelectorAll(`#comments-section-${postId} .comments-count-number`);

    countElements.forEach(element => {
        if (absoluteCount !== null) {
            element.textContent = absoluteCount > 0 ? `Comments (${absoluteCount})` : 'Comments';
        } else if (increment !== 0) {
            const currentText = element.textContent;
            const currentCount = parseInt(currentText.match(/\d+/)?.[0] || '0');
            const newCount = Math.max(0, currentCount + increment);
            element.textContent = newCount > 0 ? `Comments (${newCount})` : 'Comments';
        }
    });

    numberElements.forEach(element => {
        if (absoluteCount !== null) {
            element.textContent = absoluteCount;
        } else if (increment !== 0) {
            const currentCount = parseInt(element.textContent || '0');
            element.textContent = Math.max(0, currentCount + increment);
        }
    });
}

/**
 * Load initial comment counts for all posts
 */
function loadCommentCounts() {
    const commentButtons = document.querySelectorAll('.view-comments-btn');

    commentButtons.forEach(button => {
        const postId = button.getAttribute('data-post-id');
        if (postId) {
            loadCommentCountForPost(postId);
        }
    });
}

/**
 * Load comment count for a specific post
 */
function loadCommentCountForPost(postId) {
    const formData = new FormData();
    formData.append('action', 'get_post_comments');
    formData.append('post_id', postId);
    formData.append('nonce', getCommentsNonce());

    const ajaxUrl = (typeof newsroom_ajax !== 'undefined' && newsroom_ajax.ajax_url)
        ? newsroom_ajax.ajax_url
        : '/wp-admin/admin-ajax.php';

    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCommentCount(postId, 0, data.data.comments_count);
        }
    })
    .catch(error => {
        console.error('Error loading comment count for post', postId, ':', error);
    });
}

/**
 * Get nonce for inline comments
 */
function getInlineCommentNonce() {
    if (typeof newsroom_ajax !== 'undefined' && newsroom_ajax.add_comment_nonce) {
        return newsroom_ajax.add_comment_nonce;
    }
    console.warn('⚠️ Add comment nonce not found');
    return '';
}

/**
 * Get nonce for getting comments
 */
function getCommentsNonce() {
    if (typeof newsroom_ajax !== 'undefined' && newsroom_ajax.get_comments_nonce) {
        return newsroom_ajax.get_comments_nonce;
    }
    console.warn('⚠️ Get comments nonce not found');
    return '';
}



/**
 * Debug function to check DOM structure
 */
window.debugInlineComments = function() {
    console.log('🔍 DEBUG: Checking inline comments structure...');

    const commentsSections = document.querySelectorAll('.inline-comments-section');
    const commentForms = document.querySelectorAll('.inline-comment-form');
    const replyButtons = document.querySelectorAll('.inline-reply-btn');
    const commentButtons = document.querySelectorAll('.view-comments-btn');

    console.log('📊 Found elements:');
    console.log('  - Comments sections:', commentsSections.length);
    console.log('  - Comment forms:', commentForms.length);
    console.log('  - Reply buttons:', replyButtons.length);
    console.log('  - Comment buttons:', commentButtons.length);

    commentsSections.forEach((section, index) => {
        console.log(`  Section ${index}:`, section.id, 'Display:', section.style.display);
    });

    commentForms.forEach((form, index) => {
        console.log(`  Form ${index}:`, form.id, 'Display:', form.style.display);
    });
};

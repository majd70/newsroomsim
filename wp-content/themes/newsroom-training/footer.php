<!-- Footer -->


<footer class="site-footer bg-dark text-light py-4 mt-5" >
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <p class="mb-0">
                    Powered by <a href="https://signalbridge.com" target="_blank" class="text-light">SignalBridge.com</a>
                </p>
            </div>
        </div>
    </div>
</footer>

<?php 

if (function_exists('wp_footer')) {
    wp_footer(); 
} else {
    // Standalone mode - include JavaScript manually
    echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>' . "\n";
    echo '<script src="' . (defined('ASSETS_URL') ? ASSETS_URL : '/themes/newsroom-training/assets') . '/js/main.js"></script>' . "\n";
}
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const checkboxes = document.querySelectorAll('input[name="bulk_delete[]"]');
    const bulkActions = document.querySelector('.bulk-actions');

    if (checkboxes.length && bulkActions) {
        checkboxes.forEach(cb => {
            cb.addEventListener("change", function() {
                const anyChecked = [...checkboxes].some(c => c.checked);
                bulkActions.style.display = anyChecked ? "block" : "none";
            });
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('.delete-checkbox');
    const deleteBtn = document.getElementById('bulkDeleteBtn');

    function toggleDeleteBtn() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        if (deleteBtn) {
            deleteBtn.style.display = anyChecked ? 'inline-block' : 'none';
        }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', toggleDeleteBtn);
    });
});

// AJAX Delete Handler with SweetAlert2
document.addEventListener('DOMContentLoaded', function() {
    // Handle single post delete via AJAX
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-single-post-ajax')) {
            e.preventDefault();

            const button = e.target.closest('.delete-single-post-ajax');
            const postId = button.getAttribute('data-post-id');
            const nonce = button.getAttribute('data-nonce');

            // Show SweetAlert2 confirmation
            Swal.fire({
                title: 'Delete Post?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable button during deletion
                    button.disabled = true;
                    button.style.opacity = '0.5';

                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send AJAX request
                    fetch('<?php echo function_exists("admin_url") ? admin_url("admin-ajax.php") : "/wp-admin/admin-ajax.php"; ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'delete_single_post',
                            post_id: postId,
                            nonce: nonce
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Find and remove the post card
                            const postCard = button.closest('.content-card, .social-card, .news-card');
                            if (postCard) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: 'Post has been deleted successfully.',
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                postCard.style.transition = 'opacity 0.3s';
                                postCard.style.opacity = '0';
                                setTimeout(() => {
                                    postCard.remove();
                                }, 300);
                            }
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.data || 'Failed to delete post',
                                icon: 'error'
                            });
                            button.disabled = false;
                            button.style.opacity = '1';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the post',
                            icon: 'error'
                        });
                        button.disabled = false;
                        button.style.opacity = '1';
                    });
                }
            });
        }
    });

    // Handle delete all posts button
    const deleteAllBtn = document.getElementById('deleteAllPostsBtn');
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener('click', function() {
            const nonce = this.getAttribute('data-nonce');

            // Show SweetAlert2 confirmation
            Swal.fire({
                title: 'Delete ALL Posts?',
                html: '<p>This will permanently delete <strong>ALL</strong> news articles and social media posts!</p><p class="text-danger">This action cannot be undone!</p>',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete everything!',
                cancelButtonText: 'Cancel',
                input: 'checkbox',
                inputPlaceholder: 'I understand this will delete all posts'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting All Posts...',
                        text: 'Please wait, this may take a moment',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send AJAX request
                    fetch('<?php echo function_exists("admin_url") ? admin_url("admin-ajax.php") : "/wp-admin/admin-ajax.php"; ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'delete_all_posts',
                            nonce: nonce
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.data.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload the page to show empty state
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.data || 'Failed to delete posts',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting posts',
                            icon: 'error'
                        });
                    });
                } else if (result.isConfirmed && !result.value) {
                    Swal.fire({
                        title: 'Confirmation Required',
                        text: 'Please check the confirmation box to proceed',
                        icon: 'info'
                    });
                }
            });
        });
    }

    // Handle copy link button
    document.addEventListener('click', function(e) {
        if (e.target.closest('.copy-link-btn')) {
            e.preventDefault();

            const button = e.target.closest('.copy-link-btn');
            const permalink = button.getAttribute('data-permalink');

            if (!permalink) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Could not get post link',
                    icon: 'error',
                    timer: 2000
                });
                return;
            }

            // Copy to clipboard
            navigator.clipboard.writeText(permalink).then(() => {
                // Show success message
                Swal.fire({
                    title: 'Copied!',
                    text: 'Post link copied to clipboard',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                // Change icon temporarily
                const icon = button.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-check';
                button.style.color = '#28a745';

                setTimeout(() => {
                    icon.className = originalClass;
                    button.style.color = '';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to copy link',
                    icon: 'error',
                    timer: 2000
                });
            });
        }
    });

    // Handle delete comment button
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-comment-btn')) {
            e.preventDefault();

            const button = e.target.closest('.delete-comment-btn');
            const commentId = button.getAttribute('data-comment-id');
            const nonce = button.getAttribute('data-nonce');

            // Show SweetAlert2 confirmation
            Swal.fire({
                title: 'Delete Comment?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send AJAX request
                    fetch('<?php echo function_exists("admin_url") ? admin_url("admin-ajax.php") : "/wp-admin/admin-ajax.php"; ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'delete_comment',
                            comment_id: commentId,
                            nonce: nonce
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Comment has been deleted.',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Remove the comment from DOM
                            const commentItem = document.getElementById('comment-' + commentId);
                            if (commentItem) {
                                commentItem.style.transition = 'opacity 0.3s';
                                commentItem.style.opacity = '0';
                                setTimeout(() => {
                                    commentItem.remove();
                                }, 300);
                            }
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.data || 'Failed to delete comment',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while deleting the comment',
                            icon: 'error'
                        });
                    });
                }
            });
        }
    });

    // Edit Modal Handlers for Single Post Pages
    // Twitter Edit Modal
    const editTwitterModal = document.getElementById('editTwitterModal');
    if (editTwitterModal) {
        editTwitterModal.addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget;
            if (button && button.tagName === 'I') {
                button = button.closest('button');
            }

            if (!button) return;

            const postId = button.getAttribute('data-post-id');
            const displayName = button.getAttribute('data-display-name');
            const handle = button.getAttribute('data-handle');
            const text = button.getAttribute('data-text');

            document.getElementById('editTwitterPostId').value = postId || '';
            document.getElementById('editTwitterDisplayName').value = displayName || '';
            document.getElementById('editTwitterHandle').value = handle || '';
            document.getElementById('editTwitterText').value = text || '';

            // Clear media input
            const mediaInput = document.getElementById('editTwitterMedia');
            const mediaPreview = document.getElementById('editTwitterMediaPreview');
            if (mediaInput) mediaInput.value = '';
            if (mediaPreview) mediaPreview.innerHTML = '';
        });
    }

    // Facebook Edit Modal
    const editFacebookModal = document.getElementById('editFacebookModal');
    if (editFacebookModal) {
        editFacebookModal.addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget;
            if (button && button.tagName === 'I') {
                button = button.closest('button');
            }

            if (!button) return;

            const postId = button.getAttribute('data-post-id');
            const displayName = button.getAttribute('data-display-name');
            const handle = button.getAttribute('data-handle');
            const text = button.getAttribute('data-text');

            document.getElementById('editFacebookPostId').value = postId || '';
            document.getElementById('editFacebookDisplayName').value = displayName || '';
            // Facebook doesn't use handles - set hidden field to default value
            document.getElementById('editFacebookHandle').value = 'facebook_user';
            document.getElementById('editFacebookText').value = text || '';

            // Clear media input
            const mediaInput = document.getElementById('editFacebookMedia');
            const mediaPreview = document.getElementById('editFacebookMediaPreview');
            if (mediaInput) mediaInput.value = '';
            if (mediaPreview) mediaPreview.innerHTML = '';
        });
    }

    // Instagram Edit Modal
    const editInstagramModal = document.getElementById('editInstagramModal');
    if (editInstagramModal) {
        editInstagramModal.addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget;
            if (button && button.tagName === 'I') {
                button = button.closest('button');
            }

            if (!button) return;

            const postId = button.getAttribute('data-post-id');
            const displayName = button.getAttribute('data-display-name');
            const handle = button.getAttribute('data-handle');
            const text = button.getAttribute('data-text');

            document.getElementById('editInstagramPostId').value = postId || '';
            // Instagram doesn't show display name - set hidden field to default value
            document.getElementById('editInstagramDisplayName').value = 'Instagram User';
            document.getElementById('editInstagramHandle').value = handle || '';
            document.getElementById('editInstagramText').value = text || '';

            // Clear media input
            const mediaInput = document.getElementById('editInstagramMedia');
            const mediaPreview = document.getElementById('editInstagramMediaPreview');
            if (mediaInput) mediaInput.value = '';
            if (mediaPreview) mediaPreview.innerHTML = '';
        });
    }

    // Truth Social Edit Modal
    const editTruthModal = document.getElementById('editTruthModal');
    if (editTruthModal) {
        editTruthModal.addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget;
            if (button && button.tagName === 'I') {
                button = button.closest('button');
            }

            if (!button) return;

            const postId = button.getAttribute('data-post-id');
            const displayName = button.getAttribute('data-display-name');
            const handle = button.getAttribute('data-handle');
            const text = button.getAttribute('data-text');

            document.getElementById('editTruthPostId').value = postId || '';
            document.getElementById('editTruthDisplayName').value = displayName || '';
            document.getElementById('editTruthHandle').value = handle || '';
            document.getElementById('editTruthText').value = text || '';

            // Clear media input
            const mediaInput = document.getElementById('editTruthMedia');
            const mediaPreview = document.getElementById('editTruthMediaPreview');
            if (mediaInput) mediaInput.value = '';
            if (mediaPreview) mediaPreview.innerHTML = '';
        });
    }
});

</script>

<!-- Initialize Bootstrap Dropdowns -->
<script>
(function() {
    'use strict';

    function initDropdowns() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl, {
                    autoClose: true,
                    boundary: 'viewport'
                });
            });
            console.log('✅ Bootstrap dropdowns initialized:', dropdownList.length);

            // Add manual click handler as fallback
            dropdownElementList.forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var menu = this.nextElementSibling;
                    if (menu && menu.classList.contains('dropdown-menu')) {
                        // Close all other dropdowns
                        document.querySelectorAll('.dropdown-menu.show').forEach(function(otherMenu) {
                            if (otherMenu !== menu) {
                                otherMenu.classList.remove('show');
                            }
                        });

                        // Toggle this dropdown
                        menu.classList.toggle('show');
                        this.setAttribute('aria-expanded', menu.classList.contains('show'));

                        console.log('🖱️ Dropdown clicked, show:', menu.classList.contains('show'));
                    }
                });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                        menu.classList.remove('show');
                        var toggle = menu.previousElementSibling;
                        if (toggle) {
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
            });

        } else {
            console.warn('⚠️ Bootstrap not loaded yet, retrying...');
            setTimeout(initDropdowns, 100);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDropdowns);
    } else {
        initDropdowns();
    }
})();
</script>

</body>
</html>
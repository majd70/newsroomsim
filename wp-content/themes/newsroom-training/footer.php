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
});

</script>

</body>
</html>
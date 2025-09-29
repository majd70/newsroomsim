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
        deleteBtn.style.display = anyChecked ? 'inline-block' : 'none';
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', toggleDeleteBtn);
    });
});


</script>

</body>
</html>
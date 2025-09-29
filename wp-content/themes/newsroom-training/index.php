<?php
/**
 * The main template file for Newsroom Training Theme
 * Hybrid mode - works in both standalone and WordPress environments
 * 
 * @package Newsroom_Training
 */

// ---------------------------
// INSERT HANDLER (WordPress mode)
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_insert'])) {
    if (function_exists('wp_insert_post')) {
        $title = sanitize_text_field($_POST['insert_title']);
        $content = sanitize_textarea_field($_POST['insert_text']);
        $youtube_url = esc_url_raw($_POST['insert_youtube_url']);

        $new_post = array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'news_article',
        );
        $post_id = wp_insert_post($new_post);

        // Save YouTube/Facebook URL as post meta
        if ($post_id && !empty($youtube_url)) {
            update_post_meta($post_id, '_news_video_embed', $youtube_url);
        }

        // Handle media uploads (multiple files)
        if ($post_id && !empty($_FILES['insert_media']['name'][0])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            foreach ($_FILES['insert_media']['name'] as $key => $value) {
                if ($_FILES['insert_media']['name'][$key]) {
                    $file = array(
                        'name'     => $_FILES['insert_media']['name'][$key],
                        'type'     => $_FILES['insert_media']['type'][$key],
                        'tmp_name' => $_FILES['insert_media']['tmp_name'][$key],
                        'error'    => $_FILES['insert_media']['error'][$key],
                        'size'     => $_FILES['insert_media']['size'][$key]
                    );
                    $_FILES['single_media'] = $file;
                    $attachment_id = media_handle_upload('single_media', $post_id);
                    if ($key == 0 && is_numeric($attachment_id)) {
                        set_post_thumbnail($post_id, $attachment_id); // Set first as featured
                    }
                }
            }
        }
        // Redirect to avoid resubmission
        wp_redirect(home_url());
        exit;
    } else {
        // Standalone mode: implement your own insert logic here if needed
    }
}

// ---------------------------
// BULK DELETE HANDLER (top of index.php)
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    // Verify nonce (important for security in WP mode)
    if (function_exists('wp_verify_nonce')) {
        if (!isset($_POST['bulk_delete_nonce']) || 
            !wp_verify_nonce($_POST['bulk_delete_nonce'], 'bulk_delete_action')) {
            wp_die('Security check failed');
        }
    }
    if (!empty($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
        foreach ($_POST['delete_ids'] as $raw) {
            $id = intval($raw);
            if ($id) {
                if (function_exists('wp_delete_post')) {
                    // WordPress mode
                    wp_delete_post($id, true);
                } else {
                    // Standalone mode — call your custom delete
                    newsroom_delete_content($id);
                }
            }
        }
    }
    // Redirect back to prevent resubmission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// ---------------------------
// MAIN TEMPLATE LOGIC
// ---------------------------
if (function_exists('get_header')) {
    // WordPress mode
    get_header();
    $scenario = isset($_GET['scenario']) ? sanitize_text_field($_GET['scenario']) : '';
    $news_args = array(
        'post_type' => 'news_article',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );
    $social_args = array(
        'post_type' => 'social_post',
        'post_status' => 'publish', 
        'posts_per_page' => -1
    );
    if ($scenario) {
        $news_args['tax_query'] = array(
            array(
                'taxonomy' => 'training_scenario',
                'field' => 'slug',
                'terms' => $scenario
            )
        );
        $social_args['tax_query'] = array(
            array(
                'taxonomy' => 'training_scenario', 
                'field' => 'slug',
                'terms' => $scenario
            )
        );
    }
    $news_posts = get_posts($news_args);
    $social_posts = get_posts($social_args);
    $all_posts = array_merge($news_posts, $social_posts);
    usort($all_posts, function($a, $b) {
        $a_pinned = get_post_meta($a->ID, '_pinned', true);
        $b_pinned = get_post_meta($b->ID, '_pinned', true);
        if ($a_pinned && !$b_pinned) return -1;
        if ($b_pinned && !$a_pinned) return 1;
        return strtotime($b->post_date) - strtotime($a->post_date);
    });
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="container mt-4">
    <!-- Insert Button -->
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#insertModal">
            Insert
        </button>
    </div>
    <!-- Insert Modal -->
    <div class="modal fade" id="insertModal" tabindex="-1" aria-labelledby="insertModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="post" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="insertModalLabel">Create New Post</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="insertMedia" class="form-label">Media Upload (any type)</label>
                <input class="form-control" type="file" id="insertMedia" name="insert_media[]" multiple>
                <small class="form-text text-muted">You can upload images, videos, PDFs, etc.</small>
              </div>
              <div class="mb-3">
                <label for="insertYoutube" class="form-label">YouTube or Facebook URL</label>
                <input class="form-control" type="url" id="insertYoutube" name="insert_youtube_url" placeholder="https://youtube.com/... or https://facebook.com/...">
                <small class="form-text text-muted">Paste a YouTube or Facebook link to embed.</small>
              </div>
              <div class="mb-3">
                <label for="insertTitle" class="form-label">Title</label>
                <input class="form-control" type="text" id="insertTitle" name="insert_title" required>
              </div>
              <div class="mb-3">
                <label for="insertText" class="form-label">Text</label>
                <textarea class="form-control" id="insertText" name="insert_text" rows="3" required></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" name="submit_insert" value="1">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Content Feed -->
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <?php if (empty($all_posts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <h3 class="text-muted">No content available</h3>
                    <p class="text-muted">Create some news articles or social media posts to see them here.</p>
                </div>
            <?php else: ?>
            <form method="post" action="">
               <?php if (function_exists('wp_nonce_field')) wp_nonce_field('bulk_delete_action', 'bulk_delete_nonce'); ?>
                <?php foreach ($all_posts as $post): 
                    setup_postdata($post);
                    if ($post->post_type === 'news_article'):
                        get_template_part('template-parts/content', 'news');
                    else:
                        get_template_part('template-parts/content', 'social'); 
                    endif;
                ?>
                <!-- Reply Button -->
                <div class="mb-4 text-end">
                    <a href="<?php echo get_permalink($post->ID); ?>" class="btn btn-primary">
                        Reply
                    </a>
                </div>
                <?php endforeach; wp_reset_postdata(); ?>
                <!-- Bulk Delete Button -->
                <div class="text-end my-3">
                    <button type="submit" 
                            name="bulk_delete" 
                            value="1" 
                            id="bulkDeleteBtn" 
                            class="btn btn-danger" 
                            style="display:none;">
                        Delete Selected
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<?php 
    get_footer();
} else {
    // Standalone mode - use original functionality
    session_start();
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
    require_once 'includes/auth.php';
    require_once 'themes/newsroom-training/theme-config.php';
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
    $user = getCurrentUser();
    $scenario = isset($_GET['scenario']) ? sanitize_text_field($_GET['scenario']) : null;
    $filteredContent = newsroom_get_content('all', $scenario);
    get_header(); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <div class="container mt-4">
        <!-- Insert Button -->
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#insertModal">
                Insert
            </button>
        </div>
        <!-- Insert Modal -->
        <div class="modal fade" id="insertModal" tabindex="-1" aria-labelledby="insertModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post" enctype="multipart/form-data">
                <div class="modal-header">
                  <h5 class="modal-title" id="insertModalLabel">Create New Post</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="insertMedia" class="form-label">Media Upload (any type)</label>
                    <input class="form-control" type="file" id="insertMedia" name="insert_media[]" multiple>
                    <small class="form-text text-muted">You can upload images, videos, PDFs, etc.</small>
                  </div>
                  <div class="mb-3">
                    <label for="insertYoutube" class="form-label">YouTube or Facebook URL</label>
                    <input class="form-control" type="url" id="insertYoutube" name="insert_youtube_url" placeholder="https://youtube.com/... or https://facebook.com/...">
                    <small class="form-text text-muted">Paste a YouTube or Facebook link to embed.</small>
                  </div>
                  <div class="mb-3">
                    <label for="insertTitle" class="form-label">Title</label>
                    <input class="form-control" type="text" id="insertTitle" name="insert_title" required>
                  </div>
                  <div class="mb-3">
                    <label for="insertText" class="form-label">Text</label>
                    <textarea class="form-control" id="insertText" name="insert_text" rows="3" required></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary" name="submit_insert" value="1">Submit</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- Content Feed -->
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php if (empty($filteredContent)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">No content available</h3>
                        <p class="text-muted">Check back later for new posts.</p>
                    </div>
                <?php else: ?>
      <form method="post" action="">
    <?php 
    usort($filteredContent, function($a, $b) {
        if (!empty($a['pinned']) && empty($b['pinned'])) return -1;
        if (!empty($b['pinned']) && empty($a['pinned'])) return 1;
        return $b['timestamp'] - $a['timestamp'];
    });
    foreach ($filteredContent as $item): 
        $GLOBALS['current_item'] = $item;
        if ($item['type'] === 'news'):
            include 'template-parts/content-news.php';
        else:
            include 'template-parts/content-social.php';
        endif;
    endforeach; 
    ?>
    <!-- Bulk Delete Button -->
<div class="text-end my-3">
    <button type="submit" 
            name="bulk_delete" 
            value="1" 
            id="bulkDeleteBtn" 
            class="btn btn-danger" 
            style="display:none;">
        Delete Selected
    </button>
</div>
</form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php get_footer();
}

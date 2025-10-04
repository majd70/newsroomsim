<?php
/**
 * The main template file for Newsroom Training Theme
 * Hybrid mode - works in both standalone and WordPress environments
 *
 * @package Newsroom_Training
 */

// Start session for success messages
if (!session_id()) {
    session_start();
}

// Check for success message from previous request
if (isset($_SESSION['insert_success']) && $_SESSION['insert_success']) {
    $insert_success = true;
    unset($_SESSION['insert_success']); // Clear it after reading
}

// ---------------------------
// IMAGE UPLOAD HANDLER (supports multiple images)
// ---------------------------
function handle_multiple_image_uploads($file_key) {
    $uploaded_urls = array();

    if (!isset($_FILES[$file_key]) || !is_array($_FILES[$file_key]['name'])) {
        return $uploaded_urls;
    }

    $file_count = count($_FILES[$file_key]['name']);

    for ($i = 0; $i < $file_count; $i++) {
        // Skip if no file or error
        if ($_FILES[$file_key]['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($_FILES[$file_key]['error'][$i] !== UPLOAD_ERR_OK) {
            error_log("Upload error for $file_key[$i]: " . $_FILES[$file_key]['error'][$i]);
            continue;
        }

        // Check if it's an image
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES[$file_key]['type'][$i];

        if (!in_array($file_type, $allowed_types)) {
            error_log("Invalid file type for $file_key[$i]: $file_type");
            continue;
        }

        // Use WordPress upload handler if available
        if (function_exists('wp_handle_upload') && defined('ABSPATH')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');

            // Reconstruct $_FILES array for single file
            $single_file = array(
                'name'     => $_FILES[$file_key]['name'][$i],
                'type'     => $_FILES[$file_key]['type'][$i],
                'tmp_name' => $_FILES[$file_key]['tmp_name'][$i],
                'error'    => $_FILES[$file_key]['error'][$i],
                'size'     => $_FILES[$file_key]['size'][$i]
            );

            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($single_file, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                error_log("Image uploaded successfully: " . $movefile['url']);
                $uploaded_urls[] = $movefile['url'];
            } else {
                error_log("Upload error: " . $movefile['error']);
            }
        } else {
            // Standalone mode - manual upload
            $upload_dir = __DIR__ . '/uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = pathinfo($_FILES[$file_key]['name'][$i], PATHINFO_EXTENSION);
            $new_filename = uniqid('img_') . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES[$file_key]['tmp_name'][$i], $target_path)) {
                // Generate URL
                if (function_exists('get_template_directory_uri')) {
                    $url = get_template_directory_uri() . '/uploads/' . $new_filename;
                } else {
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
                    $url = $protocol . '://' . $host . $script_dir . '/uploads/' . $new_filename;
                }
                error_log("Image uploaded successfully: $url");
                $uploaded_urls[] = $url;
            } else {
                error_log("Failed to move uploaded file");
            }
        }
    }

    return $uploaded_urls;
}

// ---------------------------
// INSERT HANDLER (WordPress mode)
// ---------------------------
$insert_error = '';
// $insert_success already set above from session

// Check if form is submitted - look for content_type instead of submit_insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content_type']) && !empty($_POST['content_type'])) {
    // Debug: Always log when form is submitted
    error_log('=== FORM SUBMITTED ===');
    error_log('POST Data: ' . print_r($_POST, true));
    error_log('FILES Data: ' . print_r($_FILES, true));

    // Also write to a simple text file for debugging
    file_put_contents(
        __DIR__ . '/form_debug.txt',
        date('Y-m-d H:i:s') . " - Form submitted\n" .
        "POST: " . print_r($_POST, true) . "\n" .
        "FILES: " . print_r($_FILES, true) . "\n\n",
        FILE_APPEND
    );

    // Verify nonce
    if (!isset($_POST['insert_content_nonce']) || !wp_verify_nonce($_POST['insert_content_nonce'], 'insert_content_action')) {
        error_log('Nonce verification failed!');
        $insert_error = 'Security check failed. Please refresh the page and try again.';
    } else if (function_exists('wp_insert_post')) {

        $post_type = isset($_POST['content_type']) ? sanitize_text_field($_POST['content_type']) : '';
        $edit_post_id = isset($_POST['edit_post_id']) ? intval($_POST['edit_post_id']) : 0;
        $is_edit_mode = $edit_post_id > 0;

        error_log('Content Type: ' . $post_type);
        error_log('Edit Mode: ' . ($is_edit_mode ? 'YES' : 'NO'));
        if ($is_edit_mode) {
            error_log('Editing Post ID: ' . $edit_post_id);
        }

        // Validation
        $validation_errors = array();

        if ($post_type === 'news_article') {
            // Validate News Article
            // Support both insert_title and headline field names
            $title = isset($_POST['insert_title']) ? trim(sanitize_text_field($_POST['insert_title'])) :
                     (isset($_POST['headline']) ? trim(sanitize_text_field($_POST['headline'])) : '');
            $content = isset($_POST['insert_text']) ? trim(sanitize_textarea_field($_POST['insert_text'])) :
                       (isset($_POST['body']) ? trim(sanitize_textarea_field($_POST['body'])) : '');
            $author = isset($_POST['insert_author']) ? trim(sanitize_text_field($_POST['insert_author'])) :
                      (isset($_POST['author']) ? trim(sanitize_text_field($_POST['author'])) : '');

            if (empty($title)) {
                $validation_errors[] = 'Headline is required';
            }
            if (empty($author)) {
                $validation_errors[] = 'Author is required';
            }
            if (empty($content)) {
                $validation_errors[] = 'Article body is required';
            }

            if (empty($validation_errors)) {
                $category = isset($_POST['insert_category']) ? sanitize_text_field($_POST['insert_category']) : '';

                // Handle multiple featured images upload
                $featured_images = array();
                if (!empty($_FILES['insert_featured_image_files']['name'][0])) {
                    $featured_images = handle_multiple_image_uploads('insert_featured_image_files');
                }

                // Use first image as featured image (for backward compatibility)
                $featured_image = !empty($featured_images) ? $featured_images[0] : '';

                // Store all images as JSON in meta
                $all_images_json = !empty($featured_images) ? json_encode($featured_images) : '';

                $video_url = isset($_POST['insert_video_url']) ? esc_url_raw($_POST['insert_video_url']) : '';
                $breaking = isset($_POST['insert_breaking']) ? '1' : '0';

                error_log('Creating news article: ' . $title);
                if (!empty($featured_images)) {
                    error_log('Featured images (' . count($featured_images) . '): ' . implode(', ', $featured_images));
                }

                if ($is_edit_mode) {
                    // Update existing post
                    $update_post = array(
                        'ID'           => $edit_post_id,
                        'post_title'   => $title,
                        'post_content' => $content,
                    );
                    $post_id = wp_update_post($update_post);

                    if ($post_id && !is_wp_error($post_id)) {
                        // Update meta
                        update_post_meta($post_id, '_news_author', $author);
                        update_post_meta($post_id, '_news_breaking', $breaking);
                        update_post_meta($post_id, '_news_video_embed', $video_url);

                        // Update images only if new ones uploaded
                        if (!empty($featured_images)) {
                            update_post_meta($post_id, '_news_featured_image', $featured_image);
                            update_post_meta($post_id, '_news_all_images', $all_images_json);
                        }

                        error_log('News article updated successfully: ' . $post_id);
                    }
                } else {
                    // Create new post
                    $new_post = array(
                        'post_title'   => $title,
                        'post_content' => $content,
                        'post_status'  => 'publish',
                        'post_type'    => 'news_article',
                        'meta_input' => array(
                            '_news_author' => $author,
                            '_news_breaking' => $breaking,
                            '_news_featured_image' => $featured_image,
                            '_news_all_images' => $all_images_json,
                            '_news_video_embed' => $video_url
                        )
                    );
                    $post_id = wp_insert_post($new_post);
                }

                if ($post_id && !is_wp_error($post_id)) {
                    error_log('News article ' . ($is_edit_mode ? 'updated' : 'created') . ' successfully: ' . $post_id);

                    // Verify images were saved
                    if (!empty($featured_images)) {
                        $saved_images = get_post_meta($post_id, '_news_all_images', true);
                        error_log('All images saved in DB: ' . $saved_images);
                    }

                    if (!empty($category)) {
                        wp_set_post_terms($post_id, array($category), 'news_category');
                    }
                    $insert_success = true;
                } else {
                    $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
                    error_log('Error creating news article: ' . $error_message);
                    $insert_error = 'Failed to create news article: ' . $error_message;
                }
            } else {
                $insert_error = implode(', ', $validation_errors);
            }
        } elseif (in_array($post_type, array('social_twitter', 'social_facebook', 'social_instagram'))) {
            // Validate Social Media Post
            $platform = str_replace('social_', '', $post_type);
            // Support both insert_display_name and display_name field names
            $display_name = isset($_POST['insert_display_name']) ? trim(sanitize_text_field($_POST['insert_display_name'])) :
                           (isset($_POST['display_name']) ? trim(sanitize_text_field($_POST['display_name'])) : '');
            $handle = isset($_POST['insert_handle']) ? trim(sanitize_text_field($_POST['insert_handle'])) :
                     (isset($_POST['handle']) ? trim(sanitize_text_field($_POST['handle'])) : '');
            $text = isset($_POST['insert_text']) ? trim(sanitize_textarea_field($_POST['insert_text'])) :
                   (isset($_POST['text']) ? trim(sanitize_textarea_field($_POST['text'])) : '');

            if (empty($display_name)) {
                $validation_errors[] = 'Display name is required';
            }
            if (empty($handle)) {
                $validation_errors[] = 'Handle/Username is required';
            }
            if (empty($text)) {
                $validation_errors[] = 'Post text is required';
            }

            if (empty($validation_errors)) {
                // Handle multiple media images upload
                $media_images = array();
                if (!empty($_FILES['insert_media_files']['name'][0])) {
                    $media_images = handle_multiple_image_uploads('insert_media_files');
                }

                // Use first image as main media (for backward compatibility)
                $media = !empty($media_images) ? $media_images[0] : '';

                // Store all images as JSON in meta
                $all_media_json = !empty($media_images) ? json_encode($media_images) : '';

                error_log(($is_edit_mode ? 'Updating' : 'Creating') . ' social post: ' . $platform . ' - ' . $display_name);
                if (!empty($media_images)) {
                    error_log('Media images (' . count($media_images) . '): ' . implode(', ', $media_images));
                }

                if ($is_edit_mode) {
                    // Update existing post
                    $update_post = array(
                        'ID'           => $edit_post_id,
                        'post_title'   => $display_name . ' - ' . ucfirst($platform) . ' Post',
                        'post_content' => $text,
                    );
                    $post_id = wp_update_post($update_post);

                    if ($post_id && !is_wp_error($post_id)) {
                        // Update meta
                        update_post_meta($post_id, '_social_platform', $platform);
                        update_post_meta($post_id, '_social_display_name', $display_name);
                        update_post_meta($post_id, '_social_handle', $handle);

                        // Update images only if new ones uploaded
                        if (!empty($media_images)) {
                            update_post_meta($post_id, '_social_media', $media);
                            update_post_meta($post_id, '_social_all_media', $all_media_json);
                        }

                        error_log('Social post updated successfully: ' . $post_id);
                    }
                } else {
                    // Create new post
                    $new_post = array(
                        'post_title'   => $display_name . ' - ' . ucfirst($platform) . ' Post',
                        'post_content' => $text,
                        'post_status'  => 'publish',
                        'post_type'    => 'social_post',
                        'meta_input' => array(
                            '_social_platform' => $platform,
                            '_social_display_name' => $display_name,
                            '_social_handle' => $handle,
                            '_social_media' => $media,
                            '_social_all_media' => $all_media_json
                        )
                    );
                    $post_id = wp_insert_post($new_post);
                }

                if ($post_id && !is_wp_error($post_id)) {
                    error_log('Social post ' . ($is_edit_mode ? 'updated' : 'created') . ' successfully: ' . $post_id);

                    // Verify images were saved
                    if (!empty($media_images)) {
                        $saved_media = get_post_meta($post_id, '_social_all_media', true);
                        error_log('All media saved in DB: ' . $saved_media);
                    }

                    wp_set_post_terms($post_id, array($platform), 'social_platform');
                    $insert_success = true;
                } else {
                    $error_message = is_wp_error($post_id) ? $post_id->get_error_message() : 'Unknown error';
                    error_log('Error creating social post: ' . $error_message);
                    $insert_error = 'Failed to create social post: ' . $error_message;
                }
            } else {
                $insert_error = implode(', ', $validation_errors);
            }
        } else {
            error_log('Unknown content type: ' . $post_type);
            $insert_error = 'Invalid content type selected';
        }

        // Only redirect if successful
        if ($insert_success) {
            $_SESSION['insert_success'] = true;
            error_log('✅ Post created successfully! Redirecting...');

            // Redirect to avoid resubmission
            $redirect_url = function_exists('home_url') ? home_url() : $_SERVER['PHP_SELF'];
            error_log('Redirect URL: ' . $redirect_url);

            if (function_exists('wp_redirect')) {
                wp_redirect($redirect_url);
            } else {
                header('Location: ' . $redirect_url);
            }
            exit;
        }
    } else {
        // Standalone mode: implement your own insert logic here if needed
        error_log('❌ WordPress functions not available');
        $insert_error = 'WordPress functions not available';
    }
} else {
    // Log why form was not processed
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('⚠️ POST request received but content_type missing or empty');
        error_log('POST keys: ' . implode(', ', array_keys($_POST)));
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
    <?php
    // Show success message (already read from session at top of file)
    if ($insert_success) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> Content published successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }

    // Show error message
    if (!empty($insert_error)) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> ' . esc_html($insert_error) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
    ?>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-end gap-2 mb-3">
        <?php
        // Only Newsroom Operator and Administrator can delete all posts
        if (current_user_can('delete_others_posts')):
        ?>
        <button type="button" class="btn btn-danger" id="deleteAllPostsBtn" data-nonce="<?php echo wp_create_nonce('delete_all_posts_nonce'); ?>">
            <i class="fas fa-trash-alt me-2"></i>Delete All Posts
        </button>
        <?php endif; ?>

        <?php
        // Only Newsroom Operator and Administrator can publish posts
        if (current_user_can('publish_posts')):
        ?>
        <button type="button" class="btn btn-publish-post" data-bs-toggle="modal" data-bs-target="#insertModal">
            <i class="fas fa-plus-circle me-2"></i>Publish Post
        </button>
        <?php endif; ?>
    </div>
    <!-- Insert Modal -->
    <div class="modal fade" id="insertModal" tabindex="-1" aria-labelledby="insertModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data" id="insertForm" novalidate>
            <?php if (function_exists('wp_nonce_field')) wp_nonce_field('insert_content_action', 'insert_content_nonce'); ?>
            <div class="modal-header">
              <h5 class="modal-title" id="insertModalLabel">Create Content</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <?php if (!empty($insert_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <strong>Error:</strong> <?php echo esc_html($insert_error); ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              <!-- Content Type Tabs -->
              <ul class="nav nav-tabs mb-3" id="contentTypeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="news-tab" data-bs-toggle="tab" data-bs-target="#news-content" type="button" role="tab" aria-controls="news-content" aria-selected="true">News Article</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="twitter-tab" data-bs-toggle="tab" data-bs-target="#twitter-content" type="button" role="tab" aria-controls="twitter-content" aria-selected="false">X (Twitter)</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="facebook-tab" data-bs-toggle="tab" data-bs-target="#facebook-content" type="button" role="tab" aria-controls="facebook-content" aria-selected="false">Facebook</button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="instagram-tab" data-bs-toggle="tab" data-bs-target="#instagram-content" type="button" role="tab" aria-controls="instagram-content" aria-selected="false">Instagram</button>
                </li>
              </ul>

              <!-- Tab Content -->
              <div class="tab-content" id="contentTypeTabContent" style="min-height: 400px;">
                <!-- News Article Form -->
                <div class="tab-pane fade show active" id="news-content" role="tabpanel">
                  <input type="hidden" name="content_type" value="news_article" id="content_type_field">
                  <input type="hidden" name="edit_post_id" value="" id="edit_post_id_field">

                  <div class="mb-3">
                    <label for="insertTitle" class="form-label">Headline *</label>
                    <input class="form-control" type="text" id="insertTitle" name="insert_title" required>
                    <div class="invalid-feedback">Please enter a headline.</div>
                  </div>
                  <div class="mb-3">
                    <label for="insertAuthor" class="form-label">Author *</label>
                    <input class="form-control" type="text" id="insertAuthor" name="insert_author" placeholder="Author alias" required>
                    <div class="invalid-feedback">Please enter an author name.</div>
                  </div>
                  <div class="mb-3">
                    <label for="insertText" class="form-label">Article Body *</label>
                    <textarea class="form-control" id="insertText" name="insert_text" rows="5" required></textarea>
                    <div class="invalid-feedback">Please enter article body.</div>
                  </div>
                  <div class="mb-3">
                    <label for="insertCategory" class="form-label">Category</label>
                    <select class="form-select" id="insertCategory" name="insert_category">
                      <option value="">Select Category</option>
                      <option value="Breaking News">Breaking News</option>
                      <option value="Politics">Politics</option>
                      <option value="Business">Business</option>
                      <option value="Technology">Technology</option>
                      <option value="Sports">Sports</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="insertFeaturedImage" class="form-label">Article Images (you can select multiple)</label>
                    <input class="form-control" type="file" id="insertFeaturedImage" name="insert_featured_image_files[]" accept="image/*" multiple>
                    <div class="form-text">Select one or more images</div>
                    <div id="featuredImagePreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                  </div>
                  <div class="mb-3">
                    <label for="insertVideoUrl" class="form-label">Video Embed URL</label>
                    <input class="form-control" type="url" id="insertVideoUrl" name="insert_video_url" placeholder="YouTube or Vimeo URL">
                  </div>
                  <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" id="insertBreaking" name="insert_breaking" value="1">
                    <label class="form-check-label" for="insertBreaking">Mark as breaking news</label>
                  </div>
                </div>

                <!-- Twitter Form -->
                <div class="tab-pane fade" id="twitter-content" role="tabpanel">
                  <input type="hidden" name="content_type" value="social_twitter" id="content_type_field_twitter">

                  <div class="mb-3">
                    <label for="twitterDisplayName" class="form-label">Display Name *</label>
                    <input class="form-control" type="text" id="twitterDisplayName" name="insert_display_name" data-required-for="social_twitter">
                    <div class="invalid-feedback">Please enter a display name.</div>
                  </div>
                  <div class="mb-3">
                    <label for="twitterHandle" class="form-label">Handle/Username *</label>
                    <input class="form-control" type="text" id="twitterHandle" name="insert_handle" placeholder="@username" data-required-for="social_twitter">
                    <div class="invalid-feedback">Please enter a handle/username.</div>
                  </div>
                  <div class="mb-3">
                    <label for="twitterText" class="form-label">Post Text *</label>
                    <textarea class="form-control" id="twitterText" name="insert_text" rows="4" data-required-for="social_twitter"></textarea>
                    <div class="invalid-feedback">Please enter post text.</div>
                  </div>
                  <div class="mb-3">
                    <label for="twitterMedia" class="form-label">Post Images (you can select multiple)</label>
                    <input class="form-control" type="file" id="twitterMedia" name="insert_media_files[]" accept="image/*" multiple>
                    <div class="form-text">Select one or more images</div>
                    <div id="twitterMediaPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                  </div>
                </div>

                <!-- Facebook Form -->
                <div class="tab-pane fade" id="facebook-content" role="tabpanel">
                  <input type="hidden" name="content_type" value="social_facebook" id="content_type_field_facebook">

                  <div class="mb-3">
                    <label for="facebookDisplayName" class="form-label">Display Name *</label>
                    <input class="form-control" type="text" id="facebookDisplayName" name="insert_display_name" data-required-for="social_facebook">
                    <div class="invalid-feedback">Please enter a display name.</div>
                  </div>
                  <div class="mb-3">
                    <label for="facebookHandle" class="form-label">Handle/Username *</label>
                    <input class="form-control" type="text" id="facebookHandle" name="insert_handle" data-required-for="social_facebook">
                    <div class="invalid-feedback">Please enter a handle/username.</div>
                  </div>
                  <div class="mb-3">
                    <label for="facebookText" class="form-label">Post Text *</label>
                    <textarea class="form-control" id="facebookText" name="insert_text" rows="4" data-required-for="social_facebook"></textarea>
                    <div class="invalid-feedback">Please enter post text.</div>
                  </div>
                  <div class="mb-3">
                    <label for="facebookMedia" class="form-label">Post Images (you can select multiple)</label>
                    <input class="form-control" type="file" id="facebookMedia" name="insert_media_files[]" accept="image/*" multiple>
                    <div class="form-text">Select one or more images</div>
                    <div id="facebookMediaPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                  </div>
                </div>

                <!-- Instagram Form -->
                <div class="tab-pane fade" id="instagram-content" role="tabpanel">
                  <input type="hidden" name="content_type" value="social_instagram" id="content_type_field_instagram">

                  <div class="mb-3">
                    <label for="instagramDisplayName" class="form-label">Display Name *</label>
                    <input class="form-control" type="text" id="instagramDisplayName" name="insert_display_name" data-required-for="social_instagram">
                    <div class="invalid-feedback">Please enter a display name.</div>
                  </div>
                  <div class="mb-3">
                    <label for="instagramHandle" class="form-label">Handle/Username *</label>
                    <input class="form-control" type="text" id="instagramHandle" name="insert_handle" placeholder="@username" data-required-for="social_instagram">
                    <div class="invalid-feedback">Please enter a handle/username.</div>
                  </div>
                  <div class="mb-3">
                    <label for="instagramText" class="form-label">Post Text *</label>
                    <textarea class="form-control" id="instagramText" name="insert_text" rows="4" data-required-for="social_instagram"></textarea>
                    <div class="invalid-feedback">Please enter post text.</div>
                  </div>
                  <div class="mb-3">
                    <label for="instagramMedia" class="form-label">Post Images (you can select multiple)</label>
                    <input class="form-control" type="file" id="instagramMedia" name="insert_media_files[]" accept="image/*" multiple>
                    <div class="form-text">Select one or more images</div>
                    <div id="instagramMediaPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" name="submit_insert" value="1">Publish Post</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Twitter Modal -->
    <div class="modal fade" id="editTwitterModal" tabindex="-1" aria-labelledby="editTwitterModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="editTwitterForm" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('insert_content_action', 'insert_content_nonce'); ?>
            <div class="modal-header">
              <h5 class="modal-title" id="editTwitterModalLabel">Edit X (Twitter) Post</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="content_type" value="social_twitter">
              <input type="hidden" name="edit_post_id" id="editTwitterPostId">

              <div class="mb-3">
                <label for="editTwitterDisplayName" class="form-label">Display Name *</label>
                <input class="form-control" type="text" id="editTwitterDisplayName" name="insert_display_name" required>
              </div>
              <div class="mb-3">
                <label for="editTwitterHandle" class="form-label">Handle *</label>
                <input class="form-control" type="text" id="editTwitterHandle" name="insert_handle" required>
              </div>
              <div class="mb-3">
                <label for="editTwitterText" class="form-label">Tweet Text *</label>
                <textarea class="form-control" id="editTwitterText" name="insert_text" rows="4" required></textarea>
              </div>
              <div class="mb-3">
                <label for="editTwitterMedia" class="form-label">Post Images (you can select multiple)</label>
                <input class="form-control" type="file" id="editTwitterMedia" name="insert_media_files[]" accept="image/*" multiple>
                <div class="form-text">Select one or more images</div>
                <div id="editTwitterMediaPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" name="submit_insert" value="1">Update Post</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Facebook Modal -->
    <div class="modal fade" id="editFacebookModal" tabindex="-1" aria-labelledby="editFacebookModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="editFacebookForm" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('insert_content_action', 'insert_content_nonce'); ?>
            <div class="modal-header">
              <h5 class="modal-title" id="editFacebookModalLabel">Edit Facebook Post</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="content_type" value="social_facebook">
              <input type="hidden" name="edit_post_id" id="editFacebookPostId">

              <div class="mb-3">
                <label for="editFacebookDisplayName" class="form-label">Display Name *</label>
                <input class="form-control" type="text" id="editFacebookDisplayName" name="insert_display_name" required>
              </div>
              <div class="mb-3">
                <label for="editFacebookHandle" class="form-label">Handle *</label>
                <input class="form-control" type="text" id="editFacebookHandle" name="insert_handle" required>
              </div>
              <div class="mb-3">
                <label for="editFacebookText" class="form-label">Post Text *</label>
                <textarea class="form-control" id="editFacebookText" name="insert_text" rows="4" required></textarea>
              </div>
              <div class="mb-3">
                <label for="editFacebookMedia" class="form-label">Post Images (you can select multiple)</label>
                <input class="form-control" type="file" id="editFacebookMedia" name="insert_media_files[]" accept="image/*" multiple>
                <div class="form-text">Select one or more images</div>
                <div id="editFacebookMediaPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" name="submit_insert" value="1">Update Post</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Instagram Modal -->
    <div class="modal fade" id="editInstagramModal" tabindex="-1" aria-labelledby="editInstagramModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="editInstagramForm" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('insert_content_action', 'insert_content_nonce'); ?>
            <div class="modal-header">
              <h5 class="modal-title" id="editInstagramModalLabel">Edit Instagram Post</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="content_type" value="social_instagram">
              <input type="hidden" name="edit_post_id" id="editInstagramPostId">

              <div class="mb-3">
                <label for="editInstagramDisplayName" class="form-label">Display Name *</label>
                <input class="form-control" type="text" id="editInstagramDisplayName" name="insert_display_name" required>
              </div>
              <div class="mb-3">
                <label for="editInstagramHandle" class="form-label">Handle *</label>
                <input class="form-control" type="text" id="editInstagramHandle" name="insert_handle" required>
              </div>
              <div class="mb-3">
                <label for="editInstagramText" class="form-label">Caption *</label>
                <textarea class="form-control" id="editInstagramText" name="insert_text" rows="4" required></textarea>
              </div>
              <div class="mb-3">
                <label for="editInstagramMedia" class="form-label">Post Images (you can select multiple)</label>
                <input class="form-control" type="file" id="editInstagramMedia" name="insert_media_files[]" accept="image/*" multiple>
                <div class="form-text">Select one or more images</div>
                <div id="editInstagramMediaPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" name="submit_insert" value="1">Update Post</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit News Modal -->
    <div class="modal fade" id="editNewsModal" tabindex="-1" aria-labelledby="editNewsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="editNewsForm" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('insert_content_action', 'insert_content_nonce'); ?>
            <div class="modal-header">
              <h5 class="modal-title" id="editNewsModalLabel">Edit News Article</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="content_type" value="news_article">
              <input type="hidden" name="edit_post_id" id="editNewsPostId">

              <div class="mb-3">
                <label for="editNewsTitle" class="form-label">Headline *</label>
                <input class="form-control" type="text" id="editNewsTitle" name="insert_title" required>
              </div>
              <div class="mb-3">
                <label for="editNewsAuthor" class="form-label">Author *</label>
                <input class="form-control" type="text" id="editNewsAuthor" name="insert_author" required>
              </div>
              <div class="mb-3">
                <label for="editNewsBody" class="form-label">Article Body *</label>
                <textarea class="form-control" id="editNewsBody" name="insert_body" rows="6" required></textarea>
              </div>
              <div class="mb-3">
                <label for="editNewsCategory" class="form-label">Category</label>
                <select class="form-select" id="editNewsCategory" name="insert_category">
                  <option value="">Select Category</option>
                  <option value="Politics">Politics</option>
                  <option value="Business">Business</option>
                  <option value="Technology">Technology</option>
                  <option value="Sports">Sports</option>
                  <option value="Entertainment">Entertainment</option>
                  <option value="Health">Health</option>
                  <option value="Science">Science</option>
                  <option value="World">World</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="editNewsVideoUrl" class="form-label">Video URL (optional)</label>
                <input class="form-control" type="url" id="editNewsVideoUrl" name="insert_video_url">
              </div>
              <div class="mb-3 form-check">
                <input class="form-check-input" type="checkbox" id="editNewsBreaking" name="insert_breaking" value="1">
                <label class="form-check-label" for="editNewsBreaking">Breaking News</label>
              </div>
              <div class="mb-3">
                <label for="editNewsImages" class="form-label">Article Images (you can select multiple)</label>
                <input class="form-control" type="file" id="editNewsImages" name="insert_image_files[]" accept="image/*" multiple>
                <div class="form-text">Select one or more images</div>
                <div id="editNewsImagesPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" name="submit_insert" value="1">Update Article</button>
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
                <!-- Reply Button - Only for Trainee and above (not Viewer) -->
                <?php if (current_user_can('add_reply') || current_user_can('edit_posts')): ?>
                <div class="mb-4 text-end">
                    <a href="<?php echo get_permalink($post->ID); ?>" class="btn btn-primary">
                        Reply
                    </a>
                </div>
                <?php endif; ?>
                <?php endforeach; wp_reset_postdata(); ?>
                <!-- Bulk Delete Button - Only for Newsroom Operator and Administrator -->
                <?php if (current_user_can('delete_others_posts')): ?>
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
                <?php endif; ?>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Fancy Publish Post Button */
.btn-publish-post {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 16px;
    padding: 12px 28px;
    border: none;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-publish-post:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    color: white;
}

.btn-publish-post:active {
    transform: translateY(0);
    box-shadow: 0 2px 10px rgba(102, 126, 234, 0.4);
}

.btn-publish-post i {
    font-size: 18px;
}

/* Ensure tab content displays properly */
#insertModal .tab-content {
    display: block !important;
}
#insertModal .tab-pane {
    display: none;
}
#insertModal .tab-pane.active {
    display: block !important;
}

/* Image preview styling */
[id$="Preview"] {
    margin-top: 10px;
}
[id$="Preview"] img {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
[id$="Preview"] .btn-danger {
    vertical-align: top;
}

/* File input styling */
input[type="file"] {
    padding: 8px;
}
.form-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
    margin-bottom: 0.25rem;
}

/* Multiple images grid styling */
.social-media .row,
.media-wrapper .row {
    margin: 0;
}
.social-media img,
.media-wrapper img {
    transition: transform 0.2s;
}
.social-media img:hover,
.media-wrapper img:hover {
    transform: scale(1.02);
}

/* 3 Dots Menu Styling */
.post-menu {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
}
.post-menu .btn-link {
    font-size: 1.2rem;
    text-decoration: none;
    color: #6c757d !important;
    transition: color 0.2s;
    background: none;
    border: none;
    padding: 5px 10px;
}
.post-menu .btn-link:hover {
    color: #000 !important;
}
.post-menu .dropdown-menu {
    min-width: 180px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1001;
}
.post-menu .dropdown-item {
    padding: 0.5rem 1rem;
    transition: background-color 0.2s;
}
.post-menu .dropdown-item:hover {
    background-color: #f8f9fa;
}
.post-menu .dropdown-item.text-danger:hover {
    background-color: #fee;
}

/* Adjust social header for menu */
.social-header {
    position: relative;
}

/* Adjust card-meta for menu */
.card-meta {
    position: relative;
    display: flex;
    align-items: center;
}

/* Ensure cards don't hide dropdown */
.content-card {
    overflow: visible !important;
}
.social-card, .news-card {
    overflow: visible !important;
}

/* Ensure social actions are clickable */
.social-actions {
    position: relative;
    z-index: 100;
    pointer-events: auto;
}

.social-actions * {
    pointer-events: auto;
}

/* Inline Comments Styling */
.inline-comments-section {
    margin-top: 15px;
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
    background-color: #f8f9fa;
    border-radius: 0 0 8px 8px;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        max-height: 0;
        padding-top: 0;
    }
    to {
        opacity: 1;
        max-height: 500px;
        padding-top: 15px;
    }
}

.comments-container {
    padding: 0 15px 15px;
}

.comments-header h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 15px;
}

.comments-list {
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.comment-item {
    background: white !important;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
}

.comment-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.comment-item:last-child {
    margin-bottom: 0;
}

.inline-comment-form {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.inline-comment-form textarea {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 10px;
    font-size: 14px;
    resize: vertical;
    min-height: 80px;
    transition: border-color 0.2s ease;
}

.inline-comment-form textarea:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.social-action-btn {
    background: none;
    border: none;
    color: #6c757d;
    font-size: 14px;
    padding: 8px 12px;
    border-radius: 20px;
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    margin-right: 10px;
    cursor: pointer;
    pointer-events: auto;
    z-index: 10;
}

.social-action-btn:hover {
    background-color: #e9ecef;
    color: #495057;
    text-decoration: none;
}

.social-action-btn:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(0,123,255,.25);
}

.social-action-btn.active {
    background-color: #007bff;
    color: white;
}

.social-actions {
    padding: 10px 15px;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    position: relative;
    z-index: 5;
}

/* Loading states */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Comment count styling */
.comments-count {
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .comments-container {
        padding: 0 10px 10px;
    }

    .inline-comment-form {
        padding: 10px;
    }

    .social-action-btn {
        font-size: 13px;
        padding: 6px 10px;
        margin-right: 5px;
    }

    .comments-list {
        max-height: 300px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ═══════════════════════════════════════════════════
// DELETE FUNCTION - MUST BE GLOBAL (outside DOMContentLoaded)
// ═══════════════════════════════════════════════════
window.confirmDelete = function(button, postId) {
    console.log('═══════════════════════════════════════════════════');
    console.log('🗑️ DELETE BUTTON CLICKED!');
    console.log('Post ID:', postId);
    console.log('Button element:', button);
    console.log('Button HTML:', button.outerHTML);

    if (confirm('Are you sure you want to delete this post?')) {
        console.log('✅ User confirmed deletion');

        // Find the form
        const form = button.closest('form');
        console.log('📋 Form found:', form);

        if (!form) {
            console.error('❌ NO FORM FOUND!');
            alert('Error: Could not find form');
            return false;
        }

        console.log('📋 Form action:', form.action);
        console.log('📋 Form method:', form.method);

        // Check for nonce
        const nonceField = form.querySelector('input[name="bulk_delete_nonce"]');
        console.log('🔐 Nonce field found:', nonceField);
        console.log('🔐 Nonce value:', nonceField ? nonceField.value : 'NOT FOUND');

        // Remove any existing delete_ids[] hidden inputs to avoid duplicates
        const existingInputs = form.querySelectorAll('input[name="delete_ids[]"]');
        console.log('🔍 Found existing delete_ids[] inputs:', existingInputs.length);
        existingInputs.forEach(input => {
            if (input.type === 'hidden') {
                console.log('🗑️ Removing existing hidden input:', input.value);
                input.remove();
            }
        });

        // Add the post ID to delete
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'delete_ids[]';
        hiddenInput.value = postId;
        form.appendChild(hiddenInput);
        console.log('✅ Added hidden input with post ID:', postId);

        // Log all form data
        const formData = new FormData(form);
        console.log('📦 FORM DATA BEING SUBMITTED:');
        for (let [key, value] of formData.entries()) {
            console.log(`   ${key}: ${value}`);
        }

        console.log('📤 SUBMITTING FORM NOW...');
        console.log('═══════════════════════════════════════════════════');
        return true; // Allow form submission
    } else {
        console.log('❌ User cancelled deletion');
        console.log('═══════════════════════════════════════════════════');
        return false; // Prevent form submission
    }
};

console.log('✅ confirmDelete function defined globally');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    console.log('Bootstrap version:', typeof bootstrap !== 'undefined' ? 'Loaded' : 'NOT LOADED');

    // Handle tab switching and update content_type field
    const tabs = document.querySelectorAll('#contentTypeTabs button[data-bs-toggle="tab"]');
    const contentTypeField = document.getElementById('content_type_field');

    console.log('Found tabs:', tabs.length);
    console.log('Content type field:', contentTypeField);

    // Add form validation and submission handler
    const insertForm = document.getElementById('insertForm');
    console.log('Insert form found:', insertForm ? 'YES' : 'NO');

    // Also add click handler to submit button for debugging
    const submitBtn = document.querySelector('button[name="submit_insert"]');
    console.log('Submit button found:', submitBtn ? 'YES' : 'NO');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            console.log('>>> Submit button CLICKED <<<');
        });
    }

    if (insertForm) {
        console.log('Attaching submit event listener to form');
        insertForm.addEventListener('submit', function(e) {
            console.log('=== FORM SUBMISSION STARTED ===');

            // Get current content type from the enabled field
            const enabledContentTypeField = insertForm.querySelector('[id^="content_type_field"]:not([disabled])');
            const contentType = enabledContentTypeField ? enabledContentTypeField.value : 'news_article';
            console.log('Content type:', contentType);
            console.log('Enabled field:', enabledContentTypeField ? enabledContentTypeField.id : 'NONE');

            // Log all form data before validation
            const formData = new FormData(insertForm);
            console.log('Form data before validation:');
            for (let [key, value] of formData.entries()) {
                console.log('  ' + key + ': ' + value);
            }

            // Get the active tab (must have both 'show' and 'active' classes)
            const activeTab = insertForm.querySelector('.tab-pane.show.active');
            console.log('Active tab:', activeTab ? activeTab.id : 'NONE');

            // IMPORTANT: Disable all content_type fields from inactive tabs
            // This prevents multiple content_type values from being submitted
            const allContentTypeFields = insertForm.querySelectorAll('[id^="content_type_field"]');
            allContentTypeFields.forEach(field => {
                const fieldTab = field.closest('.tab-pane');
                if (fieldTab && fieldTab.classList.contains('active')) {
                    // Enable the field in the active tab
                    field.disabled = false;
                    console.log('Enabled content_type field in active tab:', field.id);
                } else {
                    // Disable the field in inactive tabs (disabled fields are not submitted)
                    field.disabled = true;
                    console.log('Disabled content_type field in inactive tab:', field.id);
                }
            });

            // STEP 1: Disable ALL inputs in ALL tabs first
            const allTabs = insertForm.querySelectorAll('.tab-pane');
            allTabs.forEach(tab => {
                const inputs = tab.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    // Skip hidden content_type fields
                    if (input.name !== 'content_type') {
                        input.disabled = true;
                        input.removeAttribute('required');
                    }
                });
            });

            // STEP 2: Enable ONLY inputs in the active tab
            if (activeTab) {
                const activeInputs = activeTab.querySelectorAll('input, textarea, select');
                activeInputs.forEach(input => {
                    input.disabled = false;
                    console.log('Enabled input:', input.name, 'value:', input.value);
                });

                // STEP 3: Add required attributes ONLY for fields in the active tab
                if (contentType === 'news_article') {
                    // News article required fields
                    const requiredFields = ['insert_title', 'insert_author', 'insert_text'];
                    requiredFields.forEach(fieldName => {
                        const field = activeTab.querySelector('[name="' + fieldName + '"]');
                        if (field) {
                            field.setAttribute('required', 'required');
                            console.log('Set required on:', fieldName);
                        }
                    });
                } else if (contentType.startsWith('social_')) {
                    // Social media required fields - only in active tab
                    const requiredFields = activeTab.querySelectorAll('[data-required-for="' + contentType + '"]');
                    console.log('Found ' + requiredFields.length + ' required fields for ' + contentType + ' in active tab');
                    requiredFields.forEach(field => {
                        field.setAttribute('required', 'required');
                        console.log('Set required on:', field.name, 'value:', field.value);
                    });
                }
            }

            // Validate form
            const isValid = insertForm.checkValidity();
            console.log('Form validity check result:', isValid);

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                insertForm.classList.add('was-validated');
                console.log('❌ Form validation FAILED');

                // Log which fields are invalid (only enabled fields)
                const invalidFields = Array.from(insertForm.querySelectorAll(':invalid')).filter(f => !f.disabled);
                console.log('Invalid fields:', invalidFields.length);

                const invalidFieldNames = [];
                invalidFields.forEach(field => {
                    console.log('  - Invalid:', field.name, 'value:', field.value, 'message:', field.validationMessage);

                    // Get friendly field name from label
                    const label = field.closest('.mb-3')?.querySelector('label');
                    const friendlyName = label ? label.textContent.replace('*', '').trim() : field.name;
                    invalidFieldNames.push(friendlyName);
                });

                // Show alert to user with friendly names
                if (invalidFieldNames.length > 0) {
                    alert('❌ Please fill in all required fields:\n\n• ' + invalidFieldNames.join('\n• '));
                }

                return false;
            }

            insertForm.classList.add('was-validated');
            console.log('✅ Form is valid, submitting...');
            console.log('About to submit form to:', insertForm.action);

            // Log final form data that will be submitted
            const finalFormData = new FormData(insertForm);
            console.log('=== FINAL FORM DATA TO BE SUBMITTED ===');
            for (let [key, value] of finalFormData.entries()) {
                console.log('  ' + key + ': ' + value);
            }
            console.log('=== END FINAL FORM DATA ===');

            // Disable submit button to prevent double submission
            let submitBtnTemp = insertForm.querySelector('button[type="submit"]');
            if (submitBtnTemp) {
                submitBtnTemp.disabled = true;
                submitBtnTemp.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publishing...';
            }

            console.log('Form submission handler complete, browser will now submit form');
            // Let the form submit naturally (don't call e.preventDefault())
        });

        // Reset form when modal is closed
        const insertModal = document.getElementById('insertModal');
        if (insertModal) {
            insertModal.addEventListener('hidden.bs.modal', function() {
                insertForm.reset();
                insertForm.classList.remove('was-validated');

                // Re-enable all inputs
                const allInputs = insertForm.querySelectorAll('input, textarea, select');
                allInputs.forEach(input => {
                    input.disabled = false;
                    input.removeAttribute('required');
                });

                let submitBtnReset = insertForm.querySelector('button[type="submit"]');
                if (submitBtnReset) {
                    submitBtnReset.disabled = false;
                    submitBtnReset.innerHTML = 'Publish Post';
                }

                // Clear image previews and selected files
                document.querySelectorAll('[id$="Preview"]').forEach(preview => {
                    preview.innerHTML = '';
                });

                // Clear selected files map
                selectedFilesMap.clear();
                imageInputs.forEach(({ input }) => {
                    selectedFilesMap.set(input, []);
                });

                // Reset edit mode
                document.getElementById('edit_post_id_field').value = '';
                let submitBtnModal = insertForm.querySelector('button[type="submit"]');
                if (submitBtnModal) {
                    submitBtnModal.textContent = 'Publish Post';
                }
            });
        }

        // Image preview handlers for multiple images with accumulation
        const imageInputs = [
            { input: 'insertFeaturedImage', preview: 'featuredImagePreview' },
            { input: 'twitterMedia', preview: 'twitterMediaPreview' },
            { input: 'facebookMedia', preview: 'facebookMediaPreview' },
            { input: 'instagramMedia', preview: 'instagramMediaPreview' },
            { input: 'editTwitterMedia', preview: 'editTwitterMediaPreview' },
            { input: 'editFacebookMedia', preview: 'editFacebookMediaPreview' },
            { input: 'editInstagramMedia', preview: 'editInstagramMediaPreview' },
            { input: 'editNewsImages', preview: 'editNewsImagesPreview' }
        ];

        // Store selected files for each input
        const selectedFilesMap = new Map();

        imageInputs.forEach(({ input, preview }) => {
            const fileInput = document.getElementById(input);
            const previewDiv = document.getElementById(preview);

            if (fileInput && previewDiv) {
                // Initialize empty array for this input
                selectedFilesMap.set(input, []);

                fileInput.addEventListener('change', function(e) {
                    const newFiles = Array.from(e.target.files);

                    if (newFiles.length === 0) return;

                    // Add new files to existing ones
                    const existingFiles = selectedFilesMap.get(input) || [];
                    const allFiles = [...existingFiles, ...newFiles];
                    selectedFilesMap.set(input, allFiles);

                    // Update preview
                    updatePreview(input, preview, allFiles);

                    // Update file input with all files (using DataTransfer)
                    updateFileInput(fileInput, allFiles);
                });
            }
        });

        function updatePreview(inputId, previewId, files) {
            const previewDiv = document.getElementById(previewId);
            previewDiv.innerHTML = ''; // Clear and rebuild

            files.forEach((file, index) => {
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgContainer = document.createElement('div');
                        imgContainer.className = 'position-relative d-inline-block';
                        imgContainer.innerHTML = `
                            <img src="${e.target.result}" class="img-thumbnail" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                    data-index="${index}" style="padding: 2px 6px; font-size: 12px;">
                                ×
                            </button>
                        `;

                        // Add remove handler
                        const removeBtn = imgContainer.querySelector('button');
                        removeBtn.onclick = function() {
                            removeImage(inputId, previewId, index);
                        };

                        previewDiv.appendChild(imgContainer);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Add "Clear All" button if multiple images
            if (files.length > 0) {
                const clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'btn btn-sm btn-danger mt-2';
                clearBtn.textContent = 'Clear All (' + files.length + ')';
                clearBtn.onclick = function() {
                    selectedFilesMap.set(inputId, []);
                    previewDiv.innerHTML = '';
                    document.getElementById(inputId).value = '';
                };
                previewDiv.appendChild(clearBtn);
            }
        }

        function removeImage(inputId, previewId, index) {
            const files = selectedFilesMap.get(inputId) || [];
            files.splice(index, 1);
            selectedFilesMap.set(inputId, files);

            updatePreview(inputId, previewId, files);

            const fileInput = document.getElementById(inputId);
            updateFileInput(fileInput, files);
        }

        function updateFileInput(fileInput, files) {
            // Create new DataTransfer object to update file input
            const dataTransfer = new DataTransfer();
            files.forEach(file => {
                dataTransfer.items.add(file);
            });
            fileInput.files = dataTransfer.files;
        }
    }

    let previousTabType = 'news'; // Track if previous tab was news or social

    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('data-bs-target');
            console.log('Tab switched to:', targetId);

            let currentTabType = 'news';

            // Determine current tab type
            if (targetId === '#news-content') {
                currentTabType = 'news';
            } else if (targetId === '#twitter-content' || targetId === '#facebook-content' || targetId === '#instagram-content') {
                currentTabType = 'social';
            }

            // Only clear fields when switching between news and social types
            // Don't clear when switching between social tabs (Twitter/Facebook/Instagram)
            if (previousTabType !== currentTabType) {
                console.log('Switching between different content types, clearing fields');
                const form = document.getElementById('insertForm');
                const inputs = form.querySelectorAll('input:not([type="hidden"]):not([name="content_type"]), textarea, select');
                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                });
            } else {
                console.log('Staying within same content type, keeping field values');
            }

            previousTabType = currentTabType;
        });
    });

    // Reset form when modal is closed
    const insertModal = document.getElementById('insertModal');
    if (insertModal) {
        insertModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('insertForm').reset();
            // Reset to News Article tab
            const newsTab = document.getElementById('news-tab');
            if (newsTab && typeof bootstrap !== 'undefined') {
                const newsTabInstance = new bootstrap.Tab(newsTab);
                newsTabInstance.show();
            }
            if (contentTypeField) {
                contentTypeField.value = 'news_article';
            }
        });

        // When modal opens, ensure first tab is active
        insertModal.addEventListener('shown.bs.modal', function() {
            console.log('Modal opened');
            const firstTab = document.querySelector('#news-content');
            if (firstTab) {
                console.log('First tab found, showing it');
                firstTab.classList.add('show', 'active');
            }
        });

        // Show modal if there's an error
        <?php if (!empty($insert_error)): ?>
        const modalInstance = new bootstrap.Modal(insertModal);
        modalInstance.show();
        <?php endif; ?>
    }

    // Show/hide bulk delete button
    const checkboxes = document.querySelectorAll('input[name="delete_ids[]"]');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    if (checkboxes.length > 0 && bulkDeleteBtn) {
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                bulkDeleteBtn.style.display = anyChecked ? 'inline-block' : 'none';
            });
        });
    }
});
</script>

<script>
// ═══════════════════════════════════════════════════
// DELETE FUNCTION - MUST BE GLOBAL (WordPress Mode)
// ═══════════════════════════════════════════════════
window.confirmDelete = function(button, postId) {
    console.log('═══════════════════════════════════════════════════');
    console.log('🗑️ DELETE BUTTON CLICKED!');
    console.log('Post ID:', postId);
    console.log('Button element:', button);
    console.log('Button HTML:', button.outerHTML);

    if (confirm('Are you sure you want to delete this post?')) {
        console.log('✅ User confirmed deletion');

        // Find the form
        const form = button.closest('form');
        console.log('📋 Form found:', form);

        if (!form) {
            console.error('❌ NO FORM FOUND!');
            alert('Error: Could not find form');
            return false;
        }

        console.log('📋 Form action:', form.action);
        console.log('📋 Form method:', form.method);

        // Check for nonce
        const nonceField = form.querySelector('input[name="bulk_delete_nonce"]');
        console.log('🔐 Nonce field found:', nonceField);
        console.log('🔐 Nonce value:', nonceField ? nonceField.value : 'NOT FOUND');

        // Remove any existing delete_ids[] hidden inputs to avoid duplicates
        const existingInputs = form.querySelectorAll('input[name="delete_ids[]"]');
        console.log('🔍 Found existing delete_ids[] inputs:', existingInputs.length);
        existingInputs.forEach(input => {
            if (input.type === 'hidden') {
                console.log('🗑️ Removing existing hidden input:', input.value);
                input.remove();
            }
        });

        // Add the post ID to delete
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'delete_ids[]';
        hiddenInput.value = postId;
        form.appendChild(hiddenInput);
        console.log('✅ Added hidden input with post ID:', postId);

        // Log all form data
        const formData = new FormData(form);
        console.log('📦 FORM DATA BEING SUBMITTED:');
        for (let [key, value] of formData.entries()) {
            console.log(`   ${key}: ${value}`);
        }

        console.log('📤 SUBMITTING FORM NOW...');
        console.log('═══════════════════════════════════════════════════');
        return true; // Allow form submission
    } else {
        console.log('❌ User cancelled deletion');
        console.log('═══════════════════════════════════════════════════');
        return false; // Prevent form submission
    }
};

console.log('✅ confirmDelete function defined globally (WordPress Mode)');
</script>

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
        <!-- Publish Post Button -->
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="btn btn-publish-post" data-bs-toggle="modal" data-bs-target="#insertModal">
                <i class="fas fa-plus-circle me-2"></i>Publish Post
            </button>
        </div>
        <!-- Insert Modal (Same as WordPress mode) -->
        <div class="modal fade" id="insertModal" tabindex="-1" aria-labelledby="insertModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <form method="post" enctype="multipart/form-data" id="insertFormStandalone">
                <div class="modal-header">
                  <h5 class="modal-title" id="insertModalLabel">Create Content</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <!-- Content Type Tabs -->
                  <ul class="nav nav-tabs mb-3" id="contentTypeTabsStandalone" role="tablist">
                    <li class="nav-item" role="presentation">
                      <button class="nav-link active" id="news-tab-sa" data-bs-toggle="tab" data-bs-target="#news-content-sa" type="button" role="tab" aria-controls="news-content-sa" aria-selected="true">News Article</button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link" id="twitter-tab-sa" data-bs-toggle="tab" data-bs-target="#twitter-content-sa" type="button" role="tab" aria-controls="twitter-content-sa" aria-selected="false">X (Twitter)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link" id="facebook-tab-sa" data-bs-toggle="tab" data-bs-target="#facebook-content-sa" type="button" role="tab" aria-controls="facebook-content-sa" aria-selected="false">Facebook</button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button class="nav-link" id="instagram-tab-sa" data-bs-toggle="tab" data-bs-target="#instagram-content-sa" type="button" role="tab" aria-controls="instagram-content-sa" aria-selected="false">Instagram</button>
                    </li>
                  </ul>

                  <!-- Tab Content -->
                  <div class="tab-content" id="contentTypeTabContentStandalone" style="min-height: 400px;">
                    <!-- News Article Form -->
                    <div class="tab-pane fade show active" id="news-content-sa" role="tabpanel">
                      <input type="hidden" name="content_type" value="news_article" id="content_type_field_sa">

                      <div class="mb-3">
                        <label for="insertTitleSa" class="form-label">Headline *</label>
                        <input class="form-control" type="text" id="insertTitleSa" name="insert_title">
                      </div>
                      <div class="mb-3">
                        <label for="insertAuthorSa" class="form-label">Author *</label>
                        <input class="form-control" type="text" id="insertAuthorSa" name="insert_author" placeholder="Author alias">
                      </div>
                      <div class="mb-3">
                        <label for="insertTextSa" class="form-label">Article Body *</label>
                        <textarea class="form-control" id="insertTextSa" name="insert_text" rows="5"></textarea>
                      </div>
                      <div class="mb-3">
                        <label for="insertCategorySa" class="form-label">Category</label>
                        <select class="form-select" id="insertCategorySa" name="insert_category">
                          <option value="">Select Category</option>
                          <option value="Breaking News">Breaking News</option>
                          <option value="Politics">Politics</option>
                          <option value="Business">Business</option>
                          <option value="Technology">Technology</option>
                          <option value="Sports">Sports</option>
                        </select>
                      </div>
                      <div class="mb-3">
                        <label for="insertFeaturedImageSa" class="form-label">Featured Image URL</label>
                        <input class="form-control" type="url" id="insertFeaturedImageSa" name="insert_featured_image">
                      </div>
                      <div class="mb-3">
                        <label for="insertVideoUrlSa" class="form-label">Video Embed URL</label>
                        <input class="form-control" type="url" id="insertVideoUrlSa" name="insert_video_url" placeholder="YouTube or Vimeo URL">
                      </div>
                      <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="insertBreakingSa" name="insert_breaking" value="1">
                        <label class="form-check-label" for="insertBreakingSa">Mark as breaking news</label>
                      </div>
                    </div>

                    <!-- Twitter Form -->
                    <div class="tab-pane fade" id="twitter-content-sa" role="tabpanel">
                      <div class="mb-3">
                        <label for="twitterDisplayNameSa" class="form-label">Display Name *</label>
                        <input class="form-control" type="text" id="twitterDisplayNameSa" name="insert_display_name">
                      </div>
                      <div class="mb-3">
                        <label for="twitterHandleSa" class="form-label">Handle/Username *</label>
                        <input class="form-control" type="text" id="twitterHandleSa" name="insert_handle" placeholder="@username">
                      </div>
                      <div class="mb-3">
                        <label for="twitterTextSa" class="form-label">Post Text *</label>
                        <textarea class="form-control" id="twitterTextSa" name="insert_text" rows="4"></textarea>
                      </div>
                      <div class="mb-3">
                        <label for="twitterAvatarSa" class="form-label">Avatar URL</label>
                        <input class="form-control" type="url" id="twitterAvatarSa" name="insert_avatar">
                      </div>
                      <div class="mb-3">
                        <label for="twitterMediaSa" class="form-label">Media URL</label>
                        <input class="form-control" type="url" id="twitterMediaSa" name="insert_media_url">
                      </div>
                    </div>

                    <!-- Facebook Form -->
                    <div class="tab-pane fade" id="facebook-content-sa" role="tabpanel">
                      <div class="mb-3">
                        <label for="facebookDisplayNameSa" class="form-label">Display Name *</label>
                        <input class="form-control" type="text" id="facebookDisplayNameSa" name="insert_display_name">
                      </div>
                      <div class="mb-3">
                        <label for="facebookHandleSa" class="form-label">Handle/Username *</label>
                        <input class="form-control" type="text" id="facebookHandleSa" name="insert_handle">
                      </div>
                      <div class="mb-3">
                        <label for="facebookTextSa" class="form-label">Post Text *</label>
                        <textarea class="form-control" id="facebookTextSa" name="insert_text" rows="4"></textarea>
                      </div>
                      <div class="mb-3">
                        <label for="facebookAvatarSa" class="form-label">Avatar URL</label>
                        <input class="form-control" type="url" id="facebookAvatarSa" name="insert_avatar">
                      </div>
                      <div class="mb-3">
                        <label for="facebookMediaSa" class="form-label">Media URL</label>
                        <input class="form-control" type="url" id="facebookMediaSa" name="insert_media_url">
                      </div>
                    </div>

                    <!-- Instagram Form -->
                    <div class="tab-pane fade" id="instagram-content-sa" role="tabpanel">
                      <div class="mb-3">
                        <label for="instagramDisplayNameSa" class="form-label">Display Name *</label>
                        <input class="form-control" type="text" id="instagramDisplayNameSa" name="insert_display_name">
                      </div>
                      <div class="mb-3">
                        <label for="instagramHandleSa" class="form-label">Handle/Username *</label>
                        <input class="form-control" type="text" id="instagramHandleSa" name="insert_handle" placeholder="@username">
                      </div>
                      <div class="mb-3">
                        <label for="instagramTextSa" class="form-label">Post Text *</label>
                        <textarea class="form-control" id="instagramTextSa" name="insert_text" rows="4"></textarea>
                      </div>
                      <div class="mb-3">
                        <label for="instagramAvatarSa" class="form-label">Avatar URL</label>
                        <input class="form-control" type="url" id="instagramAvatarSa" name="insert_avatar">
                      </div>
                      <div class="mb-3">
                        <label for="instagramMediaSa" class="form-label">Media URL</label>
                        <input class="form-control" type="url" id="instagramMediaSa" name="insert_media_url">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary" name="submit_insert" value="1">Publish Post</button>
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
               <?php if (function_exists('wp_nonce_field')) wp_nonce_field('bulk_delete_action', 'bulk_delete_nonce'); ?>
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

    <style>
    /* Fancy Publish Post Button */
    .btn-publish-post {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        font-size: 16px;
        padding: 12px 28px;
        border: none;
        border-radius: 50px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-publish-post:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        color: white;
    }

    .btn-publish-post:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.4);
    }

    .btn-publish-post i {
        font-size: 18px;
    }

    /* Ensure tab content displays properly */
    #insertModal .tab-content {
        display: block !important;
    }
    #insertModal .tab-pane {
        display: none;
    }
    #insertModal .tab-pane.active {
        display: block !important;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle tab switching and update content_type field for standalone mode
        const tabs = document.querySelectorAll('#contentTypeTabsStandalone button[data-bs-toggle="tab"]');
        const contentTypeField = document.getElementById('content_type_field_sa');

        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function(event) {
                const targetId = event.target.getAttribute('data-bs-target');

                // Update content_type based on active tab
                if (targetId === '#news-content-sa') {
                    contentTypeField.value = 'news_article';
                } else if (targetId === '#twitter-content-sa') {
                    contentTypeField.value = 'social_twitter';
                } else if (targetId === '#facebook-content-sa') {
                    contentTypeField.value = 'social_facebook';
                } else if (targetId === '#instagram-content-sa') {
                    contentTypeField.value = 'social_instagram';
                }

                // Clear all form fields when switching tabs
                const form = document.getElementById('insertFormStandalone');
                const inputs = form.querySelectorAll('input:not([type="hidden"]), textarea, select');
                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else if (input.tagName === 'SELECT') {
                        input.selectedIndex = 0;
                    } else {
                        input.value = '';
                    }
                });
            });
        });

        // Reset form when modal is closed
        const insertModal = document.getElementById('insertModal');
        insertModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('insertFormStandalone').reset();
            // Reset to News Article tab
            const newsTab = document.getElementById('news-tab-sa');
            const newsTabInstance = new bootstrap.Tab(newsTab);
            newsTabInstance.show();
            contentTypeField.value = 'news_article';
        });

        // Show/hide bulk delete button
        const checkboxes = document.querySelectorAll('input[name="delete_ids[]"]');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

        if (checkboxes.length > 0 && bulkDeleteBtn) {
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                    bulkDeleteBtn.style.display = anyChecked ? 'inline-block' : 'none';
                });
            });
        }

        // ---------------------------
        // POST MENU ACTIONS
        // ---------------------------

        // Copy Link
        document.addEventListener('click', function(e) {
            const copyBtn = e.target.closest('.copy-link-btn');

            if (copyBtn) {
                e.preventDefault();
                e.stopPropagation();
                const btn = copyBtn;
                const postId = btn.dataset.postId;
                const postUrl = window.location.origin + window.location.pathname + '?post=' + postId;

                navigator.clipboard.writeText(postUrl).then(() => {
                    // Show success feedback
                    const originalHtml = btn.innerHTML;
                    const originalTitle = btn.title;
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.title = 'Copied!';
                    btn.style.background = 'rgba(25, 135, 84, 0.2)';
                    btn.style.color = '#198754';

                    setTimeout(() => {
                        btn.innerHTML = originalHtml;
                        btn.title = originalTitle;
                        btn.style.background = '';
                        btn.style.color = '';
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                    alert('Failed to copy link');
                });
            }
        });

        // Edit Twitter Post - Populate modal with current data
        const editTwitterModal = document.getElementById('editTwitterModal');
        console.log('🔍 Twitter modal element found:', editTwitterModal);

        if (editTwitterModal) {
            editTwitterModal.addEventListener('show.bs.modal', function(event) {
                console.log('🔥🔥🔥 TWITTER MODAL EVENT FIRED 🔥🔥🔥');

                // Get the button that triggered the modal - might be the icon inside
                let button = event.relatedTarget;

                // If the clicked element is the icon, get the parent button
                if (button && button.tagName === 'I') {
                    button = button.closest('button');
                    console.log('📌 Clicked on icon, found parent button:', button);
                }

                console.log('Button element:', button);

                if (!button) {
                    console.error('❌ NO BUTTON FOUND!');
                    alert('ERROR: No button found!');
                    return;
                }

                console.log('Button HTML:', button.outerHTML.substring(0, 500));

                // Get all data-* attributes
                const allAttrs = {};
                for (let i = 0; i < button.attributes.length; i++) {
                    const attr = button.attributes[i];
                    if (attr.name.startsWith('data-')) {
                        allAttrs[attr.name] = attr.value;
                    }
                }
                console.log('📊 All data attributes:', allAttrs);

                // Populate form fields
                const postId = button.getAttribute('data-post-id');
                const displayName = button.getAttribute('data-display-name');
                const handle = button.getAttribute('data-handle');
                const text = button.getAttribute('data-text');

                console.log('📝 Values extracted:', {
                    postId,
                    displayName,
                    handle,
                    textLength: text ? text.length : 0
                });

                // Set form fields
                const postIdField = document.getElementById('editTwitterPostId');
                const displayNameField = document.getElementById('editTwitterDisplayName');
                const handleField = document.getElementById('editTwitterHandle');
                const textField = document.getElementById('editTwitterText');

                if (postIdField) {
                    postIdField.value = postId || '';
                    console.log('✅ Set post ID:', postId);
                } else {
                    console.error('❌ Post ID field not found');
                }
                if (displayNameField) {
                    displayNameField.value = displayName || '';
                    console.log('✅ Set display name:', displayName);
                } else {
                    console.error('❌ Display name field not found');
                }
                if (handleField) {
                    handleField.value = handle || '';
                    console.log('✅ Set handle:', handle);
                } else {
                    console.error('❌ Handle field not found');
                }
                if (textField) {
                    textField.value = text || '';
                    console.log('✅ Set text (length):', text ? text.length : 0);
                } else {
                    console.error('❌ Text field not found');
                }

                // Clear image preview and file input
                const mediaInput = document.getElementById('editTwitterMedia');
                const mediaPreview = document.getElementById('editTwitterMediaPreview');
                if (mediaInput) {
                    mediaInput.value = '';
                    selectedFilesMap.set('editTwitterMedia', []);
                }
                if (mediaPreview) {
                    mediaPreview.innerHTML = '';
                }

                console.log('✅✅✅ Twitter form populated successfully!');
            });
            console.log('✅ Twitter modal event listener attached');
        } else {
            console.error('❌ Twitter modal element NOT found!');
        }

        // Edit Facebook Post - Populate modal with current data
        const editFacebookModal = document.getElementById('editFacebookModal');
        if (editFacebookModal) {
            editFacebookModal.addEventListener('show.bs.modal', function(event) {
                // Get the button that triggered the modal - might be the icon inside
                let button = event.relatedTarget;

                // If the clicked element is the icon, get the parent button
                if (button && button.tagName === 'I') {
                    button = button.closest('button');
                    console.log('📌 Clicked on icon, found parent button');
                }

                console.log('✅ Edit Facebook Modal Opened');
                console.log('📊 Button dataset:', button ? button.dataset : 'NO BUTTON');

                if (!button) {
                    console.error('❌ No button found for Facebook modal');
                    return;
                }

                // Populate form fields - use getAttribute for kebab-case attributes
                const postId = button.getAttribute('data-post-id');
                const displayName = button.getAttribute('data-display-name');
                const handle = button.getAttribute('data-handle');
                const text = button.getAttribute('data-text');

                document.getElementById('editFacebookPostId').value = postId || '';
                document.getElementById('editFacebookDisplayName').value = displayName || '';
                document.getElementById('editFacebookHandle').value = handle || '';
                document.getElementById('editFacebookText').value = text || '';

                // Clear image preview and file input
                const mediaInput = document.getElementById('editFacebookMedia');
                const mediaPreview = document.getElementById('editFacebookMediaPreview');
                if (mediaInput) {
                    mediaInput.value = '';
                    selectedFilesMap.set('editFacebookMedia', []);
                }
                if (mediaPreview) {
                    mediaPreview.innerHTML = '';
                }

                console.log('✅ Facebook form populated:', {
                    postId,
                    displayName,
                    handle,
                    textLength: text ? text.length : 0
                });
            });
        }

        // Edit Instagram Post - Populate modal with current data
        const editInstagramModal = document.getElementById('editInstagramModal');
        if (editInstagramModal) {
            editInstagramModal.addEventListener('show.bs.modal', function(event) {
                // Get the button that triggered the modal - might be the icon inside
                let button = event.relatedTarget;

                // If the clicked element is the icon, get the parent button
                if (button && button.tagName === 'I') {
                    button = button.closest('button');
                    console.log('📌 Clicked on icon, found parent button');
                }

                console.log('✅ Edit Instagram Modal Opened');
                console.log('📊 Button dataset:', button ? button.dataset : 'NO BUTTON');

                if (!button) {
                    console.error('❌ No button found for Instagram modal');
                    return;
                }

                // Populate form fields - use getAttribute for kebab-case attributes
                const postId = button.getAttribute('data-post-id');
                const displayName = button.getAttribute('data-display-name');
                const handle = button.getAttribute('data-handle');
                const text = button.getAttribute('data-text');

                document.getElementById('editInstagramPostId').value = postId || '';
                document.getElementById('editInstagramDisplayName').value = displayName || '';
                document.getElementById('editInstagramHandle').value = handle || '';
                document.getElementById('editInstagramText').value = text || '';

                // Clear image preview and file input
                const mediaInput = document.getElementById('editInstagramMedia');
                const mediaPreview = document.getElementById('editInstagramMediaPreview');
                if (mediaInput) {
                    mediaInput.value = '';
                    selectedFilesMap.set('editInstagramMedia', []);
                }
                if (mediaPreview) {
                    mediaPreview.innerHTML = '';
                }

                console.log('✅ Instagram form populated:', {
                    postId,
                    displayName,
                    handle,
                    textLength: text ? text.length : 0
                });
            });
        }

        // Edit News Article - Populate modal with current data
        const editNewsModal = document.getElementById('editNewsModal');
        if (editNewsModal) {
            editNewsModal.addEventListener('show.bs.modal', function(event) {
                // Get the button that triggered the modal - might be the icon inside
                let button = event.relatedTarget;

                // If the clicked element is the icon, get the parent button
                if (button && button.tagName === 'I') {
                    button = button.closest('button');
                    console.log('📌 Clicked on icon, found parent button');
                }

                console.log('✅ Edit News Modal Opened');
                console.log('📊 Button dataset:', button ? button.dataset : 'NO BUTTON');

                if (!button) {
                    console.error('❌ No button found for News modal');
                    return;
                }

                console.log('📊 All attributes:', {
                    postId: button.getAttribute('data-post-id'),
                    headline: button.getAttribute('data-headline'),
                    author: button.getAttribute('data-author'),
                    category: button.getAttribute('data-category'),
                    breaking: button.getAttribute('data-breaking')
                });

                // Populate form fields - use getAttribute for kebab-case attributes
                const postId = button.getAttribute('data-post-id');
                const headline = button.getAttribute('data-headline');
                const author = button.getAttribute('data-author');
                const body = button.getAttribute('data-body');
                const category = button.getAttribute('data-category');
                const video = button.getAttribute('data-video');
                const breaking = button.getAttribute('data-breaking');

                document.getElementById('editNewsPostId').value = postId || '';
                document.getElementById('editNewsTitle').value = headline || '';
                document.getElementById('editNewsAuthor').value = author || '';
                document.getElementById('editNewsBody').value = body || '';
                document.getElementById('editNewsCategory').value = category || '';
                document.getElementById('editNewsVideoUrl').value = video || '';
                document.getElementById('editNewsBreaking').checked = breaking === '1';

                // Clear image preview and file input
                const imagesInput = document.getElementById('editNewsImages');
                const imagesPreview = document.getElementById('editNewsImagesPreview');
                if (imagesInput) {
                    imagesInput.value = '';
                    selectedFilesMap.set('editNewsImages', []);
                }
                if (imagesPreview) {
                    imagesPreview.innerHTML = '';
                }

                console.log('✅ News form populated:', {
                    postId,
                    headline,
                    author,
                    bodyLength: body ? body.length : 0,
                    category,
                    breaking
                });
            });
        }

        // Insert modal - No special logic needed, just for creating new posts
        console.log('✅ Insert modal ready for creating new posts');

        // Note: confirmDelete function is now defined globally at the top of the script
    });

});

// Action buttons initialized
console.log('✅ Action buttons ready!');
</script>
    <?php get_footer();
}

\
<?php
/**
 * Plugin Name: Newsroom Addons
 * Description: Photo upload (post + reply), edit/delete (own posts), and link preview (Open Graph/Twitter) for the Newsroom training site.
 * Version: 1.0.0
 * Author: Your Name
 */

if ( ! defined('ABSPATH') ) exit; // নিরাপত্তা (security)

/**
 * 1) Role capability ensure (upload_files/edit/delete)
 */
add_action('init', function () {
  $role = get_role('newsroom_operator');
  if ($role) {
    $role->add_cap('upload_files');
    $role->add_cap('edit_posts');
    $role->add_cap('delete_posts');
  }
});

/**
 * 2) Enqueue JS/CSS এবং AJAX nonce (frontend)
 */
add_action('wp_enqueue_scripts', function () {
  wp_enqueue_script('nr-addons', plugin_dir_url(__FILE__).'nr-addons.js', array('jquery'), '1.0', true);
  wp_localize_script('nr-addons', 'nrAjax', array(
    'url'   => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('nr_ajax_nonce'),
  ));
  wp_enqueue_style('nr-addons-css', plugin_dir_url(__FILE__).'nr-addons.css', array(), '1.0');
});

/**
 * 3) Photo Upload — POST
 * আপনার create-post প্রসেস শেষে do_action('nr_after_post_create',$post_id) কল দিলে এটা চলবে।
 */
add_action('nr_after_post_create', function ($post_id) {
  if ( empty($_FILES['nr_photo']['name']) ) return;
  if ( ! is_user_logged_in() ) return;

  require_once ABSPATH.'wp-admin/includes/file.php';
  require_once ABSPATH.'wp-admin/includes/image.php';
  require_once ABSPATH.'wp-admin/includes/media.php';

  $allowed = array('image/jpeg','image/png','image/gif','image/webp');
  if ( ! in_array($_FILES['nr_photo']['type'], $allowed, true) ) return;

  $attach_id = media_handle_upload('nr_photo', $post_id);
  if ( ! is_wp_error($attach_id) ) {
    set_post_thumbnail($post_id, $attach_id);              // কার্ডে দেখাতে সুবিধা
    update_post_meta($post_id, '_nr_photo_id', $attach_id);
  }
});

/**
 * 4) Photo Upload — REPLY (comment)
 */
add_filter('comment_form_defaults', function($d){
  // ফর্মে ফাইল ইনপুট যোগ
  $d['comment_field'] .= '<p class="comment-form-nr-photo"><label>Photo (ঐচ্ছিক): <input type="file" name="nr_comment_photo" accept="image/*"></label></p>';
  return $d;
});

add_action('comment_post', function($comment_id){
  if ( empty($_FILES['nr_comment_photo']['name']) ) return;

  require_once ABSPATH.'wp-admin/includes/file.php';
  require_once ABSPATH.'wp-admin/includes/image.php';
  require_once ABSPATH.'wp-admin/includes/media.php';

  $attach_id = media_handle_upload('nr_comment_photo', 0);
  if ( ! is_wp_error($attach_id) ) {
    add_comment_meta($comment_id, '_nr_comment_photo_id', $attach_id);
  }
});

/**
 * 5) Edit/Delete — AJAX (own post only)
 */
add_action('wp_ajax_nr_delete_post', function(){
  check_ajax_referer('nr_ajax_nonce');
  $post_id = intval($_POST['id'] ?? 0);
  $post = get_post($post_id);
  if ( ! $post ) wp_send_json_error('Not found');

  if ( get_current_user_id() !== (int)$post->post_author || ! current_user_can('delete_post', $post_id) ) {
    wp_send_json_error('No permission');
  }

  $result = wp_delete_post($post_id, true);

  if ($result) {
    // Store the deletion event for live updates
    set_transient('newsroom_post_deleted_' . $post_id, array(
      'post_id' => $post_id,
      'deleted_by' => get_current_user_id(),
      'timestamp' => current_time('Y-m-d H:i:s')
    ), 300); // Keep for 5 minutes

    wp_send_json_success();
  } else {
    wp_send_json_error('Failed to delete post');
  }
});

add_action('wp_ajax_nr_edit_post', function(){
  check_ajax_referer('nr_ajax_nonce');
  $post_id = intval($_POST['id'] ?? 0);
  $post = get_post($post_id);
  if ( ! $post ) wp_send_json_error('Not found');

  if ( get_current_user_id() !== (int)$post->post_author || ! current_user_can('edit_post', $post_id) ) {
    wp_send_json_error('No permission');
  }

  $update = array(
    'ID'           => $post_id,
    'post_title'   => sanitize_text_field($_POST['title'] ?? $post->post_title),
    'post_content' => wp_kses_post($_POST['content'] ?? $post->post_content),
  );
  $result = wp_update_post($update);

  if ($result && !is_wp_error($result)) {
    // Refresh post data after update
    $updated_post = get_post($post_id);

    // Store the edit event for live updates
    $transient_data = array(
      'post_id' => $post_id,
      'post_type' => $updated_post->post_type,
      'edited_by' => get_current_user_id(),
      'timestamp' => current_time('Y-m-d H:i:s')
    );

    error_log("Storing edit transient: " . print_r($transient_data, true));
    set_transient('newsroom_post_edited_' . $post_id, $transient_data, 300); // Keep for 5 minutes

    wp_send_json_success();
  } else {
    wp_send_json_error('Failed to update post');
  }
});

/**
 * 6) Link Preview (Open Graph/Twitter Card) — AJAX
 */
function nr_fetch_link_preview($url){
  $resp = wp_remote_get(esc_url_raw($url), array('timeout'=>8, 'redirection'=>3, 'user-agent'=>'Mozilla/5.0 NRPreview'));
  if ( is_wp_error($resp) ) return array();
  $html = wp_remote_retrieve_body($resp);
  if ( ! $html ) return array();

  $grab = function($pattern) use ($html){
    return (preg_match($pattern, $html, $m)) ? trim($m[1]) : '';
  };
  $title = $grab('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)/i');
  $desc  = $grab('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)/i');
  $img   = $grab('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)/i');

  if (!$title) $title = $grab('/<meta[^>]+name=["\']twitter:title["\'][^>]+content=["\']([^"\']+)/i');
  if (!$desc)  $desc  = $grab('/<meta[^>]+name=["\']twitter:description["\'][^>]+content=["\']([^"\']+)/i');
  if (!$img)   $img   = $grab('/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)/i');

  return array('title'=>$title,'description'=>$desc,'image'=>$img);
}

add_action('wp_ajax_nr_link_preview', 'nr_link_preview');
add_action('wp_ajax_nopriv_nr_link_preview', 'nr_link_preview');
function nr_link_preview(){
  $url = esc_url_raw($_POST['url'] ?? '');
  if ( ! $url ) wp_send_json_error('No URL');
  $data = nr_fetch_link_preview($url);
  if ( empty($data) ) wp_send_json_error('No preview');
  wp_send_json_success($data);
}

/**
 * 7) Helper: comment image output (template tag)
 * থিম/টেমপ্লেটে চাইলে ব্যবহার করুন: echo nr_comment_image();
 */
function nr_comment_image($comment_id=null){
  $comment_id = $comment_id ?: get_comment_ID();
  $pid = get_comment_meta($comment_id, '_nr_comment_photo_id', true);
  if ($pid){
    return wp_get_attachment_image($pid, 'medium');
  }
  return '';
}

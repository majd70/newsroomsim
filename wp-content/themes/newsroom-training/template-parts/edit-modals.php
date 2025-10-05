<!-- Edit X Modal -->
<div class="modal fade" id="editTwitterModal" tabindex="-1" aria-labelledby="editTwitterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editTwitterForm" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('insert_content_action', 'insert_content_nonce'); ?>
        <div class="modal-header">
          <h5 class="modal-title" id="editTwitterModalLabel">Edit X Post</h5>
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


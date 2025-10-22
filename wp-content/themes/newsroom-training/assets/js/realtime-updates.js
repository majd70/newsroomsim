/**
 * Simple Real-time Updates System
 * Clean approach without form conflicts
 */

class SimpleRealtimeUpdates {
    constructor() {
        this.ajaxUrl = window.newsroom_ajax?.ajax_url || '/wp-admin/admin-ajax.php';
        this.nonce = window.newsroom_ajax?.nonce || '';
        this.addCommentNonce = window.newsroom_ajax?.add_comment_nonce || '';
        this.wpCurrentTime = window.newsroom_ajax?.wp_current_time || '';
        this.wpTimezoneOffset = window.newsroom_ajax?.wp_timezone_offset || 0;
        
        // Start checking from 1 hour ago to catch recent activity
        this.lastPostCheck = this.getTimestampMinutesAgo(60);
        this.lastCommentCheck = this.getTimestampMinutesAgo(60);
        this.lastDeleteCheck = this.getTimestampMinutesAgo(60);
        this.lastEditCheck = this.getTimestampMinutesAgo(60);

        console.log('🕐 Initial post check timestamp:', this.lastPostCheck);
        console.log('🕐 Initial comment check timestamp:', this.lastCommentCheck);
        console.log('🕐 Initial delete check timestamp:', this.lastDeleteCheck);
        console.log('🕐 Initial edit check timestamp:', this.lastEditCheck);
        console.log('🕐 Current JavaScript Date:', new Date().toString());
        console.log('🕐 Current JavaScript Year:', new Date().getFullYear());
        console.log('🕐 Current JavaScript Month:', new Date().getMonth() + 1);
        console.log('🕐 Current JavaScript Day:', new Date().getDate());
        
        this.pollInterval = null;
        this.isPolling = false;
        this.addedPostIds = new Set(); // Track added post IDs to prevent duplicates
        this.addedCommentIds = new Set(); // Track added comment IDs to prevent duplicates
        
        console.log('🚀 Simple Real-time Updates initialized');
        console.log('📡 AJAX URL:', this.ajaxUrl);
        console.log('🔑 Nonce:', this.nonce);
        console.log('🕐 WordPress Current Time:', this.wpCurrentTime);
        console.log('🕐 WordPress Timezone Offset:', this.wpTimezoneOffset);
        
        this.init();
    }

    initializeExistingPosts() {
        // Find all existing posts on the page and track their IDs
        const existingPosts = document.querySelectorAll('[data-post-id]');
        existingPosts.forEach(post => {
            const postId = post.dataset.postId;
            if (postId) {
                this.addedPostIds.add(postId);
                console.log('📝 Tracking existing post ID:', postId);
            }
        });
        console.log('📝 Total existing posts tracked:', this.addedPostIds.size);

        // Find all existing comments on the page and track their IDs
        const existingComments = document.querySelectorAll('[id^="comment-"]');
        existingComments.forEach(comment => {
            const commentId = comment.id.replace('comment-', '');
            if (commentId) {
                this.addedCommentIds.add(commentId);
                console.log('💬 Tracking existing comment ID:', commentId);
            }
        });
        console.log('💬 Total existing comments tracked:', this.addedCommentIds.size);
    }
    
    init() {
        // Initialize with existing posts on the page to prevent duplicates
        this.initializeExistingPosts();

        // Force refresh timestamps to ensure we're using current time
        console.log('🔄 Refreshing timestamps to current time...');
        this.refreshTimestamps();

        // Setup comment buttons
        this.setupCommentButtons();

        // Start polling for updates
        this.startPolling();

        // Setup tab visibility
        this.setupTabVisibility();
    }

    refreshTimestamps() {
        // Start checking from 10 minutes ago to catch recent activity
        this.lastPostCheck = this.getTimestampMinutesAgo(10);
        this.lastCommentCheck = this.getTimestampMinutesAgo(10);
        this.lastDeleteCheck = this.getTimestampMinutesAgo(10);

        console.log('🔄 Timestamps refreshed:');
        console.log('🔄 Post check from:', this.lastPostCheck);
        console.log('🔄 Comment check from:', this.lastCommentCheck);
        console.log('  - Delete operations:', this.lastDeleteCheck);
    }
    
    getCurrentTimestamp() {
        // Let PHP handle the timezone - just request current server time
        return 'SERVER_TIME_NOW';
    }

    getTimestampMinutesAgo(minutes) {
        // Let PHP handle the timezone - request time X minutes ago
        return `SERVER_TIME_MINUS_${minutes}`;
    }
    
    setupCommentButtons() {
        // Handle comment button clicks
        document.addEventListener('click', (event) => {
            if (event.target.matches('.realtime-comment-btn')) {
                console.log('💬 Comment button clicked');
                this.handleCommentSubmission(event.target);
            }
        });
        
        console.log('✅ Comment buttons setup complete');
    }
    
    async handleCommentSubmission(button) {
        const form = button.closest('.realtime-comment-form');
        const textarea = form.querySelector('.comment-textarea');
        const postId = form.getAttribute('data-post-id');
        const content = textarea.value.trim();
        
        console.log('📝 Submitting comment for post:', postId);
        console.log('📝 Content:', content);
        
        if (!content) {
            alert('Please enter a comment');
            return;
        }
        
        // Show loading state
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Posting...';
        
        try {
            const formData = new FormData();
            formData.append('action', 'add_comment_inline');
            formData.append('nonce', this.nonce); // Use main nonce
            formData.append('post_id', postId);
            formData.append('comment_content', content);
            
            console.log('📡 Sending comment via AJAX...');
            
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            console.log('📡 Comment response:', data);
            
            if (data.success) {
                console.log('✅ Comment posted successfully');
                console.log('📝 Comment data received:', data.data);

                // Clear textarea
                textarea.value = '';

                // Add comment immediately to current user's view
                this.addCommentToPost(postId, data.data);

                // Show success message
                this.showNotification('Comment posted!', 'success');
                
            } else {
                console.error('❌ Comment failed:', data.data);
                alert('Failed to post comment: ' + (data.data || 'Unknown error'));
            }
            
        } catch (error) {
            console.error('❌ Comment error:', error);
            alert('Error posting comment. Please try again.');
        } finally {
            // Restore button
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }
    
    addCommentToPost(postId, commentData) {
        console.log('📝 Adding comment to post:', postId);
        console.log('📝 Comment data:', commentData);

        // Check for duplicate comment
        if (commentData.comment_id && this.addedCommentIds.has(commentData.comment_id.toString())) {
            console.log('💬 Skipping duplicate comment:', commentData.comment_id);
            return;
        }

        const commentsList = document.querySelector(`#comments-list-${postId}`);
        if (!commentsList) {
            console.log('❌ Comments list not found for post:', postId);
            return;
        }

        // Track this comment ID
        if (commentData.comment_id) {
            this.addedCommentIds.add(commentData.comment_id.toString());
            console.log('💬 Tracking new comment ID:', commentData.comment_id);
        }
        
        // Remove "no comments" message if exists
        const noCommentsMsg = commentsList.querySelector('p.text-muted');
        if (noCommentsMsg && noCommentsMsg.textContent.includes('No comments yet')) {
            noCommentsMsg.remove();
        }
        
        // Create comment HTML
        const commentHtml = `
            <div class="comment-item border-bottom py-2 new-comment" id="comment-${commentData.comment_id}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong>${commentData.author_name || 'Anonymous'}</strong>
                        <small class="text-muted ms-2">${commentData.date || 'just now'}</small>
                        <p class="mb-1 mt-1">${commentData.content || ''}</p>
                    </div>
                    ${commentData.can_delete ? `
                        <button class="btn btn-sm btn-outline-danger delete-comment-btn"
                                data-comment-id="${commentData.comment_id}"
                                data-nonce="${commentData.delete_nonce}">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
        
        // Add to comments list
        commentsList.insertAdjacentHTML('beforeend', commentHtml);
        
        // Highlight new comment
        const newComment = document.getElementById(`comment-${commentData.comment_id}`);
        if (newComment) {
            newComment.style.backgroundColor = '#e8f5e8';
            setTimeout(() => {
                newComment.style.backgroundColor = '';
            }, 3000);
        }
        
        console.log('✅ Comment added to post:', postId);
    }
    
    startPolling() {
        if (this.isPolling) {
            console.log('⚠️ Already polling, skipping start');
            return;
        }

        this.isPolling = true;
        console.log('🔄 Started polling for updates every 5 seconds');
        console.log('🔄 AJAX URL:', this.ajaxUrl);
        console.log('🔄 Nonce available:', !!this.nonce);

        // Start immediately
        this.checkForUpdates();

        this.pollInterval = setInterval(() => {
            console.log('⏰ Polling interval triggered');
            this.checkForUpdates();
        }, 5000); // Check every 5 seconds
    }
    
    stopPolling() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
        this.isPolling = false;
        console.log('⏸️ Stopped polling');
    }
    
    async checkForUpdates() {
        console.log('🔍 Checking for updates...');
        console.log('🔍 Current timestamp:', new Date().toISOString());
        console.log('🔍 Last post check:', this.lastPostCheck);
        console.log('🔍 Last comment check:', this.lastCommentCheck);

        try {
            // Check for new posts, comments, delete operations, and edit operations in parallel
            await Promise.all([
                this.checkForNewPosts(),
                this.checkForNewComments(),
                this.checkForDeleteOperations(),
                this.checkForEditOperations()
            ]);
            console.log('✅ Update check completed');
        } catch (error) {
            console.error('❌ Error checking for updates:', error);
        }
    }
    
    async checkForNewPosts() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_new_posts');
            formData.append('nonce', this.nonce);
            formData.append('since_timestamp', this.lastPostCheck);

            console.log('📡 Checking posts since:', this.lastPostCheck);

            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('📡 Posts response:', data);

            if (data.success) {
                if (data.data.posts_count > 0) {
                    console.log(`📝 Found ${data.data.posts_count} new posts`);
                    console.log('📝 Posts HTML length:', data.data.posts_html.length);
                    this.addNewPostsToFeed(data.data.posts_html);

                    // Only update timestamp if we got a valid latest_timestamp from server
                    if (data.data.latest_timestamp && data.data.latest_timestamp !== 'SERVER_TIME_NOW') {
                        this.lastPostCheck = data.data.latest_timestamp;
                        console.log('📝 Updated post check timestamp to:', this.lastPostCheck);
                    }

                    this.showNotification(`${data.data.posts_count} new post(s) added!`, 'success');
                } else {
                    console.log('📝 No new posts found');
                    // Don't update timestamp when no posts found - keep checking from same point
                    // This prevents missing posts that might be created between checks
                }
            } else {
                console.error('❌ Posts check failed:', data.data);
            }

        } catch (error) {
            console.error('❌ Error checking posts:', error);
        }
    }
    
    async checkForNewComments() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_new_comments');
            formData.append('nonce', this.nonce);
            formData.append('since_timestamp', this.lastCommentCheck);

            console.log('📡 Checking comments since:', this.lastCommentCheck);

            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('📡 Comments response:', data);

            if (data.success) {
                if (data.data.comments_count > 0) {
                    console.log(`💬 Found ${data.data.comments_count} new comments`);
                    this.addNewCommentsToFeed(data.data.comments_by_post);

                    // Only update timestamp if we got a valid latest_timestamp from server
                    if (data.data.latest_timestamp && data.data.latest_timestamp !== 'SERVER_TIME_NOW') {
                        this.lastCommentCheck = data.data.latest_timestamp;
                        console.log('💬 Updated comment check timestamp to:', this.lastCommentCheck);
                    }

                    this.showNotification(`${data.data.comments_count} new comment(s) added!`, 'success');
                } else {
                    console.log('💬 No new comments found');
                    // Don't update timestamp when no comments found - keep checking from same point
                }
            } else {
                console.error('❌ Comments check failed:', data.data);
            }

        } catch (error) {
            console.error('❌ Error checking comments:', error);
        }
    }

    async checkForDeleteOperations() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_delete_operations');
            formData.append('nonce', this.nonce);
            formData.append('since_timestamp', this.lastDeleteCheck);

            console.log('📡 Checking delete operations since:', this.lastDeleteCheck);

            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('📡 Delete operations response:', data);

            if (data.success) {
                if (data.data.operations_count > 0) {
                    console.log(`🗑️ Found ${data.data.operations_count} delete operations`);
                    this.processDeleteOperations(data.data.operations);

                    // Only update timestamp if we got a valid latest_timestamp from server
                    if (data.data.latest_timestamp && data.data.latest_timestamp !== 'SERVER_TIME_NOW') {
                        this.lastDeleteCheck = data.data.latest_timestamp;
                        console.log('🗑️ Updated delete check timestamp to:', this.lastDeleteCheck);
                    }

                    this.showNotification(`${data.data.operations_count} item(s) deleted by other users`, 'info');
                } else {
                    console.log('🗑️ No new delete operations found');
                }
            } else {
                console.error('❌ Delete operations check failed:', data.data);
            }

        } catch (error) {
            console.error('❌ Error checking delete operations:', error);
        }
    }

    processDeleteOperations(operations) {
        console.log('🗑️ Processing delete operations:', operations);

        operations.forEach(operation => {
            if (operation.type === 'post_deleted') {
                this.removePostFromFeed(operation.post_id);
            } else if (operation.type === 'comment_deleted') {
                this.removeCommentFromFeed(operation.comment_id, operation.post_id);
            }
        });
    }

    async checkForEditOperations() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_edit_operations');
            formData.append('nonce', this.nonce);
            formData.append('since_timestamp', this.lastEditCheck);

            console.log('📝 Checking edit operations since:', this.lastEditCheck);

            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('📝 Edit operations response:', data);

            if (data.success && data.data.operations && data.data.operations.length > 0) {
                this.processEditOperations(data.data.operations);

                // Update the last check timestamp to server time
                if (data.data.server_time) {
                    this.lastEditCheck = data.data.server_time;
                    console.log('📝 Updated edit check timestamp to:', this.lastEditCheck);
                }
            }

        } catch (error) {
            console.error('❌ Error checking edit operations:', error);
        }
    }

    processEditOperations(operations) {
        console.log('📝 Processing edit operations:', operations);

        operations.forEach(operation => {
            if (operation.type === 'post_edited') {
                this.refreshEditedPost(operation.post_id, operation.post_type);
            }
        });
    }

    removePostFromFeed(postId) {
        console.log('🗑️ Removing post from feed:', postId);

        // Find the post element by data-post-id attribute
        const postElement = document.querySelector(`[data-post-id="${postId}"]`);

        if (postElement) {
            // Add fade-out animation
            postElement.style.transition = 'opacity 0.5s ease-out';
            postElement.style.opacity = '0';

            // Remove after animation
            setTimeout(() => {
                postElement.remove();
                console.log('✅ Post removed from feed:', postId);
            }, 500);
        } else {
            console.log('⚠️ Post element not found for removal:', postId);
        }
    }

    removeCommentFromFeed(commentId, postId) {
        console.log('🗑️ Removing comment from feed:', commentId, 'from post:', postId);

        // Find the comment element by ID
        const commentElement = document.getElementById(`comment-${commentId}`);

        if (commentElement) {
            // Add fade-out animation
            commentElement.style.transition = 'opacity 0.5s ease-out';
            commentElement.style.opacity = '0';

            // Remove after animation
            setTimeout(() => {
                commentElement.remove();
                console.log('✅ Comment removed from feed:', commentId);

                // Update comment count if needed
                this.updateCommentCountAfterDeletion(postId);
            }, 500);
        } else {
            console.log('⚠️ Comment element not found for removal:', commentId);
        }
    }

    updateCommentCountAfterDeletion(postId) {
        // Count remaining comments for this post
        const commentsList = document.querySelector(`#comments-list-${postId}`);
        if (commentsList) {
            const remainingComments = commentsList.querySelectorAll('.comment-item').length;

            // Update comment count display
            const countElement = document.querySelector(`#comments-section-${postId} .comments-count-number`);
            if (countElement) {
                countElement.textContent = remainingComments;
            }

            // Show "no comments" message if no comments left
            if (remainingComments === 0) {
                commentsList.innerHTML = '<p class="text-muted text-center py-3">No comments yet. Be the first to comment!</p>';
            }
        }
    }

    async refreshEditedPost(postId, postType) {
        console.log('🔄 Refreshing edited post:', postId, 'type:', postType);

        try {
            // Fetch the updated post content
            const formData = new FormData();
            formData.append('action', 'get_updated_post');
            formData.append('nonce', this.nonce);
            formData.append('post_id', postId);

            // Also pass the post type for debugging
            if (postType) {
                formData.append('expected_post_type', postType);
            }

            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('🔄 Updated post response:', data);

            if (!data.success) {
                console.error('❌ Failed to fetch updated post. Error:', data.data);
                console.error('❌ Post ID:', postId, 'Expected Type:', postType);
            }

            if (data.success && data.data.post_html) {
                // Find the existing post element
                const existingPost = document.querySelector(`[data-post-id="${postId}"]`);

                if (existingPost) {
                    // Create a temporary container to parse the new HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.data.post_html;
                    const newPost = tempDiv.firstElementChild;

                    if (newPost) {
                        // Add visual indication that this post was updated
                        newPost.style.transition = 'background-color 0.5s ease';
                        newPost.style.backgroundColor = '#fff3cd'; // Light yellow

                        // Replace the existing post with the updated one
                        existingPost.replaceWith(newPost);

                        // Remove highlight after 3 seconds
                        setTimeout(() => {
                            newPost.style.backgroundColor = '';
                        }, 3000);

                        console.log('✅ Post refreshed successfully:', postId);
                        this.showNotification('A post was updated by another user', 'info');
                    } else {
                        console.error('❌ Could not parse updated post HTML');
                    }
                } else {
                    console.log('⚠️ Post element not found for refresh:', postId);
                }
            } else {
                console.error('❌ Failed to fetch updated post:', data.data || 'Unknown error');
            }

        } catch (error) {
            console.error('❌ Error refreshing edited post:', error);
        }
    }
    
    addNewPostsToFeed(postsHtml) {
        console.log('📝 Adding new posts to feed, HTML length:', postsHtml.length);

        if (!postsHtml.trim()) {
            console.log('❌ No posts HTML to add');
            return;
        }

        const feedContainer = document.querySelector('.col-lg-8.mx-auto');
        console.log('📝 Feed container found:', !!feedContainer);

        const form = feedContainer?.querySelector('form');
        console.log('📝 Form element found:', !!form);

        if (!form) {
            console.log('❌ Feed form not found');
            return;
        }

        // Create temporary container
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = postsHtml;

        console.log('📝 Number of new posts to add:', tempDiv.children.length);

        // Insert new posts at the top (check for duplicates)
        Array.from(tempDiv.children).reverse().forEach((post, index) => {
            // Extract post ID from the post element
            const postId = this.extractPostId(post);

            if (postId && this.addedPostIds.has(postId)) {
                console.log(`📝 Skipping duplicate post ${postId}`);
                return; // Skip this post as it's already been added
            }

            console.log(`📝 Adding new post ${index + 1} (ID: ${postId})`);

            // Track this post ID
            if (postId) {
                this.addedPostIds.add(postId);
            }

            post.classList.add('new-post');
            post.style.backgroundColor = '#e8f5e8';
            form.insertBefore(post, form.firstChild);

            // Remove highlight after 5 seconds
            setTimeout(() => {
                post.style.backgroundColor = '';
            }, 5000);
        });

        this.showNotification('New posts added!', 'info');
        console.log('✅ New posts added to feed successfully');
    }

    extractPostId(postElement) {
        // Try to extract post ID from various possible attributes/elements
        // Look for data-post-id attribute
        if (postElement.dataset && postElement.dataset.postId) {
            return postElement.dataset.postId;
        }

        // Look for ID attribute that might contain post ID
        if (postElement.id && postElement.id.includes('post-')) {
            const match = postElement.id.match(/post-(\d+)/);
            if (match) return match[1];
        }

        // Look for elements with post ID in their attributes
        const postIdElement = postElement.querySelector('[data-post-id]');
        if (postIdElement) {
            return postIdElement.dataset.postId;
        }

        // Look for comment forms or buttons that might have post ID
        const commentForm = postElement.querySelector('form[data-post-id]');
        if (commentForm) {
            return commentForm.dataset.postId;
        }

        // Look for like/comment buttons that might have post ID
        const actionButton = postElement.querySelector('[data-post-id]');
        if (actionButton) {
            return actionButton.dataset.postId;
        }

        console.log('⚠️ Could not extract post ID from element:', postElement);
        return null;
    }
    
    addNewCommentsToFeed(commentsByPost) {
        console.log('💬 Adding new comments to feed:', commentsByPost);

        Object.keys(commentsByPost).forEach(postId => {
            const comments = commentsByPost[postId];
            console.log(`💬 Adding ${comments.length} comments to post ${postId}`);

            const commentsList = document.querySelector(`#comments-list-${postId}`);
            console.log(`💬 Comments list found for post ${postId}:`, !!commentsList);

            if (!commentsList) {
                console.log(`❌ Comments list not found for post ${postId}`);
                return;
            }

            comments.forEach((comment, index) => {
                console.log(`💬 Adding comment ${index + 1} to post ${postId}`);
                this.addCommentToPost(postId, comment);
            });
        });

        this.showNotification('New comments added!', 'info');
        console.log('✅ New comments added to feed successfully');
    }
    
    setupTabVisibility() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPolling();
                console.log('👁️ Tab hidden, polling stopped');
            } else {
                this.startPolling();
                console.log('👁️ Tab visible, polling resumed');
            }
        });
    }
    
    showNotification(message, type = 'info') {
        // Simple notification system
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 9999;
            font-size: 14px;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Public methods for debugging
    getStatus() {
        return {
            isPolling: this.isPolling,
            lastPostCheck: this.lastPostCheck,
            lastCommentCheck: this.lastCommentCheck
        };
    }
    
    forceCheck() {
        console.log('🔄 Force checking for updates...');
        this.checkForUpdates();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 DOM loaded, checking for newsroom container...');

    // Try multiple selectors to find the newsroom container
    const selectors = [
        '.col-lg-8.mx-auto',
        '.col-lg-8',
        '.newsroom-frontend',
        '.container'
    ];

    let container = null;
    for (const selector of selectors) {
        container = document.querySelector(selector);
        if (container) {
            console.log('🔍 Container found with selector:', selector);
            break;
        }
    }

    console.log('🔍 Final container found:', !!container);

    if (container) {
        console.log('🚀 Initializing real-time updates...');
        window.simpleRealtimeUpdates = new SimpleRealtimeUpdates();
        console.log('✅ Real-time updates initialized:', !!window.simpleRealtimeUpdates);
    } else {
        console.log('❌ Newsroom container not found, real-time updates not initialized');
        console.log('🔍 Available elements:', document.querySelectorAll('div[class*="col"], div[class*="container"]').length);
    }
});

// Make available globally for debugging
window.SimpleRealtimeUpdates = SimpleRealtimeUpdates;

# User Roles & Permissions Guide

## Overview
The Newsroom Training Platform has exactly **4 user roles** with specific permissions:

---

## 1. Administrator
**Full system access**

### Navigation Access:
- ✅ Feed
- ✅ Create Content
- ✅ Admin
- ✅ Blog

### Content Permissions:
- ✅ Create/Publish posts (News & Social Media)
- ✅ Edit all posts (own and others)
- ✅ Delete all posts (own and others)
- ✅ Bulk delete posts (checkbox selection)
- ✅ Delete All Posts button

### Interaction Permissions:
- ✅ Add comments/replies
- ✅ Edit own comments
- ✅ Delete own comments
- ✅ Delete others' comments
- ✅ Like/Unlike posts

### UI Elements Visible:
- ✅ "Publish Post" button
- ✅ "Delete All Posts" button
- ✅ "Delete Selected" (bulk delete) button
- ✅ Checkboxes on posts for bulk selection
- ✅ Edit button on all posts
- ✅ Delete button on all posts
- ✅ Copy Link button
- ✅ Reply button
- ✅ Comment form
- ✅ Delete button on all comments

---

## 2. Newsroom Operator
**Can create/edit/delete content**

### Navigation Access:
- ✅ Feed
- ✅ Create Content
- ❌ Admin (Administrator only)
- ✅ Blog

### Content Permissions:
- ✅ Create/Publish posts (News & Social Media)
- ✅ Edit all posts (own and others)
- ✅ Delete all posts (own and others)
- ✅ Bulk delete posts (checkbox selection)
- ✅ Delete All Posts button

### Interaction Permissions:
- ✅ Add comments/replies
- ✅ Edit own comments
- ✅ Delete own comments
- ✅ Delete others' comments
- ✅ Like/Unlike posts

### UI Elements Visible:
- ✅ "Publish Post" button
- ✅ "Delete All Posts" button
- ✅ "Delete Selected" (bulk delete) button
- ✅ Checkboxes on posts for bulk selection
- ✅ Edit button on all posts
- ✅ Delete button on all posts
- ✅ Copy Link button
- ✅ Reply button
- ✅ Comment form
- ✅ Delete button on all comments

---

## 3. Trainee
**Can read content + add replies/comments and like posts**

### Navigation Access:
- ✅ Feed
- ❌ Create Content (Operator/Admin only)
- ❌ Admin (Administrator only)
- ✅ Blog

### Content Permissions:
- ❌ Create/Publish posts
- ❌ Edit posts
- ❌ Delete posts
- ❌ Bulk delete posts

### Interaction Permissions:
- ✅ Add comments/replies
- ✅ Edit own comments
- ✅ Delete own comments only
- ❌ Delete others' comments
- ✅ Like/Unlike posts

### UI Elements Visible:
- ❌ "Publish Post" button (hidden)
- ❌ "Delete All Posts" button (hidden)
- ❌ "Delete Selected" button (hidden)
- ❌ Checkboxes on posts (hidden)
- ❌ Edit button on posts (hidden)
- ❌ Delete button on posts (hidden)
- ✅ Copy Link button
- ✅ Reply button
- ✅ Comment form
- ✅ Delete button on own comments only

---

## 4. Viewer
**Read-only access to content (no interaction)**

### Navigation Access:
- ✅ Feed
- ❌ Create Content (Operator/Admin only)
- ❌ Admin (Administrator only)
- ✅ Blog

### Content Permissions:
- ❌ Create/Publish posts
- ❌ Edit posts
- ❌ Delete posts
- ❌ Bulk delete posts

### Interaction Permissions:
- ❌ Add comments/replies
- ❌ Edit comments
- ❌ Delete comments
- ❌ Like/Unlike posts

### UI Elements Visible:
- ❌ "Publish Post" button (hidden)
- ❌ "Delete All Posts" button (hidden)
- ❌ "Delete Selected" button (hidden)
- ❌ Checkboxes on posts (hidden)
- ❌ Edit button on posts (hidden)
- ❌ Delete button on posts (hidden)
- ✅ Copy Link button (read-only action)
- ❌ Reply button (hidden)
- ❌ Comment form (hidden - shows message: "You don't have permission to add comments")
- ❌ Delete button on comments (hidden)

---

## WordPress Capabilities Mapping

### Administrator
- `manage_options` - Full admin access
- `publish_posts` - Can publish content
- `edit_posts` - Can edit own posts
- `edit_others_posts` - Can edit others' posts
- `delete_posts` - Can delete own posts
- `delete_others_posts` - Can delete others' posts
- `moderate_comments` - Can moderate all comments
- All custom capabilities

### Newsroom Operator
- `publish_posts` - Can publish content
- `edit_posts` - Can edit own posts
- `edit_others_posts` - Can edit others' posts
- `delete_posts` - Can delete own posts
- `delete_others_posts` - Can delete others' posts
- `edit_news_articles` - Can edit news articles
- `edit_social_posts` - Can edit social posts
- `delete_others_social_posts` - Can delete others' social posts
- `delete_others_news_articles` - Can delete others' news articles

### Trainee
- `read` - Can read content
- `add_reply` - Can add comments/replies
- `edit_own_reply` - Can edit own replies
- `delete_own_reply` - Can delete own replies
- `like_post` - Can like posts
- `unlike_post` - Can unlike posts

### Viewer
- `read` - Can read content only
- No other capabilities

---

## Implementation Details

### Files Modified:
1. **header.php** - Navigation menu based on roles
2. **index.php** - Publish Post and Delete All Posts buttons
3. **content-social.php** - Edit/Delete buttons and checkboxes
4. **content-news.php** - Edit/Delete buttons and checkboxes
5. **single-social_post.php** - Comment form and delete buttons
6. **setup-user-roles.php** - Role definitions and capabilities

### Permission Checks Used:
```php
// Administrator only
current_user_can('manage_options')

// Newsroom Operator and Administrator
current_user_can('publish_posts')
current_user_can('delete_others_posts')

// Trainee and above (not Viewer)
current_user_can('add_reply')

// Own content only
get_current_user_id() == $comment->user_id && current_user_can('delete_own_reply')
```

---

## Testing Checklist

### Test as Administrator:
- [ ] Can see "Create Content" and "Admin" in navigation
- [ ] Can see "Publish Post" button
- [ ] Can see "Delete All Posts" button
- [ ] Can see checkboxes on posts
- [ ] Can edit and delete any post
- [ ] Can add and delete any comment

### Test as Newsroom Operator:
- [ ] Can see "Create Content" but NOT "Admin" in navigation
- [ ] Can see "Publish Post" button
- [ ] Can see "Delete All Posts" button
- [ ] Can see checkboxes on posts
- [ ] Can edit and delete any post
- [ ] Can add and delete any comment

### Test as Trainee:
- [ ] Cannot see "Create Content" or "Admin" in navigation
- [ ] Cannot see "Publish Post" button
- [ ] Cannot see "Delete All Posts" button
- [ ] Cannot see checkboxes on posts
- [ ] Cannot see edit/delete buttons on posts
- [ ] Can see "Reply" button
- [ ] Can add comments
- [ ] Can delete only own comments

### Test as Viewer:
- [ ] Cannot see "Create Content" or "Admin" in navigation
- [ ] Cannot see "Publish Post" button
- [ ] Cannot see "Delete All Posts" button
- [ ] Cannot see checkboxes on posts
- [ ] Cannot see edit/delete buttons on posts
- [ ] Cannot see "Reply" button
- [ ] Cannot add comments (shows permission message)
- [ ] Cannot delete any comments

---

## Notes
- All permission checks are done server-side for security
- UI elements are hidden using PHP conditionals, not just CSS
- Unauthorized actions are blocked at the WordPress capability level
- The system prevents both UI access and direct action attempts


# User Permissions Summary

## Quick Reference

| Feature | Administrator | Newsroom Operator | Trainee | Viewer |
|---------|--------------|-------------------|---------|--------|
| **Navigation** |
| Feed | ✅ | ✅ | ✅ | ✅ |
| Create Content | ✅ | ✅ | ❌ | ❌ |
| Admin | ✅ | ❌ | ❌ | ❌ |
| Blog | ✅ | ✅ | ✅ | ✅ |
| **Content Actions** |
| Publish Post | ✅ | ✅ | ❌ | ❌ |
| Edit Posts | ✅ All | ✅ All | ❌ | ❌ |
| Delete Posts | ✅ All | ✅ All | ❌ | ❌ |
| Delete All Posts | ✅ | ✅ | ❌ | ❌ |
| Bulk Delete | ✅ | ✅ | ❌ | ❌ |
| **Interactions** |
| Reply/Comment | ✅ | ✅ | ✅ | ❌ |
| Delete Own Comments | ✅ | ✅ | ✅ | ❌ |
| Delete Others' Comments | ✅ | ✅ | ❌ | ❌ |
| Like Posts | ✅ | ✅ | ✅ | ❌ |
| Copy Link | ✅ | ✅ | ✅ | ✅ |

---

## UI Elements Visibility

### Top Navigation Bar
```
Administrator:        Feed | Create Content | Admin | Blog
Newsroom Operator:    Feed | Create Content | Blog
Trainee:              Feed | Blog
Viewer:               Feed | Blog
```

### Feed Page Buttons
```
Administrator:        [Publish Post] [Delete All Posts]
Newsroom Operator:    [Publish Post] [Delete All Posts]
Trainee:              (no buttons)
Viewer:               (no buttons)
```

### Post Actions (3-dot menu)
```
Administrator:        [Copy Link] [Edit] [Delete]
Newsroom Operator:    [Copy Link] [Edit] [Delete]
Trainee:              [Copy Link]
Viewer:               [Copy Link]
```

### Post Checkboxes (for bulk delete)
```
Administrator:        ✅ Visible
Newsroom Operator:    ✅ Visible
Trainee:              ❌ Hidden
Viewer:               ❌ Hidden
```

### Reply Button (on posts)
```
Administrator:        ✅ Visible
Newsroom Operator:    ✅ Visible
Trainee:              ✅ Visible
Viewer:               ❌ Hidden
```

### Comment Form (on single post page)
```
Administrator:        ✅ Can comment
Newsroom Operator:    ✅ Can comment
Trainee:              ✅ Can comment
Viewer:               ❌ Shows: "You don't have permission to add comments"
```

### Delete Comment Button
```
Administrator:        ✅ On all comments
Newsroom Operator:    ✅ On all comments
Trainee:              ✅ On own comments only
Viewer:               ❌ Never visible
```

---

## Key WordPress Capabilities

### Administrator
- `manage_options` (full admin)
- `publish_posts`
- `delete_others_posts`
- All other capabilities

### Newsroom Operator
- `publish_posts`
- `edit_others_posts`
- `delete_others_posts`
- `edit_news_articles`
- `edit_social_posts`

### Trainee
- `read`
- `add_reply`
- `delete_own_reply`
- `like_post`

### Viewer
- `read` only

---

## Permission Check Examples

```php
// Check if user can see "Admin" menu
if (current_user_can('manage_options')) {
    // Show Admin link
}

// Check if user can publish posts
if (current_user_can('publish_posts')) {
    // Show Publish Post button
}

// Check if user can delete others' posts
if (current_user_can('delete_others_posts')) {
    // Show Delete All Posts button
    // Show checkboxes for bulk delete
}

// Check if user can add replies
if (current_user_can('add_reply') || current_user_can('edit_posts')) {
    // Show Reply button
    // Show comment form
}

// Check if user can delete a specific comment
$can_delete = (get_current_user_id() == $comment->user_id && current_user_can('delete_own_reply')) 
           || current_user_can('delete_others_posts');
```

---

## Setup Instructions

1. Run the setup script:
```bash
php setup-user-roles.php
```

2. This will create/update the 4 roles:
   - Administrator (WordPress default)
   - Newsroom Operator (custom)
   - Trainee (custom)
   - Viewer (custom)

3. Assign users to roles via WordPress Admin:
   - Go to Users → All Users
   - Edit user
   - Change role from dropdown
   - Save

---

## Security Notes

✅ All permissions are checked server-side
✅ UI elements are hidden using PHP conditionals
✅ Direct action attempts are blocked by WordPress capabilities
✅ Nonces are used for all form submissions
✅ User input is sanitized and validated

---

## Files Modified

1. `wp-content/themes/newsroom-training/header.php`
   - Navigation menu based on roles

2. `wp-content/themes/newsroom-training/index.php`
   - Publish Post button
   - Delete All Posts button
   - Bulk Delete button
   - Reply button

3. `wp-content/themes/newsroom-training/template-parts/content-social.php`
   - Edit/Delete buttons
   - Checkboxes for bulk selection

4. `wp-content/themes/newsroom-training/template-parts/content-news.php`
   - Edit/Delete buttons
   - Checkboxes for bulk selection

5. `wp-content/themes/newsroom-training/single-social_post.php`
   - Comment form
   - Delete comment buttons

6. `setup-user-roles.php`
   - Role definitions and capabilities


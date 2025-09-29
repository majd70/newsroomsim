<?php
/**
 * Content Management Functions
 * Handles news articles and social media posts
 */

/**
 * Register post types (simulation)
 */
function newsroom_register_post_types() {
    // This is a simulation - actual data is stored in JSON files
    // But provides WordPress-like structure for better organization
}

/**
 * Get all content (news and social media posts)
 */
function newsroom_get_content($filter = 'all', $scenario = null) {
    $content = getContent(); // Use original function from includes/functions.php
    
    // Filter by type
    if ($filter !== 'all') {
        $content = array_filter($content, function($item) use ($filter) {
            if ($filter === 'news') {
                return $item['type'] === 'news';
            } else {
                return $item['type'] === $filter;
            }
        });
    }
    
    // Filter by scenario
    if ($scenario) {
        $content = array_filter($content, function($item) use ($scenario) {
            return isset($item['scenario']) && $item['scenario'] === $scenario;
        });
    }
    
    return $content;
}

/**
 * Add new content item
 */
function newsroom_add_content($content_data) {
    return addContent($content_data); // Use original function
}

/**
 * Get content by ID
 */
function newsroom_get_content_by_id($id) {
    $content = getContent();
    foreach ($content as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }
    return null;
}

/**
 * Update content item
 */
function newsroom_update_content($id, $data) {
    $content = getContent();
    $updated = false;
    
    foreach ($content as &$item) {
        if ($item['id'] === $id) {
            $item = array_merge($item, $data);
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        file_put_contents(CONTENT_FILE, json_encode($content, JSON_PRETTY_PRINT));
        return true;
    }
    
    return false;
}

/**
 * Delete content item
 */
function newsroom_delete_content($id) {
    $content = getContent();
    $content = array_filter($content, function($item) use ($id) {
        return $item['id'] !== $id;
    });
    
    return file_put_contents(CONTENT_FILE, json_encode(array_values($content), JSON_PRETTY_PRINT));
}

/**
 * Get content statistics
 */
function newsroom_get_content_stats() {
    $content = getContent();
    
    $stats = [
        'total' => count($content),
        'news' => 0,
        'twitter' => 0,
        'facebook' => 0,
        'instagram' => 0,
        'pinned' => 0
    ];
    
    foreach ($content as $item) {
        if (isset($item['type'])) {
            if ($item['type'] === 'news') {
                $stats['news']++;
            } else {
                $stats[$item['type']]++;
            }
        }
        
        if (!empty($item['pinned'])) {
            $stats['pinned']++;
        }
    }
    
    return $stats;
}
?>
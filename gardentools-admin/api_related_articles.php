<?php
/**
 * Frontend API: Related Articles by Tags
 * Lấy bài viết liên quan dựa trên tag matching (không yêu cầu đăng nhập)
 *
 * Usage:
 * GET /api/related_articles.php?article_id=123&type=news_for_project&limit=4
 *
 * Parameters:
 * - article_id: ID bài viết hiện tại (required)
 * - type: auto | news_for_project | projects_for_news | same_type (default: auto)
 * - limit: 1-20 (default: 6)
 *
 * @version 1.1
 */

// Error handling - Always output JSON
error_reporting(0);
ini_set('display_errors', 0);

// Custom error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'success' => false,
        'message' => 'Server error',
        'debug' => "Error [$errno]: $errstr in $errfile:$errline"
    ));
    exit;
});

// Custom exception handler
set_exception_handler(function ($e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'success' => false,
        'message' => 'Exception occurred',
        'debug' => $e->getMessage()
    ));
    exit;
});

define('TTH_SYSTEM', true);

// Set default HTTP_HOST for CLI/API context
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost';
}
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/api/related_articles.php';
}

// Load define and config
$definePath = dirname(__DIR__) . '/define.php';
$configPath = dirname(__DIR__) . '/config.php';

if (!file_exists($definePath) || !file_exists($configPath)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => 'Config not found'));
    exit;
}

require_once $definePath;
require_once $configPath;

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Set language
$_SESSION["language"] = 'vi';

// Load functions
require_once(_F_FUNCTIONS . DIRECTORY_SEPARATOR . "Function.php");

// Connect to database
try {
    $db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'message' => 'Database connection error'));
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Get parameters
$articleId = isset($_GET['article_id']) ? intval($_GET['article_id']) : 0;
$limit = isset($_GET['limit']) ? min(20, max(1, intval($_GET['limit']))) : 6;
$type = isset($_GET['type']) ? trim($_GET['type']) : 'auto';

if ($articleId <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Article ID không hợp lệ'));
    exit;
}

// Get current article info
$db->table = "article";
$db->condition = "article_id = $articleId AND is_active = 1";
$db->limit = "1";
$currentArticleRows = $db->select();

if (empty($currentArticleRows)) {
    echo json_encode(array('success' => false, 'message' => 'Bài viết không tồn tại'));
    exit;
}

$currentArticle = $currentArticleRows[0];
$currentMenuId = intval($currentArticle['article_menu_id']);

// Get category info
$db->table = "article_menu";
$db->condition = "article_menu_id = $currentMenuId";
$db->limit = "1";
$menuRows = $db->select();

$categoryId = !empty($menuRows) ? intval($menuRows[0]['category_id']) : 0;

// Determine target category based on type
$targetCategoryId = null;

if ($type === 'auto') {
    // Auto: Nếu đang xem dự án -> lấy tin tức, và ngược lại
    $targetCategoryId = ($categoryId == 2) ? 3 : 2;
} elseif ($type === 'news_for_project') {
    $targetCategoryId = 3; // Tin tức
} elseif ($type === 'projects_for_news') {
    $targetCategoryId = 2; // Dự án
} elseif ($type === 'same_type') {
    $targetCategoryId = $categoryId; // Cùng loại
}

// Check if tags tables exist
$tagsTableExists = false;
$checkResult = $db->query("SHOW TABLES LIKE '" . TTH_DATA_PREFIX . "tags'");
if (!empty($checkResult)) {
    $tagsTableExists = true;
}

$relatedArticles = array();

// Try to get related articles by tag matching
if ($tagsTableExists) {
    $sql = "SELECT
                a.article_id,
                a.name,
                a.img,
                a.comment,
                a.created_time,
                am.name as menu_name,
                am.slug as menu_slug,
                COUNT(at2.tag_id) as match_score
            FROM " . TTH_DATA_PREFIX . "article_tags at1
            INNER JOIN " . TTH_DATA_PREFIX . "article_tags at2 ON at1.tag_id = at2.tag_id AND at1.article_id != at2.article_id
            INNER JOIN " . TTH_DATA_PREFIX . "article a ON at2.article_id = a.article_id
            INNER JOIN " . TTH_DATA_PREFIX . "article_menu am ON a.article_menu_id = am.article_menu_id
            WHERE at1.article_id = $articleId
                AND a.is_active = 1
                AND am.is_active = 1";

    if ($targetCategoryId !== null) {
        $sql .= " AND am.category_id = $targetCategoryId";
    }

    $sql .= " GROUP BY a.article_id
              ORDER BY match_score DESC, a.created_time DESC
              LIMIT $limit";

    $result = $db->query($sql);
    if (is_array($result)) {
        $relatedArticles = $result;
    }
}

// If not enough articles, fill with latest articles from target category
$foundCount = count($relatedArticles);
$excludeIds = array($articleId);

foreach ($relatedArticles as $article) {
    $excludeIds[] = $article['article_id'];
}

if ($foundCount < $limit && $targetCategoryId !== null) {
    $remaining = $limit - $foundCount;
    $excludeIdsStr = implode(',', $excludeIds);

    $fillSql = "SELECT
                    a.article_id,
                    a.name,
                    a.img,
                    a.comment,
                    a.created_time,
                    am.name as menu_name,
                    am.slug as menu_slug,
                    0 as match_score
                FROM " . TTH_DATA_PREFIX . "article a
                INNER JOIN " . TTH_DATA_PREFIX . "article_menu am ON a.article_menu_id = am.article_menu_id
                WHERE a.is_active = 1
                    AND am.is_active = 1
                    AND am.category_id = $targetCategoryId
                    AND a.article_id NOT IN ($excludeIdsStr)
                ORDER BY a.created_time DESC
                LIMIT $remaining";

    $fillArticles = $db->query($fillSql);
    if (is_array($fillArticles)) {
        $relatedArticles = array_merge($relatedArticles, $fillArticles);
    }
}

// Get category slug for URL building
$catSlug = '';
if ($targetCategoryId !== null) {
    $db->table = "category";
    $db->condition = "category_id = $targetCategoryId AND is_active = 1";
    $db->limit = "1";
    $catRows = $db->select();
    if (!empty($catRows)) {
        $catSlug = $catRows[0]['slug'];
    }
}

// Format output
$stringObj = new StringHelper();
$formattedArticles = array();

foreach ($relatedArticles as $article) {
    $imgUrl = '';
    if (!empty($article['img']) && $article['img'] != 'no') {
        $imgUrl = HOME_URL . '/uploads/article/' . $article['img'];
    } else {
        $imgUrl = HOME_URL . '/assets/images/no-image.png';
    }

    // Build article URL
    $articleSlug = $stringObj->getSlug($article['name']) . '-' . $article['article_id'];
    $articleUrl = HOME_URL_LANG . '/' . $catSlug . '/' . $article['menu_slug'] . '/' . $articleSlug;

    $formattedArticles[] = array(
        'id' => $article['article_id'],
        'title' => stripslashes($article['name']),
        'excerpt' => !empty($article['comment']) ? $stringObj->crop(strip_tags(stripslashes($article['comment'])), 100) : '',
        'image_url' => $imgUrl,
        'url' => $articleUrl,
        'menu_name' => stripslashes($article['menu_name']),
        'date' => date('d/m/Y', $article['created_time']),
        'match_score' => intval($article['match_score'])
    );
}

echo json_encode(array(
    'success' => true,
    'data' => array(
        'current_article' => array(
            'id' => $articleId,
            'title' => stripslashes($currentArticle['name']),
            'category_id' => $categoryId,
            'category_type' => ($categoryId == 2) ? 'project' : (($categoryId == 3) ? 'news' : 'other')
        ),
        'related_articles' => $formattedArticles,
        'type' => $type,
        'target_category_id' => $targetCategoryId,
        'total' => count($formattedArticles)
    )
));

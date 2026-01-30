<?php
/**
 * AJAX Sitemap Generator
 * Xử lý request tạo sitemap từ Admin
 */

@session_start();
define('TTH_SYSTEM', true);
$_SESSION["language"] = 'vi';

header('Content-Type: application/json; charset=utf-8');

// Kiểm tra login
if (empty($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Chưa đăng nhập'));
    exit;
}

require_once(__DIR__ . '/../define.php');
require_once(_F_FUNCTIONS . DIRECTORY_SEPARATOR . "Function.php");

try {
    $db = new ActiveRecord(TTH_DB_HOST, TTH_DB_USER, TTH_DB_PASS, TTH_DB_NAME);
} catch (DatabaseConnException $e) {
    echo json_encode(array('success' => false, 'message' => 'Lỗi kết nối database'));
    exit;
}

// Class SitemapGenerator
class SitemapGenerator
{
    private $db;
    private $homeUrl;
    private $urls = array();
    private $stringHelper;

    private $config = array(
        'home' => array('priority' => '1.0', 'changefreq' => 'daily'),
        'category' => array('priority' => '0.8', 'changefreq' => 'weekly'),
        'article' => array('priority' => '0.6', 'changefreq' => 'monthly'),
        'project' => array('priority' => '0.9', 'changefreq' => 'weekly'),
        'product' => array('priority' => '0.7', 'changefreq' => 'weekly'),
        'page' => array('priority' => '0.5', 'changefreq' => 'monthly'),
    );

    public function __construct($db)
    {
        $this->db = $db;
        $this->homeUrl = HOME_URL;
        $this->stringHelper = new StringHelper();
    }

    public function generate()
    {
        $this->urls = array();
        $this->addHomepage();
        $this->addStaticPages();
        $this->addArticles();
        $this->addProjects();
        $this->addProducts();
        $this->addGalleries();
        return $this->buildXml();
    }

    private function addHomepage()
    {
        $this->urls[] = array(
            'loc' => $this->homeUrl,
            'lastmod' => date('Y-m-d'),
            'changefreq' => $this->config['home']['changefreq'],
            'priority' => $this->config['home']['priority'],
        );
    }

    private function addStaticPages()
    {
        $staticPages = array('gioi-thieu', 'lien-he');
        foreach ($staticPages as $slug) {
            if (file_exists(_F_MODULES . DS . str_replace('-', '_', $slug) . '.php')) {
                $this->urls[] = array(
                    'loc' => $this->homeUrl . '/' . $slug,
                    'lastmod' => date('Y-m-d'),
                    'changefreq' => $this->config['page']['changefreq'],
                    'priority' => $this->config['page']['priority'],
                );
            }
        }

        try {
            $this->db->table = "plugin_page";
            $this->db->condition = "is_active = 1";
            $this->db->order = "sort ASC";
            $this->db->limit = "";
            $pages = $this->db->select();
            if (is_array($pages)) {
                foreach ($pages as $page) {
                    if (!empty($page['alias'])) {
                        $this->urls[] = array(
                            'loc' => $this->homeUrl . '/' . $page['alias'],
                            'lastmod' => date('Y-m-d'),
                            'changefreq' => $this->config['page']['changefreq'],
                            'priority' => $this->config['page']['priority'],
                        );
                    }
                }
            }
        } catch (Exception $e) {
        }
    }

    private function addArticles()
    {
        try {
            $this->db->table = "article_menu";
            $this->db->condition = "is_active = 1";
            $this->db->order = "sort ASC";
            $this->db->limit = "";
            $menus = $this->db->select();
            if (is_array($menus)) {
                foreach ($menus as $menu) {
                    $menuSlug = $this->stringHelper->getSlug($menu['name']);
                    $this->urls[] = array(
                        'loc' => $this->homeUrl . '/tin-tuc/' . $menuSlug . '-' . $menu['article_menu_id'],
                        'lastmod' => date('Y-m-d'),
                        'changefreq' => $this->config['category']['changefreq'],
                        'priority' => $this->config['category']['priority'],
                    );
                }
            }
        } catch (Exception $e) {
        }

        try {
            $this->db->table = "article";
            $this->db->condition = "is_active = 1";
            $this->db->order = "article_id DESC";
            $this->db->limit = "1000";
            $articles = $this->db->select();
            if (is_array($articles)) {
                foreach ($articles as $article) {
                    $articleSlug = $this->stringHelper->getLinkHtml($article['name'], $article['article_id']);
                    $menuSlug = $this->getMenuSlug('article_menu', $article['article_menu_id']);
                    $this->urls[] = array(
                        'loc' => $this->homeUrl . '/tin-tuc/' . $menuSlug . '/' . $articleSlug,
                        'lastmod' => !empty($article['updated_at']) ? date('Y-m-d', strtotime($article['updated_at'])) : date('Y-m-d'),
                        'changefreq' => $this->config['article']['changefreq'],
                        'priority' => $this->config['article']['priority'],
                    );
                }
            }
        } catch (Exception $e) {
        }
    }

    private function addProjects()
    {
        // Danh mục dự án BĐS (article_project_menu)
        try {
            $this->db->table = "article_project_menu";
            $this->db->condition = "is_active = 1";
            $this->db->order = "sort ASC";
            $this->db->limit = "";
            $menus = $this->db->select();
            if (is_array($menus)) {
                foreach ($menus as $menu) {
                    $menuSlug = !empty($menu['slug']) ? $menu['slug'] : $this->stringHelper->getSlug($menu['name']);
                    $this->urls[] = array(
                        'loc' => $this->homeUrl . '/du-an/' . $menuSlug,
                        'lastmod' => date('Y-m-d'),
                        'changefreq' => $this->config['category']['changefreq'],
                        'priority' => $this->config['project']['priority'],
                    );
                }
            }
        } catch (Exception $e) {
        }

        // Chi tiết dự án BĐS (article_project)
        try {
            $this->db->table = "article_project";
            $this->db->condition = "is_active = 1";
            $this->db->order = "article_project_id DESC";
            $this->db->limit = "500";
            $projects = $this->db->select();
            if (is_array($projects)) {
                foreach ($projects as $project) {
                    $projectSlug = $this->stringHelper->getSlug($project['name']) . '-' . $project['article_project_id'];
                    $menuSlug = $this->getArticleMenuSlug('article_project_menu', $project['article_project_menu_id']);
                    $this->urls[] = array(
                        'loc' => $this->homeUrl . '/du-an/' . $menuSlug . '/' . $projectSlug,
                        'lastmod' => !empty($project['modified_time']) ? date('Y-m-d', $project['modified_time']) : date('Y-m-d'),
                        'changefreq' => $this->config['project']['changefreq'],
                        'priority' => $this->config['project']['priority'],
                    );
                }
            }
        } catch (Exception $e) {
        }
    }

    private function addProducts()
    {
        // Danh mục sản phẩm BĐS (article_product_menu)
        try {
            $this->db->table = "article_product_menu";
            $this->db->condition = "is_active = 1";
            $this->db->order = "sort ASC";
            $this->db->limit = "";
            $menus = $this->db->select();
            if (is_array($menus)) {
                foreach ($menus as $menu) {
                    $menuSlug = !empty($menu['slug']) ? $menu['slug'] : $this->stringHelper->getSlug($menu['name']);
                    $this->urls[] = array(
                        'loc' => $this->homeUrl . '/san-pham/' . $menuSlug,
                        'lastmod' => date('Y-m-d'),
                        'changefreq' => $this->config['category']['changefreq'],
                        'priority' => $this->config['product']['priority'],
                    );
                }
            }
        } catch (Exception $e) {
        }

        // Chi tiết sản phẩm BĐS (article_product)
        try {
            $this->db->table = "article_product";
            $this->db->condition = "is_active = 1";
            $this->db->order = "article_product_id DESC";
            $this->db->limit = "1000";
            $products = $this->db->select();
            if (is_array($products)) {
                foreach ($products as $product) {
                    $productSlug = $this->stringHelper->getSlug($product['name']) . '-' . $product['article_product_id'];
                    $menuSlug = $this->getArticleMenuSlug('article_product_menu', $product['article_product_menu_id']);
                    $this->urls[] = array(
                        'loc' => $this->homeUrl . '/san-pham/' . $menuSlug . '/' . $productSlug,
                        'lastmod' => !empty($product['modified_time']) ? date('Y-m-d', $product['modified_time']) : date('Y-m-d'),
                        'changefreq' => $this->config['product']['changefreq'],
                        'priority' => $this->config['product']['priority'],
                    );
                }
            }
        } catch (Exception $e) {
        }
    }

    private function addGalleries()
    {
        try {
            $this->db->table = "gallery_menu";
            $this->db->condition = "is_active = 1";
            $this->db->order = "sort ASC";
            $this->db->limit = "";
            $menus = $this->db->select();
            if (is_array($menus)) {
                foreach ($menus as $menu) {
                    $menuSlug = $this->stringHelper->getSlug($menu['name']);
                    $this->urls[] = array(
                        'loc' => $this->homeUrl . '/thu-vien/' . $menuSlug . '-' . $menu['gallery_menu_id'],
                        'lastmod' => date('Y-m-d'),
                        'changefreq' => $this->config['category']['changefreq'],
                        'priority' => '0.5',
                    );
                }
            }
        } catch (Exception $e) {
        }
    }

    private function getMenuSlug($table, $id)
    {
        try {
            $idField = str_replace('_menu', '_menu_id', $table);
            $this->db->table = $table;
            $this->db->condition = $idField . " = " . intval($id);
            $this->db->order = "";
            $this->db->limit = "1";
            $result = $this->db->select();
            if (is_array($result) && !empty($result[0])) {
                return $this->stringHelper->getSlug($result[0]['name']) . '-' . $id;
            }
        } catch (Exception $e) {
        }
        return 'menu-' . $id;
    }

    /**
     * Lấy slug của article menu (article_project_menu, article_product_menu)
     */
    private function getArticleMenuSlug($table, $id)
    {
        try {
            $idField = $table . '_id'; // article_project_menu_id, article_product_menu_id
            $this->db->table = $table;
            $this->db->condition = $idField . " = " . intval($id);
            $this->db->order = "";
            $this->db->limit = "1";
            $result = $this->db->select();
            if (is_array($result) && !empty($result[0])) {
                // Ưu tiên dùng slug nếu có, nếu không thì tạo từ name
                if (!empty($result[0]['slug'])) {
                    return $result[0]['slug'];
                }
                return $this->stringHelper->getSlug($result[0]['name']);
            }
        } catch (Exception $e) {
        }
        return 'menu-' . $id;
    }

    private function buildXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($this->urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }

    public function saveToFile($filename = 'sitemap.xml')
    {
        $xml = $this->generate();
        $filepath = ROOT_DIR . DS . $filename;
        $result = @file_put_contents($filepath, $xml);
        if ($result === false) {
            return array(
                'success' => false,
                'message' => 'Không thể ghi file. Kiểm tra quyền thư mục.',
            );
        }
        return array(
            'success' => true,
            'message' => 'Sitemap đã được tạo thành công!',
            'url_count' => count($this->urls),
            'filesize' => filesize($filepath),
        );
    }
}

// Xử lý request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_sitemap') {
    $generator = new SitemapGenerator($db);
    $result = $generator->saveToFile();
    echo json_encode($result);
} else {
    echo json_encode(array('success' => false, 'message' => 'Invalid request'));
}

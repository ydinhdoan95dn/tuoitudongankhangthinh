<?php
/**
 * Analytics Class - Page View Tracking
 * Theo dõi hành vi người dùng trên website
 */

if (!defined('TTH_SYSTEM')) {
    die('Please stop!');
}

class Analytics
{
    private static $db;

    /**
     * Initialize database connection
     */
    public static function init()
    {
        global $db;
        self::$db = $db;
    }

    /**
     * Track a page view
     *
     * @param string $contentType - article, project, page, category
     * @param int|null $contentId - ID của nội dung
     * @param string|null $pageTitle - Tiêu đề trang
     */
    public static function track($contentType = 'page', $contentId = null, $pageTitle = null)
    {
        self::init();

        // Skip bots
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (self::isBot($userAgent)) {
            return false;
        }

        // Get request data
        $pageUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        // Parse referrer domain
        $referrerDomain = '';
        if ($referrer) {
            $parsed = parse_url($referrer);
            $referrerDomain = isset($parsed['host']) ? $parsed['host'] : '';
        }

        // Get UTM parameters
        $utmSource = isset($_GET['utm_source']) ? $_GET['utm_source'] : null;
        $utmMedium = isset($_GET['utm_medium']) ? $_GET['utm_medium'] : null;
        $utmCampaign = isset($_GET['utm_campaign']) ? $_GET['utm_campaign'] : null;

        // Detect device type
        $deviceType = self::detectDeviceType($userAgent);

        // Parse browser and OS
        $browser = self::detectBrowser($userAgent);
        $os = self::detectOS($userAgent);

        // Hash IP for privacy
        $ipHash = hash('sha256', (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . date('Y-m-d'));

        // Session tracking
        if (!isset($_SESSION['analytics_session_id'])) {
            $_SESSION['analytics_session_id'] = bin2hex(random_bytes(16));
        }
        $sessionId = $_SESSION['analytics_session_id'];

        // Use 0 for NULL content_id for consistency
        $safeContentId = $contentId ? intval($contentId) : 0;

        // Check if unique view
        $isUnique = 1;
        self::$db->table = "page_views";
        self::$db->condition = "session_id = '" . $sessionId . "' AND content_type = '" . $contentType . "' AND content_id = " . $safeContentId . " AND DATE(created_at) = CURDATE()";
        self::$db->limit = "1";
        $existing = self::$db->select();
        $isUnique = empty($existing) ? 1 : 0;

        // Insert page view record
        try {
            self::$db->table = "page_views";
            $data = array(
                'content_type' => $contentType,
                'content_id' => $safeContentId,
                'page_url' => substr($pageUrl, 0, 500),
                'page_title' => $pageTitle ? substr($pageTitle, 0, 255) : null,
                'referrer' => $referrer ? substr($referrer, 0, 500) : null,
                'referrer_domain' => $referrerDomain ? substr($referrerDomain, 0, 255) : null,
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'user_agent' => $userAgent,
                'device_type' => $deviceType,
                'browser' => $browser,
                'os' => $os,
                'ip_hash' => $ipHash,
                'session_id' => $sessionId,
                'is_unique' => $isUnique
            );
            self::$db->insert($data);

            // Update daily aggregation
            self::updateDailyStats($contentType, $contentId, $deviceType, $isUnique);

            // Update traffic sources
            self::updateTrafficSource($referrerDomain, $utmSource, $utmMedium);

            // Update content view counter
            if ($contentId && $isUnique) {
                self::incrementContentViews($contentType, $contentId);
            }

            // Update legacy Online Users table (Real-time)
            self::updateOnlineUser($pageUrl);

            return true;
        } catch (Exception $e) {
            error_log("Analytics track error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update daily aggregated stats
     */
    private static function updateDailyStats($contentType, $contentId, $deviceType, $isUnique)
    {
        $today = date('Y-m-d');

        // Use 0 for NULL content_id to avoid unique key issues
        $safeContentId = $contentId ? intval($contentId) : 0;

        // Check existing record
        self::$db->table = "page_views_daily";
        $condition = "content_type = '" . $contentType . "' AND view_date = '" . $today . "' AND content_id = " . $safeContentId;
        self::$db->condition = $condition;
        self::$db->limit = "1";
        $existing = self::$db->select();

        $deviceColumn = $deviceType . '_views';
        if (!in_array($deviceColumn, array('desktop_views', 'mobile_views', 'tablet_views'))) {
            $deviceColumn = 'desktop_views';
        }

        if (!empty($existing)) {
            // Update existing
            self::$db->table = "page_views_daily";
            self::$db->condition = "id = " . $existing[0]['id'];
            self::$db->update(array(
                'total_views' => $existing[0]['total_views'] + 1,
                'unique_views' => $existing[0]['unique_views'] + $isUnique,
                $deviceColumn => $existing[0][$deviceColumn] + 1
            ));
        } else {
            // Insert new
            self::$db->table = "page_views_daily";
            $data = array(
                'content_type' => $contentType,
                'content_id' => $safeContentId,
                'view_date' => $today,
                'total_views' => 1,
                'unique_views' => $isUnique,
                $deviceColumn => 1
            );
            self::$db->insert($data);
        }
    }

    /**
     * Update traffic sources
     */
    private static function updateTrafficSource($referrerDomain, $utmSource, $utmMedium)
    {
        $today = date('Y-m-d');

        // Determine source type
        $sourceType = 'direct';
        $sourceName = null;

        if ($utmSource) {
            if (in_array($utmMedium, array('cpc', 'ppc', 'paid'))) {
                $sourceType = 'paid';
            } elseif ($utmMedium === 'email') {
                $sourceType = 'email';
            } elseif (in_array($utmMedium, array('social', 'social-media'))) {
                $sourceType = 'social';
            } else {
                $sourceType = 'referral';
            }
            $sourceName = $utmSource;
        } elseif ($referrerDomain) {
            $socialDomains = array('facebook.com', 'fb.com', 'instagram.com', 'twitter.com', 'x.com', 'linkedin.com', 'tiktok.com', 'youtube.com', 'zalo.me');
            $searchEngines = array('google.', 'bing.com', 'yahoo.', 'coccoc.com', 'baidu.com');

            $isSocial = false;
            $isOrganic = false;

            foreach ($socialDomains as $social) {
                if (stripos($referrerDomain, $social) !== false) {
                    $isSocial = true;
                    $sourceName = str_replace(array('.com', '.me'), '', $social);
                    break;
                }
            }

            if (!$isSocial) {
                foreach ($searchEngines as $engine) {
                    if (stripos($referrerDomain, $engine) !== false) {
                        $isOrganic = true;
                        $sourceName = explode('.', $engine)[0];
                        break;
                    }
                }
            }

            if ($isSocial) {
                $sourceType = 'social';
            } elseif ($isOrganic) {
                $sourceType = 'organic';
            } else {
                $sourceType = 'referral';
                $sourceName = $referrerDomain;
            }
        }

        // Upsert traffic source - use empty string consistently for NULL source_name
        try {
            $safeSourceName = $sourceName ? $sourceName : '';

            self::$db->table = "traffic_sources";
            $condition = "source_date = '" . $today . "' AND source_type = '" . $sourceType . "' AND source_name = '" . $safeSourceName . "'";
            self::$db->condition = $condition;
            self::$db->limit = "1";
            $existing = self::$db->select();

            if (!empty($existing)) {
                self::$db->table = "traffic_sources";
                self::$db->condition = "id = " . $existing[0]['id'];
                self::$db->update(array('total_visits' => $existing[0]['total_visits'] + 1));
            } else {
                self::$db->table = "traffic_sources";
                self::$db->insert(array(
                    'source_date' => $today,
                    'source_type' => $sourceType,
                    'source_name' => $safeSourceName,
                    'total_visits' => 1,
                    'unique_visitors' => 1
                ));
            }
        } catch (Exception $e) {
            error_log("Traffic source update error: " . $e->getMessage());
        }
    }

    /**
     * Increment view counter in content tables
     */
    private static function incrementContentViews($contentType, $contentId)
    {
        $tables = array(
            'article' => 'article',
            'project' => 'article'
        );

        $table = isset($tables[$contentType]) ? $tables[$contentType] : null;
        if ($table) {
            try {
                self::$db->table = $table;
                self::$db->condition = ($table == 'article' ? 'article_id' : 'id') . " = " . $contentId;
                self::$db->limit = "1";
                $row = self::$db->select();
                if (!empty($row)) {
                    $currentViews = isset($row[0]['views']) ? $row[0]['views'] : 0;
                    self::$db->table = $table;
                    self::$db->condition = ($table == 'article' ? 'article_id' : 'id') . " = " . $contentId;
                    self::$db->update(array('views' => $currentViews + 1));
                }
            } catch (Exception $e) {
                error_log("Increment views error: " . $e->getMessage());
            }
        }
    }

    /**
     * Get summary stats for admin dashboard
     */
    public static function getSummary($days = 30)
    {
        self::init();

        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        // Total views
        self::$db->table = "page_views_daily";
        self::$db->condition = "view_date >= '" . $startDate . "'";
        self::$db->limit = "";
        $rows = self::$db->select();

        $totalViews = 0;
        $uniqueViews = 0;
        $activeDays = 0;
        $datesSeen = array();

        foreach ($rows as $row) {
            $totalViews += $row['total_views'];
            $uniqueViews += $row['unique_views'];
            if (!in_array($row['view_date'], $datesSeen)) {
                $datesSeen[] = $row['view_date'];
                $activeDays++;
            }
        }

        // Today views
        self::$db->table = "page_views_daily";
        self::$db->condition = "view_date = '" . date('Y-m-d') . "'";
        self::$db->limit = "";
        $todayRows = self::$db->select();
        $todayViews = 0;
        foreach ($todayRows as $row) {
            $todayViews += $row['total_views'];
        }

        return array(
            'total_views' => $totalViews,
            'unique_views' => $uniqueViews,
            'today_views' => $todayViews,
            'avg_daily' => $activeDays > 0 ? round($totalViews / $activeDays) : 0
        );
    }

    /**
     * Get traffic overview
     */
    public static function getTrafficOverview($days = 30)
    {
        self::init();

        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        // Daily views
        self::$db->table = "page_views_daily";
        self::$db->condition = "view_date >= '" . $startDate . "'";
        self::$db->order = "view_date ASC";
        self::$db->limit = "";
        $allRows = self::$db->select();

        // Aggregate by date
        $dailyData = array();
        foreach ($allRows as $row) {
            $date = $row['view_date'];
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = array('view_date' => $date, 'views' => 0, 'unique_views' => 0);
            }
            $dailyData[$date]['views'] += $row['total_views'];
            $dailyData[$date]['unique_views'] += $row['unique_views'];
        }
        $dailyViews = array_values($dailyData);

        // By device
        $desktop = 0;
        $mobile = 0;
        $tablet = 0;
        foreach ($allRows as $row) {
            $desktop += $row['desktop_views'];
            $mobile += $row['mobile_views'];
            $tablet += $row['tablet_views'];
        }
        $byDevice = array('desktop' => $desktop, 'mobile' => $mobile, 'tablet' => $tablet);

        // Traffic sources
        self::$db->table = "traffic_sources";
        self::$db->condition = "source_date >= '" . $startDate . "'";
        self::$db->order = "";
        self::$db->limit = "";
        $sourceRows = self::$db->select();

        $sourceData = array();
        foreach ($sourceRows as $row) {
            $type = $row['source_type'];
            if (!isset($sourceData[$type])) {
                $sourceData[$type] = array('source_type' => $type, 'visits' => 0);
            }
            $sourceData[$type]['visits'] += $row['total_visits'];
        }
        // Sort by visits
        usort($sourceData, function ($a, $b) {
            return $b['visits'] - $a['visits'];
        });
        $trafficSources = array_values($sourceData);

        // Top referrers
        $referrerData = array();
        foreach ($sourceRows as $row) {
            if ($row['source_name']) {
                $key = $row['source_name'] . '_' . $row['source_type'];
                if (!isset($referrerData[$key])) {
                    $referrerData[$key] = array(
                        'source_name' => $row['source_name'],
                        'source_type' => $row['source_type'],
                        'visits' => 0
                    );
                }
                $referrerData[$key]['visits'] += $row['total_visits'];
            }
        }
        usort($referrerData, function ($a, $b) {
            return $b['visits'] - $a['visits'];
        });
        $topReferrers = array_slice(array_values($referrerData), 0, 10);

        return array(
            'daily_views' => $dailyViews,
            'by_device' => $byDevice,
            'traffic_sources' => $trafficSources,
            'top_referrers' => $topReferrers
        );
    }

    /**
     * Get top content by views
     */
    public static function getTopContent($contentType, $days = 30, $limit = 5)
    {
        self::init();

        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        self::$db->table = "page_views_daily";
        self::$db->condition = "content_type = '" . $contentType . "' AND view_date >= '" . $startDate . "' AND content_id IS NOT NULL";
        self::$db->order = "";
        self::$db->limit = "";
        $rows = self::$db->select();

        // Aggregate by content_id
        $contentData = array();
        foreach ($rows as $row) {
            $cid = $row['content_id'];
            if (!isset($contentData[$cid])) {
                $contentData[$cid] = array('content_id' => $cid, 'total_views' => 0, 'unique_views' => 0);
            }
            $contentData[$cid]['total_views'] += $row['total_views'];
            $contentData[$cid]['unique_views'] += $row['unique_views'];
        }

        // Sort by total_views
        usort($contentData, function ($a, $b) {
            return $b['total_views'] - $a['total_views'];
        });

        return array_slice(array_values($contentData), 0, $limit);
    }

    /**
     * Check if user agent is a bot
     */
    private static function isBot($userAgent)
    {
        $userAgent = strtolower($userAgent);
        $bots = array('bot', 'crawler', 'spider', 'slurp', 'googlebot', 'bingbot', 'facebookexternalhit', 'baidu', 'yandex');
        foreach ($bots as $bot) {
            if (strpos($userAgent, $bot) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detect device type
     */
    private static function detectDeviceType($userAgent)
    {
        $userAgent = strtolower($userAgent);

        if (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $userAgent)) {
            return 'mobile';
        }
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }
        if (preg_match('/windows|macintosh|linux/i', $userAgent)) {
            return 'desktop';
        }
        return 'unknown';
    }

    /**
     * Detect browser
     */
    private static function detectBrowser($userAgent)
    {
        if (preg_match('/coc coc/i', $userAgent))
            return 'Coc Coc';
        if (preg_match('/edg/i', $userAgent))
            return 'Edge';
        if (preg_match('/chrome/i', $userAgent))
            return 'Chrome';
        if (preg_match('/firefox/i', $userAgent))
            return 'Firefox';
        if (preg_match('/safari/i', $userAgent))
            return 'Safari';
        if (preg_match('/opera|opr/i', $userAgent))
            return 'Opera';
        return 'Other';
    }

    /**
     * Detect OS
     */
    private static function detectOS($userAgent)
    {
        if (preg_match('/windows nt 10/i', $userAgent))
            return 'Windows 10';
        if (preg_match('/windows/i', $userAgent))
            return 'Windows';
        if (preg_match('/macintosh|mac os x/i', $userAgent))
            return 'macOS';
        if (preg_match('/android/i', $userAgent))
            return 'Android';
        if (preg_match('/iphone|ipad|ipod/i', $userAgent))
            return 'iOS';
        if (preg_match('/linux/i', $userAgent))
            return 'Linux';
        return 'Other';
    }
    /**
     * Update legacy 'online' table for real-time tracking
     */
    private static function updateOnlineUser($url)
    {
        $sessionId = session_id();
        if (empty($sessionId))
            return;

        $time = time();
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $url = substr($url, 0, 250);

        try {
            // Delete expired sessions (15 mins)
            $timeout = $time - 900;
            self::$db->table = "online";
            self::$db->condition = "time < $timeout";
            self::$db->delete();

            // Insert or Update current session
            self::$db->table = "online";
            self::$db->condition = "session_id = '" . $sessionId . "'";
            self::$db->limit = "1";
            $existing = self::$db->select();

            if (!empty($existing)) {
                self::$db->table = "online";
                self::$db->condition = "session_id = '" . $sessionId . "'";
                self::$db->update(array(
                    'time' => $time,
                    'url' => $url,
                    'ip' => $ip
                ));
            } else {
                self::$db->table = "online";
                self::$db->insert(array(
                    'session_id' => $sessionId,
                    'time' => $time,
                    'ip' => $ip,
                    'url' => $url
                ));
            }
        } catch (Exception $e) {
            // Ignore online table errors
        }
    }
}

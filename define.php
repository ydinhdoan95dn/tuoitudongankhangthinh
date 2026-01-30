<?php
/**
 * Garden Tools - Define File
 * Path definitions and system constants
 */

if (!defined('TTH_SYSTEM')) {
    die('Direct access not allowed');
}

// =========================================================
// DIRECTORY DEFINITIONS
// =========================================================
define('ROOT_DIR', realpath(dirname(__FILE__)));
define('FILE_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('TTH_DATETIME_FORMAT', 'd/m/Y H:i:s');
define('DS', DIRECTORY_SEPARATOR);

// =========================================================
// LOAD CONFIGURATION
// =========================================================
include_once(ROOT_DIR . DS . "config.php");

// =========================================================
// SERVER CONFIGURATION
// =========================================================
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
define('HOME_URL', $protocol . $_SERVER['HTTP_HOST']);

// =========================================================
// ENVIRONMENT
// =========================================================
define('DEVELOPMENT_ENVIRONMENT', true);
define('ERROR_LOG_DIR', ROOT_DIR . DS . 'tmp' . DS . 'logs' . DS . 'error_log');

// =========================================================
// ADMIN CONFIGURATION
// =========================================================
define('ADMIN_DIR', '/gardentools-admin');
define('TTH_DATA_PREFIX', 'gt_');
define('TTH_PATH', 'ol');
define('MAX_EXECUTION_TIME', 0);

// =========================================================
// LANGUAGE CONFIGURATION
// =========================================================
$vLang = (!empty($_SESSION["language"]) && isset($_SESSION["language"])) ? $_SESSION["language"] : 'vi';
define('TTH_LANGUAGE', $vLang);
$pLang = (TTH_LANGUAGE == 'vi') ? '' : '/' . TTH_LANGUAGE;
define('PATH_LANG', $pLang);
define('HOME_URL_LANG', HOME_URL . $pLang);

// =========================================================
// SYSTEM PATH DEFINITIONS
// =========================================================
// Frontend paths
define("_F_INCLUDES", ROOT_DIR . DS . "includes");
define("_F_MODULES", ROOT_DIR . DS . "modules");
define("_F_CLASSES", _F_INCLUDES . DS . "class");
define("_F_FUNCTIONS", _F_INCLUDES . DS . "function");
define("_F_TEMPLATES", _F_MODULES . DS . "temp");
define("_F_ACTIONS", _F_MODULES . DS . "action");

// Admin paths
define("_A_INCLUDES", ROOT_DIR . DS . ADMIN_DIR . DS . "includes");
define("_A_MODULES", ROOT_DIR . DS . ADMIN_DIR . DS . "modules");
define("_A_FUNCTIONS", _A_INCLUDES . DS . "function");
define("_A_TEMPLATES", _A_MODULES . DS . "temp");
define("_A_ACTIONS", _A_MODULES . DS . "action");

// Upload paths
define("UPLOAD_DIR", ROOT_DIR . DS . "uploads");
define("UPLOAD_URL", HOME_URL . "/uploads");

// Assets paths
define("ASSETS_DIR", ROOT_DIR . DS . "assets");
define("ASSETS_URL", HOME_URL . "/assets");

// =========================================================
// HELPER FUNCTIONS
// =========================================================

/**
 * Get home URL
 */
function home_url()
{
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
    $url .= $_SERVER['HTTP_HOST'];
    return $url;
}

/**
 * Get asset URL
 */
function asset_url($path = '')
{
    return ASSETS_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Get upload URL
 */
function upload_url($path = '')
{
    return UPLOAD_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Redirect to URL
 */
function redirect($url)
{
    header("Location: " . $url);
    exit;
}

/**
 * Clean input
 */
function clean_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Format price
 */
function format_price($price)
{
    return number_format($price, 0, ',', '.') . 'đ';
}

/**
 * Create slug from string
 */
function create_slug($string)
{
    $search = array(
        '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
        '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
        '#(ì|í|ị|ỉ|ĩ)#',
        '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
        '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
        '#(ỳ|ý|ỵ|ỷ|ỹ)#',
        '#(đ)#',
        '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
        '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
        '#(Ì|Í|Ị|Ỉ|Ĩ)#',
        '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
        '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
        '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
        '#(Đ)#',
        "/[^a-zA-Z0-9\-\_]/",
    );
    $replace = array(
        'a',
        'e',
        'i',
        'o',
        'u',
        'y',
        'd',
        'A',
        'E',
        'I',
        'O',
        'U',
        'Y',
        'D',
        '-',
    );
    $string = preg_replace($search, $replace, $string);
    $string = preg_replace('/(-)+/', '-', $string);
    $string = strtolower($string);
    return trim($string, '-');
}

// =========================================================
// REGISTER GLOBALS (if file exists)
// =========================================================
if (file_exists(ROOT_DIR . DS . "register_globals.php")) {
    include_once(ROOT_DIR . DS . "register_globals.php");
    if (function_exists('register_globals')) {
        register_globals();
    }
}

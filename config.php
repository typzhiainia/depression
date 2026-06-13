<?php
// SQLite 数据库配置
define('DB_FILE', __DIR__ . '/data/depression.db');
define('DB_CHARSET', 'utf8mb4');

// 应用配置
define('APP_NAME', '心理健康测评中心');
define('APP_URL', 'http://localhost:8080');
define('SESSION_NAME', 'depression_session');

// 时区设置
date_default_timezone_set('Asia/Shanghai');

// 错误报告设置（生产环境请关闭）
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session 配置（确保兼容性）
ini_set('session.save_handler', 'files');
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);          // 开发环境为0，生产环境改为1（需HTTPS）
ini_set('session.cookie_samesite', 'Strict'); // 防 iframe 嵌套劫持
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 7200);      // 2小时会话过期

// ========== CSRF Token 系统 ==========
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// 确保 data 目录存在（用于存放数据库和session）
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_save_path(sys_get_temp_dir());
    session_start();
}

// 数据库连接类 (SQLite)
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $this->connection = new PDO('sqlite:' . DB_FILE);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::ATTR_ASSOC);

            // 启用外键约束（SQLite默认关闭）
            $this->connection->exec('PRAGMA foreign_keys = ON');
        } catch (PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function now() {
        return date('Y-m-d H:i:s');
    }

    public function getConnection() {
        return $this->connection;
    }

    private function __clone() {}
}

// 辅助函数
function db() {
    return Database::getInstance()->getConnection();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * 获取客户端真实 IP 地址
 */
function get_real_ip() {
    $ipHeaders = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ipHeaders as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

// ==================== Admin Auth ====================
define('ADMIN_SESSION_KEY', 'admin_logged_in');

function is_admin_logged_in() {
    return isset($_SESSION[ADMIN_SESSION_KEY]) && $_SESSION[ADMIN_SESSION_KEY] === true;
}

function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: admin/login.php');
        exit;
    }
}

function admin_logout() {
    unset($_SESSION[ADMIN_SESSION_KEY]);
    session_regenerate_id(true);
}

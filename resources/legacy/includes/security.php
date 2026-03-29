<?php
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters before starting
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

/**
 * Generate CSRF Token
 */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCsrfToken($token)
{
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Regenerate Session ID (Prevent Fixation)
 */
function regenerateSession()
{
    session_regenerate_id(true);
}

/**
 * Sanitize Output
 */
function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
// 3. Output Sanitization Helper
function sanitizeInput($data)
{
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Smart Redirect if User is Logged In
 */
function redirectIfLoggedIn()
{
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
        $role = $_SESSION['user_role'];

        // Dynamic base path detection
        // Assumes this file is in includes/ and project root is one level up
        // But for URL redirection, we need the web root relative path
        // simple way: define the project folder name dynamically or relatively

        $base_url = '/ifi_cms_morong/modules'; // Default fallback

        // Attempt to detect base web path if possible, but for XAMPP htdocs/ifi_cms_morong it's standard.
        // To be safer against folder renames, we can use relative redirects if the structure is known.
        // However, header Location expects absolute path or full URL usually for best practice, though relative works.
        // Let's stick to the project name but make it standard.

        if ($role === 'Admin') {
            header("Location: /ifi_cms_morong/modules/admin/dashboard.php");
            exit;
        } elseif ($role === 'Treasurer') {
            header("Location: /ifi_cms_morong/modules/treasurer/dashboard.php");
            exit;
        } elseif ($role === 'Priest') {
            header("Location: /ifi_cms_morong/modules/priest/dashboard.php");
            exit;
        }
    }
}
?>
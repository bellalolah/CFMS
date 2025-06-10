<?php
// CORS headers for React frontend
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Handle preflight request early
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// session cookie config before session start
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 1800,      // 30 minutes session lifetime
    'domain' => 'localhost', // change this if deploying
    'path' => '/',
    'secure' => false,       // set true if using https
    'httponly' => true
]);

session_start();

// Autoload classes (adjust path as needed)
require_once __DIR__ . '/vendor/autoload.php';


require_once __DIR__ . './cfms/src/Config/session.php';



// Use your Router class (adjust namespace to your CFMS project)
use CFMS\Core\Router;

$route = new Router();

// Define routes - example (adjust controllers & methods)
$route->get('/', ["CFMS\Controller\AuthController", "loginPage"]);
$route->post('/login', ["CFMS\Controller\AuthController", "loginUser"]);
$route->get('/dashboard', ["CFMS\Controller\DashboardController", "index"]);
$route->post('/logout', ["CFMS\Controller\AuthController", "logoutUser"]);
$route->get('/feedback', ["CFMS\Controller\FeedbackController", "listFeedback"]);
$route->post('/feedback/submit', ["CFMS\Controller\FeedbackController", "submitFeedback"]);

// Dispatch routes
$route->dispatch();

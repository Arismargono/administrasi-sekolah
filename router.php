<?php
// Simple router to handle requests
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove query parameters and leading slash
$uri = ltrim($uri, '/');

// If no specific file is requested or root is requested, default to index.php
if ($uri === '' || $uri === '/' || $uri === 'index.php') {
    require 'index.php';
    exit();
}

// If the requested file exists, serve it
if (file_exists($uri) && is_file($uri)) {
    // For PHP files, include them
    if (pathinfo($uri, PATHINFO_EXTENSION) === 'php') {
        require $uri;
    } else {
        // For other files, serve them directly
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon'
        ];
        
        $ext = pathinfo($uri, PATHINFO_EXTENSION);
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        
        readfile($uri);
    }
    exit();
}

// If we get here, the file doesn't exist
http_response_code(404);
echo "<h1>404 - Page Not Found</h1>";
echo "<p>The requested resource was not found on this server.</p>";
echo "<a href='/'>Go to Home Page</a>";
?>
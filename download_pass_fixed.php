<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $filePath = '/var/www/html/' . $file;
    
    // Security check - ensure file is within allowed directory and is a pkpass file
    if (strpos($file, '..') !== false || !preg_match('/^[a-zA-Z0-9_\/\-\.]+\.pkpass$/', $file)) {
        http_response_code(403);
        echo "Access denied";
        exit;
    }
    
    if (file_exists($filePath) && pathinfo($file, PATHINFO_EXTENSION) === 'pkpass') {
        header('Content-Type: application/vnd.apple.pkpass');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        readfile($filePath);
        exit;
    }
}
http_response_code(404);
echo "File not found";
?>

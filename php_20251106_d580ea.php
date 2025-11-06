<?php
/**
 * AWS Linux Web Hosting - File Upload Handler
 * Secure file upload with validation and error handling
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

class FileUploader {
    private $uploadDir;
    private $maxFileSize;
    private $allowedTypes;
    
    public function __construct() {
        $this->uploadDir = 'uploads/';
        $this->maxFileSize = 50 * 1024 * 1024; // 50MB
        $this->allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'video/mp4', 'video/mov', 'video/avi', 'video/webm', 'video/quicktime'
        ];
        
        $this->createUploadDirectory();
    }
    
    private function createUploadDirectory() {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
            file_put_contents($this->uploadDir . '.htaccess', 'Deny from all');
        }
    }
    
    private function sanitizeFileName($filename) {
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $filename);
        return time() . '_' . $filename;
    }
    
    private function validateFile($file) {
        $errors = [];
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $errors[] = "File too large: " . $file['name'] . " (max 50MB)";
        }
        
        // Check file type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            $errors[] = "Invalid file type: " . $file['name'];
        }
        
        // Check for PHP files disguised as images/videos
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar'];
        if (in_array($extension, $dangerousExtensions)) {
            $errors[] = "Dangerous file type: " . $file['name'];
        }
        
        return $errors;
    }
    
    public function uploadFiles() {
        $results = [
            'success' => [],
            'errors' => []
        ];
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $results['errors'][] = "Invalid request method";
            return $results;
        }
        
        if (!isset($_FILES['files'])) {
            $results['errors'][] = "No files uploaded";
            return $results;
        }
        
        foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['files']['error'][$key] !== UPLOAD_ERR_OK) {
                $results['errors'][] = "Upload error for: " . $_FILES['files']['name'][$key];
                continue;
            }
            
            $file = [
                'name' => $_FILES['files']['name'][$key],
                'type' => $_FILES['files']['type'][$key],
                'tmp_name' => $tmpName,
                'size' => $_FILES['files']['size'][$key]
            ];
            
            // Validate file
            $validationErrors = $this->validateFile($file);
            if (!empty($validationErrors)) {
                $results['errors'] = array_merge($results['errors'], $validationErrors);
                continue;
            }
            
            // Sanitize and move file
            $sanitizedName = $this->sanitizeFileName($file['name']);
            $destination = $this->uploadDir . $sanitizedName;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $results['success'][] = [
                    'original_name' => $file['name'],
                    'saved_name' => $sanitizedName,
                    'size' => $this->formatFileSize($file['size']),
                    'url' => $destination
                ];
                
                // Log successful upload
                $this->logUpload($file['name'], $sanitizedName, $file['size']);
            } else {
                $results['errors'][] = "Failed to move uploaded file: " . $file['name'];
            }
        }
        
        return $results;
    }
    
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function logUpload($originalName, $savedName, $size) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'original_name' => $originalName,
            'saved_name' => $savedName,
            'size' => $size,
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $logFile = 'uploads/upload_log.json';
        $logs = [];
        
        if (file_exists($logFile)) {
            $logs = json_decode(file_get_contents($logFile), true) ?: [];
        }
        
        $logs[] = $logEntry;
        file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));
    }
}

// Handle upload
$uploader = new FileUploader();
$results = $uploader->uploadFiles();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($results);
?>
<?php
/**
 * AWS Linux Web Hosting - Gallery Display
 * Dynamic media gallery with file information
 */

class MediaGallery {
    private $uploadDir;
    
    public function __construct() {
        $this->uploadDir = 'uploads/';
    }
    
    public function getMediaFiles() {
        $mediaFiles = [];
        
        if (!is_dir($this->uploadDir)) {
            return $mediaFiles;
        }
        
        $files = scandir($this->uploadDir);
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $videoExtensions = ['mp4', 'mov', 'avi', 'webm', 'mkv'];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.htaccess') {
                continue;
            }
            
            $filePath = $this->uploadDir . $file;
            $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $fileSize = filesize($filePath);
            $fileMtime = filemtime($filePath);
            
            $mediaType = 'other';
            if (in_array($fileExtension, $imageExtensions)) {
                $mediaType = 'image';
            } elseif (in_array($fileExtension, $videoExtensions)) {
                $mediaType = 'video';
            }
            
            // Extract original filename from timestamp prefixed name
            $originalName = preg_replace('/^\d+_/', '', $file);
            
            $mediaFiles[] = [
                'filename' => $file,
                'original_name' => $originalName,
                'path' => $filePath,
                'type' => $mediaType,
                'size' => $this->formatFileSize($fileSize),
                'upload_date' => date('M d, Y H:i', $fileMtime),
                'extension' => $fileExtension
            ];
        }
        
        // Sort by upload date (newest first)
        usort($mediaFiles, function($a, $b) {
            return filemtime($b['path']) - filemtime($a['path']);
        });
        
        return $mediaFiles;
    }
    
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    public function displayGallery() {
        $mediaFiles = $this->getMediaFiles();
        
        if (empty($mediaFiles)) {
            echo '
            <div class="empty-gallery">
                <div class="empty-icon">📁</div>
                <h3>No Media Files Yet</h3>
                <p>Upload your first photo or video to get started!</p>
            </div>';
            return;
        }
        
        echo '<div class="gallery-section">';
        echo '<h2>📸 Media Gallery (' . count($mediaFiles) . ' files)</h2>';
        echo '<div class="gallery-grid">';
        
        foreach ($mediaFiles as $media) {
            $this->displayMediaItem($media);
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    private function displayMediaItem($media) {
        echo '<div class="media-item" data-type="' . $media['type'] . '">';
        echo '<div class="media-preview">';
        
        if ($media['type'] === 'image') {
            echo '<img src="' . $media['path'] . '" alt="' . htmlspecialchars($media['original_name']) . '" loading="lazy">';
        } elseif ($media['type'] === 'video') {
            echo '<video controls preload="metadata">';
            echo '<source src="' . $media['path'] . '" type="video/' . $media['extension'] . '">';
            echo 'Your browser does not support the video tag.';
            echo '</video>';
        } else {
            echo '<div class="file-icon">📄</div>';
        }
        
        echo '</div>';
        echo '<div class="media-info">';
        echo '<h4 title="' . htmlspecialchars($media['original_name']) . '">' . htmlspecialchars($this->truncateFilename($media['original_name'])) . '</h4>';
        echo '<div class="file-details">';
        echo '<span class="file-size">' . $media['size'] . '</span>';
        echo '<span class="file-date">' . $media['upload_date'] . '</span>';
        echo '</div>';
        echo '<div class="media-actions">';
        echo '<button class="btn-download" onclick="downloadFile(\'' . $media['filename'] . '\')">⬇️</button>';
        echo '<button class="btn-delete" onclick="deleteFile(\'' . $media['filename'] . '\')">🗑️</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    private function truncateFilename($filename, $length = 20) {
        if (strlen($filename) <= $length) {
            return $filename;
        }
        return substr($filename, 0, $length) . '...';
    }
}

// Display the gallery
$gallery = new MediaGallery();
$gallery->displayGallery();
?>
/**
 * AWS Linux Web Hosting - JavaScript Functionality
 * Enhanced user experience with AJAX and animations
 */

class MediaManager {
    constructor() {
        this.initEventListeners();
        this.loadGallery();
    }

    initEventListeners() {
        // File input change handler
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelection(e));
        }

        // Form submission handler
        const uploadForm = document.querySelector('.upload-form');
        if (uploadForm) {
            uploadForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
    }

    handleFileSelection(event) {
        const files = event.target.files;
        const fileList = document.getElementById('fileList');
        
        if (!fileList) return;

        fileList.innerHTML = '';

        if (files.length > 0) {
            Array.from(files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <span>${file.name}</span>
                    <span>${this.formatFileSize(file.size)}</span>
                `;
                fileList.appendChild(fileItem);
            });
        }
    }

    async handleFormSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        try {
            // Show loading state
            submitBtn.innerHTML = '<span class="loading"></span> Uploading...';
            submitBtn.disabled = true;

            const response = await fetch('upload.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success && result.success.length > 0) {
                this.showNotification(`Successfully uploaded ${result.success.length} file(s)`, 'success');
                form.reset();
                document.getElementById('fileList').innerHTML = '';
                this.loadGallery();
            }

            if (result.errors && result.errors.length > 0) {
                result.errors.forEach(error => {
                    this.showNotification(error, 'error');
                });
            }

        } catch (error) {
            this.showNotification('Upload failed: ' + error.message, 'error');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    async deleteFile(filename) {
        if (!confirm(`Are you sure you want to delete "${filename}"?`)) {
            return;
        }

        try {
            const response = await fetch(`delete.php?file=${encodeURIComponent(filename)}`);
            const result = await response.json();

            if (result.success) {
                this.showNotification('File deleted successfully', 'success');
                this.loadGallery();
            } else {
                this.showNotification('Delete failed: ' + result.error, 'error');
            }
        } catch (error) {
            this.showNotification('Delete failed: ' + error.message, 'error');
        }
    }

    async downloadFile(filename) {
        try {
            const response = await fetch(`uploads/${filename}`);
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            this.showNotification('Download failed: ' + error.message, 'error');
        }
    }

    async loadGallery() {
        // In a real implementation, this would fetch gallery content via AJAX
        // For now, we'll just reload the page section
        const gallerySection = document.querySelector('.gallery-section');
        if (gallerySection) {
            // Trigger a partial page reload for the gallery
            const response = await fetch(window.location.href);
            const text = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newGallery = doc.querySelector('.gallery-section');
            if (newGallery) {
                gallerySection.innerHTML = newGallery.innerHTML;
            }
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notif => notif.remove());

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span class="notification-icon">${this.getNotificationIcon(type)}</span>
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">×</button>
        `;

        // Add styles if not already added
        if (!document.querySelector('#notification-styles')) {
            const styles = document.createElement('style');
            styles.id = 'notification-styles';
            styles.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 8px;
                    color: white;
                    z-index: 1000;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    max-width: 400px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    animation: slideIn 0.3s ease;
                }
                .notification-success { background: var(--success); }
                .notification-error { background: var(--danger); }
                .notification-warning { background: var(--warning); }
                .notification-info { background: var(--info); }
                .notification-close {
                    background: none;
                    border: none;
                    color: white;
                    font-size: 18px;
                    cursor: pointer;
                    margin-left: auto;
                }
                @keyframes slideIn {
                    from { transform: translateX(100%); }
                    to { transform: translateX(0); }
                }
            `;
            document.head.appendChild(styles);
        }

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || 'ℹ️';
    }
}

// Global functions for HTML onclick attributes
function deleteFile(filename) {
    if (window.mediaManager) {
        window.mediaManager.deleteFile(filename);
    }
}

function downloadFile(filename) {
    if (window.mediaManager) {
        window.mediaManager.downloadFile(filename);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.mediaManager = new MediaManager();
});

// Add some cool AWS-themed animations
document.addEventListener('DOMContentLoaded', () => {
    // Animate header elements
    const header = document.querySelector('header');
    if (header) {
        header.style.opacity = '0';
        header.style.transform = 'translateY(-20px)';
        
        setTimeout(() => {
            header.style.transition = 'all 0.6s ease';
            header.style.opacity = '1';
            header.style.transform = 'translateY(0)';
        }, 100);
    }

    // Add floating animation to media items
    const mediaItems = document.querySelectorAll('.media-item');
    mediaItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in-up');
    });
});

// Add CSS for animations
const animationStyles = `
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fade-in-up 0.6s ease both;
}
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = animationStyles;
document.head.appendChild(styleSheet);
# aws-linux-webhosting
Host multiple websites on AWS EC2 using Apache, PHP, and free DNS. Includes file upload + gallery system
# 🖥️ AWS Linux Web Hosting - Media Sharing Platform

A modern, secure media sharing platform designed specifically for AWS Linux environments.

## 🌟 Features

- **Secure File Uploads** with validation and sanitization
- **Media Gallery** with responsive grid layout
- **File Management** (upload, delete, download)
- **AWS Optimized** for Linux EC2 instances
- **Mobile Responsive** design
- **Real-time Notifications**
- **Activity Logging**
- **Security Headers** and protection

## 🚀 Quick Setup

### 1. Prerequisites
- AWS EC2 Linux instance (Amazon Linux 2)
- Apache Web Server
- PHP 7.4+
- Required PHP extensions: `fileinfo`, `json`

### 2. Installation

```bash
# Connect to your EC2 instance
ssh -i your-key.pem ec2-user@your-ec2-ip

# Install web server and PHP
sudo yum update -y
sudo yum install httpd php php-fileinfo -y

# Start services
sudo systemctl start httpd
sudo systemctl enable httpd

# Deploy files
sudo cp -r aws-linux-webhosting/* /var/www/html/
sudo chown -R apache:apache /var/www/html/
sudo chmod -R 755 /var/www/html/

# Create uploads directory
sudo mkdir /var/www/html/uploads
sudo chown apache:apache /var/www/html/uploads
sudo chmod 755 /var/www/html/uploads


http://myawsgo.duckdns.org/secondsite/

![Uploading A_banner_for_a_GitHub_repository_or_tutorial_title.png…]()


# aws-linux-webhosting
Host multiple websites on AWS EC2 using Apache, PHP, and free DNS. Includes file upload + gallery system
# 🖥️ AWS Linux Web Hosting - Media Sharing Platform

<img width="921" height="630" alt="2e2a8998-9823-4655-8325-a16b3e5f7acd" src="https://github.com/user-attachments/assets/d27d03ac-3093-4dc8-a24f-05d1<img width="552" height="395" alt="f3125bba-b530-4eff-8cee-f612f0b284e1" src="https://github.com/user-attachments/assets/8b5a6d2d-05d6-4094-9df4-b1bdbabeab2b" />
d8b810d8" />
<img width="550" height="148" alt="72089ed3-bf18-431f-a4fe-31c38622e77e" src="https://github.com/user-attachments/assets/a5e7ccf2-9acc-4005-bc49-3d0664e61ffd" />

<img width="552" height="395" alt="f3125bba-b530-4eff-8cee-f612f0b284e1" src="https://github.com/user-attachments/assets/4b727529-2187-43c9-8b49-e648765a2bb6" />

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
<img width="552" height="395" alt="f3125bba-b530-4eff-8cee-f612f0b284e1" src="https://github.com/user-attachments/assets/9344389c-18be-41dd-90d4-55470bf0c52e" />


# Start services
sudo systemctl start httpd
sudo systemctl enable httpd

<img width="550" height="148" alt="72089ed3-bf18-431f-a4fe-31c38622e77e" src="https://github.com/user-attachments/assets/e300aceb-b4e3-4344-ab6b-628d671e6c96" />
<img width="552" height="395" alt="f3125bba-b530-4eff-8cee-f612f0b284e1" src="https://github.com/user-attachments/assets/c85ab4ea-b435-4347-af2e-641c25b9e5e2" />


# Deploy files
sudo cp -r aws-linux-webhosting/* /var/www/html/
sudo chown -R apache:apache /var/www/html/
sudo chmod -R 755 /var/www/html/

# Create uploads directory
sudo mkdir /var/www/html/uploads
sudo chown apache:apache /var/www/html/uploads
sudo chmod 755 /var/www/html/uploads

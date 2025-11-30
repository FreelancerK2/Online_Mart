#!/bin/bash

# Apache2 Setup Script for Online Mart
# This script helps set up the project on Apache2

echo "========================================="
echo "Online Mart - Apache2 Setup Script"
echo "========================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "This script requires sudo privileges."
    echo "Please run: sudo bash setup-apache.sh"
    exit 1
fi

PROJECT_DIR="/home/somnang/Developer/Online_Mart"
APACHE_SITES="/etc/apache2/sites-available"

echo "Step 1: Enabling Apache modules..."
a2enmod rewrite
a2enmod php8.4 2>/dev/null || a2enmod php8.3 2>/dev/null || a2enmod php8.2 2>/dev/null || echo "PHP module may already be enabled"

echo ""
echo "Step 2: Copying virtual host configuration..."
cp "$PROJECT_DIR/apache2-setup.conf" "$APACHE_SITES/online-mart.conf"

echo ""
echo "Step 3: Enabling the site..."
a2ensite online-mart.conf

echo ""
echo "Step 4: Adding to hosts file..."
if ! grep -q "online-mart.local" /etc/hosts; then
    echo "127.0.0.1    online-mart.local" >> /etc/hosts
    echo "Added online-mart.local to /etc/hosts"
else
    echo "online-mart.local already exists in /etc/hosts"
fi

echo ""
echo "Step 5: Setting permissions..."
chown -R www-data:www-data "$PROJECT_DIR"
chmod -R 755 "$PROJECT_DIR"
chmod -R 775 "$PROJECT_DIR/image"

echo ""
echo "Step 6: Testing Apache configuration..."
apache2ctl configtest

echo ""
echo "Step 7: Reloading Apache..."
systemctl reload apache2

echo ""
echo "========================================="
echo "Setup Complete!"
echo "========================================="
echo ""
echo "You can now access the site at:"
echo "  http://online-mart.local"
echo ""
echo "Or if you prefer localhost:"
echo "  http://localhost/online-mart"
echo ""
echo "Make sure MySQL is running and the database is imported!"
echo ""


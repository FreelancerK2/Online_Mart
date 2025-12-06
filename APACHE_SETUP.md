# Apache2 Setup Guide for Online Mart

This guide will help you set up the Online Mart project on Apache2.

## Prerequisites

- Apache2 installed and running
- PHP 8.3+ installed
- MySQL/MariaDB running
- mod_rewrite enabled

## Setup Steps

### 1. Enable Required Apache Modules

Run the following commands (requires sudo):

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 2. Option A: Use Virtual Host (Recommended)

#### Step 2.1: Copy the virtual host configuration

```bash
sudo cp apache2-setup.conf /etc/apache2/sites-available/online-mart.conf
```

#### Step 2.2: Enable the site

```bash
sudo a2ensite online-mart.conf
sudo systemctl reload apache2
```

#### Step 2.3: Add to hosts file (for local development)

```bash
sudo echo "127.0.0.1    online-mart.local" >> /etc/hosts
```

#### Step 2.4: Access the site

Open your browser and navigate to:

```
http://online-mart.local
```

### 3. Option B: Use Default Document Root

If you prefer to use the default Apache document root:

#### Step 3.1: Create a symlink

```bash
sudo ln -s /home/somnang/Developer/Online_Mart /var/www/html/online-mart
```

#### Step 3.2: Access the site

Open your browser and navigate to:

```
http://localhost/online-mart
```

### 4. Verify Database Connection

Make sure MySQL is running and the database is set up:

```bash
# Check if MySQL is running
sudo systemctl status mysql

# Import database if needed
mysql -u root -pLensomnang@21 mini-mart < mini-mart.sql
```

### 5. Set Proper Permissions

Ensure Apache can read the files:

```bash
sudo chown -R www-data:www-data /home/somnang/Developer/Online_Mart
sudo chmod -R 755 /home/somnang/Developer/Online_Mart
```

For the image upload directories, you may need write permissions:

```bash
sudo chmod -R 775 /home/somnang/Developer/Online_Mart/image
```

## Troubleshooting

### Check Apache Error Logs

```bash
sudo tail -f /var/log/apache2/error.log
```

### Check if mod_rewrite is enabled

```bash
apache2ctl -M | grep rewrite
```

### Test Apache Configuration

```bash
sudo apache2ctl configtest
```

### Restart Apache

```bash
sudo systemctl restart apache2
```

## Database Configuration

The database configuration is in `config.php`:

- Host: `127.0.0.1:3306`
- Username: `root`
- Password: `Lensomnang@21`
- Database: `mini-mart`

Make sure these credentials match your MySQL setup.

## Security Notes

⚠️ **Important:**

- Change the database password in production
- The `.htaccess` file protects `config.php` from direct access
- Consider using environment variables for sensitive configuration

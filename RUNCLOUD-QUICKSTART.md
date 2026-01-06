# RunCloud Deployment - Quick Start

## 🚀 Quick Deployment Steps

### 1. Push to Git
```bash
git init
git add .
git commit -m "Initial commit"
git remote add origin YOUR_REPO_URL
git push -u origin main
```

### 2. RunCloud: Create Web App
- **Name**: glasgow-subway
- **Public Path**: `/public`
- **PHP**: 8.1+
- **Stack**: Native

### 3. RunCloud: Deploy Code
- Go to **Git** tab
- Setup Git deployment with your repo
- Click **Deploy Now**

### 4. RunCloud: Create Database
- **Name**: glasgow_subway
- **User**: Create with strong password
- **Note credentials!**

### 5. SSH Setup
```bash
ssh runcloud@YOUR_SERVER_IP
cd /home/runcloud/webapps/glasgow-subway

# Copy environment file
cp .env.example .env
nano .env  # Update DB credentials

# Install & setup
composer install --optimize-autoloader --no-dev
php artisan key:generate

# Create directories
mkdir -p storage/framework/{sessions,views,cache/data}
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Database
php artisan migrate --force
php artisan db:seed --class=LineStatusSeeder --force

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. RunCloud: Add Cron Job
- **Command**: `cd /home/runcloud/webapps/glasgow-subway && php artisan spt:poll`
- **Frequency**: `*/5 * * * *` (Every 5 minutes)

### 7. RunCloud: Enable SSL
- Go to **SSL/TLS** tab
- Install Let's Encrypt certificate
- Enable **Force HTTPS**

### 8. Update .env
```bash
APP_URL=https://your-domain.com
```

## ✅ Done!

Visit your domain - you should see the Glasgow Subway Status page!

---

## 📋 Production .env Template

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_DATABASE=glasgow_subway
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SPT_SOURCE=live
```

See `DEPLOYMENT.md` for full documentation.

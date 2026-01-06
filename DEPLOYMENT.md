# Deployment Guide - Glasgow Subway Status

This guide will walk you through deploying the Glasgow Subway Status application to a RunCloud server.

## Prerequisites

- RunCloud account with server connected
- Git repository (GitHub, GitLab, or Bitbucket)
- SSH access to your RunCloud server (optional but helpful)
- Domain name pointing to your server (optional)

---

## Step 1: Prepare Your Git Repository

### 1.1 Initialize Git (if not already done)

```bash
cd "c:\wamp64\www\Glasgow Subway"
git init
```

### 1.2 Check .gitignore

Ensure your `.gitignore` includes:

```
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
```

### 1.3 Initial Commit

```bash
git add .
git commit -m "Initial commit - Glasgow Subway Status"
```

### 1.4 Push to Remote Repository

```bash
# Add your remote (replace with your repo URL)
git remote add origin https://github.com/yourusername/glasgow-subway-status.git
git branch -M main
git push -u origin main
```

---

## Step 2: Create Web Application in RunCloud

### 2.1 Log into RunCloud Dashboard

1. Go to your RunCloud dashboard
2. Select your server

### 2.2 Create New Web Application

1. Click **"Web Applications"** → **"Create Web Application"**
2. Fill in the form:
   - **Name**: `glasgow-subway` (or your preferred name)
   - **Domain Name**: Your domain (e.g., `subway.yourdomain.com`) or server IP
   - **Public Path**: `/public` (Laravel's public directory)
   - **PHP Version**: Select PHP 8.1 or higher
   - **Stack**: Select **Native** (recommended for Laravel)

3. Click **"Add Web Application"**

### 2.3 Note Your Application Path

RunCloud will create your app at something like:
```
/home/runcloud/webapps/glasgow-subway
```

---

## Step 3: Deploy Code via Git

### 3.1 Option A: Using RunCloud Git Deployment (Recommended)

1. In your web application settings, go to **"Git"** tab
2. Click **"Setup Git Deployment"**
3. Fill in:
   - **Git Provider**: GitHub/GitLab/Bitbucket
   - **Repository**: Your repo URL
   - **Branch**: `main`
   - **Deploy Key**: Copy the SSH key provided and add to your Git repo's deploy keys
4. Click **"Deploy Now"**

### 3.2 Option B: Manual SSH Deployment

SSH into your server and clone:

```bash
ssh runcloud@your-server-ip
cd /home/runcloud/webapps/glasgow-subway
rm -rf * .* 2>/dev/null  # Clear default files
git clone https://github.com/yourusername/glasgow-subway-status.git .
```

---

## Step 4: Set Up Database

### 4.1 Create MySQL Database in RunCloud

1. Go to **"Database"** → **"Add Database"**
2. Create:
   - **Database Name**: `glasgow_subway` (or your choice)
   - **Database User**: Create new user with strong password
   - **Collation**: `utf8mb4_unicode_ci`

3. **Note down**: Database name, username, password

---

## Step 5: Configure Environment

### 5.1 Create .env File

SSH into your server or use RunCloud's File Manager:

```bash
cd /home/runcloud/webapps/glasgow-subway
cp .env.example .env
nano .env  # or use RunCloud File Manager
```

### 5.2 Update .env for Production

```bash
APP_NAME="Glasgow Subway Status"
APP_ENV=production
APP_KEY=  # Will generate in next step
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Database Configuration (use your RunCloud database credentials)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=glasgow_subway
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# SPT API Configuration
SPT_API_BASE=https://www.spt.co.uk/api/disruption/category/
SPT_SOURCE=live
SPT_API_TIMEOUT=10

# Staleness threshold in minutes
STALENESS_THRESHOLD=10
```

---

## Step 6: Install Dependencies & Run Migrations

### 6.1 SSH into Server

```bash
ssh runcloud@your-server-ip
cd /home/runcloud/webapps/glasgow-subway
```

### 6.2 Install Composer Dependencies

```bash
composer install --optimize-autoloader --no-dev
```

### 6.3 Generate Application Key

```bash
php artisan key:generate
```

### 6.4 Create Required Directories

```bash
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/app/fixtures
mkdir -p bootstrap/cache
```

### 6.5 Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R runcloud:runcloud storage bootstrap/cache
```

### 6.6 Run Migrations

```bash
php artisan migrate --force
```

### 6.7 Seed Initial Line Status

```bash
php artisan db:seed --class=LineStatusSeeder --force
```

### 6.8 Test Initial Poll (Optional)

```bash
php artisan spt:poll
```

---

## Step 7: Set Up Automated Polling (Cron Job)

### 7.1 Add Cron Job in RunCloud

1. In RunCloud dashboard, go to your web application
2. Click **"Cron Job"** tab
3. Click **"Add Cron Job"**
4. Fill in:
   - **Command**: 
     ```bash
     cd /home/runcloud/webapps/glasgow-subway && php artisan spt:poll >> /dev/null 2>&1
     ```
   - **Frequency**: Every 5 minutes
   - **Or use cron syntax**: `*/5 * * * *`

5. Click **"Add Cron Job"**

### 7.2 Alternative: Laravel Scheduler (Advanced)

If you want to use Laravel's scheduler:

**Add to cron:**
```bash
* * * * * cd /home/runcloud/webapps/glasgow-subway && php artisan schedule:run >> /dev/null 2>&1
```

**Update `app/Console/Kernel.php`:**
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('spt:poll')->everyFiveMinutes();
}
```

---

## Step 8: Configure SSL (HTTPS)

### 8.1 Using RunCloud SSL

1. In your web application, go to **"SSL/TLS"** tab
2. Choose:
   - **Let's Encrypt** (Free, automatic renewal) - Recommended
   - **Custom SSL** (if you have your own certificate)
3. Click **"Install SSL Certificate"**
4. Enable **"Force HTTPS"**

### 8.2 Update .env

```bash
APP_URL=https://your-domain.com
```

---

## Step 9: Performance Optimization

### 9.1 Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9.2 Enable OPcache in RunCloud

1. Go to **"Settings"** → **"PHP Settings"**
2. Ensure OPcache is enabled
3. Recommended settings:
   - `opcache.enable=1`
   - `opcache.memory_consumption=128`
   - `opcache.max_accelerated_files=10000`

---

## Step 10: Verify Deployment

### 10.1 Check Website

Visit your domain: `https://your-domain.com`

You should see:
- ✅ Three status cards (Inner Circle, Outer Circle, System)
- ✅ Recent updates section
- ✅ Auto-refresh working

### 10.2 Test API Endpoints

- `https://your-domain.com/api/status` - Should return JSON with line statuses
- `https://your-domain.com/api/updates` - Should return recent disruptions

### 10.3 Check Logs

```bash
tail -f storage/logs/laravel.log
```

Look for successful poll messages.

---

## Deployment Checklist

- [ ] Git repository created and pushed
- [ ] RunCloud web application created
- [ ] Code deployed via Git
- [ ] MySQL database created
- [ ] `.env` file configured with production settings
- [ ] Composer dependencies installed
- [ ] Application key generated
- [ ] Storage directories created with correct permissions
- [ ] Database migrations run
- [ ] Line status seeded
- [ ] Cron job configured for polling
- [ ] SSL certificate installed
- [ ] Configuration cached
- [ ] Website accessible and functional
- [ ] API endpoints working
- [ ] Logs showing successful polls

---

## Troubleshooting

### Issue: 500 Server Error

**Solution:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Ensure permissions
chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Issue: Database Connection Error

**Solution:**
- Verify database credentials in `.env`
- Check database exists in RunCloud
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

### Issue: Cron Job Not Running

**Solution:**
- Check cron job is enabled in RunCloud
- Verify path is correct
- Test manually: `php artisan spt:poll`
- Check logs for errors

### Issue: SSL Certificate Problem (cURL error 60)

**Solution:**

This happens when the server can't verify SSL certificates for the SPT API.

**Option 1: Update CA certificates (Recommended)**
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install ca-certificates
sudo update-ca-certificates
```

**Option 2: Configure PHP to use CA bundle**

Download latest CA bundle:
```bash
wget https://curl.se/ca/cacert.pem -O /home/runcloud/cacert.pem
```

Add to php.ini (via RunCloud PHP Settings):
```ini
curl.cainfo="/home/runcloud/cacert.pem"
openssl.cafile="/home/runcloud/cacert.pem"
```

**Option 3: Disable SSL verification (NOT RECOMMENDED for production)**

Only use for testing:

Edit `config/spt.php`:
```php
'verify_ssl' => env('SPT_VERIFY_SSL', true),
```

Update `.env`:
```bash
SPT_VERIFY_SSL=false
```

---

## Updating Your Application

### Deploy Updates via Git

1. Make changes locally
2. Commit and push:
   ```bash
   git add .
   git commit -m "Your update message"
   git push origin main
   ```

3. In RunCloud, click **"Deploy Now"** in Git tab

4. SSH into server and run:
   ```bash
   cd /home/runcloud/webapps/glasgow-subway
   composer install --optimize-autoloader --no-dev
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## Support

For issues specific to:
- **RunCloud**: Check RunCloud documentation or support
- **Application**: Check `storage/logs/laravel.log`
- **SPT API**: Verify API is accessible: https://www.spt.co.uk/api/disruption/category/

---

## Next Steps

- Set up monitoring (e.g., UptimeRobot) to check if site is online
- Configure email notifications for errors (Laravel Mail)
- Add analytics (optional)
- Set up automated backups in RunCloud
- Consider adding a favicon and meta tags for better SEO

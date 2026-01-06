# Quick Start Guide

## Installation Steps

1. **Install Laravel and Dependencies**
   ```bash
   cd "c:\wamp64\www\Glasgow Subway"
   composer install
   ```

2. **Configure Environment**
   ```bash
   # Copy the example environment file (if needed)
   copy .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

3. **Update Database Credentials**
   
   Edit `.env` file:
   ```
   DB_DATABASE=subway_checker
   DB_USERNAME=root
   DB_PASSWORD=your_mysql_password
   ```

4. **Run Database Migrations**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start Development Server**
   ```bash
   php artisan serve
   ```
   
   Visit: http://localhost:8000

## Testing with Fixture Data

1. **Enable Fixture Mode**
   
   In `.env`, change:
   ```
   SPT_SOURCE=fixture
   ```

2. **Run the Poller**
   ```bash
   php artisan spt:poll
   ```

3. **View Results**
   
   Open browser to http://localhost:8000
   
   You should see subway status updates from the fixture files!

## Testing with Custom Disruption

Use PowerShell or curl to inject a test disruption:

```powershell
$body = @{
    disruptionType = "subway"
    title = "Inner Circle - Test Suspension"
    snippet = "This is a test disruption for the Inner Circle. Service suspended for testing."
    publishedDate = "06/01/2026"
} | ConvertTo-Json

Invoke-WebRequest -Uri "http://localhost:8000/api/dev/inject-disruption" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body
```

Or use curl:
```bash
curl -X POST http://localhost:8000/api/dev/inject-disruption \
  -H "Content-Type: application/json" \
  -d '{
    "disruptionType": "subway",
    "title": "Inner Circle - Test Suspension",
    "snippet": "This is a test disruption. Service suspended.",
    "publishedDate": "06/01/2026"
  }'
```

## Going Live with Real API

1. **Switch to Live Mode**
   
   In `.env`:
   ```
   SPT_SOURCE=live
   ```

2. **Test the Connection**
   ```bash
   php artisan spt:poll --dry-run
   ```

3. **Run for Real**
   ```bash
   php artisan spt:poll
   ```

## Schedule Automatic Polling

**Option 1: Laravel Scheduler**

Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('spt:poll')->everyFiveMinutes();
}
```

Then run:
```bash
php artisan schedule:work
```

**Option 2: Windows Task Scheduler**

1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Every 5 minutes
4. Action: Start a program
   - Program: `C:\wamp64\bin\php\php8.x.x\php.exe`
   - Arguments: `"c:\wamp64\www\Glasgow Subway\artisan" spt:poll`
   - Start in: `c:\wamp64\www\Glasgow Subway`

## API Endpoints

- **GET** `/api/status` - Current line status
- **GET** `/api/updates?limit=20` - Recent updates
- **POST** `/api/dev/inject-disruption` - Inject test disruption (local only)

## Troubleshooting

**Issue: "SQLSTATE[HY000] [1045] Access denied"**
- Check MySQL credentials in `.env`
- Ensure MySQL service is running in WAMP

**Issue: "Class 'App\...' not found"**
```bash
composer dump-autoload
```

**Issue: No updates showing**
- Run the poller: `php artisan spt:poll`
- Check logs: `storage/logs/laravel.log`

**Issue: "Target class [Controller] does not exist"**
- Create base Controller if missing:
```bash
php artisan make:controller Controller
```

## Next Steps

- Read full `README.md` for detailed documentation
- Customize parsing keywords in `app/Services/DisruptionParser.php`
- Adjust UI styling in `resources/views/status.blade.php`
- Set up automated polling via Task Scheduler

---

Need help? Check the main README.md file for full documentation.

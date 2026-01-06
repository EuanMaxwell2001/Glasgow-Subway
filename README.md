# Glasgow Subway Status - Unofficial Service Monitor

An unofficial web application that monitors and displays the current Glasgow Subway service status by polling the SPT (Strathclyde Partnership for Transport) Disruptions API.

## ⚠️ Disclaimer

This is an **unofficial** service status tool. It is **not affiliated with or endorsed by SPT**.

- Information may be delayed or incorrect
- Always check [official SPT channels](https://www.spt.co.uk/travel-with-spt/subway/) before travel
- Provided as-is without warranties

## Features

- ✅ Real-time monitoring of Inner and Outer Circle subway lines
- ✅ Automatic polling of SPT Disruptions API
- ✅ Smart parsing to determine line status (Running, Disrupted, Suspended, Unknown)
- ✅ Recent service updates display
- ✅ Staleness warnings when data is outdated
- ✅ Fixture mode for testing without live data
- ✅ Dev injection endpoint for testing specific scenarios
- ✅ Clean Tailwind CSS interface

## Tech Stack

- **Backend**: Laravel (PHP)
- **Database**: MySQL
- **Frontend**: Tailwind CSS + Vanilla JavaScript
- **HTTP Client**: Laravel HTTP (Guzzle)

## Project Structure

```
app/
├── Console/Commands/
│   └── PollSptDisruptions.php      # Artisan command to poll API
├── Http/Controllers/
│   ├── StatusController.php        # API endpoints for status & updates
│   └── DevController.php           # Dev-only disruption injection
├── Models/
│   ├── ServiceUpdate.php           # Service updates model
│   └── LineStatus.php              # Line status model
└── Services/
    ├── SptApiClient.php            # SPT API client with fixture support
    └── DisruptionParser.php        # Text parsing & status determination

database/
├── migrations/
│   ├── *_create_service_updates_table.php
│   ├── *_create_line_status_table.php
│   └── *_create_poller_metadata_table.php
└── seeders/
    └── LineStatusSeeder.php

storage/app/fixtures/
├── spt_disruptions_page1.json      # Sample fixture data
└── spt_disruptions_page2.json

resources/views/
└── status.blade.php                # Main UI page
```

## Installation & Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- MySQL (via WAMP or similar)
- Node.js (optional, for asset compilation)

### Step 1: Install Laravel

Since you're starting fresh, you need to install Laravel first:

```bash
cd "c:\wamp64\www\Glasgow Subway"
composer create-project laravel/laravel .
```

**Note**: The `.` at the end installs Laravel in the current directory.

### Step 2: Configure Database

1. Create the database in MySQL:
   - Open MySQL Workbench
   - Database is already created: `subway_checker`

2. Update `.env` file with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=subway_checker
   DB_USERNAME=root
   DB_PASSWORD=your_password_here
   ```

3. Generate application key:
   ```bash
   php artisan key:generate
   ```

### Step 3: Run Migrations

```bash
php artisan migrate
```

This creates three tables:
- `service_updates` - Stores all disruption items (append-only)
- `line_status` - Current status snapshot for inner/outer/system
- `poller_metadata` - Tracks last successful poll time

### Step 4: Seed Initial Data

```bash
php artisan db:seed
```

This creates the three initial line status records (inner, outer, system) with status = 'unknown'.

### Step 5: Test the Application

Start the development server:

```bash
php artisan serve
```

Visit: http://localhost:8000

## Usage

### Manual Polling

Run the poller command manually:

```bash
# Live mode (fetches from real API)
php artisan spt:poll

# Dry run (no database changes)
php artisan spt:poll --dry-run
```

### Fixture Mode (Testing)

To test with fixture data instead of hitting the live API:

1. Set in `.env`:
   ```
   SPT_SOURCE=fixture
   ```

2. Run the poller:
   ```bash
   php artisan spt:poll
   ```

The fixture files in `storage/app/fixtures/` contain sample disruptions including:
- Both circles suspended
- Outer circle delays
- Inner circle restored
- System-wide reduced service
- Non-subway disruptions (bus, train)

### Dev Injection Endpoint

For local testing, you can inject custom disruptions:

```bash
POST http://localhost:8000/api/dev/inject-disruption
Content-Type: application/json

{
  "disruptionType": "subway",
  "title": "Inner Circle - Emergency Suspension",
  "snippet": "The Inner Circle has been suspended due to an emergency. No service is running.",
  "publishedDate": "06/01/2026",
  "url": "https://www.spt.co.uk/test"
}
```

**Note**: This endpoint only works when `APP_ENV=local`.

### Scheduled Polling

To run the poller automatically, add to your task scheduler:

**Windows (Task Scheduler)**:
- Create a new task to run every 5 minutes
- Action: `php.exe "c:\wamp64\www\Glasgow Subway\artisan" spt:poll`

**Linux/Mac (Cron)**:
```bash
*/5 * * * * cd /path/to/project && php artisan spt:poll >> /dev/null 2>&1
```

Or use Laravel's built-in scheduler by adding to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('spt:poll')->everyFiveMinutes();
}
```

Then run the scheduler:
```bash
php artisan schedule:work
```

## API Endpoints

### GET /api/status

Returns current line status for inner and outer circles.

**Response**:
```json
{
  "inner": {
    "status": "disrupted",
    "message": "Inner Circle experiencing delays",
    "updated_at": "2026-01-06T10:30:00+00:00"
  },
  "outer": {
    "status": "running",
    "message": "Service operating normally",
    "updated_at": "2026-01-06T10:30:00+00:00"
  },
  "meta": {
    "last_checked_at": "2026-01-06T10:35:00+00:00",
    "stale": false
  }
}
```

### GET /api/updates?limit=20

Returns recent subway-related service updates.

**Response**:
```json
{
  "updates": [
    {
      "title": "Inner Circle Service Restored",
      "snippet": "Services have resumed...",
      "published_date": "06/01/2026",
      "disruption_type": "subway",
      "url": "https://...",
      "fetched_at": "2026-01-06T10:30:00+00:00"
    }
  ],
  "count": 1
}
```

## Configuration

Edit `config/spt.php` or `.env`:

```php
// SPT API base URL
SPT_API_BASE=https://www.spt.co.uk/api/disruption/category/

// Source mode: 'live' or 'fixture'
SPT_SOURCE=live

// API timeout in seconds
SPT_API_TIMEOUT=10

// Minutes before data is considered stale
STALENESS_THRESHOLD=10
```

## How It Works

### 1. Data Collection (Poller)

The `spt:poll` command:
1. Fetches all pages from SPT API (or fixture files)
2. Generates a deterministic `source_id` hash for each item
3. Stores new items in `service_updates` table (append-only)
4. Filters for subway-related disruptions
5. Passes subway items to the parser

### 2. Parsing Logic

The `DisruptionParser` analyzes title + snippet text to determine:

**Affected Lines**:
- Contains "inner and outer", "both circles", "all services" → Both lines
- Contains "outer" → Outer circle
- Contains "inner" → Inner circle
- Otherwise → System-wide

**Status**:
- Contains "suspended", "no service", "closed" → Suspended
- Contains "resumed", "operating normally", "restored" → Running
- Contains "delays", "disruption", "reduced service" → Disrupted
- Otherwise → Unknown (no update made)

### 3. Status Updates

Only updates line status if:
- Status is NOT "unknown"
- Parser is confident about affected line(s)

Updates `line_status` table with:
- New status
- User-friendly message
- Timestamp
- Source ID reference

### 4. Frontend Display

The UI:
- Fetches `/api/status` and `/api/updates`
- Shows color-coded status badges
- Warns if data is stale (>10 minutes old)
- Auto-refreshes every 60 seconds

## Testing Strategy

### Test with Fixtures

1. Set `SPT_SOURCE=fixture` in `.env`
2. Run `php artisan spt:poll`
3. Check the UI at http://localhost:8000
4. You should see:
   - Suspended status (from "both circles suspended" fixture)
   - Various updates in the recent updates list

### Test with Injection

1. Ensure `APP_ENV=local`
2. POST to `/api/dev/inject-disruption` with test data
3. Check UI to see immediate update

### Test Staleness Warning

1. Stop polling for >10 minutes
2. Refresh the UI
3. Yellow staleness warning should appear

## Database Schema

### service_updates

| Column | Type | Description |
|--------|------|-------------|
| id | PK | Auto-increment |
| source | string | Default: 'spt_disruptions' |
| source_id | string (unique) | SHA1 hash of key fields |
| disruption_type | string | e.g., 'subway', 'bus' |
| title | text | Disruption title |
| snippet | text | Description |
| url | text | Link to details |
| published_date | date | Parsed from dd/mm/yyyy |
| fetched_at | timestamp | When we fetched it |
| raw_json | json | Original API response |

### line_status

| Column | Type | Description |
|--------|------|-------------|
| id | PK | Auto-increment |
| line | enum | 'inner', 'outer', 'system' (unique) |
| status | enum | 'running', 'suspended', 'disrupted', 'unknown' |
| message | text | User-friendly message |
| last_update_at | timestamp | When status last changed |
| last_source_id | string | Reference to service_update |

### poller_metadata

| Column | Type | Description |
|--------|------|-------------|
| id | PK | Auto-increment |
| key | string (unique) | Metadata key |
| value | text | Metadata value |

## Troubleshooting

### "Class not found" errors

Run:
```bash
composer dump-autoload
```

### Database connection failed

- Check MySQL is running (WAMP services)
- Verify credentials in `.env`
- Test connection: `php artisan migrate:status`

### No updates showing

- Run poller: `php artisan spt:poll`
- Check logs: `storage/logs/laravel.log`
- Verify API is accessible: Test in browser or Postman

### Fixture mode not working

- Check files exist: `storage/app/fixtures/spt_disruptions_page*.json`
- Verify `SPT_SOURCE=fixture` in `.env`
- Clear config cache: `php artisan config:clear`

## Development

### Adding New Keywords

Edit `app/Services/DisruptionParser.php`:

```php
private function determineStatus(string $text): string
{
    // Add new keywords to arrays
    $suspendedKeywords = [
        'suspended',
        'your_new_keyword',
        // ...
    ];
}
```

### Changing Polling Interval

For Laravel scheduler, edit `app/Console/Kernel.php`:

```php
$schedule->command('spt:poll')->everyMinute();      // 1 min
$schedule->command('spt:poll')->everyFiveMinutes(); // 5 min
$schedule->command('spt:poll')->hourly();           // 60 min
```

## License

This project is provided as-is for educational and informational purposes. No warranty or official support is provided.

## Credits

- Built for monitoring Glasgow Subway (SPT) service status
- Uses public SPT Disruptions API
- Not affiliated with SPT

---

**Last Updated**: January 2026

# Project Instructions – Unofficial Glasgow Subway Status (SPT Disruptions API)

## Goal
Build a small web app that shows the current Glasgow Subway service status:
- Inner Circle
- Outer Circle

Status must be derived from SPT disruption items returned by the SPT API.

We must:
1) poll the SPT API
2) store raw disruption items (append-only)
3) derive a current status snapshot (inner/outer/system)
4) render a simple status page + recent updates
5) support a “mock/fixture mode” so we can test subway disruptions when none exist live

---

## Data Source
SPT Disruptions API endpoint:

- Base URL:
  https://www.spt.co.uk/api/disruption/category/

- Query params:
  - category=all
  - order=descending
  - page=<int>

Example:
https://www.spt.co.uk/api/disruption/category/?category=all&order=descending&page=1

Response shape:
- results[] array of items
- pages (total pages)
- currentPage
- perPage
- totalResults

Each item contains:
- title (string)
- url (string)
- snippet (string)
- publishedDate (string in dd/mm/yyyy)
- disruptionType (string, e.g. "bus", expected "subway" for subway items)
- plus misc fields (image, linkText, linkDestination, etc.)

---

## Stack (recommended)
- Backend: Laravel (preferred) OR plain PHP CLI
- DB: PostgreSQL
- Frontend: Tailwind CSS
- Scheduler: cron (or Laravel Scheduler)
- HTTP Client: Guzzle (Laravel HTTP client is fine)

---

## Core Architecture

### A) Poller Job (scheduled)
Fetches ALL pages from the API, stores new items, and updates the derived snapshot.

### B) Parser
Determines whether an item affects:
- Inner Circle
- Outer Circle
- Both
- Unknown/system

### C) API endpoints for the UI
- GET /api/status
- GET /api/updates?limit=20

### D) UI
Single page showing:
- Inner status
- Outer status
- last updated times
- recent disruption items
- staleness warning

---

## Database Schema

### Table: service_updates (append-only)
Stores every disruption item we ingest.

Columns:
- id (pk)
- source (string) default 'spt_disruptions'
- source_id (string) unique
- disruption_type (string)                // item.disruptionType
- title (text)
- snippet (text)
- url (text)
- published_date (date, nullable)         // parsed from dd/mm/yyyy
- fetched_at (timestamp)
- raw_json (jsonb)

Deriving source_id:
- Prefer a stable ID if SPT provides one (not in sample).
- Otherwise create a deterministic hash using:
  sha1(disruptionType + '|' + title + '|' + publishedDate + '|' + url)

---

### Table: line_status (derived snapshot)
One row per line:
- inner
- outer
- system (optional, but recommended)

Columns:
- id (pk)
- line (enum: inner, outer, system) unique
- status (enum: running, suspended, disrupted, unknown)
- message (text)
- last_update_at (timestamp)
- last_source_id (string, nullable)

Seed 3 rows (inner, outer, system) during migration.

---

## Poller Job Requirements

### Inputs
- Config:
  - SPT_API_BASE=https://www.spt.co.uk/api/disruption/category/
  - category=all
  - order=descending
  - start page=1
- Pagination:
  - First call page=1
  - Use response.pages to loop pages 2..pages

### Steps
1) GET page=1
2) Read response.pages
3) Loop through all pages and collect results[]
4) For each item:
   - compute source_id hash
   - upsert into service_updates (insert only if new)
   - if inserted new: pass it to parser

### Failure Handling
- Add timeouts (e.g., 5–10s)
- On error, log and exit gracefully
- Track last successful poll time for staleness banner

### Don’t Do
- Do NOT scrape HTML
- Do NOT rely on the site’s rendered cards

---

## Filtering for Subway
We expect subway items to be identifiable via:
- item.disruptionType == 'subway' (likely)
BUT code defensively:
- treat disruptionType case-insensitively
- if disruptionType not present, fall back to keyword match on title/snippet containing 'subway'

Pipeline:
- Keep all disruptions in service_updates (optional)
- Only feed SUBWAY-related items into the status parser (recommended)

---

## Parsing Rules (Initial Version)

### Text to parse
Combine:
text = (title + "\n" + snippet).lower()

### Determine affected line(s)
- If text contains indicators of both circles:
  - 'inner and outer'
  - 'inner & outer'
  - 'both circles'
  - 'all services'
  => affected = both

- If text contains 'outer':
  => affected includes outer

- If text contains 'inner':
  => affected includes inner

If affected is empty:
- mark as system/unknown (do not overwrite inner/outer unless you choose to)

### Determine status
- If text contains:
  - 'suspended', 'no service', 'not running', 'closed'
  => status = suspended

- Else if text contains:
  - 'delays', 'disruption', 'part suspended', 'reduced service'
  => status = disrupted

- Else if text contains:
  - 'resumed', 'operating normally', 'running normally', 'restored'
  => status = running

- Else:
  => status = unknown (do not overwrite previous line status by default)

### Snapshot updates
When the parser resolves (affected lines + status):
- Update corresponding row(s) in line_status:
  - status
  - message (use a short, user-friendly message derived from title/snippet)
  - last_update_at = now() OR derived from published_date (choose now() for “when we learned it”)
  - last_source_id = source_id

Important:
- Only update a line if the parser is confident.
- Always store service_updates even if parsing fails.

---

## API Endpoints

### GET /api/status
Return:
- inner { status, message, updated_at }
- outer { status, message, updated_at }
- meta { last_checked_at, stale:boolean }

### GET /api/updates?limit=20
Return recent service_updates:
- title, snippet, published_date, disruption_type, url

---

## Frontend Requirements
Single page:
- Inner card: Running/Suspended/Disrupted/Unknown
- Outer card: Running/Suspended/Disrupted/Unknown
- “Last checked” + staleness warning if last_checked_at > 10 minutes ago
- Recent updates list below

Tailwind for layout + simple badges.

---

## Testing Strategy (CRITICAL – because there may be no live subway disruptions)

Implement 3 modes:

### Mode 1: Live mode (default)
- Poll real SPT API

### Mode 2: Fixture mode (for development + automated tests)
- Add env flag:
  SPT_SOURCE=fixture
- Instead of HTTP calls, load JSON from:
  /storage/fixtures/spt_disruptions_page1.json
  /storage/fixtures/spt_disruptions_page2.json
- Fixtures must match real API shape: { results: [...], pages, currentPage, perPage, totalResults }

Include a fixture containing at least:
- disruptionType: "subway"
- title/snippet referencing "Inner", "Outer", "Both"
- keywords for suspended/disrupted/resumed

### Mode 3: “Inject test disruption” (local only)
For local dev only (guard with APP_ENV=local):
- POST /dev/inject-disruption
- Body: { disruptionType, title, snippet, publishedDate, url }
- Server computes source_id and inserts into service_updates, then runs parser
This makes it easy to demo UI without depending on real data.

---

## Legal / Branding Requirements
Footer must include:
- “Unofficial service status tool. Not affiliated with or endorsed by SPT.”
- “Information may be delayed or incorrect; check official channels before travel.”
- “Provided as-is without warranties.”

Avoid:
- SPT logos
- naming anything “official”

---

## Deliverables
- Poller command (Laravel Artisan command or PHP CLI script)
- Migrations + seed for line_status rows
- Parser service/module
- API endpoints
- Tailwind UI page
- Fixture mode + sample fixture JSON files
- README: local setup + how to run poller + how to use fixture mode

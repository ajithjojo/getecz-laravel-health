# Getecz Laravel Health

Laravel-native app health checks (DB / cache / storage / queue / cron) with a simple dashboard and JSON endpoint.

- ✅ No build step
- ✅ Tailwind CDN UI
- ✅ Works great for shared hosting + small VPS
- ✅ Optional token + IP allowlist

---

![](https://i.postimg.cc/L8m4gcJW/Screenshot-2025-12-15-192905.png)


## Install

```bash
composer require getecz/laravel-health
```

Laravel auto-discovers the service provider.

---

## Usage

After install, open:

- Dashboard: `/<route_prefix>` (default: `/getecz-health`)
- JSON: `/<route_prefix>/json` (default: `/getecz-health/json`)
- Widget (iframe): `/<route_prefix>/widget` (default: `/getecz-health/widget`)
- Cron heartbeat: `/<route_prefix>/heartbeat` (default: `/getecz-health/heartbeat`)

---

## Security (DON'T be stupid)

In production, you **must** lock it down.

### Option A — Token (recommended)
Set an env token:

```env
GETECZ_HEALTH_TOKEN=your-long-random-token
```

Then access with:

- Query: `/getecz-health?token=your-long-random-token`
- Header: `X-Getecz-Health-Token: your-long-random-token`

### Option B — IP allowlist

```env
GETECZ_HEALTH_ALLOWED_IPS=1.2.3.4,5.6.7.8
```

### Disable completely

```env
GETECZ_HEALTH_ENABLED=false
```

---

## Publish config (optional)

```bash
php artisan vendor:publish --tag=getecz-health-config
```

Config file: `config/getecz-health.php`

---

## Cron heartbeat

The Cron check is **based on a heartbeat**. You have two ways:

### 1) Hit the heartbeat URL (shared hosting friendly)
Set a cron (or external monitor) to hit:

`/getecz-health/heartbeat?token=...`

### 2) Run the artisan heartbeat command

```bash
php artisan getecz:health-heartbeat
```

Then schedule it:

```php
// app/Console/Kernel.php
$schedule->command('getecz:health-heartbeat')->everyMinute();
```

---

## Store history (optional)

If you want snapshots in DB:

1) Publish migrations

```bash
php artisan vendor:publish --tag=getecz-health-migrations
php artisan migrate
```

2) Enable history

```env
GETECZ_HEALTH_STORE_HISTORY=true
```

3) Create snapshots via command (recommended)

```bash
php artisan getecz:health-snapshot
php artisan getecz:health-snapshot --prune
```

---

## Embed widget

You can embed the widget in any dashboard via iframe:

```html
<iframe
  src="https://your-domain.com/getecz-health/widget?token=YOUR_TOKEN"
  style="width:100%; height:170px; border:0;"
  loading="lazy"
></iframe>
```

Or fetch JSON:

`/getecz-health/json?token=YOUR_TOKEN`

---

## Custom checks

Publish config and add your own check class to the `checks` array.

A check must implement:

`Getecz\LaravelHealth\Checks\CheckInterface`

---

## License

MIT

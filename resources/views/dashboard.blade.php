<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ config('app.name') }} • Health</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-semibold">Getecz Health</h1>
        <p class="text-slate-300 mt-1">{{ $data['app']['name'] ?? 'Laravel' }} • {{ $data['app']['env'] ?? '' }} • Laravel {{ $data['app']['laravel'] ?? '' }} • PHP {{ $data['app']['php'] ?? '' }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-sm text-slate-300">Auto refresh: {{ $refreshSeconds }}s</span>
        <button id="refreshBtn" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10">Refresh</button>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      @php
        $overall = $data['overall'] ?? 'ok';
        $pill = $overall === 'ok' ? 'bg-emerald-500/15 text-emerald-200 border-emerald-500/30' : ($overall === 'warn' ? 'bg-amber-500/15 text-amber-200 border-amber-500/30' : 'bg-rose-500/15 text-rose-200 border-rose-500/30');
      @endphp
      <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
        <div class="flex items-center justify-between">
          <div class="text-slate-300 text-sm">Overall</div>
          <div class="text-xs px-2.5 py-1 rounded-full border {{ $pill }} uppercase tracking-wide">{{ $overall }}</div>
        </div>
        <div class="mt-3 text-2xl font-semibold">{{ ($data['summary']['ok'] ?? 0) }} OK</div>
        <div class="mt-1 text-sm text-slate-300">
          {{ ($data['summary']['warn'] ?? 0) }} warn • {{ ($data['summary']['fail'] ?? 0) }} fail • {{ ($data['summary']['skip'] ?? 0) }} skip
        </div>
      </div>

      <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
        <div class="text-slate-300 text-sm">Generated</div>
        <div class="mt-3 text-lg font-semibold">{{ $data['generated_at'] ?? '' }}</div>
        <div class="mt-1 text-sm text-slate-300">Server time</div>
      </div>

      <a class="rounded-2xl border border-white/10 bg-white/5 p-4 hover:bg-white/10" href="/{{ $routePrefix }}/json{{ $token ? ('?token=' . $token) : '' }}" target="_blank" rel="noreferrer">
        <div class="text-slate-300 text-sm">JSON endpoint</div>
        <div class="mt-3 text-lg font-semibold">/{{ $routePrefix }}/json</div>
        <div class="mt-1 text-sm text-slate-300">Use for widgets/alerts</div>
      </a>

      <a class="rounded-2xl border border-white/10 bg-white/5 p-4 hover:bg-white/10" href="/{{ $routePrefix }}/widget{{ $token ? ('?token=' . $token) : '' }}" target="_blank" rel="noreferrer">
        <div class="text-slate-300 text-sm">Widget</div>
        <div class="mt-3 text-lg font-semibold">/{{ $routePrefix }}/widget</div>
        <div class="mt-1 text-sm text-slate-300">Embed via iframe</div>
      </a>
    </div>

    <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 overflow-hidden">
      <div class="px-4 py-3 border-b border-white/10 flex items-center justify-between">
        <div class="font-semibold">Checks</div>
        <div class="text-sm text-slate-300">Thresholds are intentionally conservative</div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-slate-300">
            <tr>
              <th class="px-4 py-3">Check</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3">Time</th>
              <th class="px-4 py-3">Message</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/10">
            @foreach(($data['checks'] ?? []) as $key => $c)
              @php
                $s = $c['status'] ?? 'skip';
                $badge = $s === 'ok' ? 'bg-emerald-500/15 text-emerald-200 border-emerald-500/30' : ($s === 'warn' ? 'bg-amber-500/15 text-amber-200 border-amber-500/30' : ($s === 'fail' ? 'bg-rose-500/15 text-rose-200 border-rose-500/30' : 'bg-slate-500/15 text-slate-200 border-slate-500/30'));
              @endphp
              <tr>
                <td class="px-4 py-3 font-medium">{{ $c['label'] ?? $key }}</td>
                <td class="px-4 py-3">
                  <span class="text-xs px-2.5 py-1 rounded-full border {{ $badge }} uppercase tracking-wide">{{ $s }}</span>
                </td>
                <td class="px-4 py-3 text-slate-300">{{ isset($c['time_ms']) && $c['time_ms'] !== null ? ($c['time_ms'] . ' ms') : '—' }}</td>
                <td class="px-4 py-3">
                  <div>{{ $c['message'] ?? '' }}</div>
                  @if(!empty($c['meta']))
                    <details class="mt-2 text-slate-300">
                      <summary class="cursor-pointer select-none">Details</summary>
                      <pre class="mt-2 whitespace-pre-wrap text-xs bg-black/30 p-3 rounded-xl border border-white/10">{{ json_encode($c['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-6 text-xs text-slate-400">
      Tip: lock this behind a token or IP allowlist in production.
    </div>
  </div>

  <script>
    const refreshSeconds = {{ (int) $refreshSeconds }};
    const btn = document.getElementById('refreshBtn');
    btn.addEventListener('click', () => window.location.reload());
    if (refreshSeconds > 0) {
      setTimeout(() => window.location.reload(), refreshSeconds * 1000);
    }
  </script>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Health Widget</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-transparent text-slate-100">
  @php
    $overall = $data['overall'] ?? 'unknown';
    $summary = $data['summary'] ?? ['ok'=>0,'warn'=>0,'fail'=>0,'skip'=>0];
  @endphp
  <div class="rounded-2xl border border-white/10 bg-slate-950/90 p-4">
    <div class="flex items-center justify-between">
      <div class="text-sm font-semibold">{{ $data['app']['name'] ?? 'App' }} • Health</div>
      <div class="px-2 py-1 rounded-lg text-xs font-semibold
        @if($overall==='ok') bg-emerald-600/20 text-emerald-200 border border-emerald-500/30
        @elseif($overall==='warn') bg-amber-600/20 text-amber-200 border border-amber-500/30
        @elseif($overall==='fail') bg-rose-600/20 text-rose-200 border border-rose-500/30
        @else bg-slate-600/20 text-slate-200 border border-slate-500/30 @endif">
        {{ strtoupper($overall) }}
      </div>
    </div>

    <div class="mt-3 grid grid-cols-4 gap-2 text-xs">
      <div class="rounded-xl bg-white/5 border border-white/10 p-2 text-center"><div class="text-slate-400">OK</div><div class="text-lg font-bold">{{ $summary['ok'] ?? 0 }}</div></div>
      <div class="rounded-xl bg-white/5 border border-white/10 p-2 text-center"><div class="text-slate-400">WARN</div><div class="text-lg font-bold">{{ $summary['warn'] ?? 0 }}</div></div>
      <div class="rounded-xl bg-white/5 border border-white/10 p-2 text-center"><div class="text-slate-400">FAIL</div><div class="text-lg font-bold">{{ $summary['fail'] ?? 0 }}</div></div>
      <div class="rounded-xl bg-white/5 border border-white/10 p-2 text-center"><div class="text-slate-400">SKIP</div><div class="text-lg font-bold">{{ $summary['skip'] ?? 0 }}</div></div>
    </div>

    <div class="mt-3 text-[11px] text-slate-400 flex items-center justify-between">
      <div>Updated: {{ $data['generated_at'] ?? '' }}</div>
      <a class="underline" href="/{{ $routePrefix }}{{ $token ? ('?token=' . $token) : '' }}" target="_blank">Open</a>
    </div>
  </div>
</body>
</html>

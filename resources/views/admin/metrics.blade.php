<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Private KH Downloader metrics dashboard for monitoring capacity, active sessions, and supported site usage.">
    <title>Admin Metrics</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.webp') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-page="admin" class="min-h-screen bg-zinc-50 text-zinc-950 antialiased dark:bg-zinc-950 dark:text-zinc-50">
    <div id="toast" class="toast hidden" role="status" aria-live="polite"></div>

    <header class="border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600 dark:text-teal-400">Control Center</p>
                <h1 class="text-base font-semibold text-zinc-950 dark:text-white">Admin Metrics</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="/" class="text-sm font-medium text-zinc-500 transition hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-50">Downloader</a>
                <button id="adminLogoutButton" type="button" class="secondary-button hidden">Lock</button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
        <section id="adminGate" class="mx-auto max-w-md rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-lg font-semibold text-zinc-950 dark:text-white">Protected Metrics</h2>
            <form id="adminTokenForm" class="mt-5 space-y-4">
                <label for="adminTokenInput" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Admin token</label>
                <input id="adminTokenInput" type="password" autocomplete="current-password" class="field-input" placeholder="ADMIN_API_TOKEN">
                <button type="submit" class="primary-button w-full justify-center">Open Dashboard</button>
            </form>
        </section>

        <section id="adminDashboard" class="hidden space-y-8">
            <div class="grid gap-4 md:grid-cols-3">
                <article class="metric-card">
                    <div id="loadGauge" class="gauge" style="--value: 0">
                        <div>
                            <strong id="loadGaugeValue">0 / 5</strong>
                            <span>Current Load</span>
                        </div>
                    </div>
                </article>

                <article class="metric-card">
                    <div id="successGauge" class="gauge gauge-green" style="--value: 0">
                        <div>
                            <strong id="successGaugeValue">0</strong>
                            <span>Global Success</span>
                        </div>
                    </div>
                </article>

                <article class="metric-card">
                    <div id="sessionsGauge" class="gauge gauge-amber" style="--value: 0">
                        <div>
                            <strong id="sessionsGaugeValue">0</strong>
                            <span>Active Sessions</span>
                        </div>
                    </div>
                </article>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_420px]">
                <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-base font-semibold text-zinc-950 dark:text-white">Usage By Site</h2>
                        <span id="metricsUpdatedAt" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Waiting</span>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[520px] text-left text-sm">
                            <thead class="text-xs uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">
                                <tr>
                                    <th class="py-3 font-semibold">Site</th>
                                    <th class="py-3 font-semibold">Total Requests</th>
                                    <th class="py-3 font-semibold">Last Activity</th>
                                </tr>
                            </thead>
                            <tbody id="siteUsageRows" class="divide-y divide-zinc-100 dark:divide-zinc-800"></tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 class="text-base font-semibold text-zinc-950 dark:text-white">Live Activity</h2>
                    <div id="activityFeed" class="mt-5 max-h-[460px] space-y-3 overflow-y-auto pr-1"></div>
                </section>
            </div>
        </section>
    </main>
</body>
</html>

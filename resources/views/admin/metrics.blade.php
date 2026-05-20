@extends('layouts.app')

@section('title', 'Admin Dashboard - KH Downloader')

@section('page_name', 'admin')
@section('page_body_class', 'bg-zinc-950 text-zinc-400')

@section('content')
<style>
    /* Industrial Dashboard Specific Styles */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .industrial-panel {
        background: linear-gradient(145deg, #18181b 0%, #09090b 100%);
        border: 1px solid rgba(39, 39, 42, 0.8);
        box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.05);
        position: relative;
        overflow: hidden;
    }

    .industrial-panel::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #27272a, transparent);
    }

    .data-label {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        font-size: 0.65rem;
        font-weight: 700;
        color: #71717a;
    }

    .data-value {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-variant-numeric: tabular-nums;
    }

    .scanline {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.02), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.02));
        background-size: 100% 4px, 3px 100%;
        pointer-events: none;
        z-index: 10;
        opacity: 0.2;
    }

    .led-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
        box-shadow: 0 0 8px currentColor;
    }

    /* Custom scrollbar for activity feed */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #09090b;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #27272a;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #3f3f46;
    }

    /* Radial Gauge Overrides */
    .gauge-container {
        position: relative;
        width: 160px;
        height: 160px;
    }
    .gauge-svg {
        transform: rotate(-90deg);
    }
    .gauge-bg {
        fill: none;
        stroke: #27272a;
        stroke-width: 8;
    }
    .gauge-fill {
        fill: none;
        stroke: #14b8a6;
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s ease-out;
    }
</style>

<div id="toast" class="toast hidden" role="status" aria-live="polite"></div>

<header class="sticky top-0 z-50 border-b border-zinc-800 bg-zinc-950/80 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6">
        <div class="flex items-center gap-4">
            <div class="flex h-8 w-8 items-center justify-center rounded border border-zinc-700 bg-zinc-900 shadow-inner">
                <div class="h-2 w-2 animate-pulse rounded-full bg-teal-500 shadow-[0_0_8px_rgba(20,184,166,0.8)]"></div>
            </div>
            <div>
                <span class="data-label text-zinc-500">System Monitoring</span>
                <h1 class="font-mono text-lg font-bold tracking-tight text-white">ADMIN_V1.0</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="/" class="data-label text-zinc-400 hover:text-white transition-colors">TERMINAL_EXIT</a>
            <button id="adminLogoutButton" type="button" class="hidden border border-zinc-700 bg-zinc-900 px-3 py-1 font-mono text-[10px] font-bold uppercase tracking-widest text-zinc-400 transition hover:border-red-500/50 hover:text-red-400">Lock_Access</button>
        </div>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    {{-- Auth Gate --}}
    <section id="adminGate" class="mx-auto max-w-md animate-fade-in-up">
        <div class="industrial-panel rounded-xl p-8">
            <div class="scanline"></div>
            <div class="mb-8 text-center">
                <span class="data-label text-teal-500">Security Check</span>
                <h2 class="mt-2 font-mono text-2xl font-bold text-white">Access Denied</h2>
                <p class="mt-2 text-sm text-zinc-500">Encrypted dashboard access requires level-1 token.</p>
            </div>
            <form id="adminTokenForm" class="space-y-6">
                <div class="space-y-2">
                    <label for="adminTokenInput" class="data-label">Security_Key</label>
                    <div class="relative group">
                        <input id="adminTokenInput" type="password" autocomplete="current-password" 
                            class="w-full border border-zinc-800 bg-zinc-950/50 pl-4 pr-12 py-3 font-mono text-white outline-none focus:border-teal-500/50 focus:ring-1 focus:ring-teal-500/20" 
                            placeholder="••••••••••••••••">
                        <button id="togglePasswordVisibility" type="button" class="absolute right-0 top-0 h-full w-12 flex items-center justify-center text-zinc-500 hover:text-white transition-colors border-l border-zinc-800/50 bg-zinc-900/30" aria-label="Toggle password visibility">
                            <svg id="eyeIconOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eyeIconClosed" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full bg-teal-600 py-4 font-mono text-sm font-bold uppercase tracking-[0.2em] text-white transition hover:bg-teal-500 active:scale-[0.98]">
                    Authorize_Session
                </button>
            </form>
        </div>
    </section>

    {{-- Dashboard Content --}}
    <section id="adminDashboard" class="hidden space-y-8">
        {{-- High Level Stats --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Load Gauge Card --}}
            <article class="industrial-panel rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <span class="data-label">Server_Load</span>
                    <span id="loadGaugeValue" class="data-value text-xl font-bold text-white">0 / 5</span>
                </div>
                <div class="mt-6 flex justify-center">
                    <div id="loadGauge" class="gauge-container">
                        <svg class="gauge-svg h-full w-full" viewBox="0 0 100 100">
                            <circle class="gauge-bg" cx="50" cy="50" r="42" />
                            <circle id="loadGaugeCircle" class="gauge-fill" cx="50" cy="50" r="42" stroke-dasharray="264" stroke-dashoffset="264" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span id="loadPercent" class="font-mono text-3xl font-black text-white">0%</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <span class="led-indicator text-emerald-500 bg-emerald-500"></span>
                    <span class="data-label text-[10px]">Active_Stream_Units</span>
                </div>
            </article>

            {{-- Success Rate Card --}}
            <article class="industrial-panel rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <span class="data-label">Cumulative_Success</span>
                    <span id="successGaugeValue" class="data-value text-xl font-bold text-teal-400">0</span>
                </div>
                <div class="mt-8 flex h-32 items-end justify-between gap-1">
                    @for ($i = 0; $i < 12; $i++)
                        <div class="w-full rounded-t-sm bg-zinc-800 transition-all duration-500" style="height: {{ rand(20, 90) }}%"></div>
                    @endfor
                </div>
                <div class="mt-6 flex items-center justify-center gap-2">
                    <span class="led-indicator text-teal-500 bg-teal-500"></span>
                    <span class="data-label text-[10px]">Total_Processed_Requests</span>
                </div>
            </article>

            {{-- Sessions Card --}}
            <article class="industrial-panel rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <span class="data-label">Active_Threads</span>
                    <span id="sessionsGaugeValue" class="data-value text-xl font-bold text-white">0</span>
                </div>
                <div class="mt-8 flex items-center justify-center">
                    <div class="relative flex h-24 w-24 animate-spin-slow items-center justify-center">
                        <div class="absolute h-full w-full rounded-full border-2 border-dashed border-zinc-700"></div>
                        <div class="h-16 w-16 rounded-full border-2 border-teal-500/20 bg-teal-500/5 blur-sm"></div>
                        <div class="absolute h-2 w-2 rounded-full bg-teal-500" style="top: -1px; left: 50%; transform: translateX(-50%)"></div>
                    </div>
                </div>
                <div class="mt-10 flex items-center justify-center gap-2">
                    <span class="led-indicator text-amber-500 bg-amber-500"></span>
                    <span class="data-label text-[10px]">Concurrent_Users</span>
                </div>
            </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_400px]">
            {{-- Site Usage Table --}}
            <section class="industrial-panel flex flex-col rounded-xl overflow-hidden">
                <div class="border-b border-zinc-800 bg-zinc-900/50 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="data-label">Site_Distribution</span>
                            <h3 class="mt-1 font-mono text-sm font-bold text-white">Traffic Analysis</h3>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="data-label">Refreshed</span>
                            <span id="metricsUpdatedAt" class="data-value text-xs text-zinc-500">W_SYNC...</span>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left font-mono text-xs">
                        <thead>
                            <tr class="border-b border-zinc-800 text-zinc-500 uppercase">
                                <th class="p-5 font-bold tracking-widest pl-6">Source_Identifier</th>
                                <th class="p-5 font-bold tracking-widest">Op_Count</th>
                                <th class="p-5 font-bold tracking-widest">Last_Sync</th>
                            </tr>
                        </thead>
                        <tbody id="siteUsageRows" class="divide-y divide-zinc-800/50">
                            {{-- Rows injected by JS --}}
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Live Logs --}}
            <section class="industrial-panel flex flex-col rounded-xl h-[500px]">
                <div class="border-b border-zinc-800 bg-zinc-900/50 p-5">
                    <div class="flex items-center gap-3">
                        <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></div>
                        <div>
                            <span class="data-label">Telemetry_Stream</span>
                            <h3 class="mt-1 font-mono text-sm font-bold text-white">Live Logs</h3>
                        </div>
                    </div>
                </div>
                <div id="activityFeed" class="flex-1 space-y-2 overflow-y-auto p-4 custom-scrollbar">
                    {{-- Feed injected by JS --}}
                </div>
                <div class="border-t border-zinc-800 bg-zinc-950 p-3 text-center">
                    <span class="data-label text-[8px]">Secure_Log_Protocol_Active</span>
                </div>
            </section>
        </div>
    </section>
</main>

<script>
    // Gauge Logic
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.target.id === 'loadGaugeValue') {
                    updateGauge();
                }
            });
        });

        const target = document.getElementById('loadGaugeValue');
        if (target) {
            observer.observe(target, { characterData: true, childList: true, subtree: true });
        }

        function updateGauge() {
            const text = document.getElementById('loadGaugeValue').textContent;
            const match = text.match(/(\d+)\s*\/\s*(\d+)/);
            if (match) {
                const current = parseInt(match[1]);
                const max = parseInt(match[2]);
                const percent = Math.min((current / max) * 100, 100);
                
                const circle = document.getElementById('loadGaugeCircle');
                const percentText = document.getElementById('loadPercent');
                
                if (circle) {
                    const circumference = 2 * Math.PI * 42;
                    const offset = circumference - (percent / 100) * circumference;
                    circle.style.strokeDashoffset = offset;
                }
                
                if (percentText) {
                    percentText.textContent = Math.round(percent) + '%';
                }
            }
        }
    });
</script>
@endsection

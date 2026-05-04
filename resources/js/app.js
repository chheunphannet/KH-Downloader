const statusPollMs = 5000;

const siteStyles = {
    khfullhd: { label: 'KHFullHD', className: 'site-khfullhd' },
    khdiamond: { label: 'KHDiamond', className: 'site-khdiamond' },
    khanime: { label: 'KHAnime', className: 'site-khanime' },
};

const $ = (selector, scope = document) => scope.querySelector(selector);

document.addEventListener('DOMContentLoaded', () => {
    const page = document.body.dataset.page;

    if (page === 'home') {
        initDownloader();
    }

    if (page === 'admin') {
        initAdminDashboard();
    }
});

function initDownloader() {
    const form = $('#analyzeForm');
    const input = $('#urlInput');
    const searchShell = $('.search-shell');
    const button = $('#processButton');
    const resultCard = $('#resultCard');
    const capacityBanner = $('#capacityBanner');
    const watchModal = $('#watchModal');
    const watchFrame = $('#watchFrame');
    const watchTitle = $('#watchTitle');

    const state = {
        result: null,
        downloadStates: {},
        waitTimers: {},
        serverStatus: null,
    };

    refreshServerStatus(state);
    window.setInterval(() => refreshServerStatus(state), statusPollMs);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const url = input.value.trim();

        if (!url) {
            return;
        }

        setProcessing(true, searchShell, button);
        hide(capacityBanner);
        hide(resultCard);
        resultCard.innerHTML = '';

        try {
            const response = await fetch('/api/analyze', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ url, type: 'movie' }),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.error || payload.message || 'Unsupported link or site offline.');
            }

            state.result = payload;
            state.downloadStates = {};
            clearWaitTimers(state);
            renderResultCard(resultCard, payload, state);
            show(resultCard);
        } catch (error) {
            showToast(error.message || 'Unsupported link or site offline.', 'error');
        } finally {
            setProcessing(false, searchShell, button);
        }
    });

    resultCard.addEventListener('click', (event) => {
        const watchButton = event.target.closest('[data-watch-url]');
        const downloadControl = event.target.closest('[data-download-quality]');
        const subtitleButton = event.target.closest('[data-subtitle-url]');

        if (watchButton) {
            watchTitle.textContent = state.result?.title || 'Watch Online';
            watchFrame.src = watchButton.dataset.watchUrl;
            show(watchModal);
            return;
        }

        if (downloadControl) {
            handleDownloadClick(event, downloadControl, state, capacityBanner, resultCard);
            return;
        }

        if (subtitleButton) {
            window.open(subtitleButton.dataset.subtitleUrl, '_blank', 'noopener');
        }
    });

    document.querySelectorAll('[data-close-watch]').forEach((control) => {
        control.addEventListener('click', () => {
            watchFrame.src = '';
            hide(watchModal);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !watchModal.classList.contains('hidden')) {
            watchFrame.src = '';
            hide(watchModal);
        }
    });
}

async function refreshServerStatus(state = {}) {
    try {
        const status = await fetchServerStatus();
        state.serverStatus = status;
        updateServerStatusPill(status);
        return status;
    } catch {
        updateServerStatusPill(null);
        return null;
    }
}

async function fetchServerStatus() {
    const response = await fetch('/api/server-status', {
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error('Server status unavailable.');
    }

    return response.json();
}

function updateServerStatusPill(status) {
    const pill = $('#serverStatusPill');
    const dot = $('#serverStatusPill .status-dot');
    const statusText = $('#serverStatusText');
    const loadText = $('#serverLoadText');

    if (!pill || !dot || !statusText || !loadText) {
        return;
    }

    dot.className = 'status-dot';

    if (!status) {
        dot.classList.add('bg-red-500');
        statusText.textContent = 'Offline';
        loadText.textContent = 'Unknown';
        return;
    }

    dot.classList.add(status.available ? 'bg-emerald-500' : 'bg-amber-500');
    statusText.textContent = status.available ? 'Online' : 'Full';
    loadText.textContent = `${status.current ?? 0} / ${status.max ?? 5} Slots`;
}

function setProcessing(isProcessing, searchShell, button) {
    const label = $('.button-label', button);
    const spinner = $('.button-spinner', button);

    button.disabled = isProcessing;
    searchShell.classList.toggle('is-processing', isProcessing);
    label.textContent = isProcessing ? 'Processing' : 'Process Link';
    spinner.classList.toggle('hidden', !isProcessing);
}

function renderResultCard(container, result, state) {
    const site = siteStyles[result.site] || {
        label: result.site || 'Unknown',
        className: 'site-default',
    };
    const links = Object.entries(result.links || {}).sort(([a], [b]) => Number(b) - Number(a));
    const subtitles = Array.isArray(result.subtitles) ? result.subtitles : [];

    container.innerHTML = `
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="site-badge ${site.className}">${escapeHtml(site.label)}</span>
                    ${result.can_watch ? '<span class="site-badge site-default">Watch Ready</span>' : ''}
                </div>
                <h2 class="break-words text-2xl font-bold tracking-normal text-zinc-950 dark:text-white">${escapeHtml(result.title || 'Untitled Video')}</h2>
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <div class="option-panel">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Watch</h3>
                </div>
                ${renderWatchSection(result)}
            </div>

            <div class="option-panel">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">Download</h3>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    ${links.length ? links.map(([quality, link]) => renderDownloadButton(quality, link, state.downloadStates[quality])).join('') : renderEmptyState('No download links found.')}
                </div>
            </div>
        </div>

        ${renderSubtitleDrawer(subtitles)}
    `;
}

function renderWatchSection(result) {
    if (!result.can_watch || !result.embed_url) {
        return renderEmptyState('Online playback is not available.');
    }

    return `
        <button type="button" class="primary-button w-full justify-center rounded-lg" data-watch-url="${escapeAttr(result.embed_url)}">
            Watch Online
        </button>
    `;
}

function renderDownloadButton(quality, link, state) {
    const label = downloadButtonLabel(quality, link, state);
    const isWaiting = state?.status === 'waiting';
    const spinner = isWaiting ? '<span class="button-spinner" aria-hidden="true"></span>' : '';

    if (isWaiting) {
        return `
        <button type="button" class="download-button" disabled data-download-quality="${escapeAttr(quality)}">
            ${spinner}
            <span>${escapeHtml(label)}</span>
        </button>
        `;
    }

    return `
        <a class="download-button" href="${escapeAttr(link.url)}" download data-download-quality="${escapeAttr(quality)}">
            <span>${escapeHtml(label)}</span>
        </a>
    `;
}

function downloadButtonLabel(quality, link, state) {
    if (state?.status === 'waiting') {
        return `Waiting for slot... (Pos: ${state.position})`;
    }

    if (state?.status === 'ready') {
        return `Download ${quality}p now`;
    }

    const size = link.size ? ` (${link.size})` : '';
    return `Download ${quality}p${size}`;
}

function renderSubtitleDrawer(subtitles) {
    if (!subtitles.length) {
        return '';
    }

    return `
        <div class="mt-5 border-t border-zinc-200 pt-5 dark:border-zinc-800">
            <div class="flex flex-wrap gap-2">
                ${subtitles.map((subtitle) => `
                    <button type="button" class="subtitle-chip" data-subtitle-url="${escapeAttr(subtitle.file)}">
                        <span class="rounded bg-zinc-900 px-1.5 py-0.5 text-[10px] font-bold text-white dark:bg-white dark:text-zinc-950">CC</span>
                        <span>${escapeHtml(subtitle.label || 'Subtitle')}</span>
                    </button>
                `).join('')}
            </div>
        </div>
    `;
}

function renderEmptyState(message) {
    return `<p class="rounded-lg border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">${escapeHtml(message)}</p>`;
}

function handleDownloadClick(event, control, state, capacityBanner, resultCard) {
    const quality = control.dataset.downloadQuality;
    const status = state.serverStatus;

    if (status && !status.available) {
        event.preventDefault();
        show(capacityBanner);
        queueDownload(quality, state, capacityBanner, resultCard, status);
        return;
    }

    hide(capacityBanner);
    delete state.downloadStates[quality];

    window.setTimeout(() => refreshServerStatus(state), 1000);
}

function queueDownload(quality, state, capacityBanner, resultCard, initialStatus) {
    const position = Math.max(Number(initialStatus?.wait_list || 1), 1);
    state.downloadStates[quality] = { status: 'waiting', position };
    updateDownloadButtons(resultCard, state);

    if (state.waitTimers[quality]) {
        window.clearInterval(state.waitTimers[quality]);
    }

    state.waitTimers[quality] = window.setInterval(async () => {
        const status = await refreshServerStatus(state);
        const nextPosition = Math.max(Number(status?.wait_list || 1), 1);

        if (status?.available) {
            window.clearInterval(state.waitTimers[quality]);
            delete state.waitTimers[quality];
            hide(capacityBanner);
            state.downloadStates[quality] = { status: 'ready' };
            updateDownloadButtons(resultCard, state);
            showToast('Download slot ready. Click the download button again.', 'info');
            return;
        }

        state.downloadStates[quality] = { status: 'waiting', position: nextPosition };
        updateDownloadButtons(resultCard, state);
    }, statusPollMs);
}

function updateDownloadButtons(resultCard, state) {
    if (state.result) {
        renderResultCard(resultCard, state.result, state);
    }
}

function clearWaitTimers(state) {
    Object.values(state.waitTimers).forEach((timer) => window.clearInterval(timer));
    state.waitTimers = {};
}

function initAdminDashboard() {
    const gate = $('#adminGate');
    const dashboard = $('#adminDashboard');
    const form = $('#adminTokenForm');
    const input = $('#adminTokenInput');
    const logout = $('#adminLogoutButton');

    const state = {
        token: window.sessionStorage.getItem('adminMetricsToken') || '',
        refreshTimer: null,
    };

    if (state.token) {
        showDashboard(gate, dashboard, logout);
        loadMetrics(state, gate, dashboard, logout);
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        state.token = input.value.trim();

        if (!state.token) {
            showToast('Admin token is required.', 'error');
            return;
        }

        window.sessionStorage.setItem('adminMetricsToken', state.token);
        showDashboard(gate, dashboard, logout);
        loadMetrics(state, gate, dashboard, logout);
    });

    logout.addEventListener('click', () => {
        window.sessionStorage.removeItem('adminMetricsToken');
        state.token = '';
        window.clearInterval(state.refreshTimer);
        hide(dashboard);
        hide(logout);
        show(gate);
        input.value = '';
    });
}

function showDashboard(gate, dashboard, logout) {
    hide(gate);
    show(dashboard);
    show(logout);
}

async function loadMetrics(state, gate, dashboard, logout) {
    window.clearInterval(state.refreshTimer);
    await fetchAndRenderMetrics(state, gate, dashboard, logout);

    state.refreshTimer = window.setInterval(() => {
        fetchAndRenderMetrics(state, gate, dashboard, logout);
    }, statusPollMs);
}

async function fetchAndRenderMetrics(state, gate, dashboard, logout) {
    try {
        const response = await fetch('/api/metrics', {
            headers: {
                Accept: 'application/json',
                Authorization: `Bearer ${state.token}`,
            },
        });

        const payload = await response.json().catch(() => ({}));

        if (response.status === 403 || response.status === 401) {
            window.sessionStorage.removeItem('adminMetricsToken');
            state.token = '';
            hide(dashboard);
            hide(logout);
            show(gate);
            showToast(payload.message || 'Invalid admin API token.', 'error');
            return;
        }

        if (!response.ok) {
            throw new Error(payload.message || 'Metrics unavailable.');
        }

        renderMetrics(payload);
    } catch (error) {
        showToast(error.message || 'Metrics unavailable.', 'error');
    }
}

function renderMetrics(metrics) {
    const load = parseLoad(metrics.summary?.current_server_load);
    const total = Number(metrics.summary?.grand_total_processed || 0);

    setGauge($('#loadGauge'), Math.min((load.current / load.max) * 100, 100));
    setGauge($('#successGauge'), Math.min(total, 100));
    setGauge($('#sessionsGauge'), Math.min((load.current / load.max) * 100, 100));

    $('#loadGaugeValue').textContent = `${load.current} / ${load.max}`;
    $('#successGaugeValue').textContent = total.toLocaleString();
    $('#sessionsGaugeValue').textContent = load.current.toLocaleString();
    $('#metricsUpdatedAt').textContent = `Updated ${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`;

    renderSiteRows(metrics.by_site || []);
    renderActivityFeed(metrics.recent_activity || []);
}

function parseLoad(value) {
    const match = String(value || '').match(/(\d+)\s*\/\s*(\d+)/);

    return {
        current: match ? Number(match[1]) : 0,
        max: match ? Number(match[2]) : 5,
    };
}

function setGauge(element, value) {
    if (element) {
        element.style.setProperty('--value', Number.isFinite(value) ? value : 0);
    }
}

function renderSiteRows(rows) {
    const body = $('#siteUsageRows');

    if (!body) {
        return;
    }

    if (!rows.length) {
        body.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-zinc-500 dark:text-zinc-400">No site activity yet.</td></tr>';
        return;
    }

    body.innerHTML = rows.map((row) => `
        <tr>
            <td class="py-4">
                <span class="site-badge ${(siteStyles[row.site_name] || { className: 'site-default' }).className}">${escapeHtml((siteStyles[row.site_name] || {}).label || row.site_name || 'Unknown')}</span>
            </td>
            <td class="py-4 font-semibold text-zinc-900 dark:text-zinc-100">${Number(row.total_requests || 0).toLocaleString()}</td>
            <td class="py-4 text-zinc-500 dark:text-zinc-400">${formatRelativeTime(row.updated_at)}</td>
        </tr>
    `).join('');
}

function renderActivityFeed(rows) {
    const feed = $('#activityFeed');

    if (!feed) {
        return;
    }

    if (!rows.length) {
        feed.innerHTML = '<p class="rounded-lg border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">No recent logs.</p>';
        return;
    }

    feed.innerHTML = rows.map((row) => {
        const time = formatClock(row.created_at);
        const site = escapeHtml((siteStyles[row.site] || {}).label || row.site || 'Unknown');
        const ip = escapeHtml(row.ip_address || 'Unknown IP');

        return `
            <article class="activity-item" title="${escapeAttr(row.page_url || '')}">
                <p><span class="font-mono text-xs text-zinc-500 dark:text-zinc-400">[${time}]</span> <span class="font-semibold">${ip}</span> processed a <span class="font-semibold">${site}</span> link.</p>
            </article>
        `;
    }).join('');
}

function formatRelativeTime(value) {
    const date = value ? new Date(value) : null;

    if (!date || Number.isNaN(date.getTime())) {
        return 'Unknown';
    }

    const seconds = Math.max(Math.round((Date.now() - date.getTime()) / 1000), 0);

    if (seconds < 60) {
        return 'Just now';
    }

    const minutes = Math.round(seconds / 60);

    if (minutes < 60) {
        return `${minutes} min ago`;
    }

    const hours = Math.round(minutes / 60);

    if (hours < 24) {
        return `${hours} hr ago`;
    }

    const days = Math.round(hours / 24);
    return `${days} day${days === 1 ? '' : 's'} ago`;
}

function formatClock(value) {
    const date = value ? new Date(value) : new Date();

    return date.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });
}

function showToast(message, type = 'info') {
    const toast = $('#toast');

    if (!toast) {
        return;
    }

    toast.textContent = message;
    toast.className = `toast toast-${type}`;
    show(toast);

    window.clearTimeout(showToast.timer);
    showToast.timer = window.setTimeout(() => hide(toast), 4200);
}

function show(element) {
    element?.classList.remove('hidden');
}

function hide(element) {
    element?.classList.add('hidden');
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function escapeAttr(value) {
    return escapeHtml(value).replaceAll('`', '&#096;');
}

/**
 * KH Proxy — Cloudflare Worker
 *
 * Fetches any URL on behalf of your Laravel server.
 * Because this Worker runs inside Cloudflare's own network, it is trusted
 * by Cloudflare bot protection (BotFight Mode / Turnstile) on CF-protected
 * sites — bypassing the 403 block that your VPS datacenter IP receives.
 *
 * DEPLOY INSTRUCTIONS:
 * 1. Go to https://dash.cloudflare.com → Workers & Pages → Create Worker
 * 2. Paste this entire file into the editor and click "Deploy"
 * 3. Go to the Worker → Settings → Variables → Add variable:
 *      Name:  SECRET_TOKEN
 *      Value: (any long random string, e.g. openssl rand -hex 32)
 * 4. Copy your Worker URL (e.g. https://kh-proxy.yourname.workers.dev)
 * 5. Add to your VPS .env:
 *      CF_WORKER_ENABLED=true
 *      CF_WORKER_URL=https://kh-proxy.yourname.workers.dev
 *      CF_WORKER_TOKEN=<your SECRET_TOKEN value>
 *
 * HOW IT WORKS:
 * Laravel calls → GET https://kh-proxy.yourname.workers.dev?url=TARGET&token=SECRET
 * Worker fetches TARGET from inside Cloudflare's network (trusted, not blocked)
 * Worker returns the raw HTML back to Laravel
 */

export default {
  async fetch(request, env) {
    // ── Security: validate secret token ───────────────────────────────────
    const url   = new URL(request.url);
    const token = url.searchParams.get('token') ?? '';

    if (!env.SECRET_TOKEN || token !== env.SECRET_TOKEN) {
      return new Response('Unauthorized', { status: 401 });
    }

    // ── Validate target URL ────────────────────────────────────────────────
    const targetUrl = url.searchParams.get('url') ?? '';

    if (!targetUrl) {
      return new Response('Missing "url" query parameter', { status: 400 });
    }

    let parsedTarget;
    try {
      parsedTarget = new URL(targetUrl);
    } catch {
      return new Response('Invalid "url" query parameter', { status: 400 });
    }

    // ── Fetch the target page from inside Cloudflare's network ────────────
    try {
      const response = await fetch(parsedTarget.toString(), {
        method: 'GET',
        headers: {
          'User-Agent':      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
          'Accept':          'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
          'Accept-Language': 'en-US,en;q=0.5',
          'Cache-Control':   'no-cache',
        },
        redirect: 'follow',
      });

      const html = await response.text();

      return new Response(html, {
        status: response.status,
        headers: {
          'Content-Type':                'text/html; charset=utf-8',
          'Access-Control-Allow-Origin': '*',
        },
      });
    } catch (err) {
      return new Response('Worker fetch error: ' + err.message, { status: 500 });
    }
  },
};

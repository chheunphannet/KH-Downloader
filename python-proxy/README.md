# KH Python Impersonation Proxy

This is a tiny, high-performance Python web proxy built using **FastAPI** and **curl_cffi**. It is designed specifically to bypass strict Cloudflare Turnstile challenges by impersonating a real Google Chrome browser's TLS (JA3/JA4) fingerprint and HTTP/2 characteristics.

It serves as a transparent bypass for **KHFullHD** streaming extractions.

## Deploying to Vercel (Free Forever)

Vercel is completely free and supports Python serverless functions.

1. **Deploying on Vercel**:
   - Go to [Vercel.com](https://vercel.com) and sign in/up.
   - Click **Add New** -> **Project**.
   - Import your GitHub repository.
   - Under **Framework Preset**, select **Other**.
   - Under **Root Directory**, click **Edit** and select the `python-proxy` folder.
   - Open **Environment Variables** and add:
     * **`SECRET_TOKEN`**: A long random security key (e.g. any secure string).
   - Click **Deploy**. Vercel will automatically build the environment using `vercel.json` and `api/index.py`.

> [!NOTE]
> Vercel Hobby accounts have a **10-second request timeout limit**. Since fetching pages and scraping metadata is very fast, this is usually plenty of time. If you face timeout issues or cold starts, you can use the Koyeb Docker setup below.

---

## Deploying to Koyeb (Free Tier Always-On Docker)

Koyeb offers a free tier that supports full Docker containers with no timeout limits.

1. Go to [Koyeb.com](https://www.koyeb.com) and create a free account.
2. Create a new Service, and select **GitHub** as the source.
3. Choose your repository.
4. Set the **Work Directory** to `python-proxy`.
5. Under **Builder**, select **Dockerfile** (it will auto-detect the `Dockerfile` inside the `python-proxy` folder).
6. Under **Environment Variables**, add:
   * **`SECRET_TOKEN`**: Your security key.
7. Under **Ports**, set the port to `8000` (FastAPI default).
8. Click **Deploy**. Koyeb will run the app inside a persistent container, avoiding serverless timeouts and cold starts completely for free.

---

## Integrating with Laravel (.env)

Once deployed, add these settings to your Laravel `.env` file on the VPS:

```env
PYTHON_PROXY_ENABLED=true
PYTHON_PROXY_URL=https://your-app-name.vercel.app # or Koyeb URL
PYTHON_PROXY_TOKEN=your_SECRET_TOKEN_here
```


# KH Python Impersonation Proxy

This is a tiny, high-performance Python web proxy built using **FastAPI** and **curl_cffi**. It is designed specifically to bypass strict Cloudflare Bot-Management and Turnstile challenges by impersonating a real Google Chrome browser's TLS (JA3/JA4) fingerprint and HTTP/2 characteristics.

It serves as a transparent bypass for **KHFullHD** streaming extractions.

## Deploying to Railway

1. Push this folder to your GitHub repository.
2. Go to [Railway.app](https://railway.app) and create a new project.
3. Select **Deploy from GitHub repo**, choose your repository, and select the `python-proxy` folder (or root if it's a dedicated repo).
4. Go to **Variables** in your Railway service and add:
   * **`SECRET_TOKEN`**: A long random security key (e.g. `openssl rand -hex 32` or any secure string).
5. Deploy the application. Railway will automatically build the `Dockerfile` and give you a public URL (e.g., `https://python-proxy-production.up.railway.app`).

## Integrating with Laravel (.env)

Once deployed, add these settings to your Laravel `.env` file on the VPS:

```env
PYTHON_PROXY_ENABLED=true
PYTHON_PROXY_URL=https://your-railway-app.up.railway.app
PYTHON_PROXY_TOKEN=your_SECRET_TOKEN_here
```

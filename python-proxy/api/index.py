import os
import random
from fastapi import FastAPI, Query, HTTPException, Security
from fastapi.responses import Response
from fastapi.security.api_key import APIKeyHeader
from curl_cffi import requests

app = FastAPI(title="KH Impersonation Proxy")

SECRET_TOKEN = os.getenv("SECRET_TOKEN")
PROXY_URL = os.getenv("PROXY_URL") # e.g. http://username:password@ip:port
API_KEY_HEADER = APIKeyHeader(name="X-Proxy-Token", auto_error=False)

def verify_token(token: str = Security(API_KEY_HEADER), token_query: str = Query(None, alias="token")):
    # Check header first, then fallback to query parameter
    actual_token = token or token_query
    if not SECRET_TOKEN:
        # If no token is configured in environment, allow requests (useful for local dev)
        return
    if actual_token != SECRET_TOKEN:
        raise HTTPException(status_code=401, detail="Unauthorized")

def fetch_url(url: str, headers: dict) -> Response:
    impersonate_target = "chrome120"
    
    # 0. If a custom proxy is configured, use it directly (blazing fast!)
    if PROXY_URL:
        try:
            proxies = {
                "http": PROXY_URL,
                "https": PROXY_URL
            }
            response = requests.get(
                url,
                headers=headers,
                impersonate=impersonate_target,
                proxies=proxies,
                timeout=12
            )
            return Response(
                content=response.content,
                status_code=response.status_code,
                media_type=response.headers.get("content-type") or response.headers.get("Content-Type") or "text/html"
            )
        except Exception as e:
            raise HTTPException(status_code=500, detail=f"Custom proxy error: {str(e)}")

    # 1. Try Direct Fetch first
    try:
        response = requests.get(
            url,
            headers=headers,
            impersonate=impersonate_target,
            timeout=8
        )
        # If it returns 200 and is not a Cloudflare challenge page, return it immediately
        if response.status_code == 200 and "Just a moment..." not in response.text:
            return Response(
                content=response.content,
                status_code=response.status_code,
                media_type=response.headers.get("content-type") or response.headers.get("Content-Type") or "text/html"
            )
    except Exception:
        pass

    # 2. If direct fetch gets blocked, fall back to free elite proxies
    try:
        # Fetch elite/anonymous proxies
        proxy_api = "https://api.proxyscrape.com/v2/?request=getproxies&protocol=http&timeout=3000&country=all&ssl=yes&anonymity=elite"
        proxy_resp = requests.get(proxy_api, timeout=5)
        proxies_list = [p.strip() for p in proxy_resp.text.split("\n") if p.strip()]
        
        if proxies_list:
            random.shuffle(proxies_list)
            # Try up to 8 random proxies
            for proxy_ip in proxies_list[:8]:
                try:
                    proxies = {
                        "http": f"http://{proxy_ip}",
                        "https": f"http://{proxy_ip}"
                    }
                    response = requests.get(
                        url,
                        headers=headers,
                        impersonate=impersonate_target,
                        proxies=proxies,
                        timeout=6
                    )
                    if response.status_code == 200 and "Just a moment..." not in response.text:
                        return Response(
                            content=response.content,
                            status_code=response.status_code,
                            media_type=response.headers.get("content-type") or response.headers.get("Content-Type") or "text/html"
                        )
                except Exception:
                    continue
    except Exception as e:
        print(f"Proxy fallback error: {e}")

    # 3. Last resort fallback (try direct fetch again and return whatever it outputs)
    try:
        response = requests.get(
            url,
            headers=headers,
            impersonate=impersonate_target,
            timeout=15
        )
        return Response(
            content=response.content,
            status_code=response.status_code,
            media_type=response.headers.get("content-type") or response.headers.get("Content-Type") or "text/html"
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.api_route("/{path_name:path}", methods=["GET", "POST"], dependencies=[Security(verify_token)])
def proxy(
    path_name: str = "",
    url: str = Query(None, description="The target URL to fetch"),
    referer: str = Query(None, description="Optional Referer header")
):
    if not url:
        return {"status": "ok", "message": "KH Proxy is running!"}

    headers = {
        "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
        "Accept-Language": "en-US,en;q=0.9",
    }
    if referer:
        headers["Referer"] = referer

    return fetch_url(url, headers)




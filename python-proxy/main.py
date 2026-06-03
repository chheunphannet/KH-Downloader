import os
from fastapi import FastAPI, Query, HTTPException, Security
from fastapi.responses import Response
from fastapi.security.api_key import APIKeyHeader
from curl_cffi import requests

app = FastAPI(title="KH Impersonation Proxy")

SECRET_TOKEN = os.getenv("SECRET_TOKEN")
API_KEY_HEADER = APIKeyHeader(name="X-Proxy-Token", auto_error=False)

def verify_token(token: str = Security(API_KEY_HEADER), token_query: str = Query(None, alias="token")):
    # Check header first, then fallback to query parameter
    actual_token = token or token_query
    if not SECRET_TOKEN:
        # If no token is configured in environment, allow requests (useful for local dev)
        return
    if actual_token != SECRET_TOKEN:
        raise HTTPException(status_code=401, detail="Unauthorized")

@app.api_route("/{path_name:path}", methods=["GET", "POST"], dependencies=[Security(verify_token)])
def proxy(
    path_name: str = "",
    url: str = Query(None, description="The target URL to fetch"),
    referer: str = Query(None, description="Optional Referer header")
):
    if not url:
        return {"status": "ok", "message": "KH Proxy is running!"}

    try:
        headers = {
            "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
            "Accept-Language": "en-US,en;q=0.9",
        }
        if referer:
            headers["Referer"] = referer

        # Impersonate Chrome 120 (spoofs TLS JA3 fingerprints & HTTP/2 windows)
        response = requests.get(
            url,
            headers=headers,
            impersonate="chrome120",
            timeout=15
        )

        # Forward content-type, body content, and status code transparently
        return Response(
            content=response.content,
            status_code=response.status_code,
            media_type=response.headers.get("content-type") or response.headers.get("Content-Type") or "text/html"
        )
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


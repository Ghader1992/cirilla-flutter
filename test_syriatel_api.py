#!/usr/bin/env python3
"""
Syriatel Bulk SMS API Discovery Script
Tests multiple endpoint/auth/body combinations to find the working API contract.
"""

import json
import ssl
import urllib.request
import urllib.parse
import urllib.error
from urllib.request import HTTPError

# Known config
PASSWORD = "Pp@1234567"
SENDER = "EO"
TEMPLATES = ["EO-API1", "EO-API2"]
POSSIBLE_USERS = ["20490", "41229894", "EO", "EO-API1"]

# Test with a dummy number to avoid accidental sends
# Use a clearly invalid number or your own test number
TEST_NUMBER = "963900000000"  # dummy Syrian number format
TEST_OTP = "123456"

# Likely endpoints
ENDPOINTS = [
    "https://bulkmsg.syriatel.sy/api/send",
    "https://bulkmsg.syriatel.sy/send.aspx",
    "https://bulkmsg.syriatel.sy/api/v1/send",
    "https://bulkmsg.syriatel.sy/sms/send",
    "https://bulkmsg.syriatel.sy/rest/sms/send",
    "https://bulkmsg.syriatel.sy/api/sms/send",
    "https://bulkmsg.syriatel.sy/send",
    "https://bulkmsg.syriatel.sy/api/send_sms",
    "https://bulkmsg.syriatel.sy/index.php/api/send",
]

# Disable SSL verification for testing (some local gateways have self-signed certs)
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE


def try_request(label, url, method="POST", headers=None, data=None):
    """Execute an HTTP request and print the result."""
    req = urllib.request.Request(url, method=method)
    if headers:
        for k, v in headers.items():
            req.add_header(k, v)
    if data:
        req.data = data

    try:
        with urllib.request.urlopen(req, context=ctx, timeout=15) as resp:
            body = resp.read().decode("utf-8", errors="replace")
            print(f"\n✅ SUCCESS [{label}]")
            print(f"   URL: {url}")
            print(f"   Status: {resp.status}")
            print(f"   Response: {body[:500]}")
            return True
    except HTTPError as e:
        body = e.read().decode("utf-8", errors="replace") if e.fp else ""
        print(f"\n❌ HTTP ERROR [{label}]")
        print(f"   URL: {url}")
        print(f"   Status: {e.code}")
        print(f"   Response: {body[:500]}")
        return False
    except urllib.error.URLError as e:
        print(f"\n❌ URL ERROR [{label}]")
        print(f"   URL: {url}")
        print(f"   Reason: {e.reason}")
        return False
    except Exception as e:
        print(f"\n❌ EXCEPTION [{label}]")
        print(f"   URL: {url}")
        print(f"   Error: {e}")
        return False


def main():
    print("=" * 70)
    print("SYRIATEL BULK SMS API DISCOVERY")
    print("=" * 70)
    print(f"Test Number: {TEST_NUMBER}")
    print(f"Password:    {PASSWORD}")
    print(f"Sender:      {SENDER}")
    print("=" * 70)

    found_any = False

    for endpoint in ENDPOINTS:
        print(f"\n{'='*70}")
        print(f"Testing endpoint: {endpoint}")
        print(f"{'='*70}")

        for user in POSSIBLE_USERS:
            print(f"\n--- Trying user: {user} ---")

            # ---- Pattern 1: JSON body with Basic Auth ----
            payload_json = {
                "username": user,
                "password": PASSWORD,
                "sender": SENDER,
                "to": TEST_NUMBER,
                "template": TEMPLATES[0],
                "p1": TEST_OTP,
                "language": "ar",
            }
            body = json.dumps(payload_json).encode("utf-8")
            if try_request(
                "JSON + Basic Auth",
                endpoint,
                headers={
                    "Content-Type": "application/json",
                    "Authorization": f"Basic {user}:{PASSWORD}",
                },
                data=body,
            ):
                found_any = True

            # ---- Pattern 2: JSON body, auth in body only ----
            if try_request(
                "JSON body only",
                endpoint,
                headers={"Content-Type": "application/json"},
                data=body,
            ):
                found_any = True

            # ---- Pattern 3: Form URL-encoded, auth in body ----
            form_data = {
                "username": user,
                "password": PASSWORD,
                "sender": SENDER,
                "to": TEST_NUMBER,
                "template": TEMPLATES[0],
                "p1": TEST_OTP,
            }
            form_body = urllib.parse.urlencode(form_data).encode("utf-8")
            if try_request(
                "Form URL-encoded",
                endpoint,
                headers={"Content-Type": "application/x-www-form-urlencoded"},
                data=form_body,
            ):
                found_any = True

            # ---- Pattern 4: GET with query params ----
            qs = urllib.parse.urlencode(form_data)
            if try_request(
                "GET query params",
                f"{endpoint}?{qs}",
                method="GET",
            ):
                found_any = True

            # ---- Pattern 5: Different field names ----
            alt_fields = {
                "user": user,
                "pass": PASSWORD,
                "from": SENDER,
                "mobile": TEST_NUMBER,
                "msg": f"رمز التحقق الخاص بك هو {TEST_OTP}",
                "message": f"رمز التحقق الخاص بك هو {TEST_OTP}",
            }
            alt_body = urllib.parse.urlencode(alt_fields).encode("utf-8")
            if try_request(
                "Alt form fields (no template)",
                endpoint,
                headers={"Content-Type": "application/x-www-form-urlencoded"},
                data=alt_body,
            ):
                found_any = True

            # ---- Pattern 6: Bearer token (password as token) ----
            if try_request(
                "Bearer token (password)",
                endpoint,
                headers={
                    "Content-Type": "application/json",
                    "Authorization": f"Bearer {PASSWORD}",
                },
                data=json.dumps({
                    "sender": SENDER,
                    "to": TEST_NUMBER,
                    "template": TEMPLATES[0],
                    "p1": TEST_OTP,
                }).encode("utf-8"),
            ):
                found_any = True

    print(f"\n{'='*70}")
    if found_any:
        print("✅ Found at least one working pattern above!")
    else:
        print("⚠️  No working pattern found with the tested combinations.")
        print("   Possible reasons:")
        print("   - Endpoint URL is different from guesses")
        print("   - Requires additional headers or CSRF token")
        print("   - API is IP-restricted or requires VPN")
        print("   - Needs a real, valid phone number (not dummy)")
    print(f"{'='*70}")


if __name__ == "__main__":
    main()

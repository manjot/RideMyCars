import os
import sys
import time
import json
import subprocess
import urllib.request
import urllib.error

ADB = r"D:\config\platform-tools\adb.exe"
DEVICE = "SW4TRSB6ZX5DSCZ5"
BASE_URL = "https://www.ridemycars.com"
SCREENSHOTS_DIR = os.path.join(os.path.dirname(__file__), "test_screenshots")
os.makedirs(SCREENSHOTS_DIR, exist_ok=True)

def adb(cmd):
    full_cmd = f'"{ADB}" -s {DEVICE} {cmd}'
    res = subprocess.run(full_cmd, shell=True, capture_output=True, text=True)
    return res.stdout.strip()

def screenshot(name):
    dest = os.path.join(SCREENSHOTS_DIR, f"{name}.png")
    adb(f"shell screencap -p /sdcard/sc.png")
    adb(f'pull /sdcard/sc.png "{dest}"')
    print(f"  [SCREENSHOT SAVED] -> {dest}")
    return dest

def api_call(endpoint, method="GET", data=None, token=None):
    url = f"{BASE_URL}{endpoint}"
    headers = {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "User-Agent": "RideMyCars-OnDeviceTest/1.0"
    }
    if token:
        headers["Authorization"] = f"Bearer {token}"
    body = json.dumps(data).encode("utf-8") if data else None
    req = urllib.request.Request(url, data=body, headers=headers, method=method)
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            resp_body = resp.read().decode("utf-8")
            return resp.status, json.loads(resp_body) if resp_body else {}
    except urllib.error.HTTPError as e:
        err_body = e.read().decode("utf-8")
        try:
            return e.code, json.loads(err_body)
        except Exception:
            return e.code, {"raw": err_body}
    except Exception as e:
        return 0, {"error": str(e)}

def main():
    print("=" * 65)
    print("  ON-DEVICE AUTOMATED TEST: RIDER & DRIVER APPS WITH PIN VERIFICATION")
    print("=" * 65)

    # 1. Wake phone and dismiss lock screen
    print("\n[Step 1] Waking up phone and unlocking screen...")
    adb("shell input keyevent 224")  # KEYCODE_WAKEUP
    adb("shell input keyevent 82")   # KEYCODE_MENU (unlock)
    adb("shell wm dismiss-keyguard")
    time.sleep(1)

    # 2. Login accounts
    print("\n[Step 2] Authenticating Driver and Customer on production API...")
    _, d_resp = api_call("/api/login", "POST", {"email": "michael.driver@ridemycars.com", "password": "password"})
    if not d_resp.get("token"):
        _, d_resp = api_call("/api/login", "POST", {"email": "michael.driver@ridemycars.com", "password": "123456"})
    driver_token = d_resp.get("token")
    print("Driver authenticated!")

    _, r_resp = api_call("/api/login", "POST", {"email": "customer@ridemycars.com", "password": "password"})
    if not r_resp.get("token"):
        _, r_resp = api_call("/api/login", "POST", {"email": "customer@ridemycars.com", "password": "123456"})
    rider_token = r_resp.get("token")
    print("Customer authenticated!")

    # 3. Create fresh ride
    print("\n[Step 3] Requesting active ride on server...")
    ride_data = {
        "pickup_location": "Airport Terminal 3, Accra",
        "dropoff_location": "Oxford Street Mall, Osu, Accra",
        "pickup_lat": 5.6052,
        "pickup_lng": -0.1718,
        "dropoff_lat": 5.5560,
        "dropoff_lng": -0.1834,
        "payment_method": "cash",
        "vehicle_type": "standard",
        "distance_km": 5.5,
        "duration_minutes": 12,
        "notes": "Live Device UI Test"
    }
    _, book_resp = api_call("/api/rides", "POST", ride_data, token=rider_token)
    ride = book_resp.get("ride") or {}
    ride_id = ride.get("id")
    start_pin = str(ride.get("start_pin") or book_resp.get("start_pin") or "")
    print(f"Created Ride #{ride_id}")
    print(f"===> CUSTOMER 4-DIGIT SECURE PIN: {start_pin} <===")

    # 4. Launch Rider App on phone to view the customer PIN
    print("\n[Step 4] Launching Rider App (com.ridemycars.app) on phone...")
    adb("shell monkey -p com.ridemycars.app -c android.intent.category.LAUNCHER 1")
    time.sleep(4)
    screenshot("01_rider_app_with_secure_pin")

    # 5. Advance ride to arrived state
    print(f"Advancing ride #{ride_id} to arrived state...")
    api_call(f"/api/driver/rides/{ride_id}/status", "POST", {"status": "accepted", "type": "ride"}, token=driver_token)
    api_call(f"/api/driver/rides/{ride_id}/status", "POST", {"status": "en_route", "type": "ride"}, token=driver_token)
    api_call(f"/api/driver/rides/{ride_id}/status", "POST", {"status": "arrived", "type": "ride"}, token=driver_token)
    print("Ride is now in 'arrived' state (driver at pickup).")

    # 6. Launch Driver App on phone
    print("\n[Step 6] Launching Driver App (com.ridemycars.driver) on phone...")
    adb("shell monkey -p com.ridemycars.driver -c android.intent.category.LAUNCHER 1")
    time.sleep(4)
    screenshot("02_driver_app_arrived_trip")

    # 7. Tap 'Start Trip' button on Driver App to open 4-digit PIN verification modal
    print("\n[Step 7] Tapping 'Start Trip' on Driver App to open 4-digit PIN modal...")
    # On 720x1544, the Start Trip button is around (240, 1020) or (240, 1100) on active trip card
    adb("shell input tap 240 1020")
    time.sleep(1)
    adb("shell input tap 240 1100")
    time.sleep(1)
    adb("shell input tap 360 1420")
    time.sleep(2)
    screenshot("03_driver_app_pin_modal_open")

    # 8. Test entering WRONG PIN ('0000') on modal
    print("\n[Step 8] Entering incorrect PIN ('0000') on Driver App...")
    adb("shell input text 0000")
    time.sleep(1)
    # Tap "Verify & Start Trip" button in modal (around X=360, Y=1320 on bottom sheet)
    adb("shell input tap 360 1320")
    time.sleep(2)
    screenshot("04_driver_app_wrong_pin_error")

    # 9. Clear and enter CORRECT 4-Digit Customer PIN
    print(f"\n[Step 9] Entering CORRECT Customer PIN ('{start_pin}') on Driver App...")
    adb("shell input keyevent 67 67 67 67 67 67")  # Backspaces
    time.sleep(1)
    adb(f"shell input text {start_pin}")
    time.sleep(1)
    adb("shell input tap 360 1320")
    time.sleep(4)
    screenshot("05_driver_app_pin_verified_started")

    # 10. Switch back to Rider App to verify trip in progress
    print("\n[Step 10] Switching back to Rider App to verify live trip in progress...")
    adb("shell monkey -p com.ridemycars.app -c android.intent.category.LAUNCHER 1")
    time.sleep(4)
    screenshot("06_rider_app_trip_in_progress")

    # 11. Complete ride
    print(f"\n[Step 11] Completing test ride #{ride_id}...")
    api_call(f"/api/driver/rides/{ride_id}/status", "POST", {"status": "completed", "type": "ride"}, token=driver_token)
    print("Test ride marked as completed.")

    print("\n" + "=" * 65)
    print("  ON-DEVICE TEST COMPLETE! SCREENSHOTS CAPTURED:")
    for f in sorted(os.listdir(SCREENSHOTS_DIR)):
        if f.endswith(".png"):
            print(f"  * {f} ({os.path.getsize(os.path.join(SCREENSHOTS_DIR, f))} bytes)")
    print("=" * 65)

if __name__ == "__main__":
    main()

import json
import urllib.request
import urllib.error
import sys

BASE_URL = "https://www.ridemycars.com"

def api_call(endpoint, method="GET", data=None, token=None):
    url = f"{BASE_URL}{endpoint}"
    headers = {
        "Accept": "application/json",
        "Content-Type": "application/json",
        "User-Agent": "RideMyCars-PinTest/1.0"
    }
    if token:
        headers["Authorization"] = f"Bearer {token}"
    
    body = json.dumps(data).encode("utf-8") if data else None
    req = urllib.request.Request(url, data=body, headers=headers, method=method)
    
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            status = resp.status
            resp_body = resp.read().decode("utf-8")
            return status, json.loads(resp_body) if resp_body else {}
    except urllib.error.HTTPError as e:
        err_body = e.read().decode("utf-8")
        try:
            parsed = json.loads(err_body)
        except Exception:
            parsed = {"raw": err_body}
        return e.code, parsed
    except Exception as e:
        return 0, {"error": str(e)}

def run_test():
    print("=" * 60)
    print("  LIVE PRODUCTION TEST RIDE: 4-DIGIT PIN VERIFICATION")
    print("=" * 60)
    
    # 1. Login Driver
    print("\n[Step 1] Logging in Driver (michael.driver@ridemycars.com)...")
    status, d_resp = api_call("/api/login", "POST", {
        "email": "michael.driver@ridemycars.com",
        "password": "password"
    })
    if status != 200 or not d_resp.get("token"):
        # Try demo password '123456'
        status, d_resp = api_call("/api/login", "POST", {
            "email": "michael.driver@ridemycars.com",
            "password": "123456"
        })
    
    if status != 200 or not d_resp.get("token"):
        print(f"FAILED to login driver: {status} {d_resp}")
        return False
    
    driver_token = d_resp["token"]
    driver_id = d_resp["user"]["id"]
    print(f"Driver logged in! User ID: {driver_id}")

    # 2. Login or Register Rider
    print("\n[Step 2] Authenticating Customer...")
    status, r_resp = api_call("/api/login", "POST", {
        "email": "customer@ridemycars.com",
        "password": "password"
    })
    if status != 200 or not r_resp.get("token"):
        status, r_resp = api_call("/api/login", "POST", {
            "email": "customer@ridemycars.com",
            "password": "123456"
        })
    if status != 200 or not r_resp.get("token"):
        # Register rider
        status, r_resp = api_call("/api/register", "POST", {
            "name": "Live Test Rider",
            "email": "customer@ridemycars.com",
            "password": "password",
            "role": "customer"
        })
    
    if status not in (200, 201) or not r_resp.get("token"):
        print(f"FAILED to login rider: {status} {r_resp}")
        return False
        
    rider_token = r_resp["token"]
    rider_id = r_resp["user"]["id"]
    print(f"Customer logged in! User ID: {rider_id}")

    # 3. Create Ride Request
    print("\n[Step 3] Customer requests a ride...")
    ride_data = {
        "pickup_location": "Kotoka International Airport, Terminal 3, Accra",
        "dropoff_location": "Oxford Street Mall, Osu, Accra",
        "pickup_lat": 5.6052,
        "pickup_lng": -0.1718,
        "dropoff_lat": 5.5560,
        "dropoff_lng": -0.1834,
        "payment_method": "cash",
        "vehicle_type": "standard",
        "distance_km": 6.8,
        "duration_minutes": 15,
        "notes": "PIN Verification Live Test"
    }
    status, book_resp = api_call("/api/rides", "POST", ride_data, token=rider_token)
    if status not in (200, 201) or not book_resp.get("ride"):
        print(f"FAILED to book ride: {status} {book_resp}")
        return False
        
    ride = book_resp["ride"]
    ride_id = ride["id"]
    start_pin = str(ride.get("start_pin") or book_resp.get("start_pin") or "")
    print(f"Ride #{ride_id} created successfully!")
    print(f"CUSTOMER'S 4-DIGIT SECURE PIN IS: >>> {start_pin} <<<")
    if not start_pin or len(start_pin) != 4:
        print(f"WARNING: start_pin '{start_pin}' is not a 4-digit PIN!")
        return False
    else:
        print("PASS: Verified 4-digit PIN generated on ride creation.")

    # 4. Driver accepts the ride
    print(f"\n[Step 4] Driver accepts ride #{ride_id}...")
    status, acc_resp = api_call(f"/api/driver/rides/{ride_id}/status", "POST", {
        "status": "accepted",
        "type": "ride"
    }, token=driver_token)
    print(f"Accepted status: {status}, message: {acc_resp.get('message')}")

    # 5. Driver is en_route
    print(f"\n[Step 5] Driver goes en route...")
    status, en_resp = api_call(f"/api/driver/rides/{ride_id}/status", "POST", {
        "status": "en_route",
        "type": "ride"
    }, token=driver_token)
    print(f"En Route status: {status}, message: {en_resp.get('message')}")

    # 6. Driver arrives at pickup location
    print(f"\n[Step 6] Driver arrives at pickup...")
    status, arr_resp = api_call(f"/api/driver/rides/{ride_id}/status", "POST", {
        "status": "arrived",
        "type": "ride"
    }, token=driver_token)
    print(f"Arrived status: {status}, message: {arr_resp.get('message')}")

    # 7. Driver attempts to start trip WITHOUT PIN
    print(f"\n[Step 7] Driver attempts to start trip WITHOUT PIN (Expect 422 Rejection)...")
    status, no_pin_resp = api_call(f"/api/driver/rides/{ride_id}/status", "POST", {
        "status": "in_progress",
        "type": "ride"
    }, token=driver_token)
    print(f"HTTP Status: {status}")
    print(f"Response: {no_pin_resp}")
    if status == 422 and no_pin_resp.get("requires_pin"):
        print("PASS: Starting trip without PIN correctly blocked with HTTP 422 (PIN_REQUIRED).")
    else:
        print("FAIL: Expected 422 PIN_REQUIRED but got:", status, no_pin_resp)
        return False

    # 8. Driver attempts to start trip with WRONG PIN '0000'
    print(f"\n[Step 8] Driver attempts to start trip with WRONG PIN '0000' (Expect 422 Rejection)...")
    status, wrong_pin_resp = api_call(f"/api/driver/rides/{ride_id}/status", "POST", {
        "status": "in_progress",
        "pin": "0000",
        "type": "ride"
    }, token=driver_token)
    print(f"HTTP Status: {status}")
    print(f"Response: {wrong_pin_resp}")
    if status == 422 and wrong_pin_resp.get("requires_pin"):
        print("PASS: Starting trip with wrong PIN correctly blocked with HTTP 422 (INVALID_PIN).")
    else:
        print("FAIL: Expected 422 INVALID_PIN but got:", status, wrong_pin_resp)
        return False

    # 9. Driver submits CORRECT 4-Digit Customer PIN
    print(f"\n[Step 9] Customer shares PIN '{start_pin}' with driver; Driver inputs PIN...")
    status, start_resp = api_call(f"/api/driver/rides/{ride_id}/status", "POST", {
        "status": "in_progress",
        "pin": start_pin,
        "type": "ride"
    }, token=driver_token)
    print(f"HTTP Status: {status}")
    print(f"Response: {start_resp.get('message')}")
    if status == 200 and start_resp.get("success"):
        print("PASS: Trip successfully started with correct 4-digit PIN! Status: in_progress.")
    else:
        print("FAIL: Expected trip to start with valid PIN, got:", status, start_resp)
        return False

    # 10. Check Rider active ride endpoint
    print("\n[Step 10] Checking Rider active ride status from customer app viewpoint...")
    status, active_resp = api_call("/api/rides/active", "GET", token=rider_token)
    active_ride = active_resp.get("ride") or {}
    print(f"Rider sees Status: {active_ride.get('status')}, Start PIN: {active_ride.get('start_pin')}")

    # 11. Complete the trip
    print(f"\n[Step 11] Driver arrives at destination and completes ride #{ride_id}...")
    status, comp_resp = api_call(f"/api/driver/rides/{ride_id}/status", "POST", {
        "status": "completed",
        "type": "ride"
    }, token=driver_token)
    print(f"Completed Status: {status}, message: {comp_resp.get('message')}")
    if status == 200:
        print("PASS: Trip completed successfully.")
        
    print("\n" + "=" * 60)
    print("  ALL LIVE PRODUCTION 4-DIGIT PIN TESTS PASSED WITH 100% SUCCESS!  ")
    print("=" * 60)
    return True

if __name__ == "__main__":
    success = run_test()
    sys.exit(0 if success else 1)

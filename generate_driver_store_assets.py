import os
from PIL import Image, ImageDraw, ImageFont

base_dir = "/Users/manjotsingh/RideMyCars"
out_dir = os.path.join(base_dir, "store_metadata", "driver_assets")
ios_dir = os.path.join(out_dir, "ios")
android_dir = os.path.join(out_dir, "android")

os.makedirs(os.path.join(ios_dir, "iphone_6_7_inch"), exist_ok=True)
os.makedirs(os.path.join(ios_dir, "iphone_6_5_inch"), exist_ok=True)
os.makedirs(os.path.join(ios_dir, "iphone_5_5_inch"), exist_ok=True)
os.makedirs(os.path.join(ios_dir, "ipad_12_9_inch"), exist_ok=True)
os.makedirs(os.path.join(android_dir, "phone"), exist_ok=True)
os.makedirs(os.path.join(android_dir, "tablet_7_inch"), exist_ok=True)
os.makedirs(os.path.join(android_dir, "tablet_10_inch"), exist_ok=True)

logo_path = os.path.join(base_dir, "flutter_driver_app", "assets", "logo.png")

# Font Helper
def get_font(size, bold=True):
    font_paths = [
        "/System/Library/Fonts/SFProText-Bold.otf" if bold else "/System/Library/Fonts/SFProText-Regular.otf",
        "/System/Library/Fonts/SFNSDisplayCondensed-Bold.otf" if bold else "/System/Library/Fonts/SFNSDisplayCondensed-Regular.otf",
        "/System/Library/Fonts/HelveticaNeue.ttc",
        "/System/Library/Fonts/Supplemental/Arial Bold.ttf" if bold else "/System/Library/Fonts/Supplemental/Arial.ttf",
        "/System/Library/Fonts/Supplemental/Helvetica.ttc",
        "/Library/Fonts/Arial.ttf"
    ]
    for p in font_paths:
        if os.path.exists(p):
            try:
                return ImageFont.truetype(p, size)
            except Exception:
                continue
    return ImageFont.load_default()

# --- 1. Generate 1024x1024 Driver App Store Icon (RGB, No Alpha) ---
print("Generating 1024x1024 iOS Driver App Store Icon (RGB)...")
icon_canvas = Image.new("RGB", (1024, 1024), (11, 17, 32)) # Deep Navy
draw = ImageDraw.Draw(icon_canvas)

# Radial gold ring/accent
for r in range(500, 0, -5):
    alpha = int(40 * (1 - r / 500))
    draw.ellipse([512 - r, 512 - r, 512 + r, 512 + r], fill=(11 + int(alpha * 0.9), 17 + int(alpha * 0.6), 32 + int(alpha * 0.2)))

if os.path.exists(logo_path):
    logo = Image.open(logo_path).convert("RGBA")
    w, h = logo.size
    aspect = w / h
    tw = 760
    th = int(tw / aspect)
    if th > 760:
        th = 760
        tw = int(th * aspect)
    logo_res = logo.resize((tw, th), Image.Resampling.LANCZOS)
    px = (1024 - tw) // 2
    py = (1024 - th) // 2 - 50
    icon_canvas.paste(logo_res, (px, py), logo_res)

# Badge: DRIVER PARTNER
draw.rounded_rectangle([250, 840, 774, 930], radius=24, fill=(245, 158, 11))
font_badge = get_font(42, bold=True)
draw.text((512, 885), "DRIVER PARTNER", fill=(11, 17, 32), font=font_badge, anchor="mm")

ios_icon_path = os.path.join(ios_dir, "driver_app_store_icon_1024x1024.png")
icon_canvas.save(ios_icon_path, "PNG")
print(f"Saved: {ios_icon_path}")

# Android 512x512 Icon
play_icon = icon_canvas.resize((512, 512), Image.Resampling.LANCZOS)
play_icon_path = os.path.join(android_dir, "driver_play_icon_512x512.png")
play_icon.save(play_icon_path, "PNG")
print(f"Saved: {play_icon_path}")

# --- 2. Generate Multi-Device Driver Framed Screenshots ---
driver_screens = [
    ("screencap_driver.png", "1_incoming_requests", "Accept Live Rides, Rentals & Courier Requests"),
    ("screencap_rent.png", "2_earnings_tracking", "Live Earnings, Commission Rates & Fast Payouts"),
    ("screenshot_3_chauffeurs.png", "3_hourly_chauffeur", "Earn with Hourly Chauffeur & Daily Driver Bookings"),
    ("screenshot_1_rides_home.png", "4_smart_navigation", "High-Accuracy Turn-by-Turn GPS Navigation"),
    ("screenshot_6_activity_bookings.png", "5_trip_history", "Complete Real-Time Trip Logs & Passenger Ratings")
]

def create_driver_screenshot(src_path, target_size, headline, is_rgb=True):
    tw, th = target_size
    mode = "RGB" if is_rgb else "RGBA"
    bg = (15, 23, 42) if is_rgb else (15, 23, 42, 255)
    canvas = Image.new(mode, (tw, th), bg)
    draw = ImageDraw.Draw(canvas)

    header_h = int(th * 0.14)
    for y in range(header_h):
        fac = y / float(header_h)
        r = int(16 * 0.2 * (1 - fac) + 15)
        g = int(185 * 0.2 * (1 - fac) + 23)
        b = int(129 * 0.2 * (1 - fac) + 42)
        draw.line([(0, y), (tw, y)], fill=(r, g, b))

    # Font size relative to width
    font_size = max(28, int(tw * 0.038))
    fnt = get_font(font_size, bold=True)
    draw.text((tw // 2, int(header_h * 0.52)), headline, fill=(255, 255, 255), font=fnt, anchor="mm")

    if os.path.exists(src_path):
        src_img = Image.open(src_path).convert("RGBA")
        sw, sh = src_img.size
        avail_w = int(tw * 0.88)
        avail_h = int(th * 0.80)
        scale = min(avail_w / sw, avail_h / sh)
        nw, nh = int(sw * scale), int(sh * scale)
        res = src_img.resize((nw, nh), Image.Resampling.LANCZOS)
        px = (tw - nw) // 2
        py = header_h + (th - header_h - nh) // 2
        draw.rounded_rectangle([px - 4, py - 4, px + nw + 4, py + nh + 4], radius=24, fill=(30, 41, 59), outline=(16, 185, 129), width=3)
        canvas.paste(res.convert("RGB") if is_rgb else res, (px, py))

    return canvas

dim_list = [
    (os.path.join(ios_dir, "iphone_6_7_inch"), (1290, 2796), True, "iOS 6.7\""),
    (os.path.join(ios_dir, "iphone_6_5_inch"), (1242, 2688), True, "iOS 6.5\""),
    (os.path.join(ios_dir, "iphone_5_5_inch"), (1242, 2208), True, "iOS 5.5\""),
    (os.path.join(ios_dir, "ipad_12_9_inch"), (2048, 2732), True, "iOS 12.9\" iPad"),
    (os.path.join(android_dir, "phone"), (1080, 2400), False, "Android Phone"),
    (os.path.join(android_dir, "tablet_7_inch"), (1200, 1920), False, "Android 7\" Tablet"),
    (os.path.join(android_dir, "tablet_10_inch"), (1600, 2560), False, "Android 10\" Tablet")
]

for src_file, prefix, headline in driver_screens:
    src_p = os.path.join(base_dir, src_file)
    if not os.path.exists(src_p):
        src_p = os.path.join(base_dir, "playstore_assets", src_file)
    if not os.path.exists(src_p):
        continue
    for out_sub, size, rgb, name in dim_list:
        scr = create_driver_screenshot(src_p, size, headline, is_rgb=rgb)
        sp = os.path.join(out_sub, f"{prefix}.png")
        scr.save(sp, "PNG")
        print(f"Generated {name}: {sp}")

print("\nDriver Store Graphics Generated Successfully!")

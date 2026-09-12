import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

base_dir = "/Users/manjotsingh/RideMyCars"
out_dir = os.path.join(base_dir, "store_metadata", "assets")
ios_dir = os.path.join(out_dir, "ios")
android_dir = os.path.join(out_dir, "android")

os.makedirs(os.path.join(ios_dir, "iphone_6_7_inch"), exist_ok=True)
os.makedirs(os.path.join(ios_dir, "iphone_6_5_inch"), exist_ok=True)
os.makedirs(os.path.join(ios_dir, "iphone_5_5_inch"), exist_ok=True)
os.makedirs(os.path.join(ios_dir, "ipad_12_9_inch"), exist_ok=True)
os.makedirs(os.path.join(android_dir, "phone"), exist_ok=True)
os.makedirs(os.path.join(android_dir, "tablet_7_inch"), exist_ok=True)
os.makedirs(os.path.join(android_dir, "tablet_10_inch"), exist_ok=True)

logo_path = os.path.join(base_dir, "flutter_app", "assets", "logo.png")

# --- 1. Generate 1024x1024 App Store Icon (No Alpha - Required by Apple) ---
print("Generating 1024x1024 iOS App Store Icon (RGB)...")
app_store_icon = Image.new("RGB", (1024, 1024), (11, 17, 32)) # Deep luxury dark navy
draw_icon = ImageDraw.Draw(app_store_icon)

# Subtle luxury radial gradient/glow
for r in range(500, 0, -5):
    alpha = int(35 * (1 - r / 500))
    # blend amber glow
    draw_icon.ellipse([512 - r, 512 - r, 512 + r, 512 + r], fill=(11 + int(alpha * 0.9), 17 + int(alpha * 0.6), 32 + int(alpha * 0.2)))

if os.path.exists(logo_path):
    logo = Image.open(logo_path).convert("RGBA")
    w, h = logo.size
    aspect = w / h
    tw = 820
    th = int(tw / aspect)
    if th > 820:
        th = 820
        tw = int(th * aspect)
    logo_res = logo.resize((tw, th), Image.Resampling.LANCZOS)
    px = (1024 - tw) // 2
    py = (1024 - th) // 2
    app_store_icon.paste(logo_res, (px, py), logo_res)

ios_icon_path = os.path.join(ios_dir, "app_store_icon_1024x1024.png")
app_store_icon.save(ios_icon_path, "PNG")
print(f"Saved: {ios_icon_path}")


# --- 2. Generate 512x512 Google Play Icon (RGBA) ---
print("Generating 512x512 Google Play Icon...")
play_icon = Image.new("RGBA", (512, 512), (11, 17, 32, 255))
if os.path.exists(logo_path):
    logo = Image.open(logo_path).convert("RGBA")
    w, h = logo.size
    aspect = w / h
    tw = 410
    th = int(tw / aspect)
    if th > 410:
        th = 410
        tw = int(th * aspect)
    logo_res = logo.resize((tw, th), Image.Resampling.LANCZOS)
    px = (512 - tw) // 2
    py = (512 - th) // 2
    play_icon.paste(logo_res, (px, py), logo_res)

play_icon_path = os.path.join(android_dir, "play_store_icon_512x512.png")
play_icon.save(play_icon_path, "PNG")
print(f"Saved: {play_icon_path}")


# --- 3. Generate 1024x500 Feature Graphic ---
print("Generating 1024x500 Google Play Feature Graphic...")
fg_img = Image.new("RGB", (1024, 500), (11, 17, 32))
fg_draw = ImageDraw.Draw(fg_img)

for y in range(500):
    r = int(11 + (28 - 11) * (y / 500.0))
    g = int(17 + (38 - 17) * (y / 500.0))
    b = int(32 + (54 - 32) * (y / 500.0))
    fg_draw.line([(0, y), (1024, y)], fill=(r, g, b))

fg_draw.ellipse([720, -80, 1120, 320], fill=(245, 158, 11))
# Blur/overlay with subtle alpha box
if os.path.exists(logo_path):
    logo = Image.open(logo_path).convert("RGBA")
    tw = 380
    th = int(tw * (logo.height / logo.width))
    logo_res = logo.resize((tw, th), Image.Resampling.LANCZOS)
    fg_img.paste(logo_res, (60, 95), logo_res)

# Badges
badges = [
    ("🚕  On-Demand Rides & City Cabs", (245, 158, 11)),
    ("🔑  Luxury Self-Drive Rentals", (59, 130, 246)),
    ("👨‍✈️  Hourly Dedicated Chauffeurs", (16, 185, 129)),
    ("📦  Express Door-to-Door Delivery", (168, 85, 247))
]
by = 100
for text, col in badges:
    fg_draw.rounded_rectangle([500, by, 960, by + 58], radius=14, fill=(24, 33, 47), outline=col, width=2)
    fg_draw.text((525, by + 29), text, fill=(255, 255, 255), anchor="lm")
    by += 72

fg_draw.text((60, 425), "Next-Gen Smart Mobility • Cabs • Rentals • Chauffeurs • Courier", fill=(148, 163, 184), anchor="ls")
fg_path = os.path.join(android_dir, "feature_graphic_1024x500.png")
fg_img.save(fg_path, "PNG")
print(f"Saved: {fg_path}")


# --- 4. Process Screenshots for Multi-Device Formats ---
source_screens_dir = os.path.join(base_dir, "playstore_assets")
screens = [
    ("screenshot_1_rides_home.png", "1_rides_and_cabs", "Book Fast On-Demand Rides & Airport Transfers"),
    ("screenshot_2_luxury_rentals.png", "2_luxury_rentals", "Rent Luxury & Self-Drive Cars with Daily Rates"),
    ("screenshot_3_chauffeurs.png", "3_hourly_chauffeurs", "Hire Professional Vetted Drivers Hourly"),
    ("screenshot_4_parcel_delivery.png", "4_parcel_delivery", "Instant Courier & Door-to-Door Package Delivery"),
    ("screenshot_5_rental_voucher.png", "5_transparent_booking", "Instant Digital Vouchers & Transparent Pricing"),
    ("screenshot_6_activity_bookings.png", "6_activity_tracking", "Live GPS Ride Tracking & Complete Booking History")
]

# Helper function to generate framed screenshots with banner title
def create_framed_screenshot(src_path, target_size, title, subtitle="", is_rgb=False):
    tw, th = target_size
    mode = "RGB" if is_rgb else "RGBA"
    bg_color = (15, 23, 42) if is_rgb else (15, 23, 42, 255)
    canvas = Image.new(mode, (tw, th), bg_color)
    draw = ImageDraw.Draw(canvas)

    # Top gradient / accent header
    header_h = int(th * 0.16)
    for y in range(header_h):
        fac = y / float(header_h)
        r = int(245 * 0.15 * (1 - fac) + 15)
        g = int(158 * 0.15 * (1 - fac) + 23)
        b = int(11 * 0.15 * (1 - fac) + 42)
        draw.line([(0, y), (tw, y)], fill=(r, g, b) if is_rgb else (r, g, b, 255))

    # Header Title
    title_y = int(header_h * 0.5)
    draw.text((tw // 2, title_y), title, fill=(255, 255, 255) if is_rgb else (255, 255, 255, 255), anchor="mm")

    # Load source screen
    if os.path.exists(src_path):
        src_img = Image.open(src_path).convert("RGBA")
        sw, sh = src_img.size
        
        avail_w = int(tw * 0.88)
        avail_h = int(th * 0.78)
        
        scale = min(avail_w / sw, avail_h / sh)
        new_w = int(sw * scale)
        new_h = int(sh * scale)
        
        resized = src_img.resize((new_w, new_h), Image.Resampling.LANCZOS)
        
        paste_x = (tw - new_w) // 2
        paste_y = header_h + (th - header_h - new_h) // 2
        
        # Phone border frame
        frame_pad = 6
        draw.rounded_rectangle(
            [paste_x - frame_pad, paste_y - frame_pad, paste_x + new_w + frame_pad, paste_y + new_h + frame_pad],
            radius=24,
            fill=(30, 41, 59) if is_rgb else (30, 41, 59, 255),
            outline=(245, 158, 11) if is_rgb else (245, 158, 11, 255),
            width=3
        )
        
        if is_rgb:
            canvas.paste(resized.convert("RGB"), (paste_x, paste_y))
        else:
            canvas.paste(resized, (paste_x, paste_y), resized)

    return canvas

# Target sizes specifications:
dimensions = [
    # (output_subdir, (width, height), is_rgb, format_name)
    (os.path.join(ios_dir, "iphone_6_7_inch"), (1290, 2796), True, "iOS 6.7\" Display (1290x2796)"),
    (os.path.join(ios_dir, "iphone_6_5_inch"), (1242, 2688), True, "iOS 6.5\" Display (1242x2688)"),
    (os.path.join(ios_dir, "iphone_5_5_inch"), (1242, 2208), True, "iOS 5.5\" Display (1242x2208)"),
    (os.path.join(ios_dir, "ipad_12_9_inch"), (2048, 2732), True, "iOS 12.9\" iPad Pro (2048x2732)"),
    (os.path.join(android_dir, "phone"), (1080, 2400), False, "Play Store Phone (1080x2400)"),
    (os.path.join(android_dir, "tablet_7_inch"), (1200, 1920), False, "Play Store 7\" Tablet (1200x1920)"),
    (os.path.join(android_dir, "tablet_10_inch"), (1600, 2560), False, "Play Store 10\" Tablet (1600x2560)")
]

for src_file, prefix, headline in screens:
    src_p = os.path.join(source_screens_dir, src_file)
    if not os.path.exists(src_p):
        continue
    for out_sub, size, rgb_mode, fmt_name in dimensions:
        framed = create_framed_screenshot(src_p, size, headline, is_rgb=rgb_mode)
        save_path = os.path.join(out_sub, f"{prefix}.png")
        framed.save(save_path, "PNG")
        print(f"Generated {fmt_name}: {save_path}")

print("\nAll Store Graphics Generated Successfully!")

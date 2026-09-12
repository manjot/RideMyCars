import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

out_dir = "/Users/manjotsingh/RideMyCars/playstore_assets"
os.makedirs(out_dir, exist_ok=True)

# 1. Generate 512x512 App Icon
icon_size = (512, 512)
logo_path = "/Users/manjotsingh/RideMyCars/flutter_app/assets/logo.png"

icon_img = Image.new("RGBA", icon_size, (11, 17, 32, 255)) # Dark theme background
draw = ImageDraw.Draw(icon_img)

# Rounded square / squircle background for Play Store icon
if os.path.exists(logo_path):
    logo = Image.open(logo_path).convert("RGBA")
    # Resize logo to fit nicely with padding
    logo_w, logo_h = logo.size
    aspect = logo_w / logo_h
    target_w = 400
    target_h = int(target_w / aspect)
    if target_h > 400:
        target_h = 400
        target_w = int(target_h * aspect)
    logo_resized = logo.resize((target_w, target_h), Image.Resampling.LANCZOS)
    paste_x = (512 - target_w) // 2
    paste_y = (512 - target_h) // 2
    icon_img.paste(logo_resized, (paste_x, paste_y), logo_resized)
else:
    # Draw branded fallback icon
    draw.rounded_rectangle([20, 20, 492, 492], radius=60, fill=(245, 158, 11, 255))
    # text
    draw.text((256, 256), "RideMyCars", fill=(11, 17, 32, 255), anchor="mm")

icon_out = os.path.join(out_dir, "app_icon_512x512.png")
icon_img.save(icon_out, "PNG")
print(f"Generated Icon: {icon_out} ({icon_img.size})")

# 2. Generate 1024x500 Feature Graphic
fg_img = Image.new("RGBA", (1024, 500), (11, 17, 32, 255))
fg_draw = ImageDraw.Draw(fg_img)

# Background subtle gradient / shapes
for y in range(500):
    r = int(11 + (30 - 11) * (y / 500.0))
    g = int(17 + (41 - 17) * (y / 500.0))
    b = int(32 + (59 - 32) * (y / 500.0))
    fg_draw.line([(0, y), (1024, y)], fill=(r, g, b, 255))

# Glowing accent circle
fg_draw.ellipse([700, -100, 1150, 350], fill=(245, 158, 11, 35))
fg_draw.ellipse([60, 250, 400, 600], fill=(59, 130, 246, 25))

# Paste Logo on left side
if os.path.exists(logo_path):
    logo = Image.open(logo_path).convert("RGBA")
    logo_w, logo_h = logo.size
    target_w = 340
    target_h = int(target_w * (logo_h / logo_w))
    logo_resized = logo.resize((target_w, target_h), Image.Resampling.LANCZOS)
    fg_img.paste(logo_resized, (60, 90), logo_resized)

# Feature Badges on right side
badges = [
    ("🚗  ON-DEMAND RIDES & CABS", (245, 158, 11)),
    ("🔑  LUXURY SELF-DRIVE RENTALS", (59, 130, 246)),
    ("👨‍✈️  HOURLY VETTED CHAUFFEURS", (16, 185, 129)),
    ("📦  EXPRESS PARCEL DELIVERY", (168, 85, 247))
]

badge_y = 110
for text, color in badges:
    # Pill box
    fg_draw.rounded_rectangle([480, badge_y, 960, badge_y + 60], radius=16, fill=(30, 41, 59, 230), outline=color, width=2)
    fg_draw.text((510, badge_y + 30), text, fill=(255, 255, 255, 255), anchor="lm")
    badge_y += 75

# Footer text
fg_draw.text((60, 420), "Next-Generation Smart Mobility • All-in-One App", fill=(148, 163, 184, 255), anchor="ls")

fg_out = os.path.join(out_dir, "feature_graphic_1024x500.png")
fg_img.save(fg_out, "PNG")
print(f"Generated Feature Graphic: {fg_out} ({fg_img.size})")

# 3. Process High-Resolution Phone Screenshots
brain_dir = "/Users/manjotsingh/.gemini/antigravity-ide/brain/820f269b-0218-4262-9cc8-93791de30c5f"
screen_mapping = [
    ("live_home.png", "screenshot_1_rides_home.png"),
    ("live_rates_synced_rent_tab_active.png", "screenshot_2_luxury_rentals.png"),
    ("live_driver_tab_screen.png", "screenshot_3_chauffeurs.png"),
    ("live_delivery_step1.png", "screenshot_4_parcel_delivery.png"),
    ("live_rental_voucher_modal_opened.png", "screenshot_5_rental_voucher.png"),
    ("live_activity_correct_tap.png", "screenshot_6_activity_bookings.png")
]

for src_name, dst_name in screen_mapping:
    src_path = os.path.join(brain_dir, src_name)
    if os.path.exists(src_path):
        img = Image.open(src_path)
        dst_path = os.path.join(out_dir, dst_name)
        img.save(dst_path, "PNG")
        print(f"Saved Screenshot: {dst_path} ({img.size})")


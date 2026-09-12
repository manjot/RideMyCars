import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter

out_dir = "/Users/manjotsingh/RideMyCars/playstore_assets"
logo_path = "/Users/manjotsingh/RideMyCars/flutter_app/assets/logo.png"

w, h = 1024, 500
base = Image.new("RGBA", (w, h), (11, 17, 32, 255))
draw_base = ImageDraw.Draw(base)

# 1. Elegant Deep Gradient
for y in range(h):
    ratio = y / float(h)
    r = int(10 + (18 - 10) * ratio)
    g = int(15 + (28 - 15) * ratio)
    b = int(28 + (48 - 28) * ratio)
    draw_base.line([(0, y), (w, y)], fill=(r, g, b, 255))

# 2. Glowing Ambient Lights with Gaussian Blur
glow_layer = Image.new("RGBA", (w, h), (0, 0, 0, 0))
glow_draw = ImageDraw.Draw(glow_layer)

# Amber glow top right
glow_draw.ellipse([600, -100, 1100, 300], fill=(245, 158, 11, 70))
# Blue glow bottom left
glow_draw.ellipse([-50, 200, 450, 600], fill=(59, 130, 246, 60))
# Emerald glow bottom right
glow_draw.ellipse([650, 250, 1050, 550], fill=(16, 185, 129, 45))

glow_blurred = glow_layer.filter(ImageFilter.GaussianBlur(radius=80))
banner = Image.alpha_composite(base, glow_blurred)
draw = ImageDraw.Draw(banner)

# Fonts
font_title = None
font_subtitle = None
font_badge = None

for font_path in [
    "/System/Library/Fonts/Supplemental/Arial Bold.ttf",
    "/System/Library/Fonts/Supplemental/Arial.ttf",
    "/System/Library/Fonts/Helvetica.ttc"
]:
    if os.path.exists(font_path):
        try:
            font_title = ImageFont.truetype(font_path, 22)
            font_subtitle = ImageFont.truetype(font_path, 14)
            font_badge = ImageFont.truetype(font_path, 17)
            break
        except Exception:
            pass

if font_title is None:
    font_title = ImageFont.load_default()
    font_subtitle = ImageFont.load_default()
    font_badge = ImageFont.load_default()

# 3. Logo Placement
if os.path.exists(logo_path):
    logo = Image.open(logo_path).convert("RGBA")
    logo_w, logo_h = logo.size
    target_w = 320
    target_h = int(target_w * (logo_h / float(logo_w)))
    logo_resized = logo.resize((target_w, target_h), Image.Resampling.LANCZOS)
    banner.paste(logo_resized, (65, 80), logo_resized)

# Left Side Typography
draw.text((65, 335), "ALL-IN-ONE SMART MOBILITY", font=font_title, fill=(245, 158, 11, 255))
draw.text((65, 375), "Rides • Luxury Rentals • Chauffeurs • Courier Delivery", font=font_subtitle, fill=(226, 232, 240, 255))
draw.text((65, 405), "Guaranteed Fares • 24/7 Support • Global Currency Sync", font=font_subtitle, fill=(148, 163, 184, 255))

# 4. Right Side Feature Badges
badges = [
    ("ON-DEMAND CAB RIDES", (245, 158, 11)),
    ("LUXURY FLEET RENTALS", (59, 130, 246)),
    ("VETTED CHAUFFEURS", (16, 185, 129)),
    ("EXPRESS PARCEL DELIVERY", (168, 85, 247))
]

badge_y = 65
for text, color in badges:
    # Card background
    draw.rounded_rectangle([530, badge_y, 960, badge_y + 72], radius=16, fill=(24, 33, 52, 235), outline=color, width=2)
    # Indicator dot
    draw.ellipse([555, badge_y + 26, 575, badge_y + 46], fill=color)
    # Badge text
    draw.text((590, badge_y + 36), text, font=font_badge, fill=(255, 255, 255, 255), anchor="lm")
    badge_y += 95

fg_out = os.path.join(out_dir, "feature_graphic_1024x500.png")
banner.save(fg_out, "PNG")
print(f"Generated Feature Graphic: {fg_out}")

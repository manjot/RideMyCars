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
        "/System/Library/Fonts/Avenir Next.ttc",
        "/System/Library/Fonts/HelveticaNeue.ttc",
        "/System/Library/Fonts/Supplemental/Arial Bold.ttf" if bold else "/System/Library/Fonts/Supplemental/Arial.ttf",
        "/Library/Fonts/Arial.ttf"
    ]
    for p in font_paths:
        if os.path.exists(p):
            try:
                return ImageFont.truetype(p, size, index=1 if bold else 0)
            except Exception:
                try:
                    return ImageFont.truetype(p, size)
                except Exception:
                    continue
    return ImageFont.load_default()

# --- 1. Generate 1024x1024 Driver App Store Icon (RGB, No Alpha) ---
print("Generating 1024x1024 iOS Driver App Store Icon (RGB)...")
icon_canvas = Image.new("RGB", (1024, 1024), (11, 17, 32))
draw = ImageDraw.Draw(icon_canvas)

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

draw.rounded_rectangle([250, 840, 774, 930], radius=24, fill=(245, 158, 11))
font_badge = get_font(42, bold=True)
draw.text((512, 885), "DRIVER PARTNER", fill=(11, 17, 32), font=font_badge, anchor="mm")

ios_icon_path = os.path.join(ios_dir, "driver_app_store_icon_1024x1024.png")
icon_canvas.save(ios_icon_path, "PNG")
print(f"Saved: {ios_icon_path}")

play_icon = icon_canvas.resize((512, 512), Image.Resampling.LANCZOS)
play_icon_path = os.path.join(android_dir, "driver_play_icon_512x512.png")
play_icon.save(play_icon_path, "PNG")
print(f"Saved: {play_icon_path}")


# --- 2. Native iOS UI Decorators ---

def draw_ios_status_bar(draw, width, bar_height, is_dark_bg=True, has_island=True):
    fg_color = (255, 255, 255) if is_dark_bg else (20, 20, 20)
    font_time = get_font(max(14, int(bar_height * 0.36)), bold=True)

    time_x = int(width * 0.09)
    time_y = bar_height // 2
    draw.text((time_x, time_y), "9:41", fill=fg_color, font=font_time, anchor="mm")
    
    if has_island:
        iw = int(width * 0.28)
        ih = int(bar_height * 0.58)
        ix = (width - iw) // 2
        iy = (bar_height - ih) // 2
        draw.rounded_rectangle([ix, iy, ix + iw, iy + ih], radius=ih // 2, fill=(0, 0, 0))
        cam_r = int(ih * 0.22)
        cam_cx = ix + iw - int(ih * 0.5)
        cam_cy = iy + ih // 2
        draw.ellipse([cam_cx - cam_r, cam_cy - cam_r, cam_cx + cam_r, cam_cy + cam_r], fill=(15, 23, 42))

    rx = width - int(width * 0.08)
    
    bw = int(bar_height * 0.50)
    bh = int(bar_height * 0.26)
    by = (bar_height - bh) // 2
    bx = rx - bw
    draw.rounded_rectangle([bx, by, bx + bw, by + bh], radius=max(2, bh // 4), outline=fg_color, width=max(1, int(bar_height * 0.025)))
    cap_w = max(2, int(bw * 0.08))
    cap_h = int(bh * 0.45)
    cap_y = by + (bh - cap_h) // 2
    draw.rounded_rectangle([bx + bw, cap_y, bx + bw + cap_w, cap_y + cap_h], radius=1, fill=fg_color)
    fill_pad = max(2, int(bh * 0.15))
    fill_w = int((bw - fill_pad * 2) * 0.88)
    draw.rounded_rectangle([bx + fill_pad, by + fill_pad, bx + fill_pad + fill_w, by + bh - fill_pad], radius=max(1, bh // 6), fill=(52, 199, 89))

    wx = bx - int(bar_height * 0.52)
    wy = bar_height // 2
    dot_r = max(2, int(bar_height * 0.035))
    draw.ellipse([wx - dot_r, wy + int(bar_height * 0.08) - dot_r, wx + dot_r, wy + int(bar_height * 0.08) + dot_r], fill=fg_color)
    draw.arc([wx - int(bar_height * 0.13), wy - int(bar_height * 0.06), wx + int(bar_height * 0.13), wy + int(bar_height * 0.16)], start=210, end=330, fill=fg_color, width=max(1, int(bar_height * 0.028)))
    draw.arc([wx - int(bar_height * 0.23), wy - int(bar_height * 0.18), wx + int(bar_height * 0.23), wy + int(bar_height * 0.24)], start=210, end=330, fill=fg_color, width=max(1, int(bar_height * 0.028)))

    cx = wx - int(bar_height * 0.48)
    bar_w = max(2, int(bar_height * 0.045))
    bar_gap = max(1, int(bar_height * 0.022))
    for i in range(4):
        b_h = int(bar_height * (0.10 + i * 0.055))
        b_y = by + bh - b_h
        b_x = cx + i * (bar_w + bar_gap)
        draw.rounded_rectangle([b_x, b_y, b_x + bar_w, by + bh], radius=1, fill=fg_color)

def draw_ios_home_indicator(draw, width, height, is_dark_bg=True):
    bar_w = int(width * 0.36)
    bar_h = max(4, int(width * 0.012))
    bx = (width - bar_w) // 2
    by = height - max(12, int(width * 0.03))
    color = (255, 255, 255, 200) if is_dark_bg else (0, 0, 0, 200)
    draw.rounded_rectangle([bx, by, bx + bar_w, by + bar_h], radius=bar_h // 2, fill=color)

def prepare_clean_ios_screen(src_path, is_ipad=False):
    if not os.path.exists(src_path):
        return None
    raw = Image.open(src_path).convert("RGBA")
    sw, sh = raw.size
    
    top_crop = int(sh * 0.045)
    bottom_crop = int(sh * 0.038)
    cropped = raw.crop((0, top_crop, sw, sh - bottom_crop))
    
    top_color = cropped.getpixel((sw // 2, 10))
    if len(top_color) == 4:
        top_color = (top_color[0], top_color[1], top_color[2])
    
    bot_color = cropped.getpixel((sw // 2, cropped.height - 10))
    if len(bot_color) == 4:
        bot_color = (bot_color[0], bot_color[1], bot_color[2])
    
    cw, ch = cropped.size
    ios_bar_h = int(cw * (0.11 if not is_ipad else 0.05))
    ios_home_h = int(cw * (0.05 if not is_ipad else 0.03))
    
    new_h = ch + ios_bar_h + ios_home_h
    clean_canvas = Image.new("RGBA", (cw, new_h), top_color + (255,))
    
    bot_draw = ImageDraw.Draw(clean_canvas)
    bot_draw.rectangle([0, new_h - ios_home_h - 20, cw, new_h], fill=bot_color + (255,))
    
    clean_canvas.paste(cropped, (0, ios_bar_h), cropped)
    
    draw = ImageDraw.Draw(clean_canvas)
    is_dark = (top_color[0] * 0.299 + top_color[1] * 0.587 + top_color[2] * 0.114) < 140
    
    draw_ios_status_bar(draw, cw, ios_bar_h, is_dark_bg=is_dark, has_island=(not is_ipad))
    draw_ios_home_indicator(draw, cw, new_h, is_dark_bg=is_dark)
    
    return clean_canvas


# --- 3. Process Driver Framed Screenshots ---
driver_screens = [
    ("screencap_driver.png", "1_incoming_requests", "DISPATCH & REQUESTS", "Accept Live Rides, Rentals & Delivery", "Real-time trip dispatch with transparent pickup distance & passenger details"),
    ("screencap_rent.png", "2_earnings_tracking", "EARNINGS & PAYOUTS", "Live Earnings & Instant Fast Payouts", "Track daily trip revenue, competitive commission rates and wallet balance"),
    ("screenshot_3_chauffeurs.png", "3_hourly_chauffeur", "HOURLY CHAUFFEUR", "Earn With Hourly Chauffeur Bookings", "Flexible schedule and premium rates for executive & daily driving services"),
    ("screenshot_1_rides_home.png", "4_smart_navigation", "PRECISION NAVIGATION", "High-Accuracy Turn-by-Turn GPS", "Built-in smart navigation with optimal route optimization and traffic detection"),
    ("screenshot_6_activity_bookings.png", "5_trip_history", "TRIP LOGS & RATINGS", "Complete Trip Records & 5-Star Ratings", "Full breakdown of completed rides, verified customer reviews & weekly totals")
]

def create_driver_screenshot(src_path, target_size, badge_text, headline, subtitle, is_rgb=True, is_ipad=False):
    tw, th = target_size
    mode = "RGB" if is_rgb else "RGBA"
    bg = (11, 17, 32) if is_rgb else (11, 17, 32, 255)
    canvas = Image.new(mode, (tw, th), bg)
    draw = ImageDraw.Draw(canvas)

    # Emerald driver accent gradient
    for y in range(int(th * 0.45)):
        fac = y / float(th * 0.45)
        r = int(16 * 0.15 * (1 - fac) + 11)
        g = int(185 * 0.15 * (1 - fac) + 17)
        b = int(129 * 0.15 * (1 - fac) + 32)
        draw.line([(0, y), (tw, y)], fill=(r, g, b) if is_rgb else (r, g, b, 255))

    header_top = int(th * 0.035)

    # 1. Badge Pill
    font_badge = get_font(max(20, int(tw * 0.024)), bold=True)
    badge_pad_x = int(tw * 0.03)
    badge_pad_y = int(tw * 0.012)
    badge_bbox = font_badge.getbbox(badge_text)
    badge_w = badge_bbox[2] - badge_bbox[0] + badge_pad_x * 2
    badge_h = badge_bbox[3] - badge_bbox[1] + badge_pad_y * 2
    badge_x = (tw - badge_w) // 2
    badge_y = header_top
    
    draw.rounded_rectangle([badge_x, badge_y, badge_x + badge_w, badge_y + badge_h], radius=badge_h // 2, fill=(24, 33, 47) if is_rgb else (24, 33, 47, 255), outline=(16, 185, 129) if is_rgb else (16, 185, 129, 255), width=max(2, int(tw * 0.002)))
    draw.text((tw // 2, badge_y + badge_h // 2), badge_text, fill=(16, 185, 129) if is_rgb else (16, 185, 129, 255), font=font_badge, anchor="mm")

    # 2. Main Headline
    font_head = get_font(max(32, int(tw * 0.046)), bold=True)
    head_y = badge_y + badge_h + int(th * 0.022)
    draw.text((tw // 2, head_y), headline, fill=(255, 255, 255) if is_rgb else (255, 255, 255, 255), font=font_head, anchor="mm")

    # 3. Subtitle
    font_sub = get_font(max(20, int(tw * 0.025)), bold=False)
    sub_y = head_y + int(th * 0.024)
    draw.text((tw // 2, sub_y), subtitle, fill=(148, 163, 184) if is_rgb else (148, 163, 184, 255), font=font_sub, anchor="mm")

    clean_screen = prepare_clean_ios_screen(src_path, is_ipad=is_ipad)
    if clean_screen:
        sw, sh = clean_screen.size
        top_offset = sub_y + int(th * 0.03)
        avail_w = int(tw * (0.86 if not is_ipad else 0.88))
        avail_h = th - top_offset - int(th * 0.03)
        
        scale = min(avail_w / sw, avail_h / sh)
        nw, nh = int(sw * scale), int(sh * scale)
        
        resized = clean_screen.resize((nw, nh), Image.Resampling.LANCZOS)
        
        paste_x = (tw - nw) // 2
        paste_y = top_offset + (avail_h - nh) // 2
        
        frame_pad = max(4, int(tw * 0.008))
        bezel_radius = max(24, int(nw * 0.08))
        draw.rounded_rectangle(
            [paste_x - frame_pad, paste_y - frame_pad, paste_x + nw + frame_pad, paste_y + nh + frame_pad],
            radius=bezel_radius,
            fill=(30, 41, 59) if is_rgb else (30, 41, 59, 255),
            outline=(16, 185, 129) if is_rgb else (16, 185, 129, 255),
            width=max(2, int(tw * 0.003))
        )
        
        mask = Image.new("L", (nw, nh), 0)
        mask_draw = ImageDraw.Draw(mask)
        mask_draw.rounded_rectangle([0, 0, nw, nh], radius=bezel_radius - frame_pad, fill=255)
        
        if is_rgb:
            canvas.paste(resized.convert("RGB"), (paste_x, paste_y), mask)
        else:
            canvas.paste(resized, (paste_x, paste_y), mask)

    return canvas

dim_list = [
    (os.path.join(ios_dir, "iphone_6_7_inch"), (1290, 2796), True, False, "iOS 6.7\""),
    (os.path.join(ios_dir, "iphone_6_5_inch"), (1242, 2688), True, False, "iOS 6.5\""),
    (os.path.join(ios_dir, "iphone_5_5_inch"), (1242, 2208), True, False, "iOS 5.5\""),
    (os.path.join(ios_dir, "ipad_12_9_inch"), (2048, 2732), True, True, "iOS 12.9\" iPad"),
    (os.path.join(android_dir, "phone"), (1080, 2400), False, False, "Android Phone"),
    (os.path.join(android_dir, "tablet_7_inch"), (1200, 1920), False, True, "Android 7\" Tablet"),
    (os.path.join(android_dir, "tablet_10_inch"), (1600, 2560), False, True, "Android 10\" Tablet")
]

for src_file, prefix, badge_txt, headline, subtitle in driver_screens:
    src_p = os.path.join(base_dir, src_file)
    if not os.path.exists(src_p):
        src_p = os.path.join(base_dir, "playstore_assets", src_file)
    if not os.path.exists(src_p):
        continue
    for out_sub, size, rgb, is_ipad_mode, name in dim_list:
        scr = create_driver_screenshot(src_p, size, badge_txt, headline, subtitle, is_rgb=rgb, is_ipad=is_ipad_mode)
        sp = os.path.join(out_sub, f"{prefix}.png")
        scr.save(sp, "PNG")
        print(f"Generated {name}: {sp}")

print("\nDriver Store Graphics Generated Successfully!")

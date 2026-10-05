import os
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from PIL import Image

svg_dir = r"c:\Users\HP FQ1092AU\OneDrive\Desktop\RideMyCars\laravel_web\public\images\payment-icons"
out_dir = r"c:\Users\HP FQ1092AU\OneDrive\Desktop\RideMyCars\flutter_app\assets\images\payment-icons"
os.makedirs(out_dir, exist_ok=True)

opts = Options()
opts.add_argument("--headless")
opts.add_argument("--disable-gpu")
opts.add_argument("--window-size=1200,800")
opts.add_argument("--force-device-scale-factor=3")

driver = webdriver.Chrome(options=opts)

files = [
    ("visa.svg", "visa.png"),
    ("mastercard.svg", "mastercard.png"),
    ("discover.svg", "discover.png"),
    ("amex.svg", "amex.png"),
    ("apple-pay.svg", "apple_pay.png"),
    ("mtn-momo.svg", "mtn_momo.png"),
    ("telecel.svg", "telecel.png"),
    ("airteltigo.svg", "airteltigo.png"),
]

for svg_name, png_name in files:
    svg_path = os.path.join(svg_dir, svg_name)
    with open(svg_path, "r", encoding="utf-8") as f:
        svg_content = f.read()

    # Create HTML with transparent background and padded container
    html_content = f"""<!DOCTYPE html>
<html>
<head>
<style>
* {{ margin: 0; padding: 0; box-sizing: border-box; }}
body {{ background: transparent; padding: 4px; display: inline-block; }}
#badge {{ display: inline-block; }}
</style>
</head>
<body>
<div id="badge">{svg_content}</div>
</body>
</html>"""

    temp_html = os.path.join(out_dir, "_temp.html")
    with open(temp_html, "w", encoding="utf-8") as tf:
        tf.write(html_content)

    driver.get("file:///" + temp_html.replace("\\", "/"))
    badge = driver.find_element("id", "badge")
    png_bytes = badge.screenshot_as_png

    out_path = os.path.join(out_dir, png_name)
    with open(out_path, "wb") as pf:
        pf.write(png_bytes)

    if os.path.exists(temp_html):
        os.remove(temp_html)

    im = Image.open(out_path)
    print(f"Rendered {png_name}: size={im.size} mode={im.mode}")

driver.quit()
print("All payment icons rendered successfully!")

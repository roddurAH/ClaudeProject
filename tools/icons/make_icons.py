"""Resize the masters rendered by render.mjs (./out) into every platform's icon folder.

Usage: node render.mjs && python3 make_icons.py   (needs Pillow: pip install pillow)
"""
import os
import re

from PIL import Image

HERE = os.path.dirname(os.path.abspath(__file__))
SRC = os.path.join(HERE, 'out')
APP = os.path.normpath(os.path.join(HERE, '../../mobile'))
DOCS = os.path.normpath(os.path.join(HERE, '../../docs/play-store'))

master = {n: Image.open(f'{SRC}/{n}.png').convert('RGBA') for n in ('full', 'rounded', 'fg', 'bg', 'maskable')}


def resized(name, px):
    return master[name].resize((px, px), Image.LANCZOS)


def save_rgb(img, path):
    """Flatten onto the gradient's dark green (no alpha: required by iOS / Play)."""
    flat = Image.new('RGB', img.size, (27, 94, 32))
    flat.paste(img, mask=img.split()[3])
    flat.save(path, optimize=True)


# Android: legacy launcher + adaptive (API 26+) with themed-icon monochrome
res = f'{APP}/android/app/src/main/res'
for dpi, scale in {'mdpi': 1, 'hdpi': 1.5, 'xhdpi': 2, 'xxhdpi': 3, 'xxxhdpi': 4}.items():
    d = f'{res}/mipmap-{dpi}'
    os.makedirs(d, exist_ok=True)
    resized('rounded', round(48 * scale)).save(f'{d}/ic_launcher.png', optimize=True)
    resized('fg', round(108 * scale)).save(f'{d}/ic_launcher_foreground.png', optimize=True)
    save_rgb(resized('bg', round(108 * scale)), f'{d}/ic_launcher_background.png')
os.makedirs(f'{res}/mipmap-anydpi-v26', exist_ok=True)
with open(f'{res}/mipmap-anydpi-v26/ic_launcher.xml', 'w') as f:
    f.write('''<?xml version="1.0" encoding="utf-8"?>
<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android">
    <background android:drawable="@mipmap/ic_launcher_background" />
    <foreground android:drawable="@mipmap/ic_launcher_foreground" />
    <monochrome android:drawable="@mipmap/ic_launcher_foreground" />
</adaptive-icon>
''')

# iOS: full-bleed, no alpha; size comes from the file name (e.g. Icon-App-20x20@3x.png → 60px)
ios = f'{APP}/ios/Runner/Assets.xcassets/AppIcon.appiconset'
for fn in os.listdir(ios):
    m = re.match(r'Icon-App-([\d.]+)x[\d.]+@(\d)x\.png', fn)
    if m:
        save_rgb(resized('full', round(float(m[1]) * int(m[2]))), f'{ios}/{fn}')

# macOS: rounded square inset to Apple's 824/1024 content grid
mac = f'{APP}/macos/Runner/Assets.xcassets/AppIcon.appiconset'
canvas = Image.new('RGBA', (1024, 1024), (0, 0, 0, 0))
inner = round(1024 * 824 / 944)
canvas.paste(resized('rounded', inner), ((1024 - inner) // 2,) * 2)
for fn in os.listdir(mac):
    m = re.match(r'app_icon_(\d+)\.png', fn)
    if m:
        canvas.resize((int(m[1]),) * 2, Image.LANCZOS).save(f'{mac}/{fn}', optimize=True)

# Web
resized('rounded', 32).save(f'{APP}/web/favicon.png', optimize=True)
for px in (192, 512):
    resized('rounded', px).save(f'{APP}/web/icons/Icon-{px}.png', optimize=True)
    save_rgb(resized('maskable', px), f'{APP}/web/icons/Icon-maskable-{px}.png')
save_rgb(resized('full', 180), f'{APP}/web/icons/apple-touch-icon.png')

# Windows
master['rounded'].save(f'{APP}/windows/runner/resources/app_icon.ico',
                       sizes=[(16, 16), (24, 24), (32, 32), (48, 48), (64, 64), (128, 128), (256, 256)])

# Play Store listing assets
os.makedirs(DOCS, exist_ok=True)
save_rgb(resized('full', 512), f'{DOCS}/icon-512.png')
save_rgb(Image.open(f'{SRC}/feature-graphic.png').convert('RGBA'), f'{DOCS}/feature-graphic-1024x500.png')
print('done')

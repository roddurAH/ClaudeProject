# App icon and Play Store graphics

The icon (a seedling and sun on a green gradient) is drawn as SVG in `render.mjs`.
One command renders it and resizes it into every platform folder:

```bash
cd tools/icons
npm install                 # Playwright (uses its Chromium to render the SVG)
pip install pillow          # resizing
npm run render              # = node render.mjs && python3 make_icons.py
```

| Output | Where |
|---|---|
| Android launcher (legacy + adaptive + Android 13 themed) | `mobile/android/app/src/main/res/mipmap-*` |
| iOS / macOS app icons | `mobile/ios/…/AppIcon.appiconset`, `mobile/macos/…/AppIcon.appiconset` |
| Web favicon, PWA and maskable icons, apple-touch-icon | `mobile/web/` |
| Windows icon | `mobile/windows/runner/resources/app_icon.ico` |
| Play Store icon (512) and feature graphic (1024×500) | `docs/play-store/` |

To change the design, edit the SVG in `render.mjs` (keep the plant inside the central circle, so
Android's round masks don't cut it) and run the command again. Masters are written to `./out` (git-ignored).

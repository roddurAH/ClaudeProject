# Browser walkthrough (end-to-end check)

`walkthrough.mjs` drives the **Flutter web build** and the **Laravel admin panel**
in a real browser, exactly like a user: onboarding → crop doctor → prices →
weather → fertilizer calculator → register and ask an expert → the expert
answers in the admin panel → the farmer reads the answer → My crops.
It saves screenshots to `./screenshots` and exits non-zero if a step fails.

```bash
# 1. Backend with fresh demo data
cd backend
php artisan migrate:fresh --seed
php artisan serve                       # http://127.0.0.1:8000

# 2. Web build of the app, served on port 8080
cd mobile
flutter build web --no-web-resources-cdn --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
cd build/web && python -m http.server 8080

# 3. Run the walkthrough
cd tools/e2e
npm install && npx playwright install chromium
npm run walkthrough
```

Notes
* Weather needs internet access to Open-Meteo; without it the weather screen
  shows the "temporarily unavailable" state (that is expected behaviour).
* Run `migrate:fresh --seed` again before a re-run, or set `TEST_PHONE` to a
  number that is not registered yet.

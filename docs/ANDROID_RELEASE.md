# Publishing the Android app on Google Play

This guide takes you from the source code to a signed **Android App Bundle (`.aab`)** on Google Play.
Play only accepts app bundles for new apps; an `.apk` is still useful to install on test phones.

| | |
|---|---|
| App ID (package name) | **`com.krishisathi.app`**: permanent. It can never change after the first upload |
| Version | `mobile/pubspec.yaml` → `version: 1.0.0+1` (name `1.0.0`, version code `1`) |
| Supports | Android 7.0 (API 24) and newer; targets API 36; 16 KB page-size ready |
| Store texts, Data safety answers, graphics | [`play-store/LISTING.md`](play-store/LISTING.md) and the `play-store/` folder |

---

## Test APK for your own phone (no Play Store)

Every push that changes `mobile/` runs the **Android test APK** workflow on GitHub ([`.github/workflows/android-test-apk.yml`](../.github/workflows/android-test-apk.yml)).
It builds `krishi-sathi-test.apk` and publishes it as the **`android-test` pre-release**:

1. On the phone, open the repository on GitHub → **Releases** → *Krishi Sathi – test APK* → tap `krishi-sathi-test.apk`.
2. Open the download and allow **Install unknown apps** for your browser when Android asks.
3. A newer test build is signed with a different throw-away key, so **uninstall the old test app first**.

To build one by hand: GitHub → **Actions → Android test APK → Run workflow** (you can also enter a server address to build into the app).
On your own computer: `KRISHI_ALLOW_HTTP=true flutter build apk --release` in `mobile/` (Windows: `set KRISHI_ALLOW_HTTP=true` first) → `build/app/outputs/flutter-apk/app-release.apk`.

**Connect the test app to the backend on your laptop (XAMPP):**
1. Phone and laptop on the **same Wi-Fi**.
2. In `backend/`: `php artisan serve --host=0.0.0.0 --port=8000`. Find the laptop's IP with `ipconfig` (e.g. `192.168.0.10`) and allow port 8000 in Windows Firewall.
3. In the app: **Settings → Server address (advanced)** → `http://192.168.0.10:8000/api/v1` → Save.

Without a server the app still works offline (crop doctor, calendar, calculator, articles). Prices, weather, alerts and *Ask an expert* need the server.
Test APKs allow plain `http` and use a debug key, so **never upload them to Google Play**.

---

## 0. Before you build: checklist

The published app talks to **your server over HTTPS**. Release builds block plain `http`, and they have **no built-in server address** unless you give one at build time. Without one the app works offline only, until the user types an address in Settings.

- [ ] Backend deployed on a server with a domain and HTTPS (see [PROJECT_PLAN.md §12.2](../PROJECT_PLAN.md#122-deployment)).
      Check that `https://YOUR-DOMAIN/api/v1/meta` returns JSON.
- [ ] In the server's `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://YOUR-DOMAIN`,
      **`KRISHI_OPERATOR_NAME`** (your organisation) and **`KRISHI_SUPPORT_EMAIL`** (shown on the privacy and account-deletion pages). Then `php artisan config:cache`.
- [ ] Open `https://YOUR-DOMAIN/privacy` and `https://YOUR-DOMAIN/account/delete`. Google checks both links.
- [ ] Demo passwords changed, demo prices removed (see [ADMIN_GUIDE.md §2](ADMIN_GUIDE.md)), content reviewed by an agronomist.
- [ ] A **test farmer account** on the production server for the Play reviewer (phone + PIN).
- [ ] Refresh the knowledge base built into the app (with `APP_URL` set to production):
      `cd backend && php artisan krishi:export-content`, then commit `mobile/assets/seed/`.
- [ ] Version: for every upload, raise the number after `+` in `mobile/pubspec.yaml` (`1.0.0+1` → `1.0.1+2`).
      Also update `AppConfig.appVersion` in `mobile/lib/core/config/app_config.dart` (shown in Settings → About).

## 1. Tools

* **Flutter 3.47+** (`flutter doctor` must show a working Android toolchain).
* **Android Studio** (it installs the Android SDK, platform 36 and build tools on first start; accept the licences with `flutter doctor --android-licenses`).
* JDK 17 or newer: the one bundled with Android Studio is fine (`keytool` lives in its `jbr/bin` folder).
* A **Google Play developer account** (one-time US$25 fee, identity verification): <https://play.google.com/console/signup>.

## 2. Create your upload key (once)

The upload key proves that new versions come from you. **Back it up** (the `.jks` file and both passwords) in two safe places. Without it you can't publish updates until Google resets the key, which takes days.

Windows (PowerShell or cmd):
```bat
keytool -genkey -v -keystore %USERPROFILE%\krishi-sathi-upload.jks -storetype JKS -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```
macOS / Linux:
```bash
keytool -genkey -v -keystore ~/krishi-sathi-upload.jks -storetype JKS -keyalg RSA -keysize 2048 -validity 10000 -alias upload
```
Answer the questions (name, organisation, country code `BD`). Keep the file **outside the project folder**.

Create **`mobile/android/key.properties`** (it is in `.gitignore`, so never commit it):
```properties
storePassword=the-keystore-password
keyPassword=the-key-password
keyAlias=upload
storeFile=C:/Users/YOU/krishi-sathi-upload.jks
```
Use forward slashes `/` in the path, even on Windows (a single `\` is treated as an escape character).

Google Play then re-signs your app with its own **app signing key** (Play App Signing, on by default). You only ever handle the upload key.

## 3. Build

```bash
cd mobile
flutter clean
flutter pub get
flutter test                       # all tests must pass

# Play Store bundle
flutter build appbundle --release --dart-define=API_BASE_URL=https://YOUR-DOMAIN/api/v1
# → build/app/outputs/bundle/release/app-release.aab

# APKs to install directly on test phones (one per CPU type; most phones use arm64-v8a)
flutter build apk --release --split-per-abi --dart-define=API_BASE_URL=https://YOUR-DOMAIN/api/v1
# → build/app/outputs/flutter-apk/app-arm64-v8a-release.apk, app-armeabi-v7a-release.apk, app-x86_64-release.apk
```

**Check the signature** before uploading. It must show *your* certificate, not `CN=Android Debug`:
```bash
keytool -printcert -jarfile build/app/outputs/bundle/release/app-release.aab
```
If `key.properties` is missing, the build prints `⚠ android/key.properties not found` and is signed with the debug key. That build is fine for test phones but Play rejects it.

### Signing a bundle that was built elsewhere

If someone else builds for you (for example a CI server), they can produce an **unsigned** bundle that only you sign:
```bash
KRISHI_UNSIGNED_RELEASE=true flutter build appbundle --release --dart-define=API_BASE_URL=https://YOUR-DOMAIN/api/v1
```
You then sign it with your upload key (on Windows use `set KRISHI_UNSIGNED_RELEASE=true` before the build):
```bash
jarsigner -keystore ~/krishi-sathi-upload.jks -signedjar app-release-signed.aab app-release.aab upload
keytool -printcert -jarfile app-release-signed.aab      # verify
```

## 4. Test on real phones before uploading

Install with `adb install build/app/outputs/flutter-apk/app-arm64-v8a-release.apk` (or copy the APK to the phone and open it).
Test on at least one **cheap phone (2 GB RAM, Android 8–10)** and one recent phone, with the system font set to the largest size:

- [ ] First start in **flight mode**: onboarding, crop doctor, calculator and articles work offline.
- [ ] Online: prices for your district, weather advice and alerts load; Settings → Server address shows your HTTPS URL.
- [ ] Register, ask a question **with a photo**; answer it in the admin panel; the answer appears in the app.
- [ ] Settings → **Privacy policy** opens in the browser; Settings → **Delete account** works with the PIN.
- [ ] Read-aloud works (needs the Google TTS Bangla voice on the phone).

## 5. Play Console: first release

1. **Create app**: name `কৃষি সাথী – Krishi Sathi`, default language **Bengali (bn-BD)**, App, Free. Accept the declarations.
2. **Set up your app → App content**: fill in every section with the answers in [`play-store/LISTING.md`](play-store/LISTING.md#app-content-policy--app-content):
   privacy policy URL, ads (no), app access (reviewer login), content rating, target audience (18+), Data safety, and **account deletion URL** (`https://YOUR-DOMAIN/account/delete`).
3. **Main store listing**: short and full description (Bangla, plus an English translation), icon `play-store/icon-512.png`, feature graphic `play-store/feature-graphic-1024x500.png`, phone screenshots `play-store/screenshots/`.
4. **Testing → Internal testing → Create release**: upload `app-release.aab`, add release notes, add yourself and colleagues as testers, roll out. Install from the opt-in link and repeat the checks from step 4.
5. **Closed testing** with pilot farmers and extension workers.
   *New personal developer accounts* must run a closed test with **at least 12 testers for 14 days** before they can apply for production (organisation accounts are exempt). Check the current rule in Play Console → Dashboard.
6. **Production → Create release** → upload (or promote) the bundle → **Send for review**. The first review usually takes a few days.

## 6. Updates

1. Raise the version in `pubspec.yaml` (e.g. `1.0.1+2`) and `AppConfig.appVersion`.
2. If crops, problems or articles changed: `php artisan krishi:export-content` and commit.
3. `flutter build appbundle --release --dart-define=API_BASE_URL=https://YOUR-DOMAIN/api/v1`
4. Play Console → Production (or a testing track first) → Create release → upload → review → roll out (a staged rollout of 10–20% is safest).

Content, prices and alerts change on the server and **need no app update**. If an API change breaks old versions, raise `KRISHI_MIN_APP_VERSION` in the backend `.env`: older apps then show a "Please update the app" banner on Home with a button to the Play Store.

## Troubleshooting

| Problem | Fix |
|---|---|
| Play: *"You uploaded an APK or Android App Bundle that was signed in debug mode"* | `mobile/android/key.properties` is missing or wrong. See step 2 and check with `keytool -printcert` |
| Play: *"Version code 1 has already been used"* | Raise the number after `+` in `pubspec.yaml` |
| Play: *"signed with the wrong key"* | You used a different keystore than for the first upload. Use the original, or ask Google to reset the upload key (Play Console → App integrity) |
| App shows no prices or weather, Settings → Server address is "—" | The bundle was built without `--dart-define=API_BASE_URL=…`. Rebuild |
| Works in debug, "network error" in the release app | The release app needs **https**. Plain `http` is blocked, and the certificate must be valid (Let's Encrypt is fine) |
| `Keystore file not found` | The `storeFile` path in `key.properties` is wrong; use an absolute path with `/` |
| *App not installed* when installing a new APK over an old one | The old app was signed with another key (e.g. a debug build). Uninstall it first |

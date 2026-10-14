# Krishi Sathi API (v1)

Base URL: `http://localhost:8000/api/v1` (development) · production: `https://<your-domain>/api/v1`

* All responses are JSON in **raw UTF-8**, **gzip-compressed** when the client sends `Accept-Encoding: gzip`.
* Send `Accept: application/json` and `Accept-Language: bn` (or `en`). Error and validation messages come back in that language.
* User-facing texts are returned in **both languages** (`*_bn`, `*_en`), so the app can switch language offline.
* Authenticated endpoints need `Authorization: Bearer <token>` (Laravel Sanctum, valid for 180 days).
* Dates are ISO-8601 in Asia/Dhaka time.

## Errors

| Status | When | Body |
|---|---|---|
| 401 | Missing or expired token | `{"message": "Unauthenticated."}` |
| 403 | Account disabled | `{"message": "This account has been disabled."}` |
| 404 | Not found, or a private question | `{"message": "…"}` |
| 422 | Validation failed | see below |
| 429 | Rate limit or PIN lockout | `{"message": "Too Many Attempts."}` or a validation-style `errors.phone` |
| 503 | Weather provider down and nothing cached | `{"message": "Weather service is temporarily unavailable…"}` |

```json
{
  "message": "The name field must be at least 2 characters. (and 2 more errors)",
  "errors": {
    "name":  ["The name field must be at least 2 characters."],
    "phone": ["Enter a valid Bangladeshi mobile number (01XXXXXXXXX)."],
    "pin":   ["PIN must be 4 to 6 digits."]
  }
}
```

## Rate limits

| Scope | Limit |
|---|---|
| All `/api/v1` | 120 requests / minute per user or IP |
| `auth/register`, `auth/login` | 10 / minute per IP, **plus** 5 wrong PINs per phone → 15-minute lockout |
| `POST /questions` | 15 / hour per user |
| `/weather` | 30 / minute per IP |

---

## App bootstrap

### `GET /meta`
```json
{
  "app_name": "Krishi Sathi",
  "helpline_number": "16123",
  "min_app_version": "1.0.0",
  "content_version": "08e2a95e8c83f10d",
  "units": { "decimal_per_hectare": 247.105, "decimal_per_acre": 100, "decimal_per_bigha": 33, "decimal_per_katha": 1.65 },
  "server_time": "2026-10-14T18:52:26+06:00"
}
```

### `GET /districts`
```json
{ "data": [ { "id": 45, "division_id": 6, "division_bn": "রাজশাহী", "division_en": "Rajshahi",
              "name_bn": "বগুড়া", "name_en": "Bogura", "lat": 24.8465, "lon": 89.3773 } ] }
```

---

## Knowledge base

### `GET /content`
The whole offline knowledge base. Send the version you have as `If-None-Match: "<version>"`. If nothing changed the answer is **`304 Not Modified`** with an empty body.

```json
{
  "version": "08e2a95e8c83f10d",
  "generated_at": "2026-10-14T18:00:00+06:00",
  "crops": [
    {
      "id": 10, "slug": "tomato", "name_bn": "টমেটো", "name_en": "Tomato", "category": "vegetable",
      "icon": "🍅", "image_url": null, "season": "rabi",
      "sowing_months": [9, 10, 11], "harvest_months": [12, 1, 2, 3], "duration_days": 120,
      "is_garden_friendly": true,
      "summary_bn": "…", "summary_en": "…",
      "cultivation_bn": "জাত: …\n…", "cultivation_en": "Varieties: …\n…",
      "stages": [ { "id": 70, "day_offset": 0, "stage_type": "sowing", "title_bn": "চারা রোপণ", "title_en": "Transplanting", "details_bn": "…", "details_en": "…" } ],
      "problems": [ { "id": 31, "crop_id": 10, "type": "disease", "affected_part": "leaf", "severity": "high",
                      "name_bn": "পাতা কোঁকড়ানো ভাইরাস", "name_en": "Tomato leaf curl virus", "image_url": null,
                      "symptoms_bn": "…", "symptoms_en": "…", "prevention_bn": "…", "prevention_en": "…",
                      "organic_control_bn": "…", "organic_control_en": "…", "chemical_control_bn": "…", "chemical_control_en": "…" } ],
      "fertilizer_doses": [ { "fertilizer_id": 1, "fertilizer_key": "urea", "kg_per_hectare": 550, "application_bn": "…", "application_en": "…" } ],
      "updated_at": "2026-10-14T17:00:00+06:00"
    }
  ],
  "fertilizers": [ { "id": 1, "key": "urea", "name_bn": "ইউরিয়া", "name_en": "Urea", "official_price_per_kg": 27, "notes_bn": "…", "notes_en": "…" } ],
  "articles": [ { "id": 1, "slug": "start-a-rooftop-garden", "category": "gardening", "audience": "gardener", "icon": "🏡",
                  "title_bn": "…", "title_en": "…", "summary_bn": "…", "summary_en": "…", "body_bn": "…", "body_en": "…" } ]
}
```
`problems[].affected_part` is one of `leaf, stem, fruit, grain, root, whole`. `type` is one of `pest, disease, nutrient, weed, physiological`.

### `GET /crops?garden=1&category=vegetable` · `GET /crops/{slug}` · `GET /problems/{id}`
The same shapes as above. The list leaves out the long text and nested arrays.

### `GET /articles?audience=gardener&category=ipm` · `GET /articles/{slug}`
Paginated list (`data`, `links`, `meta`) and a single article.

---

## Markets and prices

### `GET /markets` · `GET /commodities`
Reference lists.

### `GET /prices`
| Param | |
|---|---|
| `market_id` | a specific market, **or** |
| `district_id` | the nearest market (to this district) that has data |
| `type` | `wholesale` (default) or `retail` |

```json
{
  "market": { "id": 2, "name_bn": "মহাস্থান হাট", "name_en": "Mahasthan Haat", "type": "wholesale",
              "district_id": 45, "district_bn": "বগুড়া", "district_en": "Bogura" },
  "date": "2026-10-14",
  "type": "wholesale",
  "items": [
    {
      "commodity": { "id": 1, "crop_id": 1, "name_bn": "ধান (মোটা)", "name_en": "Paddy (coarse)", "category": "cereal", "unit": "maund", "icon": "🌾" },
      "date": "2026-10-14", "min": 1475, "max": 1630, "avg": 1552.5,
      "previous_avg": 1500, "change_pct": 3.5, "trend": "up",
      "source": "Demo data – not real prices"
    }
  ]
}
```
`trend` is `up`, `down` or `stable` (a change of less than ±2% against the previous entry within 14 days).

### `GET /prices/history?commodity_id=6&market_id=2&days=30&type=wholesale`
```json
{ "data": [ { "date": "2026-10-13", "min": 21, "max": 23, "avg": 22 }, { "date": "2026-10-14", "min": 20, "max": 22, "avg": 21 } ] }
```

### `GET /fertilizer-prices`
`{ "data": [ { "key": "urea", "official_price_per_kg": 27, … } ] }`

---

## Weather and alerts

### `GET /weather?district_id=45`
Cached for 60 minutes per district. If the provider fails, the last good forecast is returned with `"stale": true`, or `503` if there is none.
```json
{
  "district": { "id": 45, "name_bn": "বগুড়া", "name_en": "Bogura" },
  "current": { "temperature": 29.6, "humidity": 78, "code": 2, "wind": 11.2 },
  "daily": [ { "date": "2026-10-15", "code": 61, "t_max": 30.2, "t_min": 24.6, "rain_mm": 12.5, "rain_probability": 75, "wind_max": 18 } ],
  "advisories": [
    { "key": "heavy_rain", "level": "warning", "icon": "rain",
      "title_bn": "আগামী ৩ দিনে ভারী বৃষ্টির সম্ভাবনা", "title_en": "Heavy rain likely in the next 3 days",
      "body_bn": "এখন কীটনাশক বা ইউরিয়া সার দেবেন না…", "body_en": "Do not spray pesticide or apply urea now…" }
  ],
  "source": "Open-Meteo", "fetched_at": "2026-10-14T18:42:22+06:00", "stale": false
}
```
`code` is a WMO weather code. Advisory keys: `heavy_rain, rain_today, strong_wind, heat, cold, late_blight, dry_spell, spray_window, normal`. The rules live in `app/Services/FarmAdvisoryService.php`.

### `GET /alerts?district_id=45`
Active national alerts and alerts targeted at that district, most severe first.
```json
{ "data": [ { "id": 1, "severity": "warning", "title_bn": "…", "title_en": "…", "body_bn": "…", "body_en": "…",
              "is_national": true, "starts_at": "…", "ends_at": "…" } ] }
```

---

## Accounts

### `POST /auth/register`
| Field | Rules |
|---|---|
| `name` | required, 2–100 chars |
| `phone` | required, Bangladeshi mobile. `+8801…`, `8801…`, spaces, dashes and **Bangla digits** are accepted and normalised to `01XXXXXXXXX` |
| `pin` | required, 4–6 digits (Bangla digits accepted) |
| `district_id`, `user_type` (`farmer`, `gardener`, `learner`, `extension_worker`), `preferred_language` (`bn`/`en`), `device_name` | optional |

`201`:
```json
{ "token": "2|GMlb6f…", "user": { "id": 4, "name": "রহিম উদ্দিন", "phone": "01711111111", "role": "farmer",
  "user_type": "farmer", "district_id": 45, "preferred_language": "bn", "created_at": "…" } }
```
`role` and `is_active` sent by the client are ignored.

### `POST /auth/login`
`{ "phone": "01711111111", "pin": "12345" }` → same as register (`200`). A wrong PIN returns `422` (`errors.phone`); after 5 failures `429` for 15 minutes.

### `POST /auth/logout` 🔒 · `GET /me` 🔒 · `PATCH /me` 🔒
`PATCH /me` accepts `name`, `district_id`, `user_type`, `preferred_language`, and `pin` + `current_pin` to change the PIN.

---

## Ask an expert

### `POST /questions` 🔒 (multipart/form-data)
| Field | Rules |
|---|---|
| `body` | required, 5–2000 chars |
| `crop_id` | optional, a published crop |
| `image` | optional, jpg/png/webp ≤ 5 MB. It is resized to ≤ 1280 px and re-encoded as JPEG (EXIF/GPS removed) |

`201`:
```json
{ "data": { "id": 4, "body": "…", "image_url": "http://…/media/questions/2026/10/uuid.jpg", "status": "pending",
            "is_public": false, "is_mine": true, "crop": { "id": 10, "name_bn": "টমেটো", "name_en": "Tomato", "icon": "🍅" },
            "district": { "name_bn": "বগুড়া", "name_en": "Bogura" }, "answers": [], "answered_at": null, "created_at": "…" } }
```

### `GET /questions` 🔒
Your questions (paginated, newest first), each with `answers: [{ id, body, expert_name, created_at }]`.

### `GET /questions/{id}` 🔒
Your own question, or any public answered one; otherwise `404`.

### `GET /community/questions?crop_id=10&q=leaf`
Answered questions that an expert published. **Who asked is never included**, only the district.

---

## Media

`GET /media/{path}` serves uploaded photos with a 30-day cache header. Paths containing `..` are rejected.

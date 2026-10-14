# AI crop doctor (কৃষি ডাক্তার AI)

Farmers photograph a sick plant (or pick a photo from the gallery), say or type what is wrong in Bangla, and get advice in seconds. They can **listen** to the answer, open our expert-checked guide when the AI recognised a known problem, and send the case to a human expert with one tap.

| | |
|---|---|
| AI model | **Claude Opus 5.5** (`claude-opus-5-5`) through the Anthropic API, called **only from the Laravel server**. The API key never goes into the app |
| Voice in | The phone's own speech recogniser (`speech_to_text`, Bangla `bn_BD`). Free; works offline when the Bangla voice pack is installed; the app receives only text |
| Voice out | The existing read-aloud (`flutter_tts`). Answers are read automatically when the question was spoken |
| Cost | About **$0.04 (~৳5) per question** with Opus 5.5 (photo + question + answer); prompt caching makes the instructions and knowledge base about 20× cheaper to resend after the first call |
| Limits | 10 questions per user per day (`KRISHI_AI_DAILY_LIMIT`), 5 per minute; login required |

## Set it up

1. Create an account at <https://console.anthropic.com>, add credits (Billing) and **set a monthly spend limit**.
2. Create an API key (Settings → API keys) and put it in `backend/.env`:
   ```env
   ANTHROPIC_API_KEY=sk-ant-...
   KRISHI_AI_DRIVER=anthropic
   KRISHI_AI_MODEL=claude-opus-5-5
   KRISHI_AI_EFFORT=medium
   KRISHI_AI_DAILY_LIMIT=10
   ```
   then `php artisan config:clear`.
3. Test with a photo: `php artisan krishi:ai-check path/to/leaf.jpg --text="পাতায় হলুদ দাগ কেন?"`
   It prints the answer, the time taken, the tokens and the estimated cost.
4. In the app: Home → **কৃষি ডাক্তার (AI)**.

**No key yet?** Set `KRISHI_AI_DRIVER=demo`. The app then gets canned answers that say "এটি ডেমো উত্তর…", so you can try the screens without paying. Never leave `demo` on for real users.

**Switching model** (one line in `.env`, no app update): `claude-sonnet-5-5` (~$0.02/question, faster) or `claude-haiku-5-5` (~$0.002/question, less reliable on unclear photos). Update the prices in `config/krishi.php` (`ai.prices`) if you add another model, so the admin cost estimate stays right.

## How it works

```
App (photo + text/voice, Bangla)  →  POST /api/v1/ai/ask  →  AiDoctorService
                                                         ├─ saves the photo (resized, EXIF removed)
                                                         ├─ system prompt = rules + our knowledge-base catalogue (cached)
                                                         ├─ last 8 turns of the conversation (newest 2 photos)
                                                         └─ Claude → JSON answer (DiagnosisSchema) → stored → app
```

* **Grounded in our content.** The system prompt lists every published crop and problem (id, names, part of plant, key symptoms). When Claude recognises one, it returns its id and the app shows **"পুরো নির্দেশিকা" (full guide)**, our expert-checked page. Ids that are not in the knowledge base are dropped by the server.
* **Safety rules in the prompt:** simple Bangla; IPM and non-chemical control first; active ingredients, never brands; label dose, protective clothing and waiting period; no banned or highly hazardous pesticides; say "not sure" and ask for a better photo; refuse non-plant photos politely; send serious cases to an expert, 16123 or the Upazila Agriculture Office.
* **Structured output** (`app/Ai/DiagnosisSchema.php`): `answer` (read aloud), up to 3 `likely_problems` with confidence, 2–5 `steps`, `safety_note`, `needs_expert`, `follow_up_question`.
* **Prompt caching:** the instructions and catalogue are identical for every request until the content changes, so they are cached. Follow-ups also reuse the cached conversation.
* **Resilience:** if a safety classifier declines, the API retries on a suitable model automatically (server-side fallback). Busy or unreachable → "AI ডাক্তার এখন ব্যস্ত" and the farmer can keep the question for a human expert (it uses the offline outbox).
* **Human in the loop:** 👍/👎 on each conversation; **"বিশেষজ্ঞকে পাঠান"** creates a normal expert question containing the photo and the AI's answer. In the admin panel, **AI doctor** lists conversations (filters: 👎, "AI suggested an expert", sent to expert) with token usage and estimated cost.

## Privacy

Photos and question text (not name or phone number) go to Anthropic, which processes them on our behalf. Under Anthropic's commercial terms, API data is not used to train its models. This is stated on `/privacy` and in the Play Data safety notes (`docs/play-store/LISTING.md`). Voice is turned into text on the phone by the phone's speech service; no audio is uploaded by the app. Deleting an account deletes its AI conversations and photos.

## Files

| Part | Files |
|---|---|
| Backend | `app/Services/AiDoctorService.php` (prompt, history, escalation), `app/Ai/*` (Claude client, demo client, schema), `app/Http/Controllers/Api/V1/AiController.php`, `app/Http/Controllers/Admin/AiConversationController.php`, `resources/views/admin/ai/*`, migration `2026_10_15_000001_create_ai_tables.php`, `routes/console.php` (`krishi:ai-check`), `tests/Feature/AiDoctorTest.php` |
| App | `lib/features/ai_doctor/*`, `lib/data/ai_repository.dart`, `lib/models/ai.dart`, `lib/core/services/voice_input_service.dart`, `test/ai_doctor_test.dart` |

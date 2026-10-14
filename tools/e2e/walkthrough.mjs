// End-to-end walkthrough: farmer onboarding → crop doctor → prices → weather →
// calculator → register & ask an expert → expert answers in the admin panel →
// farmer reads the answer → My crops. Saves screenshots to ./screenshots.
//
// Prerequisites (see README.md in this folder):
//   backend:  php artisan migrate:fresh --seed && php artisan serve
//   app:      flutter build web --no-web-resources-cdn --dart-define=API_BASE_URL=http://127.0.0.1:8000/api/v1
//             then serve mobile/build/web on port 8080
import { mkdirSync } from 'node:fs';
import { chromium } from 'playwright';

const APP = process.env.APP_URL ?? 'http://127.0.0.1:8080/';
const ADMIN = process.env.ADMIN_URL ?? 'http://127.0.0.1:8000/admin';
const OUT = process.env.OUT ?? './screenshots';
const PHONE = process.env.TEST_PHONE ?? '01812345678'; // must not exist yet
mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
// A real browser locale is required: Flutter's engine parses navigator.languages.
const page = await (await browser.newContext({ viewport: { width: 412, height: 860 }, deviceScaleFactor: 2, locale: 'bn-BD' })).newPage();
page.on('pageerror', (e) => console.log('PAGE ERROR', e.message));

let n = 0;
const shot = async (name, p = page) => {
  const file = `${OUT}/${String(++n).padStart(2, '0')}-${name}.png`;
  await p.screenshot({ path: file });
  console.log('✓', file);
};

// Flutter web draws on a canvas; its accessibility tree is what we can click.
async function enableSemantics() {
  const placeholder = await page.$('flt-semantics-placeholder');
  if (placeholder) await placeholder.dispatchEvent('click');
  await page.waitForTimeout(800);
}
async function home() {
  await page.goto(APP, { waitUntil: 'networkidle' });
  await page.waitForTimeout(3500);
  await enableSemantics();
}
/** Click the smallest tappable element whose text or aria-label contains `text`. */
async function tap(text, wait = 1300) {
  const handle = await page.evaluateHandle((text) => {
    const label = (e) => (e.textContent || '') + ' ' + (e.getAttribute('aria-label') || '');
    const all = [...document.querySelectorAll('flt-semantics')].filter((e) => label(e).includes(text));
    const tappable = all.filter((e) => ['button', 'tab', 'checkbox', 'link', 'switch'].includes(e.getAttribute('role')));
    const pool = tappable.length ? tappable : all;
    return pool.sort((a, b) => label(a).length - label(b).length)[0] ?? null;
  }, text);
  const el = handle.asElement();
  if (!el) throw new Error(`nothing to tap with text "${text}"`);
  await el.click({ force: true });
  await page.waitForTimeout(wait);
}
/** Text fields are <input aria-label="label or hint">. */
async function fill(label, value) {
  await page.locator(`input[aria-label*="${label}"], textarea[aria-label*="${label}"]`).first().click({ force: true });
  await page.keyboard.type(value, { delay: 15 });
  await page.waitForTimeout(600);
}
const scroll = async (dy) => { await page.mouse.move(206, 500); await page.mouse.wheel(0, dy); await page.waitForTimeout(900); };
const back = async () => { await page.mouse.click(28, 28); await page.waitForTimeout(1200); }; // AppBar back arrow
async function step(name, fn, { fromHome = true } = {}) {
  try {
    if (fromHome) await home();
    await fn();
  } catch (e) {
    console.log('✗ step failed:', name, '-', e.message);
    process.exitCode = 1;
  }
}

await page.goto(APP, { waitUntil: 'networkidle' });
await page.waitForTimeout(5000);
await enableSemantics();

await step('onboarding', async () => {
  await shot('language');
  await tap('বাংলা');
  await tap('কৃষক');
  await fill('জেলা খুঁজুন', 'বগুড়া');
  await tap('বগুড়া');
  await tap('শুরু করুন', 2500);
  await shot('home');
}, { fromHome: false });

await step('crop doctor', async () => {
  await tap('ফসলের সমস্যা');
  await fill('ফসল খুঁজুন', 'টমেটো');
  await tap('টমেটো');
  await shot('crop-problems');
  await tap('পাতা কোঁকড়ানো ভাইরাস');
  await shot('problem-detail');
  await back();
  await tap('ক্যালেন্ডার');
  await tap('আমার ফসলে যোগ করুন');
  await tap('সংরক্ষণ করুন');
});

await step('prices', async () => {
  await tap('বাজার দর', 2500);
  await shot('prices');
  await tap('আলু', 2000);
  await shot('price-history');
});

await step('weather', async () => {
  await tap('আবহাওয়া', 2500);
  await shot('weather');
});

await step('fertilizer calculator', async () => {
  await scroll(500);
  await tap('সার হিসাব');
  await tap('ফসল বেছে নিন');
  await tap('বোরো ধান');
  await shot('calculator');
});

await step('ask an expert', async () => {
  await tap('বিশেষজ্ঞকে প্রশ্ন');
  await tap('প্রশ্ন করুন');
  await tap('নতুন? অ্যাকাউন্ট খুলুন');
  await fill('আপনার নাম', 'Test Farmer');
  await fill('মোবাইল নম্বর', PHONE);
  await fill('পিন (৪–৬ সংখ্যা)', '1234');
  await fill('পিন আবার লিখুন', '1234');
  await tap('নতুন অ্যাকাউন্ট', 2500);
  await fill('সমস্যাটি লিখুন', 'E2E: tomato leaves are curling. What should I do?');
  await tap('পাঠান', 2500);
  await shot('question-sent');
});

// The expert answers from the admin panel.
const admin = await (await browser.newContext({ viewport: { width: 1280, height: 860 } })).newPage();
await step('expert answers', async () => {
  await admin.goto(`${ADMIN}/login`);
  await admin.fill('#login', 'expert@krishisathi.test');
  await admin.fill('#password', 'Expert@12345');
  await admin.click('button[type=submit]');
  await admin.goto(`${ADMIN}/questions`);
  await admin.click('text=E2E: tomato leaves are curling');
  await admin.fill('textarea[name=body]', 'হলুদ আঠালো ফাঁদ লাগান এবং আক্রান্ত গাছ তুলে ফেলুন।');
  await admin.check('#f_is_public');
  await shot('admin-answer', admin);
  await admin.click('button:has-text("Send answer")');
}, { fromHome: false });

await step('farmer reads the answer', async () => {
  await tap('বিশেষজ্ঞকে প্রশ্ন', 2500);
  await tap('E2E: tomato leaves');
  await shot('answer');
});

await step('my crops', async () => {
  await shot('home-with-tasks');
  await scroll(500);
  await tap('আমার ফসল');
  await tap('টমেটো');
  await shot('my-crop-tasks');
});

await browser.close();

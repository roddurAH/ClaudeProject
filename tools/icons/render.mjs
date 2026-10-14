// Renders the app icon masters (1024px) and the Play Store feature graphic into ./out.
// Then run `python3 make_icons.py` to resize them into every platform folder. See README.md.
import { mkdirSync, readFileSync } from 'node:fs';
import { chromium } from 'playwright';

const OUT = new URL('./out/', import.meta.url).pathname;
mkdirSync(OUT, { recursive: true });
const font = (f) => 'data:font/ttf;base64,' +
  readFileSync(new URL('../../mobile/assets/fonts/' + f, import.meta.url)).toString('base64');
const defs = `<defs>
  <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
    <stop offset="0" stop-color="#66BB6A"/><stop offset="1" stop-color="#1B5E20"/>
  </linearGradient>
  <linearGradient id="leaf" x1="0" y1="0" x2="0" y2="1">
    <stop offset="0" stop-color="#FFFFFF"/><stop offset="1" stop-color="#E8F5E9"/>
  </linearGradient>
</defs>`;
const ground = `<path d="M0 800 Q512 690 1024 800 L1024 1024 L0 1024 Z" fill="#1B5E20" opacity="0.45"/>`;
const plant = `
  <circle cx="768" cy="232" r="84" fill="#FFCA28"/>
  <path d="M512 820 L512 500" stroke="#FFFFFF" stroke-width="44" stroke-linecap="round"/>
  <path d="M512 610 C 440 650 300 590 268 440 C 370 410 492 470 512 610 Z" fill="url(#leaf)"/>
  <path d="M512 540 C 540 420 660 360 772 382 C 768 520 640 590 512 540 Z" fill="url(#leaf)"/>
  <path d="M500 592 C 440 560 360 510 300 452" stroke="#A5D6A7" stroke-width="10" stroke-linecap="round" fill="none"/>
  <path d="M526 528 C 600 490 680 440 752 396" stroke="#A5D6A7" stroke-width="10" stroke-linecap="round" fill="none"/>`;
const svg = (body, { size = 1024, clip = '' } = {}) =>
  `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" width="${size}" height="${size}">${defs}${clip}${body}</svg>`;
const variants = {
  // iOS, Play Store 512, web maskable: full-bleed square, no transparency
  full: svg(`<rect width="1024" height="1024" fill="url(#bg)"/>${ground}${plant}`),
  // Android legacy / web / desktop: rounded square with transparent corners
  rounded: svg(`<clipPath id="r"><rect x="40" y="40" width="944" height="944" rx="210"/></clipPath>
    <g clip-path="url(#r)"><rect width="1024" height="1024" fill="url(#bg)"/>${ground}${plant}</g>`),
  // Android adaptive icon layers (108dp canvas; keep artwork in the central 66dp)
  fg: svg(`<g transform="translate(512 540) scale(0.6) translate(-512 -540)">${plant}</g>`),
  // Web maskable: full-bleed, artwork inside the 80% safe circle
  maskable: svg(`<rect width="1024" height="1024" fill="url(#bg)"/>${ground}<g transform="translate(512 530) scale(0.8) translate(-512 -530)">${plant}</g>`),
  bg: svg(`<rect width="1024" height="1024" fill="url(#bg)"/>${ground}`),
};
const browser = await chromium.launch();
const page = await (await browser.newContext({ viewport: { width: 1024, height: 1024 } })).newPage();
for (const [name, markup] of Object.entries(variants)) {
  await page.setContent(`<html><body style="margin:0;background:transparent">${markup}</body></html>`);
  await page.screenshot({ path: `${OUT}${name}.png`, omitBackground: true, clip: { x: 0, y: 0, width: 1024, height: 1024 } });
}
// Play Store feature graphic 1024×500
await page.setViewportSize({ width: 1024, height: 500 });
await page.setContent(`<html><head><style>
  @font-face { font-family: HS; src: url('${font('HindSiliguri-SemiBold.ttf')}'); font-weight: 600; }
  @font-face { font-family: HS; src: url('${font('HindSiliguri-Regular.ttf')}'); font-weight: 400; }
  body { margin:0; width:1024px; height:500px; display:flex; align-items:center; gap:44px; padding:0 60px; box-sizing:border-box;
         background: linear-gradient(135deg,#66BB6A,#1B5E20); font-family: HS, sans-serif; color:#fff; }
  h1 { font-size: 104px; font-weight:600; margin:0; line-height:1.05; }
  h2 { font-size: 40px; font-weight:400; margin:4px 0 22px; opacity:.95; }
  p { font-size: 27px; white-space: nowrap; margin:0; font-weight:400; background: rgba(0,0,0,.18); padding: 8px 18px; border-radius: 14px; display:inline-block; }
</style></head><body>
  <div style="flex:none;width:280px;height:280px">${variants.rounded.replace('width="1024" height="1024"', 'width="280" height="280"')}</div>
  <div><h1>কৃষি সাথী</h1><h2>Krishi Sathi · আপনার কৃষি সহায়ক</h2>
  <p>ফসলের সমস্যা · বাজার দর · আবহাওয়া পরামর্শ</p></div>
</body></html>`);
await page.evaluate(() => document.fonts.ready);
await page.waitForTimeout(500);
await page.screenshot({ path: `${OUT}feature-graphic.png` });
await browser.close();
console.log('rendered into', OUT);

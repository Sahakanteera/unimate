// Print each built deck to PDF (one 1920x1080 page per slide, text stays vector).
// Usage: node export-pdf.mjs <playwright-core package.json> <chrome.exe>
import { createRequire } from 'module';
import path from 'path';
import { fileURLToPath, pathToFileURL } from 'url';

const [pkg, chrome] = process.argv.slice(2);
const { chromium } = createRequire(pkg)('playwright-core');
const out = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const browser = await chromium.launch({ executablePath: chrome });
for (const name of ['UniMate-Part2-Slides', 'UniMate-Part2-Appendix']) {
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  await page.goto(pathToFileURL(path.join(out, `${name}.html`)).href + '?static=1');
  await page.waitForFunction(() => window.deck);
  await page.evaluate(() => document.fonts.ready);
  await page.emulateMedia({ media: 'print' });
  await page.pdf({ path: path.join(out, 'src', `${name}.pdf`), width: '1920px', height: '1080px', printBackground: true, preferCSSPageSize: true });
  console.log('pdf', name);
  await page.close();
}
await browser.close();

#!/usr/bin/env node
/** Capture uniquement la section facturation école (session déjà ouverte ou login requis). */
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';

const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT = join(__dirname, '..', 'screenshots');
const BASE = process.env.VRP_SCREENSHOT_BASE ?? 'http://127.0.0.1:8000';
const EMAIL = process.env.VRP_SCREENSHOT_EMAIL ?? 'marc.augier@xdm-consulting.fr';
const PASSWORD = process.env.VRP_SCREENSHOT_PASSWORD ?? 'capture-manuel';
const CHROME = process.env.CHROME_PATH ?? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

async function ensureLoggedIn(page) {
  await page.goto(`${BASE}/home`, { waitUntil: 'networkidle2' });
  if (!page.url().includes('/login')) return;
  await page.waitForSelector('input[name="email"]');
  await page.type('input[name="email"]', EMAIL, { delay: 20 });
  await page.type('input[name="password"]', PASSWORD, { delay: 20 });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('button[type="submit"]'),
  ]);
}

const browser = await puppeteer.launch({
  executablePath: CHROME,
  headless: 'new',
  args: ['--no-sandbox', '--disable-setuid-sandbox'],
});
const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 2 });
await ensureLoggedIn(page);
await page.goto(`${BASE}/school/1#billing`, { waitUntil: 'networkidle2' });
await new Promise((r) => setTimeout(r, 800));
const file = join(OUT, '09-school-billing.png');
await page.screenshot({ path: file, fullPage: true });
console.log(`✓ ${file}`);
await browser.close();

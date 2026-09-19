#!/usr/bin/env node
/**
 * Capture d'écrans pour le manuel utilisateur VRP Plan.
 * Prérequis : serveur local (php artisan serve) + compte démo seedé.
 *
 * Usage :
 *   node scripts/capture-screenshots.mjs
 *   VRP_SCREENSHOT_EMAIL=m@xdm.fr VRP_SCREENSHOT_PASSWORD=secret node scripts/capture-screenshots.mjs
 */
import { mkdir } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';

const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT = join(__dirname, '..', 'screenshots');
const BASE = process.env.VRP_SCREENSHOT_BASE ?? 'http://127.0.0.1:8000';
const EMAIL = process.env.VRP_SCREENSHOT_EMAIL ?? 'marc.augier@xdm-consulting.fr';
const PASSWORD = process.env.VRP_SCREENSHOT_PASSWORD ?? 'capture-manuel';

const CHROME =
  process.env.CHROME_PATH ??
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

const shots = [
  { name: '01-landing', path: '/', auth: false, fullPage: true },
  { name: '02-login', path: '/login', auth: false },
  { name: '03-demande-acces', path: '/demande-acces', auth: false },
  { name: '04-home', path: '/home', auth: true, fullPage: true },
  { name: '05-planning', path: '/planning', auth: true, fullPage: true },
  { name: '06-treasury', path: '/treasury', auth: true, fullPage: true },
  { name: '07-programs', path: '/program', auth: true, fullPage: true },
  { name: '08-groups', path: '/group', auth: true, fullPage: true },
];

async function ensureLoggedIn(page) {
  await page.goto(`${BASE}/home`, { waitUntil: 'networkidle2' });
  if (!page.url().includes('/login')) {
    return;
  }
  await page.waitForSelector('input[name="email"]');
  await page.type('input[name="email"]', EMAIL, { delay: 20 });
  await page.type('input[name="password"]', PASSWORD, { delay: 20 });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('button[type="submit"]'),
  ]);
  if (page.url().includes('/login')) {
    throw new Error(`Échec de connexion pour ${EMAIL} — vérifiez le mot de passe.`);
  }
}

async function capture(page, { name, path, fullPage }) {
  await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 600));
  const file = join(OUT, `${name}.png`);
  await page.screenshot({ path: file, fullPage: !!fullPage });
  console.log(`✓ ${file}`);
}

async function captureSchoolBilling(page) {
  await page.goto(`${BASE}/home`, { waitUntil: 'networkidle2' });
  const href = await page.evaluate(() => {
    const links = [...document.querySelectorAll('a[href^="/school/"]')];
    const show = links.find((a) => /^\/school\/\d+$/.test(a.getAttribute('href') ?? ''));
    return show?.getAttribute('href') ?? null;
  });
  const target = href ? `${BASE}${href}#billing` : `${BASE}/school/1#billing`;
  await page.goto(target, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 800));
  const file = join(OUT, '09-school-billing.png');
  await page.screenshot({ path: file, fullPage: true });
  console.log(`✓ ${file}`);
}

async function main() {
  await mkdir(OUT, { recursive: true });

  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox'],
  });

  const page = await browser.newPage();
  page.setDefaultTimeout(30000);
  await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 2 });

  let loggedIn = false;

  for (const shot of shots) {
    if (shot.auth && !loggedIn) {
      await ensureLoggedIn(page);
      loggedIn = true;
    }
    await capture(page, shot);
  }

  if (!loggedIn) {
    await ensureLoggedIn(page);
  }
  await captureSchoolBilling(page);

  await browser.close();
  console.log('\nCaptures terminées.');
}

main().catch((err) => {
  console.error(err.message ?? err);
  process.exit(1);
});

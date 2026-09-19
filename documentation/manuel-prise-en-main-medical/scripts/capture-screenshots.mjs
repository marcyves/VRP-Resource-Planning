#!/usr/bin/env node
/**
 * Captures d'écran — profil médical uniquement (entreprise terminology_profile = medical).
 */
import { mkdir } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer-core';

const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT = join(__dirname, '..', 'screenshots');
const BASE = process.env.VRP_SCREENSHOT_BASE ?? 'http://127.0.0.1:8000';
const EMAIL = process.env.VRP_SCREENSHOT_EMAIL ?? 'matthieu.augier@yahoo.fr';
const PASSWORD = process.env.VRP_SCREENSHOT_PASSWORD ?? 'capture-medical';
const CHROME =
  process.env.CHROME_PATH ??
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

const shots = [
  { name: '01-home-structures', path: '/home', fullPage: true },
  { name: '02-navigation', path: '/home' },
  { name: '03-prestations', path: '/program', fullPage: true },
  { name: '04-patients', path: '/group', fullPage: true },
  { name: '05-agenda', path: '/planning', fullPage: true },
  { name: '07-treasury', path: '/treasury', fullPage: true },
];

async function ensureLoggedIn(page) {
  await page.goto(`${BASE}/home`, { waitUntil: 'networkidle2' });
  if (!page.url().includes('/login')) return;
  await page.waitForSelector('input[name="email"]');
  await page.type('input[name="email"]', EMAIL, { delay: 15 });
  await page.type('input[name="password"]', PASSWORD, { delay: 15 });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2' }),
    page.click('button[type="submit"]'),
  ]);
  if (page.url().includes('/login')) {
    throw new Error(`Connexion échouée pour ${EMAIL}`);
  }
}

async function capture(page, { name, path, fullPage }) {
  await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 500));
  const file = join(OUT, `${name}.png`);
  await page.screenshot({ path: file, fullPage: !!fullPage });
  console.log(`✓ ${file}`);
}

async function captureBilling(page) {
  await page.goto(`${BASE}/home`, { waitUntil: 'networkidle2' });
  const href = await page.evaluate(() => {
    const links = [...document.querySelectorAll('a[href^="/school/"]')];
    const show = links.find((a) => /^\/school\/\d+$/.test(a.getAttribute('href') ?? ''));
    return show?.getAttribute('href') ?? null;
  });
  const target = href ? `${BASE}${href}#billing` : `${BASE}/school/35#billing`;
  await page.goto(target, { waitUntil: 'networkidle2' });
  await new Promise((r) => setTimeout(r, 700));
  const file = join(OUT, '06-facturation-structure.png');
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
  await ensureLoggedIn(page);
  for (const shot of shots) await capture(page, shot);
  await captureBilling(page);
  await browser.close();
  console.log('\nCaptures médicales terminées.');
}

main().catch((e) => {
  console.error(e.message ?? e);
  process.exit(1);
});

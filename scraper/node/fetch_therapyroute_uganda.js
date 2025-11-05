#!/usr/bin/env node
/**
 * Headless fetcher for TherapyRoute Uganda listings
 * Usage: node fetch_therapyroute_uganda.js --country=Uganda --page=1
 *
 * Tries Playwright first (Chromium). If unavailable, falls back to Puppeteer.
 * Prints extracted profile URLs (one per line) to stdout.
 */

const args = process.argv.slice(2);
const getArg = (name, def = '') => {
  const m = args.find(a => a.startsWith(`--${name}=`));
  return m ? m.split('=')[1] : def;
};

const country = getArg('country', 'Uganda');
const pageNo = parseInt(getArg('page', '1'), 10) || 1;

const targets = [
  `https://www.therapyroute.com/search?country=${encodeURIComponent(country)}&page=${pageNo}`,
  `https://www.therapyroute.com/find-nearby-therapists?country=${encodeURIComponent(country)}&page=${pageNo}`,
  `https://www.therapyroute.com/therapist?country=${encodeURIComponent(country)}&page=${pageNo}`
];

function normalizeUrl(href) {
  try {
    if (!href) return null;
    if (!/^https?:/i.test(href)) {
      href = `https://www.therapyroute.com${href.startsWith('/') ? href : '/' + href}`;
    }
    const u = new URL(href);
    if (!/^\/therapist\/.+/i.test(u.pathname)) return null;
    return u.toString();
  } catch (_) {
    return null;
  }
}

async function runWithPlaywright(url) {
  let pw;
  try {
    pw = require('playwright');
  } catch (e) { return null; }

  const browser = await pw.chromium.launch({ headless: true });
  const context = await browser.newContext({
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36',
    locale: 'en-US',
  });
  const page = await context.newPage();

  await page.route('**/*', route => {
    const req = route.request();
    // Skip heavy analytics if needed
    if (/google-analytics|googletagmanager|facebook|hotjar|segment/i.test(req.url())) {
      return route.abort();
    }
    route.continue();
  });

  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
  // Wait for result anchors to appear or fallback to network idle
  try { await page.waitForSelector('a[href*="/therapist/"]', { timeout: 15000 }); } catch (_) {}
  await page.waitForLoadState('networkidle', { timeout: 20000 }).catch(() => {});

  const hrefs = await page.$$eval('a[href*="/therapist/"]', els => els.map(e => e.getAttribute('href')).filter(Boolean));
  await browser.close();
  return hrefs.map(normalizeUrl).filter(Boolean);
}

async function runWithPuppeteer(url) {
  let puppeteer;
  try {
    puppeteer = require('puppeteer');
  } catch (e) { return null; }
  const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox','--disable-setuid-sandbox'] });
  const page = await browser.newPage();
  await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36');
  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
  try { await page.waitForSelector('a[href*="/therapist/"]', { timeout: 15000 }); } catch (_) {}
  await page.waitForNetworkIdle({ idleTime: 1000, timeout: 20000 }).catch(() => {});
  const hrefs = await page.$$eval('a[href*="/therapist/"]', els => els.map(e => e.getAttribute('href')).filter(Boolean));
  await browser.close();
  return hrefs.map(normalizeUrl).filter(Boolean);
}

(async () => {
  for (const url of targets) {
    let links = await runWithPlaywright(url);
    if (!links || links.length === 0) {
      links = await runWithPuppeteer(url);
    }
    if (Array.isArray(links) && links.length > 0) {
      for (const l of Array.from(new Set(links))) {
        process.stdout.write(l + '\n');
      }
      process.exit(0);
    }
  }
  console.error('No links found or headless libraries not installed.');
  process.exit(2);
})();
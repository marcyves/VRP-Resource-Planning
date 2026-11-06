#!/usr/bin/env node
/**
 * Generate a committed PHP catalog of Rune Icons duotone SVGs.
 * Recolors fixed greys to VRP navy CSS variables (fallback #00236f / #4059aa).
 *
 * Usage: npm run icons:rune
 */
import { copyFileSync, mkdirSync, writeFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import { buildSvg, getIconById } from "runeicons";

const root = join(dirname(fileURLToPath(import.meta.url)), "../..");

/** Semantic VRP names → Rune icon ids (duotone). */
export const ALIASES = {
    grid: "layouts-grid-2x2",
    logo: "layouts-grid-2x2",
    school: "legacy-school",
    layers: "layouts-layers-2",
    users: "identity-users",
    "calendar-range": "schedule-calendar",
    calendar: "schedule-calendar",
    receipt: "money-receipt",
    clock: "schedule-clock",
    chart: "metrics-chart-bar",
    folder: "documents-folder",
    wallet: "money-wallet",
    coins: "money-euro",
    cart: "money-shopping-cart",
    "shopping-cart": "money-shopping-cart",
    banknote: "money-credit-card",
    plus: "indicators-plus",
    add: "indicators-plus",
    person: "indicators-circle-user",
    building: "tools-house",
    logout: "identity-log-out",
    sun: "nature-sun",
    moon: "nature-moon",
    "panel-left-close": "layouts-panel-left-close",
    "panel-left-open": "layouts-panel-left",
    shield: "identity-shield-check",
    dot: "indicators-circle-dot",
    edit: "tools-pencil",
    delete: "tools-trash",
    trash: "tools-trash",
    "invoice-manual": "documents-file-plus",
    payed: "indicators-circle-check",
    "payment-restore": "arrows-rotate-ccw",
    "pdf-view": "documents-file-text",
    "pdf-download": "documents-file-down",
    archive: "documents-archive",
    "archive-restore": "documents-folder-open",
    "e-invoice": "messaging-send",
    validate: "indicators-check",
    "validate-cancel": "arrows-rotate-ccw",
    "arrow-up": "arrows-arrow-up",
    eye: "senses-eye",
    "eye-off": "senses-eye-off",
    duplicate: "code-copy",
    copy: "code-copy",
    inbox: "documents-inbox",
    empty: "documents-inbox",
    menu: "layouts-menu",
    import: "documents-import",
};

const PRIMARY = "var(--rune-duotone-primary, #00236f)";
const SECONDARY = "var(--rune-duotone-secondary, #4059aa)";

const GREY_PRIMARY = /#A4A5A6/gi;
const GREY_SECONDARY = /#DDDDDD|#D9D9D9|#F3F3F3/gi;

function recolor(svg) {
    return svg
        .replace(/\r\n/g, "\n")
        .replace(/\r/g, "\n")
        .replace(GREY_PRIMARY, PRIMARY)
        .replace(GREY_SECONDARY, SECONDARY);
}

/**
 * Stock `schedule-calendar` duotone fills the whole body (rings + header
 * line closed with Z), so at nav size it reads as a solid blob.
 * Rebuild from the official outline paths: secondary fill on the header
 * band only, primary stroke on the empty frame — same geometry, emptier.
 */
function openCalendarDuotone(size = 24) {
    const headerBand =
        "M5 4H19C20.1046 4 21 4.89543 21 6V10H3V6C3 4.89543 3.89543 4 5 4Z";
    const frame =
        "M8 2V6M16 2V6M5 4H19C20.1046 4 21 4.89543 21 6V20C21 21.1046 20.1046 22 19 22H5C3.89543 22 3 21.1046 3 20V6C3 4.89543 3.89543 4 5 4Z";
    const headerLine = "M3 10H21";

    return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" aria-hidden="true"><g fill="none"><path d="${headerBand}" fill="${SECONDARY}"/>
<path d="${frame}" stroke="${PRIMARY}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="${headerLine}" stroke="${PRIMARY}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g></svg>`;
}

/**
 * Stock `money-shopping-cart` duotone fills the basket, so at tab size it
 * reads as a wallet blob. Same official outline, basket stroked, wheels
 * in the secondary tone — a readable ShoppingCartIcon.
 */
function openShoppingCartDuotone(size = 24) {
    const basket =
        "M2.05005 2.0498H4.05005L6.71005 14.4698C6.80763 14.9247 7.06072 15.3313 7.42576 15.6197C7.7908 15.908 8.24495 16.0602 8.71005 16.0498H18.49C18.9452 16.0491 19.3865 15.8931 19.7411 15.6076C20.0956 15.3222 20.3422 14.9243 20.4401 14.4798L22.09 7.0498H5.12005";
    const wheelLeft =
        "M8 22C8.55228 22 9 21.5523 9 21C9 20.4477 8.55228 20 8 20C7.44772 20 7 20.4477 7 21C7 21.5523 7.44772 22 8 22Z";
    const wheelRight =
        "M19 22C19.5523 22 20 21.5523 20 21C20 20.4477 19.5523 20 19 20C18.4477 20 18 20.4477 18 21C18 21.5523 18.4477 22 19 22Z";

    return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" aria-hidden="true"><g fill="none"><path d="${basket}" stroke="${PRIMARY}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="${wheelLeft}" fill="${SECONDARY}" stroke="${SECONDARY}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="${wheelRight}" fill="${SECONDARY}" stroke="${SECONDARY}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g></svg>`;
}

function phpString(value) {
    return JSON.stringify(value);
}

/**
 * Pre-Rune école / school glyph restored from module-tab-icon
 * (Lucide-style graduation cap, stroke currentColor). Marc asked to
 * keep this one exception; everything else stays Rune duotone.
 */
const LEGACY_MARKUP = {
    "legacy-school": `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>`,
};

const runeIds = [...new Set(Object.values(ALIASES))].sort();
const markup = {};
const missing = [];

for (const id of runeIds) {
    if (LEGACY_MARKUP[id]) {
        markup[id] = LEGACY_MARKUP[id];
        continue;
    }
    const icon = getIconById(id);
    if (!icon?.variants?.duotone) {
        missing.push(id);
        continue;
    }
    const svg =
        id === "schedule-calendar"
            ? openCalendarDuotone(24)
            : id === "money-shopping-cart"
                ? openShoppingCartDuotone(24)
                : buildSvg(id, "duotone", 24);
    if (!svg) {
        missing.push(id);
        continue;
    }
    markup[id] = recolor(svg);
}

if (missing.length) {
    console.error("Missing duotone icons:", missing.join(", "));
    process.exit(1);
}

const generatedAt = new Date().toISOString().slice(0, 10);
const aliasEntries = Object.entries(ALIASES)
    .sort(([a], [b]) => a.localeCompare(b))
    .map(([alias, id]) => `        ${phpString(alias)} => ${phpString(id)},`)
    .join("\n");
const markupEntries = Object.entries(markup)
    .map(([id, svg]) => `        ${phpString(id)} => ${phpString(svg)},`)
    .join("\n");

const php = `<?php

namespace App\\Support;

/**
 * Generated by third-party/runeicons/build.mjs — do not edit by hand.
 *
 * Rune Icons duotone SVGs, greys recolored to VRP navy CSS variables.
 * Source: runeicons ${generatedAt} (Apache-2.0). See third-party/runeicons/.
 */
final class RuneIconCatalog
{
    /** @var array<string, string> */
    public const ALIASES = [
${aliasEntries}
    ];

    /** @var array<string, string> */
    public const MARKUP = [
${markupEntries}
    ];
}
`;

const catalogPath = join(root, "app/Support/RuneIconCatalog.php");
writeFileSync(catalogPath, php);
console.log(`Wrote ${catalogPath} (${runeIds.length} icons, ${Object.keys(ALIASES).length} aliases)`);

const vendorDir = join(root, "third-party/runeicons");
mkdirSync(vendorDir, { recursive: true });
copyFileSync(join(root, "node_modules/runeicons/LICENSE"), join(vendorDir, "LICENSE"));
writeFileSync(
    join(vendorDir, "NOTICE"),
    `VRP Plan UI icons
==================

This product includes icons from Rune Icons (https://www.runeicons.com/),
package \`runeicons\`, licensed under the Apache License, Version 2.0.
See the accompanying LICENSE file in this directory.

Copyright of the Rune Icons authors.

The duotone SVGs shipped in App\\Support\\RuneIconCatalog were generated
with buildSvg(id, "duotone", size) and recolored from the library's
fixed greys (#A4A5A6, #DDDDDD) to VRP navy (#00236f / #4059aa) via CSS
variables --rune-duotone-primary and --rune-duotone-secondary.

schedule-calendar is the same official outline, with the stock full-body
fill replaced by a header-band fill so the agenda glyph stays readable.

The school / école glyph (legacy-school) is the pre-Rune inline SVG from
resources/views/components/module-tab-icon.blade.php, restored at Marc's
request. It is not a Rune icon.

money-shopping-cart uses the official basket/wheel paths with an open
basket (no full-body fill) so Dépenses reads as a cart at tab size.
`,
);
console.log("Wrote third-party/runeicons/ LICENSE and NOTICE");

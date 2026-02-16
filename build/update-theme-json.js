/**
 * THEME JSON GENERATOR
 * * This script synchronizes Foundation SCSS variables with WordPress theme.json.
 * It automates the generation of:
 * - 🎨 Color Palettes (Extracted from $foundation-palette)
 * - 🌈 Gradients (Auto-generated Radial and Linear variations)
 * - 🅰️ Typography (Font families and responsive font sizes)
 * - 📐 Layout (Grid gutters and global border radius)
 * * It also merges manual overrides from 'theme-extra.json' to allow for
 * custom Gutenberg settings that aren't defined in SCSS.
 */

const fs = require("fs");
const path = require("path");

const themeJsonPath = path.resolve("./theme.json");
const extraJsonPath = path.resolve("./theme-extra.json");
const scssPath = path.resolve("./src/assets/scss/_settings.scss");

// --- Ensure theme.json exists ---
if (!fs.existsSync(themeJsonPath)) {
  fs.writeFileSync(themeJsonPath, "{}");
}

// --- Read source files ---
const scss = fs.readFileSync(scssPath, "utf8");
let themeJson = {};
if (fs.existsSync(themeJsonPath)) {
  const raw = fs.readFileSync(themeJsonPath, "utf8").trim();
  themeJson = raw ? JSON.parse(raw) : {};
} else {
  fs.writeFileSync(themeJsonPath, "{}");
}

// Ensure structure
themeJson.version = 3;
themeJson.settings = themeJson.settings || {};
themeJson.settings.color = themeJson.settings.color || {};
themeJson.settings.color.custom = false;
themeJson.settings.color.customGradient = false;
themeJson.settings.color.defaultPalette = false;
themeJson.settings.color.defaultGradients = false;
themeJson.settings.color.gradients = [];

// ------------------------------------------------------------
// 🎨 Extract color palette from SCSS
// ------------------------------------------------------------
const match = scss.match(/\$foundation-palette:\s*\(([\s\S]*?)\);/);
if (!match) {
  console.error("❌ No $foundation-palette found in _settings.scss");
  process.exit(1);
}

const colors = match[1]
  .split(",")
  .map(line => line.trim())
  .filter(Boolean)
  .map(line => {
    let [slug, value] = line.split(":").map(s => s.trim());
    slug = slug.replace(/^['"]|['"]$/g, ""); // strip quotes from slug if present
    const color = value.replace(/['"]+/g, "");
    return {
      slug,
      name: slug.charAt(0).toUpperCase() + slug.slice(1),
      color,
    };
  });

// Exclude unwanted colors from palette and gradients
const excludedSlugs = new Set(["success", "warning", "alert"]);

// Remove duplicate slugs (from SCSS) and exclude unwanted colors
const uniqueColors = [];
const seenColorSlugs = new Set();
colors.forEach(c => {
  if (!seenColorSlugs.has(c.slug) && !excludedSlugs.has(c.slug)) {
    uniqueColors.push(c);
    seenColorSlugs.add(c.slug);
  }
});

themeJson.settings.color.palette = uniqueColors;
// Disable default Gutenberg colors if palette is empty
if (!themeJson.settings.color.palette || themeJson.settings.color.palette.length === 0) {
  themeJson.settings.color.custom = false;
  themeJson.settings.color.palette = [];
}
console.log(`🎨 Added ${uniqueColors.length} unique colors`);

// ------------------------------------------------------------
// 🌈 Generate gradients intelligently (linear AND radial)
// ------------------------------------------------------------

// Helper: Convert HEX to HSL
function hexToHSL(hex) {
  // Remove '#' if present
  let color = hex.replace(/^#/, "");
  if (color.length === 3) {
    color = color.split("").map(c => c + c).join("");
  }

  let r = parseInt(color.substr(0, 2), 16) / 255;
  let g = parseInt(color.substr(2, 2), 16) / 255;
  let b = parseInt(color.substr(4, 2), 16) / 255;

  const max = Math.max(r, g, b);
  const min = Math.min(r, g, b);
  let h, s, l = (max + min) / 2;

  if (max === min) {
    h = s = 0; // achromatic
  } else {
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

    switch (max) {
      case r:
        h = ((g - b) / d + (g < b ? 6 : 0)) / 6;
        break;
      case g:
        h = ((b - r) / d + 2) / 6;
        break;
      case b:
        h = ((r - g) / d + 4) / 6;
        break;
    }
  }

  h = Math.round(h * 360);
  s = Math.round(s * 100);
  l = Math.round(l * 100);

  return {
    h,
    s,
    l
  };
}

// Helper: Create smooth radial gradient with multiple stops
function createRadialGradient(hex, ellipseSize = "40% 60%", position = "55% 60%") {
  const hsl = hexToHSL(hex);
  const {
    h,
    s
  } = hsl;
  let {
    l
  } = hsl;

  // Generate 24 color stops from lighter to darker
  const stops = [];
  const startL = Math.min(l + 10, 90); // Start a bit lighter
  const endL = Math.max(l - 24, 10); // End much darker
  const step = (startL - endL) / 23; // 24 stops total

  const percentages = [
    0, 10, 15, 19, 23, 27, 31, 34, 38, 41, 45, 48,
    51, 55, 58, 61, 65, 68, 72, 76, 80, 84, 90, 100
  ];

  for (let i = 0; i < 24; i++) {
    const currentL = Math.round(startL - (step * i));
    const currentS = Math.max(s - Math.floor(i / 8), s - 2); // Slightly adjust saturation
    stops.push(`hsl(${h}deg ${currentS}% ${currentL}%) ${percentages[i]}%`);
  }

  return `radial-gradient(ellipse ${ellipseSize} at ${position}, ${stops.join(", ")})`;
}

// Helper to adjust color brightness (lighten or darken) - for linear gradients
function adjustColor(hex, amount) {
  // Remove '#' if present
  let color = hex.replace(/^#/, "");
  if (color.length === 3) {
    color = color.split("").map(c => c + c).join("");
  }
  let num = parseInt(color, 16);

  let r = (num >> 16) + amount;
  let g = ((num >> 8) & 0x00FF) + amount;
  let b = (num & 0x0000FF) + amount;

  r = Math.min(255, Math.max(0, r));
  g = Math.min(255, Math.max(0, g));
  b = Math.min(255, Math.max(0, b));

  return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

function createGradientName(a, b) {
  return `${a.slug}-${b.slug}`;
}

function createGradientLabel(a, b) {
  return `${a.name} → ${b.name}`;
}

const excluded = ["light", "dark", "white", "black"]; // avoid low-contrast combos
const seenGradients = new Set();
const gradientPairs = [];

// ------------------------------------------------------------
// 1️⃣ RADIAL GRADIENTS - One per color (smooth multi-stop)
// ------------------------------------------------------------
uniqueColors.forEach(color => {
  if (excluded.includes(color.slug)) return;

  const radialGradient = createRadialGradient(color.color);
  const slug = `${color.slug}-radial`;

  if (!seenGradients.has(slug)) {
    gradientPairs.push({
      slug,
      name: `${color.name} Radial`,
      gradient: radialGradient,
    });
    seenGradients.add(slug);
  }
});

// ------------------------------------------------------------
// 2️⃣ LINEAR GRADIENTS - Light-dark self-gradients
// ------------------------------------------------------------
uniqueColors.forEach(color => {
  if (excluded.includes(color.slug)) return;

  const lightColor = adjustColor(color.color, 40); // lighten by 40
  const darkColor = adjustColor(color.color, -40); // darken by 40

  const gradientString = `linear-gradient(135deg, ${lightColor}, ${darkColor})`;
  const slug = `${color.slug}`;

  if (!seenGradients.has(slug) && !gradientPairs.some(g => g.gradient === gradientString)) {
    gradientPairs.push({
      slug,
      name: `${color.name} Light → Dark`,
      gradient: gradientString,
    });
    seenGradients.add(slug);
  }
});

// ------------------------------------------------------------
// 3️⃣ LINEAR CROSS-PAIR GRADIENTS
// ------------------------------------------------------------
// Define intentional cross pairs
const crossPairs = [
  ["primary", "secondary"],
  ["secondary", "theme-color-1"],
  ["primary", "theme-color-1"],
];

// Map slugs to color objects for quick lookup
const colorMap = {};
uniqueColors.forEach(c => {
  colorMap[c.slug] = c;
});

// Generate cross-pair gradients
crossPairs.forEach(([slugA, slugB]) => {
  if (
    excluded.includes(slugA) ||
    excluded.includes(slugB) ||
    !colorMap[slugA] ||
    !colorMap[slugB]
  ) {
    return;
  }
  const a = colorMap[slugA];
  const b = colorMap[slugB];

  const gradientString = `linear-gradient(135deg, ${a.color}, ${b.color})`;
  const slug = createGradientName(a, b);

  if (!seenGradients.has(slug) && !gradientPairs.some(g => g.gradient === gradientString)) {
    gradientPairs.push({
      slug,
      name: createGradientLabel(a, b),
      gradient: gradientString,
    });
    seenGradients.add(slug);
  }
});

themeJson.settings.color.gradients = gradientPairs;

console.log(`🌈 Generated ${gradientPairs.length} unique gradients (radial + linear)`);

// ------------------------------------------------------------
// 🔀 Merge theme-extra.json if it exists
// ------------------------------------------------------------
let extraJson = {};
if (fs.existsSync(extraJsonPath)) {
  const rawExtra = fs.readFileSync(extraJsonPath, "utf8").trim();
  extraJson = rawExtra ? JSON.parse(rawExtra) : {};

  if (extraJson.settings) {
    // Deep merge all settings
    themeJson.settings = {
      ...themeJson.settings,
      ...extraJson.settings,
    };

    // Merge colors (prevent duplicates)
    if (extraJson.settings.color ?.palette) {
      extraJson.settings.color.palette.forEach(extraColor => {
        if (!seenColorSlugs.has(extraColor.slug)) {
          themeJson.settings.color.palette.push(extraColor);
          seenColorSlugs.add(extraColor.slug);
        }
      });
    }

    // Merge gradients (prevent duplicates)
    if (extraJson.settings.color ?.gradients) {
      extraJson.settings.color.gradients.forEach(extraGrad => {
        const existing = themeJson.settings.color.gradients;
        const hasSlug = seenGradients.has(extraGrad.slug);
        const hasGradientString = existing.some(g => g.gradient === extraGrad.gradient);
        if (!hasSlug && !hasGradientString) {
          themeJson.settings.color.gradients.push(extraGrad);
          seenGradients.add(extraGrad.slug);
        }
      });
    }
  }
}

// ------------------------------------------------------------
// 🅰️ Typography and radius (extract from SCSS)
// ------------------------------------------------------------
// Extract font families
const bodyFontFamilyMatch = scss.match(/\$body-font-family\s*:\s*([^;]+);/);
const monoFontFamilyMatch = scss.match(/\$font-family-monospace\s*:\s*([^;]+);/);

// Extract header styles for xlarge breakpoint
const headerStylesMatch = scss.match(/\$header-styles:\s*\(\s*([^)]+)\);/m);
let fontSizes = [];
if (headerStylesMatch) {
  // Find xlarge block
  const xlargeBlockMatch = headerStylesMatch[1].match(/xlarge\s*:\s*\(([\s\S]*?)\)(?:,|$)/m);
  if (xlargeBlockMatch) {
    const xlargeBlock = xlargeBlockMatch[1];
    // Find p, h1, h2, h3 font-size
    const tags = ["p", "h1", "h2", "h3"];
    tags.forEach(tag => {
      const tagMatch = xlargeBlock.match(new RegExp(`${tag}\\s*:\\s*\\(([^)]+)\\)`, "m"));
      if (tagMatch) {
        // Find font-size property in the tag block
        const fontSizeMatch = tagMatch[1].match(/font-size\s*:\s*([^,)\n]+)/);
        if (fontSizeMatch) {
          fontSizes.push({
            slug: tag,
            name: tag.toUpperCase(),
            size: fontSizeMatch[1].trim()
          });
        }
      }
    });
  }
}

themeJson.settings.typography = themeJson.settings.typography || {};
themeJson.settings.border = themeJson.settings.border || {};

// Font families
const fontFamilies = [];
if (bodyFontFamilyMatch) {
  fontFamilies.push({
    slug: "base",
    name: "Base",
    fontFamily: bodyFontFamilyMatch[1].trim()
  });
}
if (monoFontFamilyMatch) {
  fontFamilies.push({
    slug: "mono",
    name: "Monospace",
    fontFamily: monoFontFamilyMatch[1].trim()
  });
}
if (fontFamilies.length > 0) {
  themeJson.settings.typography.fontFamilies = fontFamilies;
}

// Font sizes
if (fontSizes.length > 0) {
  themeJson.settings.typography.fontSizes = fontSizes;
}

// Border radius (keep as before)
const radiusMatch = scss.match(/\$global-radius:\s*([\d.]+(rem|px));/);
if (radiusMatch) {
  themeJson.settings.border.radius = radiusMatch[1];
}

// ------------------------------------------------------------
// 📐 Layout + gutters (Foundation → Gutenberg)
// ------------------------------------------------------------

themeJson.settings.custom = themeJson.settings.custom || {};

// Extract gutter numeric values from SCSS map
const gutterMatch = scss.match(/\$grid-column-gutter:\s*\(([\s\S]*?)\);/);
if (gutterMatch) {
  const mapBody = gutterMatch[1];
  const lines = mapBody.split(",").map(l => l.trim());
  const gutterMap = {};
  lines.forEach(line => {
    const [key, val] = line.split(":").map(s => s.trim());
    gutterMap[key] = val; // val is e.g. 20px
  });

  // Convert to rem if desired (assuming base 16px)
  const toRem = px => {
    if (px.endsWith("px")) {
      return `${parseFloat(px)/16}rem`;
    }
    return px; // already rem or other unit
  };

  themeJson.settings.custom.foundationGutterMobile = toRem(gutterMap.small);
  themeJson.settings.custom.foundationGutterDesktop = toRem(gutterMap.medium);
}

// ------------------------------------------------------------
// 💾 Write theme.json
// ------------------------------------------------------------
fs.writeFileSync(themeJsonPath, JSON.stringify(themeJson, null, 2));
console.log(`✅ theme.json updated successfully with extras and no duplicates`);
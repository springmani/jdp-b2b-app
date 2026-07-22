# JD Power WordPress Theme

Custom WordPress theme for JD Power. Built around ACF blocks, custom post types, and shared filter components for product discovery and insight content.

## Overview

| Area | Location | Notes |
|---|---|---|
| Theme bootstrap | `functions.php` | Enqueues assets, loads `inc/` modules |
| ACF blocks | `template-parts/blocks/` | Gutenberg block templates |
| Styles | `sass/` → compiled to `style.css` | SCSS source; edit Sass, not compiled CSS (see **Compiling CSS** below) |
| Scripts | `js/script.js`, `js/post-filters.js` | Global UI + Product Finder / Insight Center AJAX |
| ACF field groups | `acf-json/` | Synced JSON definitions for post types, taxonomies, blocks |
| Shared includes | `inc/` | Filters, rewrites, GeoTarget, navigation, etc. |

### Key features

- **Product hierarchy** — Industries, Segments, Solutions, and Products with custom rewrite rules (`inc/industry-rewrites.php`).
- **Product Finder** — AJAX-filtered product grid with region, industry, segment, and solution filters (`page-templates/product-finder.php`, `inc/post-filters.php`).
- **Insight Center** — Shared filter system for news, events, and related content (`index.php` blog view).
- **Featured Solutions block** — Curated solution cards with an optional region dropdown (`template-parts/blocks/content-jdpower-featured-solutions.php`).
- **Polylang** — Theme strings registered in `inc/polylang-strings.php` (optional; no-op if Polylang is inactive).

After adding or changing ACF-registered post types in `acf-json/post_type_*.json`, visit **Settings → Permalinks → Save** once per environment to flush rewrite rules.

---

## Compiling CSS

From the theme directory:

```bash
npm install
npm run compile:css
```

Watch mode while developing:

```bash
npm run dev
```

Sass may print deprecation warnings; the build still succeeds. Edit files under `sass/`, not `style.css`.

---

## Regional content

Products and solutions are scoped by the **`product_region`** taxonomy. Region terms use stable slugs across environments:

| Term slug | MaxMind continent | Display name (example) |
|---|---|---|
| `na` | `NA` | North America |
| `eu` | `EU` | Europe |
| `oc` | `OC` | Oceania / Australia |

Display names can be edited in WP Admin; slugs must stay consistent across Local, Staging, and Production.

### Where regions are used

- **Product Finder** — Global region dropdown filters the product grid. Defaults to the visitor's detected region when no `?region=` param is present.
- **Featured Solutions block** — Region dropdown filters visible cards client-side. Cards carry `data-region-slugs` from each solution's **Product regions this solution is available in** field (`solution_available_regions` ACF).
- **Products** — Assigned directly via the `product_region` taxonomy.
- **Regional mismatch popup** — Singular hierarchy pages outside the visitor’s region (`inc/regional-content.php`, Theme Settings → Regional Content).

### Geo detection (WP Engine GeoTarget)

On WP Engine, the theme reads GeoTarget environment variables server-side and looks up a `product_region` term whose slug matches the lowercase continent code.

**Priority order:**

1. Explicit `?region=` in the URL or AJAX payload (user choice / shared link)
2. Geo-detected continent → `product_region` slug
3. Fallback: `na` (North America) when geo is unknown, unmapped, or the matching term does not exist

**Important behaviors:**

- Removing the region filter pill in Product Finder (`region=` sent as empty) shows **all regions** — geo is not re-applied.
- **Clear filters** resets the region back to the geo-detected default.

### PHP helpers

Defined in `inc/geotarget.php`:

| Function | Purpose |
|---|---|
| `jdpower_geotarget_get_continent_code()` | Visitor's MaxMind continent code |
| `jdpower_product_region_visitor_slug()` | Detected region slug (with `na` fallback) |
| `jdpower_geotarget_script_data()` | Cache-safe data for JavaScript |

Defined in `inc/post-filters.php`:

| Function | Purpose |
|---|---|
| `jdpower_product_region_default_slug()` | Fallback slug (`na`) |
| `jdpower_post_filters_parse_request()` | Parses filter state including region |

Geo detection maps the MaxMind continent code to a `product_region` term by **slug**: the continent code lowercased (e.g. `EU` → `eu`, `OC` → `oc`). Any term in the taxonomy is used automatically when its slug matches — no hardcoded map. If no matching term exists, the theme falls back to `na`.

Display names are free to edit in WP Admin; only slugs matter for geo matching.

### JavaScript globals

Cache-bucket-safe geo data is exposed on every front-end page:

```js
window.jdpowerGeo = {
    continent: 'EU',
    detectedRegionSlug: 'eu',
    fallbackRegionSlug: 'na',
    detectedRegionLabel: 'Europe'
};
```

On Product Finder pages, `jdpowerPostFilters.defaultRegionSlug` and `jdpowerPostFilters.geo` carry the same detected region for URL sync and AJAX.

Featured Solutions exposes a programmatic API for future region switching:

```js
window.jdpowerFeaturedSolutions.setRegion( gridDomId, regionSlug );
```

### Caching constraint

WP Engine GeoTarget page cache is recommended at the **continent** level. Because cached HTML is shared by all visitors in the same continent bucket, localized JavaScript must **not** include country, state, or city — only continent-level data. The regional mismatch popup uses continent-level region names from `product_region` terms (cache-safe).

---

## Testing regional content

### WP Engine staging / production

GeoTarget must be enabled on the environment (WP Engine Support + continent cache bucket).

Use native GeoTarget URL params — these populate `$_SERVER` / env vars on WP Engine:

```
https://example.com/product-finder/?geoip&continent=EU
https://example.com/product-finder/?geoip&country=DE
https://example.com/?geoip&continent=OC&country=AU
```

Supported params: `continent`, `country`, `region`, `city`, `postalcode`. See [WP Engine GeoTarget docs](https://wpengine.com/support/geotarget/).

### Local / Docker

GeoTarget env vars are not available locally. Use the dev-only override with a **MaxMind continent code** (`NA`, `EU`, `OC` — case-insensitive):

```
http://localhost/your-product-slug/?jdp_continent=OC
http://localhost/product-finder/?jdp_continent=EU
```

This override only applies when:

1. The GeoTarget continent env var is empty, **and**
2. `WP_DEBUG` is `true` **or** `wp_get_environment_type()` is `local` / `development`, **and**
3. `?geoip` is not present in the URL

On local, if the `oc` (etc.) `product_region` term does not exist yet, the override still uses that slug for mismatch checks so you can test the popup without falling back to `na`.

It is ignored on production so visitors cannot manipulate region defaults via URL guessing.

Without geo vars or a dev override, the theme falls back to `na`.

### Test scenarios

| Scenario | Environment | Expected |
|---|---|---|
| EU visitor, no `?region=` | WP Engine | Dropdown + grid default to `eu` |
| `?geoip&continent=OC` | WP Engine staging | Oceania / Australia selected; env vars populated |
| `?region=na` in URL | Any | URL wins over geo |
| User removes region pill | Any | All regions shown |
| Clear filters | Any | Resets to geo-detected region |
| No geo vars | Local Docker | Falls back to `na` |
| `?jdp_continent=EU` | Local + `WP_DEBUG` | Europe selected |
| `?jdp_continent=OC` | Local + `WP_DEBUG` | Oceania / Australia selected |
| `?jdp_continent=EU` | Production | Ignored |

### Verify region terms exist

Confirm all required `product_region` term slugs exist in **Products → Product Regions** (or via WP-CLI):

```
wp term list product_region --fields=slug,name
```

Expected slugs: `na`, `eu`, `oc`. If a continent maps to a missing term, the theme falls back to `na`.

---

## WP Engine prerequisites

1. GeoTarget product extension enabled (included on Premium plans).
2. WP Engine GeoTarget plugin installed and active.
3. GeoTarget profile enabled by WP Engine Support — use a **continent-level** cache bucket.
4. All three `product_region` term slugs (`na`, `eu`, `oc`) present in WP Admin.

---

## Regional mismatch popup

When a visitor lands on a singular **Industry**, **Segment**, **Solution**, or **Product** that is not available in their geo-detected region, the theme auto-opens a native dialog with copy from **Theme Settings → Regional Content**.

### Theme Settings (Regional Content tab)

| Setting | Purpose |
|---|---|
| Product Finder page | Continue button destination (also used as `jdpower_post_filters_product_finder_page_id`) |
| Continue viewing / View regional products labels | Card footer text on the two choices |
| Heading, Choices heading, Global region modal image | Shared across all post types |
| Per post type: Sub heading | Availability line below the heading (Industries, Segments, Solutions, Products) |

**Product Regions** taxonomy: **Region Modal Image** on each term — map for the right card when that region is detected.

The detected region **display name** (e.g. Europe) is highlighted inline in the heading from GeoTarget → `product_region` term name. Two cards: left uses the **global** map (stay on page); right uses the **detected region** term map (Product Finder).

### When the popup shows

1. Singular `industries`, `segments`, `solution`, or `product`
2. Post has at least one assigned region **and** the visitor’s detected slug is **not** in that set
3. Region Modal tab has heading, choices heading, or a sub heading for the current post type
4. Product Finder page is selected in Theme Settings

Posts with **no** regions assigned are treated as available everywhere (no popup).

### Actions

- **Continue Viewing This Page** (left card) — closes the dialog; URL unchanged; shows global map image
- **View Products Available In My Region** (right card) — Product Finder with `?region={detectedSlug}`; shows that region’s map image

### PHP helpers

Defined in `inc/regional-content.php`:

| Function | Purpose |
|---|---|
| `jdpower_post_region_slugs()` | Region slugs assigned to a post |
| `jdpower_post_unavailable_in_visitor_region()` | Mismatch check |
| `jdpower_regional_visitor_region_label()` | Detected region display name |
| `jdpower_regional_product_finder_url()` | Product Finder URL with optional `region` arg |
| `jdpower_regional_popup_global_modal_image()` | Global map (left card) |
| `jdpower_regional_popup_region_modal_image()` | Detected region term map (right card) |

### Local testing

With `WP_DEBUG` enabled, append `?jdp_continent=EU` (or `eu`) to a mismatched singular URL to simulate a European visitor. On WP Engine staging, use `?geoip&continent=EU`.

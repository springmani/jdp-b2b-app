# Bootstrap Grid (theme copy)

Minimal copy of Bootstrap SCSS used by this theme: **containers**, **grid**, **breakpoint mixins**, **layout/spacing utilities**, plus **recommended utilities** and **helpers**. No `:root`, no components (buttons, nav, cards, etc.).

**Entry point:** `scss/bootstrap-grid.scss`

**Utilities included:** display, flex, margin, padding, **position** (top/start/end/bottom), **overflow**, **visibility**, **shadow**, **border** & **rounded**, **width/height** (w-100, vh-100, etc.), **font-size**, **font-weight**, **text-align**.

**Helpers:** `.visually-hidden` / `.visually-hidden-focusable` (a11y), `.stretched-link`. (Use native CSS `aspect-ratio` for aspect ratio.)

**Files included:**
- `_functions.scss`, `_variables.scss`, `_containers.scss`, `_grid.scss`, `_utilities.scss`
- `mixins/`: `_lists.scss`, `_breakpoints.scss`, `_container.scss`, `_grid.scss`, `_utilities.scss`, `_visually-hidden.scss`
- `vendor/_rfs.scss`
- `helpers/`: `_visually-hidden.scss`, `_stretched-link.scss`
- `utilities/_api.scss`

Breakpoints and grid defaults are overridden in `sass/variables/_bootstrap-grid-overrides.scss` (loaded before this in `style.scss`).

Once this is confirmed working, the full `sass/bootstrap/` folder can be removed.

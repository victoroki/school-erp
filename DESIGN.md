# School ERP — Design Tokens

## Colors (OKLCH-derived, tinted toward indigo)

### Neutrals
```
--slate-50:  oklch(0.984 0.003 264)  /* #f8fafc */
--slate-100: oklch(0.967 0.005 264)  /* #f1f5f9 */
--slate-200: oklch(0.928 0.008 264)  /* #e2e8f0 */
--slate-300: oklch(0.869 0.012 264)  /* #cbd5e1 */
--slate-400: oklch(0.704 0.015 264)  /* #94a3b8 */
--slate-500: oklch(0.554 0.018 264)  /* #64748b */
--slate-600: oklch(0.446 0.018 264)  /* #475569 */
--slate-700: oklch(0.372 0.016 264)  /* #334155 */
--slate-800: oklch(0.278 0.013 264)  /* #1e293b */
--slate-900: oklch(0.206 0.010 264)  /* #0f172a */
```

### Brand — Indigo
```
--indigo-50:  oklch(0.962 0.018 272)  /* #eef2ff */
--indigo-100: oklch(0.930 0.034 272)  /* #e0e7ff */
--indigo-500: oklch(0.555 0.210 272)  /* #6366f1 */
--indigo-600: oklch(0.511 0.230 272)  /* #4f46e5 */
--indigo-700: oklch(0.457 0.220 272)  /* #4338ca */
```

### Financial — Amber
```
--amber-50:  oklch(0.980 0.022 95)  /* #fffbeb */
--amber-100: oklch(0.962 0.044 95)  /* #fef3c7 */
--amber-500: oklch(0.769 0.188 70)  /* #f59e0b */
--amber-600: oklch(0.666 0.179 58)  /* #d97706 */
```

### Semantic
```
--emerald-50:  oklch(0.979 0.021 166) /* #ecfdf5 */
--emerald-500: oklch(0.696 0.170 162) /* #10b981 */
--emerald-600: oklch(0.596 0.145 163) /* #059669 */

--rose-50:  oklch(0.969 0.015 12)    /* #fff1f2 */
--rose-500: oklch(0.643 0.220 22)    /* #f43f5e */
--rose-600: oklch(0.575 0.210 22)    /* #e11d48 */
```

## Typography
- **Font:** System font stack (Inter if available)
- **Scale:** 0.7rem (labels) → 0.75rem (body small) → 0.85rem (body) → 1rem (section) → 1.25rem (page title) → 1.5rem (hero) → 2rem+ (metric values)
- **Weights:** 500 (muted), 600 (body), 700 (labels, badges), 800 (section headers), 900 (page titles, metric values)
- **Monospace:** For all currency amounts

## Spacing
- Card padding: 1.25rem–1.5rem
- Row gap: 1rem–1.5rem (varies by section)
- Item padding in lists: 0.75rem 1.25rem

## Radius
- Cards: 12px
- Buttons: 8px
- Badges/tags: 6px
- Icon boxes: 10px
- Hero/metric cards: 16px

## Elevation
- Default card: `0 1px 3px rgba(0,0,0,0.06)` + `1px solid var(--border)`
- Hover card: `0 4px 12px rgba(0,0,0,0.1)`
- Hero card: `0 4px 24px rgba(79, 70, 229, 0.25)`
- Dropdown: `0 10px 40px rgba(0,0,0,0.12)`

## Motion
```css
--ease-out: cubic-bezier(0.23, 1, 0.32, 1);
--ease-spring: cubic-bezier(0.32, 0.72, 0, 1);
```
- Button press: `transform: scale(0.97)` at 160ms
- Card hover: shadow transition at 200ms
- No layout property animations

## Component Patterns

### Page Header
```
[Icon Box] [Page Title]
           [Page Subtitle]                          [Ghost Buttons]
```

### Metric Cards
Not AdminLTE small-boxes. Clean white cards with:
- Label (uppercase, muted, 0.7rem)
- Value (monospace, large, 900 weight)
- Subtle icon (indigo tint, right-aligned)
- Full border, not colored backgrounds

### Data Tables
- Header: slate-100 background, uppercase labels, 0.7rem
- Rows: alternating slate-50, white
- Status: colored badge with semantic colors
- Amounts: monospace, right-aligned
- Actions: ghost buttons with icons

### Detail Cards
- Header bar with icon + uppercase label
- Rows: label (left, muted) | value (right)
- Divider between rows (slate-100)

### Buttons
- Primary: indigo-600 bg, white text, 8px radius
- Ghost: white bg, 1px border, slate-700 text
- Active state: scale(0.97) at 160ms

## Print Styles
- Hide navigation, sidebar, action buttons
- Show school logo, clean table borders
- Use pt units for print typography
- @page margin: 20mm

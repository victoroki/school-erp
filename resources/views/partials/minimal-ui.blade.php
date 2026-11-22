{{-- Shared minimal UI styles (warm monochrome, editorial). Used by finance and academics pages. --}}
@push('page_css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Geist+Mono:wght@400;500&family=Newsreader:opsz,wght@6..72,400&display=swap">
@endpush

@push('page_css')
<style>
    .ba {
        --ba-canvas: #F7F6F3;
        --ba-surface: #FEFEFD;
        --ba-line: #EAEAE7;
        --ba-ink: #151514;
        --ba-ink-2: #2F3437;
        --ba-muted: #787774;
        --ba-faint: #A5A49F;
        --ba-red-bg: #FDEBEC;    --ba-red: #9F2F2D;
        --ba-green-bg: #EDF3EC;  --ba-green: #346538;
        --ba-yellow-bg: #FBF3DB; --ba-yellow: #956400;
        --ba-sans: "Geist", "SF Pro Display", "Helvetica Neue", system-ui, sans-serif;
        --ba-serif: "Newsreader", "Lyon Text", Georgia, serif;
        --ba-mono: "Geist Mono", "SF Mono", ui-monospace, monospace;

        max-width: 1120px;
        padding: 32px 24px 64px;
        font-family: var(--ba-sans);
        font-size: 14.5px;
        line-height: 1.6;
        color: var(--ba-ink-2);
    }

    /* Header */
    .ba-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 32px;
    }
    .ba-title {
        margin: 0;
        font-family: var(--ba-serif);
        font-weight: 400;
        font-size: 36px;
        line-height: 1.1;
        letter-spacing: -0.025em;
        color: var(--ba-ink);
    }
    .ba-sub { margin: 6px 0 0; color: var(--ba-muted); }

    .ba-btn {
        display: inline-flex;
        align-items: center;
        height: 38px;
        padding: 0 16px;
        border-radius: 6px;
        background: var(--ba-ink);
        color: var(--ba-surface);
        font-size: 14px;
        font-weight: 500;
        white-space: nowrap;
        text-decoration: none;
        transition: background-color 150ms ease, transform 160ms cubic-bezier(0.23, 1, 0.32, 1);
    }
    .ba-btn:hover { background: #333331; color: var(--ba-surface); text-decoration: none; }
    .ba-btn:active { transform: scale(0.97); }
    .ba-btn:focus-visible { outline: 2px solid var(--ba-ink); outline-offset: 2px; }

    /* Summary strip */
    .ba-summary {
        display: grid;
        grid-template-columns: 1.3fr 1fr 1fr 1fr;
        margin: 0 0 40px;
        border: 1px solid var(--ba-line);
        border-radius: 8px;
        background: var(--ba-surface);
    }
    .ba-stat { padding: 20px 24px; border-left: 1px solid var(--ba-line); }
    .ba-stat:first-child { border-left: 0; }
    .ba-stat dt { font-size: 13px; font-weight: 400; color: var(--ba-muted); }
    .ba-stat dd {
        margin: 4px 0 0;
        font-size: 17px;
        color: var(--ba-ink);
        white-space: nowrap;
    }
    .ba-stat-main dd { font-size: 26px; letter-spacing: -0.01em; }
    .ba-num { font-family: var(--ba-mono); font-variant-numeric: tabular-nums; font-weight: 400; }
    .ba-num.is-red { color: var(--ba-red); }

    /* Account list */
    .ba-list {
        border-top: 1px solid var(--ba-line);
    }
    .ba-row {
        display: grid;
        grid-template-columns: minmax(0, 2.4fr) minmax(0, 1fr) minmax(0, 1.5fr) minmax(0, 1.3fr) auto;
        align-items: center;
        gap: 24px;
        padding: 18px 4px;
        border-bottom: 1px solid var(--ba-line);
    }
    .ba-row-head {
        padding-top: 10px;
        padding-bottom: 10px;
        font-size: 12.5px;
        color: var(--ba-muted);
    }
    .ba-row.is-inactive .ba-name,
    .ba-row.is-inactive .ba-balance { color: var(--ba-muted); }

    .ba-account { display: flex; flex-direction: column; min-width: 0; }
    .ba-name {
        color: var(--ba-ink);
        font-weight: 500;
        text-decoration: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ba-name:hover { color: var(--ba-ink); text-decoration: underline; text-underline-offset: 3px; text-decoration-color: var(--ba-line); }
    .ba-meta { font-size: 13px; color: var(--ba-muted); }
    .ba-mono { font-family: var(--ba-mono); font-size: 12.5px; font-variant-numeric: tabular-nums; margin-left: 4px; }
    .ba-min .ba-mono { margin-left: 0; color: var(--ba-ink-2); }

    .ba-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .ba-tag {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 9999px;
        background: var(--ba-canvas);
        border: 1px solid var(--ba-line);
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--ba-ink-2);
    }
    .ba-tag-muted { color: var(--ba-muted); }

    .ba-min-line { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-bottom: 6px; }
    .ba-state { font-size: 12px; font-weight: 500; }
    .ba-state.is-red { color: var(--ba-red); }
    .ba-state.is-yellow { color: var(--ba-yellow); }
    .ba-state.is-green { color: var(--ba-green); }
    .ba-track { height: 3px; background: var(--ba-line); border-radius: 2px; overflow: hidden; }
    .ba-fill { display: block; height: 100%; border-radius: 2px; }
    .ba-fill.is-red { background: var(--ba-red); }
    .ba-fill.is-yellow { background: var(--ba-yellow); }
    .ba-fill.is-green { background: var(--ba-green); }
    .ba-none { font-size: 13px; color: var(--ba-faint); }

    .ba-right { text-align: right; }
    .ba-balance {
        font-family: var(--ba-mono);
        font-variant-numeric: tabular-nums;
        font-size: 15px;
        color: var(--ba-ink);
        white-space: nowrap;
    }

    .ba-actions { display: flex; align-items: center; gap: 4px; justify-content: flex-end; }
    .ba-link {
        font-size: 13px;
        color: var(--ba-muted);
        text-decoration: underline;
        text-decoration-color: var(--ba-line);
        text-underline-offset: 3px;
        padding: 4px 6px;
        transition: color 120ms ease, text-decoration-color 120ms ease;
    }
    .ba-link:hover { color: var(--ba-ink); text-decoration-color: var(--ba-ink); }
    .ba-more {
        width: 30px; height: 30px;
        display: inline-flex; align-items: center; justify-content: center;
        border: 0; border-radius: 4px; padding: 0;
        background: transparent; color: var(--ba-muted); cursor: pointer;
        transition: background-color 120ms ease, color 120ms ease;
    }
    .ba-more svg { width: 14px; height: 14px; }
    .ba-more:hover, .ba-more[aria-expanded="true"] { background: var(--ba-canvas); color: var(--ba-ink); }
    .ba-more:focus-visible, .ba-link:focus-visible, .ba-name:focus-visible { outline: 2px solid var(--ba-ink); outline-offset: 1px; }

    .ba-menu {
        min-width: 160px;
        padding: 4px;
        border: 1px solid var(--ba-line);
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
        font-size: 14px;
    }
    .ba-menu .dropdown-item { border-radius: 4px; padding: 6px 10px; color: var(--ba-ink-2); background: none; width: 100%; text-align: left; }
    .ba-menu .dropdown-item:hover { background: var(--ba-canvas); color: var(--ba-ink); }
    .ba-menu .dropdown-divider { margin: 4px 0; border-color: var(--ba-line); }
    .ba-menu .ba-danger { color: var(--ba-red); }
    .ba-menu .ba-danger:hover { background: var(--ba-red-bg); color: var(--ba-red); }

    /* Empty + pager */
    .ba-empty {
        padding: 56px 24px;
        border: 1px solid var(--ba-line);
        border-radius: 8px;
        text-align: center;
        background: var(--ba-surface);
    }
    .ba-empty-title { margin: 0; font-size: 16px; font-weight: 500; color: var(--ba-ink); }
    .ba-empty-text { margin: 4px auto 20px; max-width: 42ch; color: var(--ba-muted); }

    .ba-pager { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 20px; }
    .ba-pager p { margin: 0; font-size: 13px; color: var(--ba-muted); }
    .ba-pager .pagination { margin: 0; }

    /* ── Ledger (bank transactions) ───────────────────────────── */

    .ba-stat-note { margin: 2px 0 0; font-size: 12.5px; color: var(--ba-muted); }
    .ba-num.is-green { color: var(--ba-green); }

    .ba-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 12px;
        margin: 0 0 24px;
    }
    .ba-field { display: flex; flex-direction: column; gap: 6px; min-width: 200px; }
    .ba-field label { margin: 0; font-size: 12.5px; font-weight: 400; color: var(--ba-muted); }
    .ba-select {
        height: 38px;
        padding: 0 32px 0 12px;
        font: inherit;
        font-size: 14px;
        color: var(--ba-ink);
        background: var(--ba-surface) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%23787774' stroke-width='1.6'%3E%3Cpath d='M4 6l4 4 4-4'/%3E%3C/svg%3E") no-repeat right 10px center / 14px;
        border: 1px solid var(--ba-line);
        border-radius: 6px;
        -webkit-appearance: none;
                appearance: none;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }
    .ba-select:focus { outline: none; border-color: var(--ba-ink); box-shadow: 0 0 0 3px rgba(21, 21, 20, 0.06); }
    .ba-btn-ghost {
        display: inline-flex; align-items: center;
        height: 38px; padding: 0 14px;
        border: 1px solid var(--ba-line); border-radius: 6px;
        background: var(--ba-surface); color: var(--ba-ink);
        font: inherit; font-size: 14px; font-weight: 500; cursor: pointer;
        transition: border-color 150ms ease, transform 160ms cubic-bezier(0.23, 1, 0.32, 1);
    }
    .ba-btn-ghost:hover { border-color: #CFCFCB; }
    .ba-btn-ghost:active { transform: scale(0.97); }
    .ba-btn-ghost:focus-visible { outline: 2px solid var(--ba-ink); outline-offset: 2px; }
    .ba-filters .ba-link { align-self: center; }

    .ba-row-ledger {
        grid-template-columns: 96px minmax(0, 1.6fr) minmax(0, 0.8fr) minmax(0, 2fr) minmax(0, 1fr) minmax(0, 1.2fr);
        padding-top: 14px;
        padding-bottom: 14px;
    }
    .ba-row-ledger.ba-row-head { padding-top: 10px; padding-bottom: 10px; }
    .ba-date { font-family: var(--ba-mono); font-size: 12.5px; color: var(--ba-ink-2); white-space: nowrap; font-variant-numeric: tabular-nums; }
    .ba-cell { display: flex; flex-direction: column; min-width: 0; }
    .ba-strong { color: var(--ba-ink); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ba-small { font-size: 13px; color: var(--ba-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ba-small .ba-mono { margin-left: 0; }

    .ba-tag-green  { background: var(--ba-green-bg);  border-color: transparent; color: var(--ba-green); }
    .ba-tag-yellow { background: var(--ba-yellow-bg); border-color: transparent; color: var(--ba-yellow); }

    .ba-amount { font-family: var(--ba-mono); font-variant-numeric: tabular-nums; font-size: 14.5px; white-space: nowrap; color: var(--ba-ink); }
    .ba-amount.is-in { color: var(--ba-green); }
    .ba-amount.is-transfer { color: var(--ba-muted); }

    .ba-empty-row { padding: 48px 4px; text-align: center; color: var(--ba-muted); border-bottom: 1px solid var(--ba-line); }

    @media (prefers-reduced-motion: reduce) {
        .ba-btn-ghost, .ba-select { transition: none; }
        .ba-btn-ghost:active { transform: none; }
    }

    @media (max-width: 860px) {
        .ba-field { min-width: 0; flex: 1 1 160px; }
        .ba-row-ledger {
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-areas: "acct amt" "desc status" "date type";
        }
        .ba-row-ledger > :nth-child(1) { grid-area: date; }
        .ba-row-ledger > :nth-child(2) { grid-area: acct; }
        .ba-row-ledger > :nth-child(3) { grid-area: type; justify-self: end; }
        .ba-row-ledger > :nth-child(4) { grid-area: desc; }
        .ba-row-ledger > :nth-child(5) { grid-area: status; justify-self: end; }
        .ba-row-ledger > :nth-child(6) { grid-area: amt; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ba-btn, .ba-link, .ba-more { transition: none; }
        .ba-btn:active { transform: none; }
    }

    /* Narrow screens: rows stack, header row hides. */
    @media (max-width: 860px) {
        .ba { padding: 24px 16px 48px; }
        .ba-head { flex-direction: column; align-items: flex-start; }
        .ba-title { font-size: 30px; }
        .ba-summary { grid-template-columns: 1fr 1fr; }
        .ba-stat-main { grid-column: 1 / -1; border-bottom: 1px solid var(--ba-line); }
        .ba-stat:nth-child(2) { border-left: 0; }
        .ba-stat:nth-child(4) { grid-column: 1 / -1; border-left: 0; border-top: 1px solid var(--ba-line); }
        .ba-row-head { display: none; }
        .ba-row {
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-areas: "acct bal" "tags actions" "min min";
            gap: 10px 16px;
        }
        .ba-account { grid-area: acct; }
        .ba-right { grid-area: bal; }
        .ba-tags { grid-area: tags; }
        .ba-actions { grid-area: actions; }
        .ba-min { grid-area: min; }
        .ba-pager { flex-direction: column; align-items: flex-start; }
    }
</style>
@endpush

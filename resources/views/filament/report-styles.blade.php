{{-- Plain CSS for report pages; the admin panel's Tailwind build doesn't include app classes. --}}
<style>
    /* Shared colours for both themes. Light first, then dark (Filament puts .dark on <html>). */
    :root {
        --yg-link: #047857; --yg-done: #059669; --yg-muted: #6b7280; --yg-rule: #e5e7eb; --yg-strong: #111827;
        --yg-negative: #dc2626; --yg-warn: #b45309;
        --yg-panel: #ffffff; --yg-panel-border: #e5e7eb; --yg-head: #f9fafb; --yg-hover: #f3f4f6;
        --yg-share: #059669; --yg-share-track: #ecfdf5;
    }
    .dark {
        --yg-link: #34d399; --yg-done: #34d399; --yg-muted: #9ca3af; --yg-rule: rgba(255,255,255,.08); --yg-strong: #f9fafb;
        --yg-negative: #f87171; --yg-warn: #fbbf24;
        --yg-panel: rgba(255,255,255,.02); --yg-panel-border: rgba(255,255,255,.1); --yg-head: rgba(255,255,255,.04); --yg-hover: rgba(255,255,255,.05);
        --yg-share: #34d399; --yg-share-track: rgba(52,211,153,.12);
    }
    @include('reports.statement-css')
    .yg-sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
    .yg-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr)); align-items: start; }
    /* Breakdown tables: stacked full width, or three side by side once each has room (never two plus one). */
    .yg-tables { container-type: inline-size; }
    .yg-grid--tables { gap: 1rem; grid-template-columns: minmax(0, 1fr); }
    @container (min-width: 64rem) { .yg-grid--tables { grid-template-columns: repeat(3, minmax(0, 1fr)); } .yg-grid--tables .yg-table th, .yg-grid--tables .yg-table td { padding-left: .5rem; padding-right: .5rem; } .yg-grid--tables .yg-table th:first-child, .yg-grid--tables .yg-table td:first-child { padding-left: .875rem; } .yg-grid--tables .yg-table th:last-child, .yg-grid--tables .yg-table td:last-child { padding-right: .875rem; } }

    /* Report tables: one look everywhere (What sold, Stock age). Each sits in a panel, rows keep their natural height. */
    .yg-panel { border: 1px solid var(--yg-panel-border); border-radius: .75rem; background: var(--yg-panel); overflow-x: auto; }
    .yg-table { width: 100%; border-collapse: collapse; font-size: .875rem; font-variant-numeric: tabular-nums; color: var(--yg-strong); }
    .yg-table caption { text-align: left; font-weight: 600; font-size: .875rem; padding: .75rem 1rem .6rem; color: var(--yg-strong); }
    .yg-table th, .yg-table td { padding: .55rem .75rem; border-bottom: 1px solid var(--yg-rule); text-align: right; white-space: nowrap; vertical-align: middle; }
    .yg-table th:first-child, .yg-table td:first-child { padding-left: 1rem; text-align: left; white-space: normal; min-width: 6rem; }
    .yg-table th:last-child, .yg-table td:last-child { padding-right: 1rem; }
    .yg-table thead th { background: var(--yg-head); font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--yg-muted); border-top: 1px solid var(--yg-rule); }
    .yg-table tbody th { font-weight: 500; }
    .yg-table tbody tr { transition: background-color .15s; }
    .yg-table tbody tr:hover { background: var(--yg-hover); }
    .yg-table tbody tr:last-child th, .yg-table tbody tr:last-child td { border-bottom: 0; }
    .yg-table tfoot th, .yg-table tfoot td { font-weight: 600; background: var(--yg-head); border-top: 1px solid var(--yg-panel-border); border-bottom: 0; }
    .yg-table__label { display: block; }
    .yg-share { display: block; height: 3px; margin-top: .3rem; max-width: 7rem; border-radius: 999px; background: var(--yg-share-track); overflow: hidden; }
    .yg-share > span { display: block; height: 100%; border-radius: inherit; background: var(--yg-share); }
    .yg-table__empty { color: var(--yg-muted); font-style: italic; text-align: left !important; }
    .yg-table .yg-neg { color: var(--yg-negative); }
    /* Guide */
    .yg-help { display: grid; gap: 1.5rem; grid-template-columns: 1fr; }
    @media (min-width: 1024px) { .yg-help { grid-template-columns: 15rem minmax(0, 1fr); } }
    .yg-help__nav ul { list-style: none; margin: 0 0 1rem; padding: 0; }
    .yg-help__nav a { display: block; padding: .3rem .6rem; border-radius: .5rem; font-size: .875rem; color: inherit; text-decoration: none; }
    .yg-help__nav a:hover { background: var(--yg-rule); }
    .yg-help__nav a.is-active { background: var(--yg-rule); font-weight: 600; }
    .yg-help__group { margin: .5rem 0 .25rem; padding: 0 .6rem; font-size: .7rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; color: var(--yg-muted); }
    .yg-help__main { min-width: 0; display: flex; flex-direction: column; gap: 1rem; }
    .yg-help__intro a, .yg-help__next a { color: var(--yg-link); font-weight: 600; }
    .yg-help__next { margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--yg-rule); }
    .yg-help__heading { margin-top: .5rem; font-size: 1rem; font-weight: 600; }
    .yg-help__cards { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr)); }
    .yg-help__card { display: flex; flex-direction: column; gap: .25rem; padding: 1rem; border: 1px solid var(--yg-rule); border-radius: .75rem; color: inherit; text-decoration: none; }
    .yg-help__card:hover { border-color: var(--yg-link); }
    .yg-help__card-title { font-weight: 600; }
    .yg-help__card-text { font-size: .875rem; color: var(--yg-muted); }
    .yg-prose { max-width: 46rem; line-height: 1.65; }
    .yg-prose > * + * { margin-top: .9rem; }
    .yg-prose h2 { margin-top: 1.75rem; font-size: 1.15rem; font-weight: 600; }
    .yg-prose ul { list-style: disc; padding-left: 1.25rem; }
    .yg-prose ol { list-style: decimal; padding-left: 1.25rem; }
    .yg-prose li + li { margin-top: .3rem; }
    .yg-prose li > ul, .yg-prose li > ol { margin-top: .3rem; }
    .yg-prose a { color: var(--yg-link); text-decoration: underline; }
    .yg-prose code { padding: .1rem .3rem; border-radius: .3rem; background: var(--yg-rule); font-size: .85em; }
    .yg-prose table { width: 100%; border-collapse: collapse; font-size: .9rem; }
    .yg-prose th, .yg-prose td { padding: .45rem .6rem; border-bottom: 1px solid var(--yg-rule); text-align: left; vertical-align: top; }
    .yg-prose th { font-weight: 600; }
    .yg-checklist { list-style: none; margin: 0; padding: 0; }
    .yg-checklist li { display: flex; gap: .75rem; align-items: flex-start; padding: .65rem 0; border-bottom: 1px solid var(--yg-rule); }
    .yg-checklist li:last-child { border-bottom: 0; }
    .yg-checklist__icon { flex: none; width: 1.25rem; height: 1.25rem; margin-top: .1rem; }
    .yg-checklist__icon.is-done { color: var(--yg-done); }
    .yg-checklist__icon.is-todo { color: var(--yg-muted); }
    .yg-checklist__body { flex: 1; min-width: 0; }
    .yg-checklist__title { font-weight: 600; }
    .yg-checklist li.is-done .yg-checklist__title { color: var(--yg-muted); text-decoration: line-through; font-weight: 500; }
    .yg-checklist__text { font-size: .875rem; color: var(--yg-muted); }
    .yg-checklist__links { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: .3rem; font-size: .85rem; }
    .yg-checklist__links a, .yg-checklist__links button { color: var(--yg-link); font-weight: 600; }
    .yg-progress { height: .5rem; border-radius: 999px; background: var(--yg-rule); overflow: hidden; margin-bottom: .5rem; }
    .yg-progress > span { display: block; height: 100%; background: var(--yg-done); }
    .yg-tips-toggle { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .5rem .75rem; font-size: .875rem; }
    .yg-tips-toggle__label { display: block; font-weight: 500; }
    .yg-tips-toggle__hint { display: block; font-size: .75rem; color: var(--yg-muted); }
    .yg-switch { flex: none; position: relative; width: 2.25rem; height: 1.25rem; border-radius: 999px; background: var(--yg-rule); transition: background .15s; }
    .yg-switch > span { position: absolute; top: .125rem; left: .125rem; width: 1rem; height: 1rem; border-radius: 999px; background: #fff; box-shadow: 0 1px 2px rgb(0 0 0 / .3); transition: transform .15s; }
    .yg-switch.is-on { background: var(--yg-done); }
    .yg-switch.is-on > span { transform: translateX(1rem); }
    /* Motion: each admin page eases in; cards and rows respond to the pointer. Off for reduced motion. */
    @keyframes yg-enter { from { opacity: 0; transform: translateY(6px); } }
    .fi-main { animation: yg-enter .32s cubic-bezier(.2,.7,.2,1) backwards; }
    .fi-wi-stats-overview-stat, .fi-section, .yg-checklist { transition: box-shadow .25s, transform .25s; }
    .fi-wi-stats-overview-stat:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -12px rgb(0 0 0 / .25); }
    .fi-ta-row { transition: background-color .15s; }
    .fi-btn, .fi-icon-btn, .fi-sidebar-item-button { transition: background-color .15s, color .15s, transform .12s; }
    .fi-btn:active, .fi-icon-btn:active { transform: scale(.97); }
    @media (prefers-reduced-motion: reduce) {
        .fi-main { animation: none; }
        .fi-wi-stats-overview-stat:hover, .fi-btn:active, .fi-icon-btn:active { transform: none; }
    }
    /* Clickable report rows and the details panels they open. */
    .yg-row-link { cursor: pointer; }
    .yg-row-link:focus-visible { outline: 2px solid var(--yg-link); outline-offset: -2px; }
    .yg-row-link th .yg-table__label::after { content: ' ›'; color: var(--yg-muted); opacity: 0; transition: opacity .15s; }
    .yg-row-link:hover th .yg-table__label::after, .yg-row-link:focus-visible th .yg-table__label::after { opacity: 1; }
    .yg-table__sub { display: block; margin-top: .1rem; font-size: .75rem; font-weight: 400; color: var(--yg-muted); }
    .yg-table__link { color: var(--yg-link); font-weight: 500; }
    .yg-table__link:hover { text-decoration: underline; }
    .yg-details { display: grid; gap: 1.25rem; }
    .yg-details__foot { font-size: .8rem; color: var(--yg-muted); }
    .yg-callout { display: flex; gap: .75rem; align-items: flex-start; padding: .9rem 1rem; border-radius: .75rem; border: 1px solid var(--yg-panel-border); background: var(--yg-head); font-size: .875rem; color: var(--yg-strong); }
    .yg-callout__icon { flex: none; width: 1.25rem; height: 1.25rem; margin-top: .05rem; color: var(--yg-muted); }
    .yg-callout__title { font-weight: 600; margin-bottom: .15rem; }
    .yg-callout.is-buy { border-color: color-mix(in srgb, var(--yg-done) 40%, transparent); background: color-mix(in srgb, var(--yg-done) 10%, transparent); }
    .yg-callout.is-buy .yg-callout__icon { color: var(--yg-done); }
    .yg-callout.is-careful { border-color: color-mix(in srgb, var(--yg-warn) 40%, transparent); background: color-mix(in srgb, var(--yg-warn) 10%, transparent); }
    .yg-callout.is-careful .yg-callout__icon { color: var(--yg-warn); }
    .yg-stats { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fill, minmax(9.5rem, 1fr)); }
    .yg-stat { padding: .75rem .9rem; border: 1px solid var(--yg-panel-border); border-radius: .75rem; background: var(--yg-panel); }
    .yg-stat dt { font-size: .7rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--yg-muted); }
    .yg-stat dd { margin-top: .2rem; font-size: 1.15rem; font-weight: 600; font-variant-numeric: tabular-nums; color: var(--yg-strong); }
    .yg-stat dd.yg-stat__text { font-size: 1rem; }
    .yg-stat dd.yg-neg { color: var(--yg-negative); }
    .yg-stat__note { display: block; margin-top: .1rem; font-size: .72rem; color: var(--yg-muted); }
</style>

{{-- Plain CSS for report pages; the admin panel's Tailwind build doesn't include app classes. --}}
<style>
    :root { --yg-link: #047857; --yg-done: #059669; --yg-muted: #6b7280; --yg-rule: #e5e7eb; --yg-strong: #111827; --yg-negative: #dc2626; --yg-warn: #b45309; }
    .dark { --yg-link: #34d399; --yg-done: #34d399; --yg-muted: #9ca3af; --yg-rule: rgba(255,255,255,.1); --yg-strong: #f9fafb; --yg-negative: #f87171; --yg-warn: #fbbf24; }
    @include('reports.statement-css')
    .yg-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr)); }
    .yg-table { width: 100%; border-collapse: collapse; font-size: .875rem; font-variant-numeric: tabular-nums; }
    .yg-table caption { text-align: left; font-weight: 600; padding-bottom: .5rem; }
    .yg-table th, .yg-table td { padding: .4rem .5rem; border-bottom: 1px solid var(--yg-rule); text-align: right; white-space: nowrap; }
    .yg-table th:first-child, .yg-table td:first-child { text-align: left; white-space: normal; }
    .yg-table thead th { font-size: .75rem; font-weight: 500; color: var(--yg-muted); }
    .yg-table__empty { color: var(--yg-muted); font-style: italic; }
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
</style>

{{-- Plain CSS for report pages; the admin panel's Tailwind build doesn't include app classes. --}}
<style>
    :root { --yg-muted: #6b7280; --yg-rule: #e5e7eb; --yg-strong: #111827; --yg-negative: #dc2626; --yg-warn: #b45309; }
    .dark { --yg-muted: #9ca3af; --yg-rule: rgba(255,255,255,.1); --yg-strong: #f9fafb; --yg-negative: #f87171; --yg-warn: #fbbf24; }
    @include('reports.statement-css')
    .yg-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr)); }
    .yg-table { width: 100%; border-collapse: collapse; font-size: .875rem; font-variant-numeric: tabular-nums; }
    .yg-table caption { text-align: left; font-weight: 600; padding-bottom: .5rem; }
    .yg-table th, .yg-table td { padding: .4rem .5rem; border-bottom: 1px solid var(--yg-rule); text-align: right; white-space: nowrap; }
    .yg-table th:first-child, .yg-table td:first-child { text-align: left; white-space: normal; }
    .yg-table thead th { font-size: .75rem; font-weight: 500; color: var(--yg-muted); }
    .yg-table__empty { color: var(--yg-muted); font-style: italic; }
</style>

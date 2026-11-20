<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $statement->title }} — {{ $statement->period }}</title>
    <style>
        @page { margin: 28mm 20mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; color: #111827; }
        .brand { font-size: 9pt; color: #047857; letter-spacing: .08em; text-transform: uppercase; margin-bottom: 4mm; }
        .generated { margin-top: 8mm; font-size: 8pt; color: #6b7280; }
        @include('reports.statement-css')
    </style>
</head>
<body>
    <div class="brand">Yande Gadgets · Legon, Accra</div>
    @include('reports.statement', ['statement' => $statement])
    <p class="generated">Generated {{ now()->format('j M Y, H:i') }}</p>
</body>
</html>

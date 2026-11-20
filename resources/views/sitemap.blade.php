{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc><changefreq>daily</changefreq></url>
    <url><loc>{{ route('shop.index') }}</loc><changefreq>daily</changefreq></url>
@foreach ($categories as $slug)
    <url><loc>{{ route('shop.index', ['category' => $slug]) }}</loc><changefreq>daily</changefreq></url>
@endforeach
@foreach ($products as $product)
    <url><loc>{{ route('shop.show', $product) }}</loc><lastmod>{{ $product->updated_at?->toAtomString() }}</lastmod></url>
@endforeach
    <url><loc>{{ route('legal.privacy') }}</loc></url>
    <url><loc>{{ route('legal.terms') }}</loc></url>
</urlset>

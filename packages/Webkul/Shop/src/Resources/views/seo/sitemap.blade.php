{{-- Split the `<?` so the PHP lexer (which the Blade compiler runs over this
     file) can never mistake the declaration for a short open tag, whatever
     the runtime's short_open_tag setting is. --}}
{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($urls as $url)
        <url>
            <loc>{{ $url }}</loc>
        </url>
    @endforeach
</urlset>

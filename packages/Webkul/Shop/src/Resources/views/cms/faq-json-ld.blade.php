{{-- FAQPage structured data, parsed live from the CMS page content (h2
     question + the blocks following it, up to the next h2) so admin edits
     stay in sync. Google shows FAQ rich results only for gov/health sites —
     this is for machine readability. --}}
@php
    $questions = [];

    if ($page->html_content) {
        libxml_use_internal_errors(true);

        $doc = new DOMDocument();

        $doc->loadHTML(
            '<?xml encoding="utf-8" ?>'. mb_convert_encoding($page->html_content, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_NOERROR
        );

        libxml_clear_errors();

        foreach ((new DOMXPath($doc))->query('//h2') as $h2) {
            $question = trim($h2->textContent);

            $answerParts = [];

            for ($node = $h2->nextSibling; $node !== null && $node->nodeName !== 'h2'; $node = $node->nextSibling) {
                $text = trim(preg_replace('/\s+/u', ' ', $node->textContent ?? ''));

                if ($text !== '') {
                    $answerParts[] = $text;
                }
            }

            $answer = trim(implode(' ', $answerParts));

            if ($question !== '' && $answer !== '') {
                $questions[] = [
                    '@type' => 'Question',
                    'name' => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $answer,
                    ],
                ];
            }
        }
    }
@endphp

@if (count($questions))
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endif

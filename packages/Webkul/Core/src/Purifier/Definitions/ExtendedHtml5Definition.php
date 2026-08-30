<?php

namespace Webkul\Core\Purifier\Definitions;

use HTMLPurifier_HTMLDefinition;
use Stevebauman\Purify\Definitions\Definition;
use Stevebauman\Purify\Definitions\Html5Definition;

class ExtendedHtml5Definition implements Definition
{
    /**
     * Apply rules to the HTML Purifier definition.
     */
    public static function apply(HTMLPurifier_HTMLDefinition $definition)
    {
        Html5Definition::apply($definition);

        $definition->addElement('div', 'Block', 'Flow', 'Common', [
            'class' => 'Class',
            'id' => 'Text',
        ]);

        $definition->addElement('button', 'Inline', 'Flow', 'Common', [
            'class' => 'Class',
            'type' => 'Enum#button,submit,reset',
        ]);

        $definition->addAttribute('img', 'data-src', 'URI');
        $definition->addAttribute('img', 'loading', 'Enum#lazy,eager');
        $definition->addAttribute('span', 'data-custom', 'Text');
        $definition->addAttribute('div', 'data-custom', 'Text');

        /**
         * Third-party embeds (X/Twitter, Instagram, TikTok...) ship <script>
         * loaders that must survive purification. Content is Empty, so inline
         * JavaScript is still stripped — only src-based loaders pass.
         */
        $definition->addElement('script', 'Inline', 'Empty', 'Common', [
            'src' => 'URI',
            'async' => 'Enum#async',
            'defer' => 'Enum#defer',
            'charset' => 'Text',
            'type' => 'Text',
            'crossorigin' => 'Text',
        ]);

        /**
         * iframe-based embeds (YouTube, Vimeo, Spotify, Google Maps...).
         */
        $definition->addElement('iframe', 'Inline', 'Empty', 'Common', [
            'src' => 'URI',
            'width' => 'Length',
            'height' => 'Length',
            'title' => 'Text',
            'allow' => 'Text',
            'allowfullscreen' => 'Enum#allowfullscreen',
            'frameborder' => 'Enum#0,1',
            'loading' => 'Enum#lazy,eager',
            'referrerpolicy' => 'Text',
            'name' => 'Text',
            'class' => 'Class',
        ]);

        /**
         * Semantic HTML5 containers emitted by markdown converters and
         * hand-written HTML.
         */
        $definition->addElement('section', 'Block', 'Flow', 'Common', [
            'class' => 'Class',
        ]);

        $definition->addElement('article', 'Block', 'Flow', 'Common', [
            'class' => 'Class',
        ]);

        $definition->addElement('figure', 'Block', 'Flow', 'Common', [
            'class' => 'Class',
        ]);

        $definition->addElement('figcaption', 'Inline', 'Flow', 'Common', [
            'class' => 'Class',
        ]);
    }
}

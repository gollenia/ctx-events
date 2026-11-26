<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Dom\HTMLDocument;
use Dom\Text;
use Dom\XPath;

final class EmailLayout
{
    public const PLACEHOLDER = '{{content}}';

    public static function fragment(string $html): HTMLDocument
    {
        return HTMLDocument::createFromString(
            '<!doctype html><html><head></head><body>' . $html . '</body></html>',
            LIBXML_NOERROR,
            'UTF-8',
        );
    }

    public static function placeholder(HTMLDocument $document): ?Text
    {
        if (substr_count($document->saveHtml(), self::PLACEHOLDER) !== 1) {
            return null;
        }

        $body = $document->body;
        if ($body === null) {
            return null;
        }

        $nodes = (new XPath($document))->query('.//text()', $body);
        foreach ($nodes as $node) {
            if ($node instanceof Text && str_contains($node->data, self::PLACEHOLDER)) {
                return $node;
            }
        }

        return null;
    }

    public static function compose(string $layout, string $content): string
    {
        $document = self::fragment($layout);
        $placeholder = self::placeholder($document);
        $body = $document->body;
        if ($placeholder === null || $body === null) {
            return '<body>' . $content . '</body>';
        }

        $contentDocument = self::fragment($content);
        [$before, $after] = explode(self::PLACEHOLDER, $placeholder->data, 2);
        $fragment = $document->createDocumentFragment();
        $fragment->append($before);
        foreach ($contentDocument->body->childNodes ?? [] as $node) {
            $fragment->append($document->importNode($node, true));
        }
        $fragment->append($after);
        $placeholder->replaceWith($fragment);

        return $document->saveHtml($body);
    }
}

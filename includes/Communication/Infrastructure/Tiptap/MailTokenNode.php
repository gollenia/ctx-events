<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure\Tiptap;

use Tiptap\Core\Node;

final class MailTokenNode extends Node
{
    public static $name = 'mailToken';

    public function addAttributes()
    {
        return [
            'token' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-token') ?: null,
                'renderHTML' => fn ($attributes) => ['data-token' => $attributes->token ?? null],
            ],
            'label' => [
                'parseHTML' => fn ($DOMNode) => $DOMNode->getAttribute('data-label') ?: null,
                'renderHTML' => fn ($attributes) => ['data-label' => $attributes->label ?? null],
            ],
        ];
    }

    public function parseHTML()
    {
        return [
            ['tag' => 'span[data-type="mail-token"]'],
        ];
    }

    public function renderHTML($node)
    {
        $resolver = $this->options['resolveToken'] ?? null;
        $token = $node->attrs->token ?? '';

        if (is_callable($resolver)) {
            return ['content' => (string) $resolver($token)];
        }

        return ['content' => htmlspecialchars((string) $token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')];
    }

    public function renderText($node)
    {
        return (string) ($node->attrs->token ?? '');
    }
}

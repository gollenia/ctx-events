<?php

declare(strict_types=1);

use Contexis\Events\Communication\Application\Contracts\EmailOptions;
use Contexis\Events\Communication\Domain\ValueObjects\ResolvedEmail;
use Contexis\Events\Communication\Infrastructure\EmailHtmlFormatter;
use Contexis\Events\Communication\Infrastructure\EmailLayout;
use Contexis\Events\Shared\Domain\ValueObjects\Email;

function formatTestEmail(string $body, bool $isHtml, string $layout, string $css = ''): string
{
    $options = new class ($layout, $css) implements EmailOptions {
        public function __construct(private string $layout, private string $css)
        {
        }

        public function layout(): string
        {
            return $this->layout;
        }

        public function css(): string
        {
            return $this->css;
        }
    };

    return (new EmailHtmlFormatter($options))->format(
        new ResolvedEmail(Email::tryFrom('recipient@example.com'), 'Subject', $body, isHtml: $isHtml),
    );
}

it('escapes legacy plain text and preserves line breaks in an HTML document', function (): void {
    expect(formatTestEmail("First & <second>\nThird", false, '{{content}}'))
        ->toContain('<meta charset="UTF-8">', 'First &amp; &lt;second&gt;<br>', 'Third');
});

it('inlines CSS after placing rendered content in the layout', function (): void {
    $html = formatTestEmail(
        '<p><img src="cid:payment-code" alt="Payment">Mail content</p>',
        true,
        '<div><h1>Header</h1>{{content}}<div>Footer</div></div>',
        'h1, p { color: #123456; } @media screen and (max-width: 600px) { p { font-size: 12px; } }',
    );

    expect($html)->toContain('Header', 'Footer', 'cid:payment-code', '@media screen', 'color: #123456');
    expect(substr_count($html, 'color: #123456'))->toBe(2);
    expect(substr_count($html, 'Mail content'))->toBe(1);
});

it('ignores invalid saved layouts and keeps the message body', function (string $layout): void {
    expect(EmailLayout::placeholder(EmailLayout::fragment($layout)))->toBeNull();
    expect(formatTestEmail('Message', false, $layout))->toContain('<body>Message</body>');
})->with([
    '<div>Missing</div>',
    '{{content}}{{content}}',
    '<div title="{{content}}">Attribute</div>',
    '<!-- {{content}} -->',
]);

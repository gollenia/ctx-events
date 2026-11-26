<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure;

use Contexis\Events\Communication\Application\Contracts\EmailOptions;
use Contexis\Events\Communication\Domain\ValueObjects\ResolvedEmail;
use Pelago\Emogrifier\CssInliner;

final readonly class EmailHtmlFormatter
{
    public function __construct(private EmailOptions $options)
    {
    }

    public function format(ResolvedEmail $email): string
    {
        $content = $email->isHtml
            ? $email->body
            : nl2br(htmlspecialchars($email->body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false);
        $body = EmailLayout::compose($this->options->layout(), $content);
        $html = '<!doctype html><html><head><meta charset="UTF-8"></head>' . $body . '</html>';
        $css = $this->options->css();

        if (trim($css) === '') {
            return $html;
        }

        try {
            return CssInliner::fromHtml($html)->inlineCss($css)->render();
        } catch (\Throwable) {
            return $html;
        }
    }
}

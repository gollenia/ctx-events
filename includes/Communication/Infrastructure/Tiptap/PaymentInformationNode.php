<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Infrastructure\Tiptap;

use Tiptap\Core\Node;

final class PaymentInformationNode extends Node
{
    public static $name = 'paymentInformation';

    public function parseHTML()
    {
        return [
            ['tag' => 'div[data-type="paymentInformation"]'],
        ];
    }

    public function renderHTML($node)
    {
        $renderer = $this->options['renderPaymentInformation'] ?? null;

        if (!is_callable($renderer)) {
            return ['content' => ''];
        }

        return ['content' => (string) $renderer()];
    }
}

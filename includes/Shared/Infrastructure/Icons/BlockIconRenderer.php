<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Icons;

use Contexis\Events\Platform\Bootstrap;

final class BlockIconRenderer
{
    public static function render(string $name): string
    {
        return Bootstrap::container()->get(IconRenderer::class)->render($name);
    }
}

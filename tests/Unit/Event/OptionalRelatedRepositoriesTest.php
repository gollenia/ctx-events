<?php

declare(strict_types=1);

use Contexis\Events\Location\Infrastructure\WpLocationRepository;
use Contexis\Events\Media\Infrastructure\WpImageRepository;
use Contexis\Events\Person\Infrastructure\WpPersonRepository;

test('optional related records can be absent', function (): void {
    expect((new WpLocationRepository())->find(null))->toBeNull()
        ->and((new WpPersonRepository())->find(null))->toBeNull()
        ->and((new WpImageRepository())->find(null))->toBeNull();
});

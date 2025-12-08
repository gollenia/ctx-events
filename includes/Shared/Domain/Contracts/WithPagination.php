<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Domain\Contracts;

interface WithPagination
{
    public function getTotalItems(): int;

    public function getTotalPages(): int;

    public function getCurrentPage(): int;

    public function getPerPage(): int;
}

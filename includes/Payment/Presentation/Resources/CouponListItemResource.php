<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Presentation\Resources;

use Contexis\Events\Payment\Application\Dtos\CouponListItem;
use Contexis\Events\Shared\Presentation\Contracts\Resource;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript(name: 'CouponListItem')]
final readonly class CouponListItemResource implements Resource
{
    public function __construct(
        public int $id,
        public string $title,
        public string $code,
        public string $discountType,
        public int $discountValue,
        public ?string $validFrom,
        public ?string $expiresAt,
        public ?int $usageLimit,
        public ?int $usageCount,
        public bool $isGlobal,
        public string $status,
    ) {
    }

    public static function fromDTO(CouponListItem $item): self
    {
        return new self(
            id: $item->id->toInt(),
            title: $item->title,
            code: $item->code,
            discountType: $item->discountType->value,
            discountValue: $item->discountValue,
            validFrom: $item->validFrom?->format(DATE_ATOM),
            expiresAt: $item->expiresAt?->format(DATE_ATOM),
            usageLimit: $item->usageLimit,
            usageCount: $item->usageCount,
            isGlobal: $item->isGlobal,
            status: $item->status->value,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'code' => $this->code,
            'discountType' => $this->discountType,
            'discountValue' => $this->discountValue,
            'validFrom' => $this->validFrom,
            'expiresAt' => $this->expiresAt,
            'usageLimit' => $this->usageLimit,
            'usageCount' => $this->usageCount,
            'isGlobal' => $this->isGlobal,
            'status' => $this->status,
        ];
    }
}

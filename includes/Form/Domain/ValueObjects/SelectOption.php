<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Domain\ValueObjects;

final class SelectOption
{
    public function __construct(
        public readonly string $label,
		public readonly ?string $value = null,
    ) {
    }

	public static function fromMixed(array|string $option): self
    {
		if(is_array($option)) {
			$label = $option['label'] ?? $option['value'] ?? '';
			
			return new self(
                label: (string)$label,
                value: isset($option['value']) ? (string)$option['value'] : null
            );
		}
		
		return new self(
			label: (string)$option,
			value: null
		);
		
	}

	public function getEffectiveValue(): string
    {
        return $this->value ?? $this->label;
    }

	public function toArray(): array
    {
        return [
            'label' => $this->label,
            'value' => $this->getEffectiveValue(),
        ];
    }
}
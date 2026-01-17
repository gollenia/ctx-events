<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain\ValueObjects;

final class BankData implements \JsonSerializable
{
    public function __construct(
        public readonly string $accountHolder,
        public readonly string $iban,
        public readonly string $bic,
        public readonly string $bankName,
        public readonly string $reference
    ) {
    }

	public static function fromArray(array $data): self
    {
		$data['iban'] = self::checkIban($data['iban']);
        return new self(
            $data['accountHolder'] ?? '',
            $data['iban'] ?? '',
            $data['bic'] ?? '',
            $data['bankName'] ?? '',
            $data['reference'] ?? '',
        );
    }

	private static function checkIban(string $iban): string
    {
        $result = $iban
            |> trim(...)
            |> strtoupper(...);
            
        $clean = preg_replace('/[\s-]/', '', $result);

        if (empty($clean)) {
             return ''; 
        }

        $len = strlen($clean);
        if ($len < 15 || $len > 34) {
            throw new \InvalidArgumentException("IBAN length invalid ($len characters).");
        }

		self::verifyChecksum($clean);

        return $clean;
    }


	private static function verifyChecksum(string $iban): void
    {
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        $numericIban = '';
        foreach (str_split($rearranged) as $char) {
            $numericIban .= is_numeric($char) ? $char : (string) (ord($char) - 55);
        }

        if (function_exists('bcmod')) {
            $remainder = (int) bcmod($numericIban, '97');
        } 
        else {
            $remainder = 0;
            $len = strlen($numericIban);
            for ($i = 0; $i < $len; $i++) {
                $digit = (int) $numericIban[$i];
                $remainder = ($remainder * 10 + $digit) % 97;
            }
        }

        if ($remainder !== 1) {
            throw new \InvalidArgumentException('IBAN checksum invalid.');
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'accountHolder' => $this->accountHolder,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'bankName' => $this->bankName,
            'reference' => $this->reference,
        ];
    }
}

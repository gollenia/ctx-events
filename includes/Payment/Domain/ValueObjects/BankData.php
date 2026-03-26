<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Domain\ValueObjects;

final readonly class BankData
{
    public function __construct(
        public string $accountHolder,
        public string $iban,
        public string $bic,
        public string $bankName
    ) {
    }

	public static function fromValues(
		string $accountHolder,
		string $iban,
		string $bic,
		string $bankName
	): self
    {
		$cleanIban = !empty($iban) ? self::checkIban($iban) : '';
		$cleanBic = !empty($bic) ? self::checkBic($bic) : '';

		return new self(
			$accountHolder,
			$cleanIban,
			$cleanBic,
			$bankName
		);
    }

	private static function checkIban(string $iban): string
    {
        $result = $iban
            |> trim(...)
            |> strtoupper(...);
            
        $clean = preg_replace('/[\s-]/', '', $result);

        $len = strlen($clean);
        if ($len < 15 || $len > 34) {
            throw new \InvalidArgumentException("BANK_DATA_INVALID_IBAN_LENGTH ($len characters).");
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
            throw new \InvalidArgumentException('BANK_DATA_INVALID_IBAN_CHECKSUM');
        }
    }

	private static function checkBic(string $bic): string 
	{
		$bic = strtoupper(trim($bic));
		if (!preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $bic)) {
			throw new \InvalidArgumentException("BANK_DATA_INVALID_BIC");
		}
		return $bic;
	}

	public function isValid(): bool
	{
		return !empty(trim($this->accountHolder)) 
        && !empty(trim($this->iban)) 
        && !empty(trim($this->bic))
		&& !empty(trim($this->bankName));
	}

    public function toArray(): array
    {
        return [
            'accountHolder' => $this->accountHolder,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'bankName' => $this->bankName
        ];
    }
}

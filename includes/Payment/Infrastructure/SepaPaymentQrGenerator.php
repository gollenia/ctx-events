<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Contexis\Events\Payment\Domain\Transaction;
use SepaQr\Data;

final class SepaPaymentQrGenerator
{
    public function generate(Transaction $transaction, string $reference, string $format = 'svg'): string
    {
        if ($transaction->bankData === null || !$transaction->bankData->isValid()) {
            throw new \DomainException('Offline payment does not contain valid bank data.');
        }

        if ($transaction->amount->toInt() <= 0) {
            throw new \DomainException('Payment QR requires a positive transaction amount.');
        }

        $paymentData = Data::create()
            ->setName($transaction->bankData->accountHolder)
            ->setIban($transaction->bankData->iban)
            ->setAmount($transaction->amount->toFloat())
            ->setCurrency($transaction->amount->currency->toString());

        if ($transaction->bankData->bic !== '') {
            $paymentData->setBic($transaction->bankData->bic);
        }

        if ($reference !== '') {
            $paymentData->setRemittanceText($reference);
        }

        [$outputType, $mimeType] = match ($format) {
            'svg' => [QRCode::OUTPUT_MARKUP_SVG, 'image/svg+xml'],
            'png' => [QRCode::OUTPUT_IMAGE_PNG, 'image/png'],
            default => throw new \DomainException('Unsupported QR format.'),
        };

        $options = new QROptions([
            'eccLevel' => QRCode::ECC_M,
            'outputType' => $outputType,
            'outputBase64' => true,
            'svgAddXmlHeader' => false,
        ]);

        $dataUri = (new QRCode($options))->render((string) $paymentData);

        if (!is_string($dataUri) || $dataUri === '') {
            throw new \DomainException('Could not generate payment QR code.');
        }

        if ($format === 'png' && !str_starts_with($dataUri, 'data:image/png')) {
            return 'data:' . $mimeType . ';base64,' . base64_encode($dataUri);
        }

        return $dataUri;
    }

    public function mimeType(string $format): string
    {
        return match ($format) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            default => throw new \DomainException('Unsupported QR format.'),
        };
    }
}

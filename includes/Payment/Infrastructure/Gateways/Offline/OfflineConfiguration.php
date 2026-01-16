<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Offline;

use Contexis\Events\Payment\Infrastructure\Contracts\GatewayConfiguration;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;

final class OfflineConfiguration implements GatewayConfiguration
{

	private const string OPTION_KEY = 'ctx_events_gateway_offline';
	public private(set) bool $isEnabled;
	public private(set) string $title;
	public private(set) BankData $bankData;
	public private(set) int $paymentTerm;
	public private(set) string $instructions;
    public function __construct(
        
    ) {
		$this->init();
    }

	private function init(): void
    {
        $data = get_option(self::OPTION_KEY, []);
        $this->isEnabled = (bool) ($data['enabled'] ?? false);
        $this->title = (string) ($data['title'] ?? __('Bank Transfer', 'ctx-events'));
        $this->bankData = new BankData(
            $data['account_holder'] ?? '',
            $data['iban'] ?? '',
            $data['bic'] ?? '',
            $data['bank_name'] ?? '',
            $data['reference'] ?? '',
        );
		$this->paymentTerm = (int) ($data['payment_term'] ?? 0);
		$this->instructions = (string) ($data['instructions'] ?? '');
    }

    public function updateFromArray(array $data): void
    {
		$this->bankData = new BankData(
            $data['accountHolder'] ?? $this->bankData->accountHolder,
            $data['iban'] ?? $this->bankData->iban,
            $data['bic'] ?? $this->bankData->bic,
            $data['bankName'] ?? $this->bankData->bankName,
            $data['reference'] ?? $this->bankData->reference,
        );

		if (array_key_exists('enabled', $data)) {
            $this->isEnabled = (bool) $data['enabled'];
        }
        
        if (array_key_exists('title', $data)) {
            $this->title = sanitize_text_field($data['title']);
        }
        
        if (array_key_exists('paymentTerm', $data)) {
            $this->paymentTerm = absint($data['paymentTerm']);
        }

        if (array_key_exists('instructions', $data)) {
            $this->instructions = sanitize_textarea_field($data['instructions']);
        }

		$this->save();
    }

	private function save(): void
    {
        // Zurück zu Snake_Case für die DB 🐍
        update_option(self::OPTION_KEY, [
            'enabled' => $this->isEnabled,
            'title' => $this->title,
            'payment_term' => $this->paymentTerm,
            'instructions' => $this->instructions,
            'account_holder' => $this->bankData->accountHolder,
            'iban' => $this->bankData->iban,
            'bic' => $this->bankData->bic,
            'bank_name' => $this->bankData->bankName,
            'reference' => $this->bankData->reference,
        ]);
    }

	public function enable(): void
	{
		$this->isEnabled = true;
		$this->save();
	}

	public function disable(): void
	{
		$this->isEnabled = false;
		$this->save();
	}

    public function getFormSchema(): array
    {
        return [
			[
				'type' => 'heading',
				'level' => 2,
				'label' => __('General settings', 'ctx-events')
			],
            [
                'type' => 'checkbox',
                'name' => 'enabled',
                'label' => __('Enable Payment Method', 'ctx-events'),
				'help' => __('Enable or disable the offline payment method', 'ctx-events'),
				'value' => $this->isEnabled,
            ],
            [
                'type' => 'text',
                'name' => 'title',
                'label' => __('Title of Payment Method', 'ctx-events'),
				'help' => __('The name the gateway presents itself to the customers', 'ctx-events'),
                'default' => __('Bank Transfer', 'ctx-events'),
				'value' => $this->title,
            ],
			[
				'type' => 'number',
				'name' => 'paymentTerm',
				'label' => __("Payment Term", 'ctx-events'),
				'help' => __("The payment term in days. Please not that bookings will automatically expire after this period", 'ctx-events'),
				'value' => $this->paymentTerm,
			],
			[
				'type' => 'heading',
				'level' => 2,
				'label' => __('Banking Details', 'ctx-events')
			],
            [
                'type' => 'textarea',
                'name' => 'accountHolder',
                'label' => __("Account Holder", 'ctx-events'),
				'help' => __("The name of the account holder", 'ctx-events'),
				'value' => $this->bankData->accountHolder,
            ],
            [
                'type' => 'textarea',
                'name' => 'iban',
                'label' => 'IBAN',
				'help' => __('The IBAN of the account', 'ctx-events'),
				'value' => $this->bankData->iban,
            ],
            [
                'type' => 'textarea',
                'name' => 'bic',
                'label' => 'BIC',
				'help' => __('The BIC of the account', 'ctx-events'),
				'value' => $this->bankData->bic,
            ],
            [
                'type' => 'textarea',
                'name' => 'bankName',
                'label' => __('Bank Name', 'ctx-events'),
				'help' => __('The name of the bank', 'ctx-events'),
				'value' => $this->bankData->bankName,
            ],
			[
				'type' => 'textarea',
				'name' => 'instructions',
				'label' => __("Instructions", "ctx-events"),
				'help' => __("Write a text that the user sees when the bank transfer information is shown", "ctx-events"),
				'value' => $this->instructions,
			],
        ];
    }
}

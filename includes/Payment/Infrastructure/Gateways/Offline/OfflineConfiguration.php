<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Offline;

use Contexis\Events\Payment\Infrastructure\Contracts\GatewayConfiguration;
use Contexis\Events\Payment\Domain\ValueObjects\BankData;
use Contexis\Events\Shared\Domain\ValueObjects\MalfunctionException;

final class OfflineConfiguration implements GatewayConfiguration
{

	private const string OPTION_KEY = 'ctx_events_gateway_offline';
	public private(set) bool $isEnabled;
	public private(set) string $title;
	public private(set) ?string $description = null;
	public private(set) ?BankData $bankData = null;
	public private(set) int $paymentTerm;
	public private(set) string $instructions;
    public function __construct(
        
    ) {
		$this->init();
    }

	private function init(): void
    {
        $data = get_option(self::OPTION_KEY, []);
        $this->mapDataToProperties($data);
    }

	private function mapDataToProperties(array $data): void
    {
        $this->isEnabled = (bool) ($data['enabled'] ?? $data['enabled'] ?? false);
        $this->title = (string) ($data['title'] ?? $this->title ?? __('Bank Transfer', 'ctx-events'));
		$this->description = (string) ($data['description'] ?? $this->description ?? '');
        $this->paymentTerm = (int) ($data['paymentTerm'] ?? $data['payment_term'] ?? 0); // Handle snake_case from DB vs camelCase from Form
        $this->instructions = (string) ($data['instructions'] ?? $this->instructions ?? '');

        $this->bankData = BankData::fromValues(
            (string) ($data['accountHolder'] ?? $data['account_holder'] ?? $this->bankData?->accountHolder ?? ''),
            (string) ($data['iban'] ?? $data['iban'] ?? $this->bankData?->iban ?? ''),
            (string) ($data['bic'] ?? $data['bic'] ?? $this->bankData?->bic ?? ''),
            (string) ($data['bankName'] ?? $data['bank_name'] ?? $this->bankData?->bankName ?? '')
        );

		if($this->isEnabled && (!$this->bankData || !$this->bankData->isValid())) {
            $this->isEnabled = false;
        }
    }

    public function updateFromArray(array $data): void
    {
		$allowedKeys = ['enabled', 'title', 'paymentTerm', 'instructions', 'accountHolder', 'iban', 'bic', 'bankName'];
    	$filteredData = array_intersect_key($data, array_flip($allowedKeys));

		if (empty($filteredData)) {
			throw new \DomainException(
				__("No valid data provided to update the offline gateway configuration.", 'ctx-events'),
				400
			);
		}

		$cleanData = [
			'enabled' => isset($data['enabled']) ? (bool) $data['enabled'] : $this->isEnabled,
			'title' => sanitize_text_field($data['title'] ?? $this->title),
			'description' => sanitize_text_field($data['description'] ?? $this->description),	
			'paymentTerm' => isset($data['paymentTerm']) ? (int) $data['paymentTerm'] : $this->paymentTerm,
			'instructions' => wp_kses_post($data['instructions'] ?? $this->instructions),
			'accountHolder' => sanitize_text_field($data['accountHolder'] ?? $this->bankData?->accountHolder ?? ''),
			'iban' => sanitize_text_field($data['iban'] ?? $this->bankData?->iban ?? ''),
			'bic' => sanitize_text_field($data['bic'] ?? $this->bankData?->bic ?? ''),
			'bankName' => sanitize_text_field($data['bankName'] ?? $this->bankData?->bankName ?? '')
		];

		$this->mapDataToProperties($cleanData);
    }

	public function save(): void
    {
        update_option(self::OPTION_KEY, [
            'enabled' => $this->isEnabled,
            'title' => $this->title,
            'payment_term' => $this->paymentTerm,
            'instructions' => $this->instructions,
            'account_holder' => $this->bankData->accountHolder,
            'iban' => $this->bankData->iban,
            'bic' => $this->bankData->bic,
            'bank_name' => $this->bankData->bankName,
            'description' => $this->description
        ]);
    }

	public function setEnabled(bool $active): void
	{
		if ($active) {
            $this->validateSettings(); 
        }
		
		$this->isEnabled = $active;
	}

	private function validateSettings(): void
	{
		if (!$this->bankData) {
			throw new \DomainException(__("Bank data is missing", 'ctx-events'), 400);
		}

		if(!$this->bankData->isValid()) {
			throw new \DomainException(__("Invalid banking data", 'ctx-events'), 400);
		}
	}


    public function getFormSchema(): array
    {
        return [
			[
				'type' => 'heading',
				'level' => 3,
				'label' => __('General settings', 'ctx-events')
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
				'type' => 'text',
				'name' => 'description',
				'label' => __('Description', 'ctx-events'),
				'help' => __('A brief description shown when the user selects the gateway', 'ctx-events'),
				'value' => $this->description,
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
				'level' => 3,
				'label' => __('Banking Details', 'ctx-events')
			],
            [
                'type' => 'text',
                'name' => 'accountHolder',
				'required' => true,
                'label' => __("Account Holder", 'ctx-events'),
				'help' => __("The name of the account holder", 'ctx-events'),
				'value' => $this->bankData->accountHolder,
            ],
            [
                'type' => 'text',
                'name' => 'iban',
				'required' => true,
                'label' => 'IBAN',
				'help' => __('The IBAN of the account', 'ctx-events'),
				'value' => $this->bankData->iban,
            ],
            [
                'type' => 'text',
                'name' => 'bic',
				'label' => 'BIC',
				'required' => true,
				'help' => __('The BIC of the account', 'ctx-events'),
				'value' => $this->bankData->bic,
            ],
            [
                'type' => 'text',
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

	public function isValid(): bool
	{
		try {
			$this->validateSettings();
			return true;
		} catch (\DomainException $e) {
			return false;
		}
	}
}

<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Mollie;

use Contexis\Events\Payment\Infrastructure\Contracts\GatewayConfiguration;

final class MollieConfiguration implements GatewayConfiguration
{

	const OPTION_KEY = 'ctx_events_gateway_mollie';
  
	public private(set) bool $isEnabled;
	public private(set) string $title;
	public private(set) string $mode;
	public private(set) string $apiKeyLive;
	public private(set) string $apiKeyTest;
	public private(set) string $returnUrl;
	public private(set) string $instructions;
    public function __construct(
       
    ) {
        $this->init();
    }

	public function init(): void
    {
        $data = get_option(self::OPTION_KEY, []);
        $this->isEnabled = (bool) ($data['enabled'] ?? false);
        $this->mode = (string) ($data['mode'] ?? 'test');
        $this->apiKeyLive = (string) ($data['api_key_live'] ?? '');
        $this->apiKeyTest = (string) ($data['api_key_test'] ?? '');
        $this->returnUrl = (string) ($data['return_url'] ?? '');
		$this->instructions = (string) ($data['instructions'] ?? '');
		$this->title = (string) ($data['title'] ?? 'Mollie');
    }

	public function getId(): string
	{
		return 'mollie';
	}

	public function getApiKey(): string 
	{
		return $this->mode === 'live' ? $this->apiKeyLive : $this->apiKeyTest;
	}

	public function updateFromArray(array $data): void
    {
		$this->isEnabled = (bool) $data['enabled'];
		$this->mode = (string) $data['mode'];
		$this->apiKeyLive = (string) $data['api_key_live'];
		$this->apiKeyTest = (string) $data['api_key_test'];
		$this->returnUrl = (string) $data['return_url'];
		$this->instructions = (string) $data['instructions'];
		$this->title = (string) $data['title'];
		$this->save();
    }

	public function save(): void
    {
        update_option(self::OPTION_KEY, [
            'enabled' => $this->isEnabled,
            'mode' => $this->mode,
            'api_key_live' => $this->apiKeyLive,
            'api_key_test' => $this->apiKeyTest,
            'return_url' => $this->returnUrl,
			'instructions' => $this->instructions,
			'title' => $this->title,
        ]);
    }

    public function getFormSchema(): array
    {
        return [
            [
                'name' => 'enabled',
                'type' => 'toggle',
                'label' => __('Activate Mollie', 'ctx-events'),
                'description' => __('Activate Mollie payments in your event system.', 'ctx-events'),
				'value' => $this->isEnabled,
            ],
            [
                'name' => 'apiKeyLive',
                'type' => 'password',
                'label' => __('Live API Key', 'ctx-events'),
                'description' => __('API key for the live environment.', 'ctx-events'),
				'value' => $this->apiKeyLive,
            ],

            [
                'name' => 'apiKeyTest',
                'type' => 'password',
                'label' => __('Test API Key', 'ctx-events'),
                'description' => __('API key for the test environment.', 'ctx-events'),
				'value' => $this->apiKeyTest,
            ],
            [
                'name' => 'mode',
                'type' => 'select',
                'label' => __('Work Mode', 'ctx-events'),
                'options' => ['test' => __('Test', 'ctx-events'), 'live' => __('Live', 'ctx-events')],
                'description' => __('Select whether to use the test or live environment for payments.', 'ctx-events'),
				'value' => $this->mode,
            ],
            [
                'name' => 'returnUrl',
                'type' => 'text',
                'label' => __('Return URL after payment (optional)', 'ctx-events'),
                'description' => __('URL to redirect to after payment.', 'ctx-events'),
				'value' => $this->returnUrl,
			],
			[
				'type' => 'textarea',
				'name' => 'instructions',
				'label' => __("Instructions", "ctx-events"),
				'description' => __("Write a text that the user sees when the bank transfer information is shown", "ctx-events"),
				'value' => $this->instructions,
			],
			[
				'name' => 'title',
				'type' => 'text',
				'label' => __('Title', 'ctx-events'),
				'description' => __('Custom title for the payment gateway.', 'ctx-events'),
				'value' => $this->title,
			]
        ];
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function enable(): void
    {
        $this->isEnabled = true;
    }

    public function disable(): void
    {
        $this->isEnabled = false;
    }
}

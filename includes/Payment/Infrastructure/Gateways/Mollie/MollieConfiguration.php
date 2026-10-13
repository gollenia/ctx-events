<?php

declare(strict_types=1);

namespace Contexis\Events\Payment\Infrastructure\Gateways\Mollie;

use Contexis\Events\Payment\Infrastructure\Contracts\GatewayConfiguration;

final class MollieConfiguration implements GatewayConfiguration
{

	const OPTION_KEY = 'ctx_events_gateway_mollie';
  
	public private(set) bool $isEnabled;
	public private(set) string $title;
	public private(set) ?string $description;
	public private(set) string $mode;
	public private(set) string $apiKeyLive;
	public private(set) string $apiKeyTest;
	public private(set) string $webhookSigningSecret;
	public private(set) string $instructions;

    /** @param array<string, mixed>|null $data */
    public function __construct(?array $data = null) {
        $data ??= get_option(self::OPTION_KEY, []);
        $this->mapDataToProperties($data);
    }

	public function getId(): string
	{
		return 'mollie';
	}

	public function getApiKey(): string 
	{
		return $this->mode === 'live' ? $this->apiKeyLive : $this->apiKeyTest;
	}

    /** @param array<string, mixed> $data */
	public function updateFromArray(array $data): void	
    {
		$cleanData = [
            'enabled' => isset($data['enabled']) ? (bool) $data['enabled'] : false,
            'title' => sanitize_text_field($data['title'] ?? 'Mollie'),
            'mode' => sanitize_key($data['mode'] ?? 'test'),
            'api_key_live' => sanitize_text_field($data['api_key_live'] ?? ''),
            'api_key_test' => sanitize_text_field($data['api_key_test'] ?? ''),
            'webhook_signing_secret' => sanitize_text_field($data['webhookSigningSecret'] ?? $data['webhook_signing_secret'] ?? ''),
            'instructions' => wp_kses_post($data['instructions'] ?? ''),
            'description' => sanitize_text_field($data['description'] ?? ''),
        ];
		$this->mapDataToProperties($cleanData);
    }

    /** @param array<string, mixed> $data */
	private function mapDataToProperties(array $data): void
    {
        $this->isEnabled = (bool) ($data['enabled'] ?? false);
        $this->title = (string) ($data['title'] ?? 'Mollie');
        $this->mode = (string) ($data['mode'] ?? 'test');
        $this->apiKeyLive = (string) ($data['api_key_live'] ?? '');
        $this->apiKeyTest = (string) ($data['api_key_test'] ?? '');
        $this->webhookSigningSecret = (string) ($data['webhook_signing_secret'] ?? '');
		$this->description = (string) ($data['description'] ?? '');
        $this->instructions = (string) ($data['instructions'] ?? '');
    }

	public function save(): void
    {
        update_option(self::OPTION_KEY, [
            'enabled' => $this->isEnabled,
            'mode' => $this->mode,
            'api_key_live' => $this->apiKeyLive,
            'api_key_test' => $this->apiKeyTest,
            'webhook_signing_secret' => $this->webhookSigningSecret,
			'instructions' => $this->instructions,
			'description' => $this->description,
			'title' => $this->title,
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function getFormSchema(): array
    {
        return [
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
                'type' => 'html',
                'label' => __('Mollie webhook URL', 'ctx-events'),
                'help' => __('Copy this URL to your Next-gen webhook configuration in Mollie.', 'ctx-events'),
                'value' => $this->webhookUrl(),
            ],
            [
                'name' => 'webhookSigningSecret',
                'type' => 'password',
                'label' => __('Webhook signing secret', 'ctx-events'),
                'description' => __('Required to verify signed Mollie Next-gen webhooks.', 'ctx-events'),
                'value' => $this->webhookSigningSecret,
            ],
			[
				'type' => 'textarea',
				'name' => 'description',
				'label' => __('Description', 'ctx-events'),
				'help' => __('A brief description shown when the user selects the gateway', 'ctx-events'),
				'value' => $this->description,
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

    public function setEnabled(bool $active): void
	{
		if ($active) {
            $this->validateSettings(); 
        }
		
		$this->isEnabled = $active;
	}

    private function webhookUrl(): string
    {
        return (string) rest_url('events/v3/payments/webhooks/mollie');
    }

	private function validateSettings(): void
	{
		if (!$this->apiKeyLive && !$this->apiKeyTest) {
			throw new \DomainException(__("API keys are missing", 'ctx-events'), 400);
		}

        if ($this->webhookSigningSecret === '') {
            throw new \DomainException(__("Webhook signing secret is missing", 'ctx-events'), 400);
        }
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

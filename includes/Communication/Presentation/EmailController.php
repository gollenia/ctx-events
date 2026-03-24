<?php

declare(strict_types=1);

namespace Contexis\Events\Communication\Presentation;

use Contexis\Events\Communication\Application\DTOs\MailCriteria;
use Contexis\Events\Communication\Application\UseCases\ListEmails;
use Contexis\Events\Communication\Application\UseCases\ResetEmailTemplate;
use Contexis\Events\Communication\Application\UseCases\UpdateEmailTemplate;
use Contexis\Events\Communication\Domain\Enums\EmailTarget;
use Contexis\Events\Communication\Domain\Enums\EmailTemplateKey;
use Contexis\Events\Communication\Domain\Enums\EmailTrigger;
use Contexis\Events\Communication\Presentation\Resources\MailListItemResource;
use Contexis\Events\Shared\Presentation\Contracts\RestController;
use WP_REST_Request;
use WP_REST_Response;

final class EmailController implements RestController
{
    public function __construct(
        private ListEmails $listEmails,
        private UpdateEmailTemplate $updateEmailTemplate,
        private ResetEmailTemplate $resetEmailTemplate,
    ) {
    }

    public function register(): void
    {
        register_rest_route('events/v3', '/emails', [
            'methods' => 'GET',
            'callback' => [$this, 'index'],
            'permission_callback' => fn (): bool => current_user_can('manage_options'),
            'args' => [
                'search' => [
                    'type' => 'string',
                    'required' => false,
                ],
                'target' => [
                    'type' => 'string',
                    'required' => false,
                    'enum' => array_map(static fn (EmailTarget $target): string => $target->value, EmailTarget::cases()),
                ],
                'trigger' => [
                    'type' => 'string',
                    'required' => false,
                    'enum' => array_map(static fn (EmailTrigger $trigger): string => $trigger->value, EmailTrigger::cases()),
                ],
            ],
        ]);

        register_rest_route('events/v3', '/emails/(?P<key>[a-z0-9_]+)', [
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update'],
                'permission_callback' => fn (): bool => current_user_can('manage_options'),
                'args' => [
                    'key' => [
                        'type' => 'string',
                        'required' => true,
                    ],
                    'enabled' => [
                        'type' => 'boolean',
                        'required' => true,
                    ],
                    'subject' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                    'body' => [
                        'type' => 'string',
                        'required' => true,
                    ],
                    'replyTo' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                    'recipientConfig' => [
                        'type' => 'object',
                        'required' => false,
                    ],
                ],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'reset'],
                'permission_callback' => fn (): bool => current_user_can('manage_options'),
                'args' => [
                    'key' => [
                        'type' => 'string',
                        'required' => true,
                    ],
                ],
            ],
        ]);
    }

    public function index(WP_REST_Request $request): WP_REST_Response
    {
        $criteria = new MailCriteria(
            search: $this->nullableString($request->get_param('search')),
            target: $request->get_param('target') ? EmailTarget::from((string) $request->get_param('target')) : null,
            trigger: $request->get_param('trigger') ? EmailTrigger::from((string) $request->get_param('trigger')) : null,
        );

        $items = $this->listEmails->execute($criteria);
        $response = array_map(static fn ($item) => MailListItemResource::fromDTO($item), $items->toArray());

        return new WP_REST_Response($response, 200);
    }

    public function update(WP_REST_Request $request): WP_REST_Response
    {
        $key = (string) $request->get_param('key');
        $updated = $this->updateEmailTemplate->execute($key, [
            'enabled' => $request->get_param('enabled'),
            'subject' => $request->get_param('subject'),
            'body' => $request->get_param('body'),
            'replyTo' => $request->get_param('replyTo'),
            'recipientConfig' => $request->get_param('recipientConfig'),
        ]);

        if (!$updated) {
            return new WP_REST_Response(['message' => 'Email template not found'], 404);
        }

        $item = $this->findByKey($key);

        return new WP_REST_Response(
            $item ? MailListItemResource::fromDTO($item) : ['message' => 'Email template not found'],
            $item ? 200 : 404
        );
    }

    public function reset(WP_REST_Request $request): WP_REST_Response
    {
        $key = (string) $request->get_param('key');
        $reset = $this->resetEmailTemplate->execute($key);

        if (!$reset) {
            return new WP_REST_Response(['message' => 'Email template not found'], 404);
        }

        $item = $this->findByKey($key);

        return new WP_REST_Response(
            $item ? MailListItemResource::fromDTO($item) : ['message' => 'Email template not found'],
            $item ? 200 : 404
        );
    }

    private function nullableString(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private function findByKey(string $key): ?\Contexis\Events\Communication\Application\DTOs\MailListItem
    {
        $templateKey = EmailTemplateKey::tryFrom($key);
        if ($templateKey === null) {
            return null;
        }

        foreach ($this->listEmails->execute(new MailCriteria())->toArray() as $item) {
            if ($item->key === $templateKey) {
                return $item;
            }
        }

        return null;
    }
}

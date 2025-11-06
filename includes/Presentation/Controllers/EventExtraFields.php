<?php

namespace Contexis\Events\Presentation\Controllers;
use Contexis\Events\Application\Services\GetEventSpaces;

final class EventExtrasField implements RestAdapter
{
    public function __construct(private GetEventSpaces $getSpaces) {}

    public function register(): void
    {
        register_rest_field('event', 'extras', [
            'get_callback' => function(array $obj) {
                $spaces = $this->getSpaces->handle((int)$obj['id']);
                return ['spaces' => $spaces->free()];
            },
            'schema' => [
                'type' => 'object',
                'properties' => ['spaces' => ['type' => 'integer','minimum' => 0]],
            ],
        ]);
    }
}
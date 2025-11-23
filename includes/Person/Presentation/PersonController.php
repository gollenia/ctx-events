<?php

namespace Contexis\Events\Person\Presentation;

use Contexis\Events\Person\Application\PersonIncludes;
use Contexis\Events\Shared\Infrastructure\Wordpress\ViewContextFactory;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class PersonController implements RestController
{
    public function __construct(
        private readonly \Contexis\Events\Person\Application\GetPerson $getPerson
    ) {
    }

    public function register(): void
    {
        register_rest_route('/events/v3', '/person/(?P<id>\d+)', array(
            'methods'   => \WP_REST_Server::READABLE,
            'callback'  => [$this, 'getItem'],
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'required' => true,
                    'description' => 'The ID of the event to retrieve.',
                    'type' => 'integer',
                ),
                'include' => array(
                    'required' => false,
                    'description' => 'Comma-separated list of related resources to include (e.g., "media").',
                    'type' => 'string',
                )
            ),
        ));
    }

    public function getItem(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $person_id = (int) $request->get_param('id');
        $include = PersonIncludes::fromArray(explode(',', $request->get_param('include') ?? ''));

        $person_dto = $this->getPerson->execute($person_id, $include, ViewContextFactory::createFromCurrentUser());

        if (!$person_dto) {
            return new \WP_REST_Response(['message' => 'Person not found'], 404);
        }

        $person_resource = new PersonResource($person_dto);

        return new \WP_REST_Response($person_resource, 200);
    }
}

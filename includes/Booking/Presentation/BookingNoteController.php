<?php

declare(strict_types=1);

namespace Contexis\Events\Booking\Presentation;

use Contexis\Events\Booking\Application\DTOs\AddBookingNoteRequest;
use Contexis\Events\Booking\Application\UseCases\AddBookingNote;
use Contexis\Events\Booking\Presentation\Resources\BookingNoteResource;
use Contexis\Events\Shared\Presentation\Contracts\RestController;

final class BookingNoteController implements RestController
{
    use BookingControllerHelpers;

    public function __construct(
        private AddBookingNote $addBookingNote,
    ) {
    }

    public function register(): void
    {
        register_rest_route('events/v3', '/bookings/(?P<uuid>[A-Za-z0-9-]{6,64})/notes', [[
            'methods'             => 'POST',
            'callback'            => [$this, 'addNote'],
            'permission_callback' => [$this, 'checkBookingAdminPermission'],
            'args'                => array_merge($this->getBaseArgs(), [
                'text' => ['required' => true, 'type' => 'string'],
            ]),
        ]]);
    }

    public function addNote(\WP_REST_Request $request): \WP_REST_Response
    {
        $currentUser = wp_get_current_user();
        $author = $currentUser->display_name ?: $currentUser->user_nicename ?: $currentUser->user_login ?: '';

        $noteRequest = new AddBookingNoteRequest(
            uuid: (string) $request->get_param('uuid'),
            text: (string) $request->get_param('text'),
            author: $author,
        );

        try {
            $note = $this->addBookingNote->execute($noteRequest);
            return new \WP_REST_Response(new BookingNoteResource($note->text, $note->date, $note->author), 201);
        } catch (\DomainException $exception) {
            return new \WP_REST_Response(['message' => $exception->getMessage()], 422);
        }
    }
}

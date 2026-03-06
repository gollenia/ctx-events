<?php

declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Domain\Contracts\HashGenerator;
use Contexis\Events\Shared\Domain\Contracts\SessionHashResolver;
use Contexis\Events\Shared\Domain\Contracts\TokenGenerator;

final readonly class WpSessionHashResolver implements SessionHashResolver
{
    private const string GUEST_COOKIE_NAME = 'ctx_booking_sid';
    private const int GUEST_COOKIE_TTL = 2592000;

    public function __construct(
        private HashGenerator $hashGenerator,
        private TokenGenerator $tokenGenerator,
    ) {
    }

    public function resolve(): string
    {
        return $this->hashGenerator->sign($this->resolveRawSessionId());
    }

    private function resolveRawSessionId(): string
    {
        if (\is_user_logged_in()) {
            $userId = (int) \get_current_user_id();
            $sessionToken = (string) \wp_get_session_token();

            return 'user:' . $userId . ':' . $sessionToken;
        }

        $guestSessionId = $this->resolveGuestSessionId();

        return 'guest:' . $guestSessionId;
    }

    private function resolveGuestSessionId(): string
    {
        $cookie = $_COOKIE[self::GUEST_COOKIE_NAME] ?? null;
        if (is_string($cookie) && $cookie !== '') {
            return $cookie;
        }

        $guestSessionId = $this->tokenGenerator->generate(24);
        $this->setGuestCookie($guestSessionId);
        $_COOKIE[self::GUEST_COOKIE_NAME] = $guestSessionId;

        return $guestSessionId;
    }

    private function setGuestCookie(string $guestSessionId): void
    {
        if (\headers_sent()) {
            return;
        }

        \setcookie(self::GUEST_COOKIE_NAME, $guestSessionId, [
            'expires' => time() + self::GUEST_COOKIE_TTL,
            'path' => '/',
            'secure' => \is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

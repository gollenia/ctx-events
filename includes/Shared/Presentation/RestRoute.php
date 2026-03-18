<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Presentation;

use Contexis\Events\Shared\Presentation\DTOs\RestRouteArgs;
use Contexis\Events\Shared\Presentation\Resources\SchemaResource;

final readonly class RestRoute
{
    private const NS = 'events/v3';

    public function __construct(
        public string $type
    ) {}

	public static function forType(string $type): self
	{
		return new self($type);
	}

    public function getForSingle(string $additional = '', string $regex = '(?P<id>\d+)'): RestRouteArgs
    {
        return RestRouteArgs::from(
            self::NS,
            sprintf('/%s/%s%s', trim($this->type, '/'), $regex, $additional)
		);
    }

    public function getForCollection(string $additional = ''): RestRouteArgs
    {
        return RestRouteArgs::from(
            self::NS,
            sprintf('/%s%s', trim($this->type, '/'), $additional)
        );
    }

    public function getIri(int|string $id): string
    {
        $path = sprintf('%s/%s/%s', self::NS, trim($this->type, '/'), (string)$id);
        return rest_url($path);
    }

    public function getFriendlyUrl(int $id): ?string
    {
        $url = get_permalink($id, false);
        return is_string($url) && $url !== '' ? $url : null;
    }

	public function getSchema(int|string $id): SchemaResource
	{
		return SchemaResource::from($this->type, $this->getIri($id));
	}
}
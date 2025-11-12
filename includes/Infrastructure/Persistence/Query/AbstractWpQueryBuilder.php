<?php

namespace Contexis\Events\Infrastructure\Persistence\Query;

use Contexis\Events\Core\Contracts\QueryRequest;

abstract class AbstractWpQueryBuilder
{
    protected array $args = [];

    abstract protected function build(QueryRequest $request): void;

	public static function fromRequest(QueryRequest $request): self {
		$instance = new self;
		$instance->build($request);
		return $instance;
	}

    public function addArg(string $key, mixed $value): self {
        $clone = clone $this;
        $clone->args[$key] = $value;
        return $clone;
    }

	public function withCache(): self 
	{
		$clone = clone $this;
		$clone->args['update_post_meta_cache'] = true;
        $clone->args['update_post_term_cache'] = true;
		return $clone;
	}

    public function getArgs(): array
    {
        return $this->args;
    }
}

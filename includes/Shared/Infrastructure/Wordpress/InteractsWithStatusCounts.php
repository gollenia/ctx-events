<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Domain\ValueObjects\StatusCounts;

trait InteractsWithStatusCounts
{
	protected function mapWpCountsToStatusCounts(object $counts): StatusCounts
	{
		return new StatusCounts(
			publish: (int) ($counts->publish ?? 0),
			future: (int) ($counts->future ?? 0),
			draft: (int) ($counts->draft ?? 0),
			private: (int) ($counts->private ?? 0),
			pending: (int) ($counts->pending ?? 0),
			trash: (int) ($counts->trash ?? 0),
		);
	}

	protected function sumStatusCounts(StatusCounts ...$statusCounts): StatusCounts
	{
		$publish = 0;
		$future = 0;
		$draft = 0;
		$private = 0;
		$pending = 0;
		$trash = 0;

		foreach ($statusCounts as $counts) {
			$publish += $counts->publish;
			$future += $counts->future;
			$draft += $counts->draft;
			$private += $counts->private;
			$pending += $counts->pending;
			$trash += $counts->trash;
		}

		return new StatusCounts(
			publish: $publish,
			future: $future,
			draft: $draft,
			private: $private,
			pending: $pending,
			trash: $trash,
		);
	}
}

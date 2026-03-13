<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Form\Application\DTOs\FormCriteria;
use Contexis\Events\Form\Application\DTOs\FormListResponse;
use Contexis\Events\Form\Domain\FormSummaryCollection;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;

use Contexis\Events\Form\Domain\Form;
use Contexis\Events\Form\Domain\FormSummary;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Infrastructure\Mappers\FormListItemMapper;
use Contexis\Events\Form\Infrastructure\Mappers\FormMapper;
use Contexis\Events\Shared\Application\ValueObjects\Pagination;
use Contexis\Events\Shared\Domain\ValueObjects\Status;
use Contexis\Events\Shared\Domain\ValueObjects\StatusCounts;
use Contexis\Events\Shared\Infrastructure\Wordpress\InteractsWithStatusCounts;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;
use WP_Query;

class WpFormRepository implements FormRepository
{
	use InteractsWithStatusCounts;

	public function __construct(
		private readonly FormDuplicatePost $duplicatePost,
	) {
	}

    public function find(FormId $formId): ?Form
    {
        $post = get_post($formId->toInt());
        if (!$post) return null;
        
		if(!FormPostTypes::isFormPostType($post->post_type)) return null;
		$snapshot = new PostSnapshot($post);
        return FormMapper::map($snapshot);
    }

	public function findByType(FormType $formType): ?FormSummaryCollection
	{
		$postType = FormPostTypes::getPostType($formType);
		$query = new WP_Query([
			'post_type' => $postType,
			'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
		]);

		$usageCounts = $this->getUsageCounts($query->get_posts());

		$forms = [];
		foreach ($query->get_posts() as $postId) {
			$forms[] = new FormSummary(
				id: FormId::from($postId),
				type: $formType,
				title: get_the_title($postId),
				description: get_post_field('post_content', $postId),
				usageCount: $usageCounts[$postId] ?? 0
			);
		}
		return FormSummaryCollection::from(...$forms);
	}

	private function getUsageCounts(array $postIds): array
	{
		global $wpdb;

        if (empty($postIds)) {
            return [];
        }

        $howMany = count($postIds);
        $placeholders = implode(', ', array_fill(0, $howMany, '%d'));

        $query = "
			SELECT meta_value as form_id, COUNT(*) as usage_count 
			FROM {$wpdb->postmeta} pm
			JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			WHERE pm.meta_key IN ('_booking_form', '_attendee_form')
			AND p.post_status != 'trash'
			GROUP BY pm.meta_value
		";

        $sql = $wpdb->prepare($query, $postIds);

        $results = $wpdb->get_results($sql);
		if(!$results) {
			return [];
		}
        $counts = [];
        foreach ($results as $row) {
            $counts[(int)$row->form_id] = (int)$row->usage_count;
        }

        return $counts;
	}

	public function findByCriteria(FormCriteria $criteria): FormListResponse
	{
		$queryBuilder = WpFormQueryBuilder::fromCriteria($criteria);
		$query = new WP_Query($queryBuilder->getArgs());
		$usageCounts = $this->getUsageCounts($query->get_posts());
		$forms = [];
		foreach ($query->get_posts() as $postId) {
			$forms[] = FormListItemMapper::map(new PostSnapshot(get_post($postId)), $usageCounts);
		}

		$pagination = Pagination::of(
            totalItems: (int)$query->found_posts,
            currentPage: $criteria->page,
            perPage: $criteria->perPage
        );

		return new FormListResponse(
			...$forms
		)->withPagination($pagination);
		
	}

	public function saveStatus(FormId $formId, Status $status): void
	{
		wp_update_post([
			'ID' => $formId->toInt(),
			'post_status' => $status->value,
		]);
	}

	public function delete(FormId $formId): bool
	{
		$post = get_post($formId->toInt());
		if (!$post || !FormPostTypes::isFormPostType($post->post_type)) {
			return false;
		}

		return wp_delete_post($formId->toInt(), true) !== false;
	}

	public function duplicate(FormId $formId): ?FormId
	{
		$newPostId = $this->duplicatePost->duplicate($formId->toInt());
		if ($newPostId === null) {
			return null;
		}

		return FormId::from($newPostId);
	}

	public function getCountsByStatus(): StatusCounts
	{
		$bookingCounts = wp_count_posts(BookingFormPost::POST_TYPE);
		$attendeeCounts = wp_count_posts(AttendeeFormPost::POST_TYPE);

		return $this->sumStatusCounts(
			$this->mapWpCountsToStatusCounts($bookingCounts),
			$this->mapWpCountsToStatusCounts($attendeeCounts),
		);
	}
}
<?php

declare(strict_types=1);

namespace Contexis\Events\Form\Infrastructure;

use Contexis\Events\Form\Domain\FormSummaryCollection;
use Contexis\Events\Form\Domain\FormId;
use Contexis\Events\Form\Domain\FormRepository;

use Contexis\Events\Form\Domain\Form;
use Contexis\Events\Form\Domain\FormSummary;
use Contexis\Events\Form\Domain\Enums\FormType;
use Contexis\Events\Form\Infrastructure\Mappers\FormMapper;
use Contexis\Events\Shared\Infrastructure\Wordpress\PostSnapshot;
use WP_Query;

class WpFormRepository implements FormRepository
{
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
		return new FormSummaryCollection(...$forms);
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
            SELECT pm.meta_value as form_id, COUNT(pm.meta_id) as usage_count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key IN ('_booking_form', '_attendee_form') 
            AND pm.meta_value IN ($placeholders)  -- Hier stehen jetzt die %d
            AND p.post_type = 'event'      
            AND p.post_status IN ('publish', 'future', 'draft')
            GROUP BY pm.meta_value
        ";

        $sql = $wpdb->prepare($query, $postIds);

        $results = $wpdb->get_results($sql);

        $counts = [];
        foreach ($results as $row) {
            $counts[(int)$row->form_id] = (int)$row->usage_count;
        }

        return $counts;
	}
}

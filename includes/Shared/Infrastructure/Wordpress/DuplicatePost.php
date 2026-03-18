<?php
declare(strict_types=1);

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

abstract class DuplicatePost
{
	public function duplicate(int $postId): ?int
	{
		$sourcePost = get_post($postId);
		if (!($sourcePost instanceof \WP_Post)) {
			return null;
		}

		if (!$this->supportsPostType($sourcePost->post_type)) {
			return null;
		}

		$newPostId = wp_insert_post($this->buildPostData($sourcePost), true);
		if (is_wp_error($newPostId)) {
			return null;
		}

		$this->duplicateTaxonomies(
			sourcePostId: (int) $sourcePost->ID,
			newPostId: (int) $newPostId,
			postType: $sourcePost->post_type,
		);

		$this->duplicateMeta(
			sourcePostId: (int) $sourcePost->ID,
			newPostId: (int) $newPostId,
		);

		$this->afterDuplicate($sourcePost, (int) $newPostId);

		return (int) $newPostId;
	}

	abstract protected function supportsPostType(string $postType): bool;

	/**
	 * @return array<string, mixed>
	 */
	protected function buildPostData(\WP_Post $sourcePost): array
	{
		return [
			'post_type' => $sourcePost->post_type,
			'post_title' => $this->buildDuplicatedTitle($sourcePost->post_title),
			'post_content' => $sourcePost->post_content,
			'post_excerpt' => $sourcePost->post_excerpt,
			'post_status' => 'draft',
			'post_author' => get_current_user_id() ?: (int) $sourcePost->post_author,
			'post_parent' => (int) $sourcePost->post_parent,
			'menu_order' => (int) $sourcePost->menu_order,
			'comment_status' => $sourcePost->comment_status,
			'ping_status' => $sourcePost->ping_status,
			'post_password' => $sourcePost->post_password,
			'to_ping' => $sourcePost->to_ping,
			'pinged' => $sourcePost->pinged,
		];
	}

	protected function buildDuplicatedTitle(string $title): string
	{
		$trimmedTitle = trim($title);
		if ($trimmedTitle === '') {
			return __('Copy', 'ctx-events');
		}

		return sprintf('%s (%s)', $trimmedTitle, __('Copy', 'ctx-events'));
	}

	protected function shouldDuplicateMetaKey(string $metaKey): bool
	{
		return !in_array($metaKey, ['_edit_lock', '_edit_last'], true);
	}

	protected function shouldDuplicateTaxonomy(string $taxonomy): bool
	{
		return true;
	}

	protected function afterDuplicate(\WP_Post $sourcePost, int $newPostId): void
	{
	}

	private function duplicateTaxonomies(int $sourcePostId, int $newPostId, string $postType): void
	{
		$taxonomies = get_object_taxonomies($postType, 'names');
		if (empty($taxonomies)) {
			return;
		}

		foreach ($taxonomies as $taxonomy) {
			if (!$this->shouldDuplicateTaxonomy($taxonomy)) {
				continue;
			}

			$termIds = wp_get_object_terms($sourcePostId, $taxonomy, ['fields' => 'ids']);
			if (is_wp_error($termIds)) {
				continue;
			}

			wp_set_object_terms($newPostId, $termIds, $taxonomy, false);
		}
	}

	private function duplicateMeta(int $sourcePostId, int $newPostId): void
	{
		$metaByKey = get_post_meta($sourcePostId);
		foreach ($metaByKey as $metaKey => $values) {
			if (!is_string($metaKey) || !$this->shouldDuplicateMetaKey($metaKey)) {
				continue;
			}

			if (!is_array($values)) {
				continue;
			}

			delete_post_meta($newPostId, $metaKey);
			foreach ($values as $value) {
				add_post_meta($newPostId, $metaKey, maybe_unserialize($value));
			}
		}
	}
}


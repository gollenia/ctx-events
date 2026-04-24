<?php

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Domain\Event;

final class EventTaxonomy
{
	public const CATEGORIES = 'ctx-event-categories';
	private const CATEGORY_COLOR_META = 'color';
	public const TAGS = 'ctx-event-tags';
	public static function register(string $slug): void
	{
		$instance = new self();
		register_taxonomy(self::TAGS, [EventPost::POST_TYPE], [
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'label' => __('Event Tags'),
            'show_admin_column' => true,
            'singular_label' => __('Event Tag'),
            'labels' => [
                'name' => __('Event Tags', 'ctx-events'),
                'singular_name' => __('Event Tag', 'ctx-events'),
                'search_items' => __('Search Event Tags', 'ctx-events'),
                'popular_items' => __('Popular Event Tags', 'ctx-events'),
                'all_items' => __('All Event Tags', 'ctx-events'),
                'parent_items' => __('Parent Event Tags', 'ctx-events'),
                'parent_item_colon' => __('Parent Event Tag:', 'ctx-events'),
                'edit_item' => __('Edit Event Tag', 'ctx-events'),
                'update_item' => __('Update Event Tag', 'ctx-events'),
                'add_new_item' => __('Add New Event Tag', 'ctx-events'),
                'new_item_name' => __('New Event Tag Name', 'ctx-events'),
                'separate_items_with_commas' => __('Separate event tags with commas', 'ctx-events'),
                'add_or_remove_items' => __('Add or remove events', 'ctx-events'),
                'choose_from_the_most_used' => __('Choose from most used event tags', 'ctx-events'),
            ]
        ]);

        register_taxonomy(self::CATEGORIES, [EventPost::POST_TYPE], [
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => $slug . '/categories', 'hierarchical' => true,'with_front' => false],
            'show_in_nav_menus' => true,
            'label' => __('Event Categories', 'ctx-events'),
            'singular_label' => __('Event Category', 'ctx-events'),
            'labels' => [
                'name' => __('Event Categories', 'ctx-events'),
                'singular_name' => __('Event Category', 'ctx-events'),
                'search_items' => __('Search Event Categories', 'ctx-events'),
                'popular_items' => __('Popular Event Categories', 'ctx-events'),
                'all_items' => __('All Event Categories', 'ctx-events'),
                'parent_items' => __('Parent Event Categories', 'ctx-events'),
                'parent_item_colon' => __('Parent Event Category:', 'ctx-events'),
                'edit_item' => __('Edit Event Category', 'ctx-events'),
                'update_item' => __('Update Event Category', 'ctx-events'),
                'add_new_item' => __('Add New Event Category', 'ctx-events'),
                'new_item_name' => __('New Event Category Name', 'ctx-events'),
                'separate_items_with_commas' => __('Separate event categories with commas', 'ctx-events'),
                'add_or_remove_items' => __('Add or remove events', 'ctx-events'),
                'choose_from_the_most_used' => __('Choose from most used event categories', 'ctx-events'),
            ]
        ]);

		$instance = new self();
		add_action(self::CATEGORIES . '_add_form_fields', [$instance, 'renderCategoryColorAddField']);
		add_action(self::CATEGORIES . '_edit_form_fields', [$instance, 'renderCategoryColorEditField']);
		add_action('created_' . self::CATEGORIES, [$instance, 'saveCategoryColor']);
		add_action('edited_' . self::CATEGORIES, [$instance, 'saveCategoryColor']);
		add_filter('manage_edit-' . self::CATEGORIES . '_columns', [$instance, 'filterCategoryColumns']);
		add_filter('manage_' . self::CATEGORIES . '_custom_column', [$instance, 'renderCategoryColumn'], 10, 3);
	}

	/**
	 * @param array<string, string> $columns
	 * @return array<string, string>
	 */
	public function filterCategoryColumns(array $columns): array
	{
		$result = [];

		foreach ($columns as $key => $label) {
			$result[$key] = $label;

			if ($key === 'name') {
				$result['color'] = __('Color', 'ctx-events');
			}
		}

		return $result;
	}

	public function renderCategoryColorAddField(): void
	{
		?>
		<div class="form-field term-color-wrap">
			<label for="ctx-events-category-color"><?php esc_html_e('Color', 'ctx-events'); ?></label>
			<input
				type="color"
				id="ctx-events-category-color"
				name="ctx_events_category_color"
				value="#2271b1"
			/>
			<p><?php esc_html_e('Used for calendar highlighting.', 'ctx-events'); ?></p>
		</div>
		<?php
	}

	public function renderCategoryColorEditField(\WP_Term $term): void
	{
		$color = sanitize_hex_color((string) get_term_meta($term->term_id, self::CATEGORY_COLOR_META, true)) ?: '#2271b1';
		?>
		<tr class="form-field term-color-wrap">
			<th scope="row">
				<label for="ctx-events-category-color"><?php esc_html_e('Color', 'ctx-events'); ?></label>
			</th>
			<td>
				<input
					type="color"
					id="ctx-events-category-color"
					name="ctx_events_category_color"
					value="<?php echo esc_attr($color); ?>"
				/>
				<p class="description"><?php esc_html_e('Used for calendar highlighting.', 'ctx-events'); ?></p>
			</td>
		</tr>
		<?php
	}

	public function saveCategoryColor(int $termId): void
	{
		$rawColor = isset($_POST['ctx_events_category_color'])
			? wp_unslash((string) $_POST['ctx_events_category_color'])
			: '';
		$color = sanitize_hex_color($rawColor);

		if ($color === null) {
			delete_term_meta($termId, self::CATEGORY_COLOR_META);
			return;
		}

		update_term_meta($termId, self::CATEGORY_COLOR_META, $color);
	}

	public function renderCategoryColumn(string $content, string $columnName, int $termId): string
	{
		if ($columnName !== 'color') {
			return $content;
		}

		$color = sanitize_hex_color((string) get_term_meta($termId, self::CATEGORY_COLOR_META, true)) ?: '#2271b1';

		return sprintf(
			'<span style="display:inline-flex;align-items:center;gap:8px;"><span style="width:12px;height:12px;border-radius:999px;background:%1$s;border:1px solid rgba(0,0,0,.12);display:inline-block;"></span><code>%1$s</code></span>',
			esc_attr($color),
		);
	}
}
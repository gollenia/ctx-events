<?php
declare(strict_types=1);

namespace Contexis\Events\Event\Infrastructure;

use Contexis\Events\Event\Application\Contracts\EventOptions;
use Contexis\Events\Event\Infrastructure\EventMeta;
use Contexis\Events\Platform\Wordpress\PluginInfo;
use Contexis\Events\Shared\Infrastructure\Abstracts\PostType;
use Contexis\Events\Shared\Infrastructure\Contracts\HasHooks;
use Contexis\Events\Shared\Infrastructure\Contracts\HasMetaData;
use Contexis\Events\Shared\Infrastructure\Contracts\HasPatterns;
use Contexis\Events\Shared\Infrastructure\Contracts\HasTaxonomies;

class EventPost extends PostType implements HasTaxonomies, HasMetaData, HasPatterns, HasHooks
{
    public const POST_TYPE = "ctx-event";
    public const CATEGORIES = 'ctx-event-categories';
    public const TAGS = 'ctx-event-tags';
    private const CATEGORY_COLOR_META = 'color';
    private const PATTERN_DIR = '/includes/Platform/Wordpress/patterns';

	public function __construct(
		private readonly EventHooks $hooks,
		private readonly EventOptions $options
	)
	{
		
	}

    public function getSlug($suffix = ''): string
    {
        return $this->options->getEventsSlug() . $suffix;
    }

    public function registerTaxonomies(): void
    {
        register_taxonomy(self::POST_TYPE . '-tags', [self::POST_TYPE], [
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

        register_taxonomy(self::POST_TYPE . '-categories', [self::POST_TYPE], [
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => $this->getSlug('/categories'), 'hierarchical' => true,'with_front' => false],
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
    }

    public function registerPostType(): void
    {
        $labels = [
            'name' => __('Events', 'ctx-events'),
            'singular_name' => __('Event', 'ctx-events'),
            'menu_name' => __('Events', 'ctx-events'),
            'add_new_item' => __('Add New Event', 'ctx-events'),
            'edit' => __('Edit', 'ctx-events'),
            'edit_item' => __('Edit Event', 'ctx-events'),
            'view' => __('View', 'ctx-events'),
            'view_item' => __('View Event', 'ctx-events'),
            'search_items' => __('Search Events', 'ctx-events'),
            'not_found' => __('No Events Found', 'ctx-events'),
            'not_found_in_trash' => __('No Events Found in Trash', 'ctx-events'),
            'parent' => __('Parent Event', 'ctx-events'),
        ];

        $event_post_type = [
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_rest' => true,
            'show_in_nav_menus' => true,
			'show_in_admin_bar' => true,
            'can_export' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => ['slug' => $this->getSlug(), 'with_front' => false],
            'has_archive' => true,
            'supports' => ['title','editor','excerpt','thumbnail','author','custom-fields'],
            'label' => __('Events', 'ctx-events'),
            'description' => __('Display events on your blog.', 'ctx-events'),
            'labels' => $labels,
            'menu_icon' => 'dashicons-calendar-alt'
        ];
        register_post_type(self::POST_TYPE, $event_post_type);
		
		register_post_status('cancelled', [
			'label'                     => _x('Cancelled', 'post status', 'ctx-events'),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop('Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'ctx-events'),
		]);
		
    }

    public function registerMeta(): void
    {
        EventMeta::registerAll(self::POST_TYPE);
    }

    public function registerPatterns(): void
    {
        if (!function_exists('register_block_pattern_category') || !function_exists('register_block_pattern')) {
            return;
        }

        register_block_pattern_category('ctx-events', [
            'label' => __('Events', 'ctx-events'),
        ]);

        register_block_pattern(
            'ctx-events/featured-event-split',
            [
                'title' => __('Featured Event: Image Left, Details Right', 'ctx-events'),
                'description' => __('Two-column featured event layout with the image on the left and event details on the right.', 'ctx-events'),
                'categories' => ['ctx-events'],
                'viewportWidth' => 1440,
                'content' => $this->loadPatternFile('featured-event-split.html'),
            ]
        );

        register_block_pattern(
            'ctx-events/featured-event-stacked',
            [
                'title' => __('Featured Event: Stacked', 'ctx-events'),
                'description' => __('Stacked featured event layout with image first and the event content underneath.', 'ctx-events'),
                'categories' => ['ctx-events'],
                'viewportWidth' => 960,
                'content' => $this->loadPatternFile('featured-event-stacked.html'),
            ]
        );

        register_block_pattern(
            'ctx-events/event-details',
            [
                'title' => __('Event Details', 'ctx-events'),
                'description' => __('Default event details block with the standard set of event metadata items.', 'ctx-events'),
                'categories' => ['ctx-events'],
                'viewportWidth' => 960,
                'content' => $this->loadPatternFile('event-details.html'),
            ]
        );
    }

	public function registerHooks(): void
	{
		$this->hooks->register();
		add_action(self::CATEGORIES . '_add_form_fields', [$this, 'renderCategoryColorAddField']);
		add_action(self::CATEGORIES . '_edit_form_fields', [$this, 'renderCategoryColorEditField']);
		add_action('created_' . self::CATEGORIES, [$this, 'saveCategoryColor']);
		add_action('edited_' . self::CATEGORIES, [$this, 'saveCategoryColor']);
		add_filter('manage_edit-' . self::CATEGORIES . '_columns', [$this, 'filterCategoryColumns']);
		add_filter('manage_' . self::CATEGORIES . '_custom_column', [$this, 'renderCategoryColumn'], 10, 3);
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

    private function loadPatternFile(string $filename): string
    {
        $path = PluginInfo::getPluginDir(self::PATTERN_DIR . '/' . $filename);
        if (!is_readable($path)) {
            return '';
        }

        $content = file_get_contents($path);
        if (!is_string($content) || $content === '') {
            return '';
        }

        return $content;
    }
}

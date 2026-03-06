<?php
use Contexis\Events\Event\Domain\EventRepository;
use Contexis\Events\Event\Domain\ValueObjects\EventId;
use Contexis\Events\Platform\Bootstrap;

$repository = Bootstrap::container()->get(EventRepository::class);
$event = $repository->find(EventId::from(get_the_ID()));

if (!$event) {
    return;
}
if (!$event->acceptsBookings()) {
    return;
}

$classNames = [
    'ctx__button',
    $attributes['iconOnly'] ? 'ctx__button--icon-only' : '',
    $attributes['iconRight'] ? 'ctx__button--reverse' : '',
];

$block_attributes = get_block_wrapper_attributes(['class' => join(" ", $classNames)]);

?>

<button <?php echo $block_attributes; ?> id="booking_button">
<?php
if ($attributes['buttonIcon']) {
    echo "<i class=\"material-icons material-symbols-outlined\">{$attributes['buttonIcon']}</i>";
}
if (!$attributes['iconOnly']) {
    echo $attributes['buttonTitle'] ?: __("Register", "ctx-events");
}
?>
</button>

<?php
add_action('wp_footer', function () {
    echo "<div id=\"booking_app\" data-post=\"" . get_the_ID() . "\"></div>";
});

<?php
declare(strict_types=1);

use Contexis\Events\Event\Domain\EventId;

$repository = new \Contexis\Events\Event\Domain\EventRepository();
$id = new EventId(get_the_ID());
$event = $repository->find($id);

if (!$event) {
    return;
}
if (!$event->isBookable()) {
    return;
}

$classNames = [
    'ctx__button',
    $attributes['iconOnly'] ? 'ctx__button--icon-only' : '',
    $attributes['iconRight'] ? 'ctx__button--reverse' : '',
];

$block_attributes = get_block_wrapper_attributes(['class' => join(" ", $classNames)]);

?>

<button <?php
declare(strict_types=1); echo $block_attributes ?> id="booking_button">
<?php
declare(strict_types=1);
if ($attributes['buttonIcon']) {
    echo "<i class=\"material-icons material-symbols-outlined\">{$attributes['buttonIcon']}</i>";
}
if (!$attributes['iconOnly']) {
    echo $attributes['buttonTitle'] ?: __("Register", "ctx-events");
}
?>
</button>

<?php
declare(strict_types=1);

add_action('wp_footer', function () {
    echo "<div id=\"booking_app\" data-post=\"" . get_the_ID() . "\"></div>";
});
?>


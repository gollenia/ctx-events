<?php

use Contexis\Events\Models\Event;
use Contexis\Events\Models\Speaker;

$event = Event::find_by_post(get_post());
if(!$event) return;
$speakerId = $attributes['customSpeakerId'] ?: $event->speaker_id;
if(!$speakerId) { return; }
$speaker = Speaker::get($speakerId);

switch($attributes['linkTo']) {
	case "mail":
		$url = "mailto:" . $speaker->email;
		break;
	case "website":
		$url = $speaker->website;
		break;
	case "call":
		$url = "tel:" . $speaker->phone;
		break;
	case "custom":
		$url = $attributes['url'];
		break;
	default:
		$url = '';
		break;
}

$gender = $speaker->gender ?: "male";

$linkIcon = $attributes['linkTo'];
if($attributes['linkTo'] == 'custom') {

	$linkIcon = 'link';

	$socialMediaIcons = [
		'facebook' => 'facebook',
		'instagram' => 'instagram',
		'linkedin' => 'linkedin',
		'twitter' => 'twitter',
		'xing' => 'xing',
		'youtube' => 'youtube',
		'vimeo' => 'vimeo',
	];

	foreach($socialMediaIcons as $key => $value) {
		if(strpos($attributes['url'], $key) !== false) {
			$linkIcon = $value;
		}
	}
}

?>



<div class="event-details-item">
	<div class="event-details-image">
		<i class="material-icons material-symbols-outlined">
			<?php if ($attributes['showPortrait'] && $speaker->image) : ?>
				<img class="event-details-image" src="<?php echo $speaker->image['sizes']['thumbnail']['url'] ?>" alt="<?php echo $speaker->name ?>">
			<?php else : ?>
			<?php echo $attributes['icon'] ? $attributes['icon'] : $gender ?>
			<?php endif; ?>
		</i>
	</div>
	<div class="event-details-text">
		<h4><?php echo $attributes['description'] ?: __("Speaker", "events") ?></h4>
		<div class="event-details-data"><?php echo $speaker->name ?></div> 
	</div>
	<?php if($attributes['showLink'] && $url) : ?>
	<div class="event-details-action">
		<a target="_blank" href="<?php echo $url ?>"><i class="material-icons material-symbols-outlined"><?php echo $linkIcon; ?></i></a>
	</div>
	<?php endif; ?>
</div> 
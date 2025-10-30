<?php

namespace Contexis\Events\Core\Utilities;

class Debug
{
	

	public static function object_to_js_console(mixed $object) {
		?>
		<script>
			console.log(<?php echo json_encode($object); ?>);
		</script>
		<?php
	}
}
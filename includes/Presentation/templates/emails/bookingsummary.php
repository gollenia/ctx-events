<?php
/*
* This displays the content of the #_BOOKINGSUMMARY placeholder
* You can override the default display settings pages by copying this file to yourthemefolder/plugins/events-manage/placeholders/ and modifying it however you need.
* For more information, see http://wp-events-plugin.com/documentation/using-template-files/
*/
/* @var $booking booking */

use Contexis\Events\Intl\Price;

 ?>
<?php foreach($booking->get_tickets_bookings() as $ticket_booking):  ?>

<?php echo $ticket_booking->get_ticket()->ticket_name; ?>

--------------------------------------
<?php _e('Quantity','events'); ?>: <?php echo $ticket_booking->get_booked_spaces(); ?>

<?php _e('Price','events'); ?>: <?php echo Price::format($ticket_booking->get_price()); ?>

<?php endforeach; ?>

=======================================

<?php 
$price_summary = $booking->get_price_summary_array();
//we should now have an array of information including base price, taxes and post/pre tax discounts
?>



<?php _e('Total Price','events'); ?> : <?php echo $price_summary['total']; ?>
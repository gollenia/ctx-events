<?php
/* @var $event Event */

use Contexis\Events\Model\Booking;

$people = array();
$bookings = $event->get_bookings();

if (count($bookings->bookings) > 0) {
    ?>
    <ul class="event-attendees">
    <?php
    foreach ($bookings as $booking) { /* @var $booking booking */
        if ($booking->booking_status == Booking::APPROVED) {
            // Holt die gespeicherten Namen aus den Buchungsdaten
            $email = $booking->booking_mail ?? null;
            $name = $booking->get_full_name;

            // Falls der Name leer ist, alternative Darstellung
            if (empty($name)) {
                $name = __('Unbekannter Teilnehmer', 'events');
            }

            // Falls die E-Mail-Adresse schon in der Liste ist, nicht doppelt anzeigen
            if (!empty($email) && in_array($email, $people)) {
                continue;
            }

            if (!empty($email)) {
                $people[] = $email;
            }

            echo '<li>' . esc_html($name) . '</li>';
        }
    }
    ?>
    </ul>
    <?php
}
?>
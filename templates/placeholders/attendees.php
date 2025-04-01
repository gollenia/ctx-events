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
            // E-Mail-Adresse aus den Buchungsdaten holen
            $email = $booking->booking_meta['registration']['user_email'] ?? null;

            // Falls keine E-Mail vorhanden ist, Avatar von einem leeren String generieren
            if (empty($email)) {
                $email = 'unknown@example.com'; // Platzhalter für Gäste ohne E-Mail
            }

            // Falls die E-Mail bereits in der Liste ist, nicht doppelt anzeigen
            if (in_array($email, $people)) {
                continue;
            }

            // E-Mail speichern, um doppelte Einträge zu vermeiden
            $people[] = $email;

            // Avatar aus der E-Mail-Adresse generieren
            echo '<li>' . get_avatar($email, 50) . '</li>';
        }
    }
    ?>
    </ul>
    <?php
}
?>
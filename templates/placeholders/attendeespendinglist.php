<?php
/* @var $event Event */
$people = array();
$bookings = $event->get_bookings();

if (count($bookings->bookings) > 0) {
    ?>
    <ul class="event-attendees">
    <?php
    foreach ($bookings as $booking) { /* @var $booking booking */
        if ($booking->is_pending()) {
            // Holt die gespeicherten Namen aus den Buchungsdaten
            $email = $booking->booking_mail ?? null;
            $name = trim(($booking->first_name ?? '') . ' ' . ($booking->last_name ?? ''));

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
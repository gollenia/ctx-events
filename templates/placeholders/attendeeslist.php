<?php
/* @var $event Event */
$people = array();
$EM_Bookings = $event->get_bookings();

if (count($EM_Bookings->bookings) > 0) {
    ?>
    <ul class="event-attendees">
    <?php
    foreach ($EM_Bookings as $EM_Booking) { /* @var $EM_Booking EM_Booking */
        if ($EM_Booking->booking_status == EM_Booking::APPROVED) {
            // Holt die gespeicherten Namen aus den Buchungsdaten
            $email = $EM_Booking->booking_mail ?? null;
            $name = $EM_Booking->full_name;

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
<?php

namespace Contexis\Events\Infrastructure\Persistence;

use Contexis\Events\Models\Booking;
use Contexis\Events\Models\BookingStatus;
use Contexis\Events\Collections\RecordCollection;
use Contexis\Events\Collections\TransactionCollection;	
use Contexis\Events\Models\Coupon;
use DateTime;

class BookingMapper implements \Contexis\Events\Core\Contracts\Mapper
{

	private static array $errors = [];

	public static function map(array $data): ?Booking
	{
		$booking = new Booking();
		return self::map_data_to_object($booking, $data);
	}

	public static function map_existing(Booking $booking, array $raw_data) : ?Booking
	{
		return self::map_data_to_object($booking, $raw_data);
	}

	private static function map_data_to_object(Booking $booking, array $raw_data) : ?Booking
    {
        self::$errors = [];
        
        $data = [];

        if (!empty($raw_data['date'])) {
            try {
                $data['date'] = new DateTime($raw_data['date']);
            } catch (\Exception $e) {
                self::$errors['date'] = __('Date is invalid.','events');
            }
        }
        
        $data['registration'] = json_decode($raw_data['registration'] ?? '{}', true) ?: [];
        $data['attendees'] = json_decode($raw_data['attendees'] ?? '[]', true) ?: [];

		$email = $data['registration']['user_email'] ?? '';
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			self::$errors['user_email'] = __('A valid email address is required.','events');
		} else {
			$data['user_email'] = $email;
		}

		$data['notes'] = new RecordCollection(json_decode($raw_data['notes'] ?? '[]', true) ?: []);
        $data['log'] = new RecordCollection(json_decode($raw_data['log'] ?? '[]', true) ?: []);
        $data['transactions'] = TransactionCollection::from_array(json_decode($raw_data['transactions'] ?? '[]', true) ?: []);

		if( !empty($raw_data['coupon']) ) {
			$data['coupon'] = Coupon::get_by_code($raw_data['coupon']) ?? null;
		}

        if (isset($raw_data['status'])) {
            try {
                $data['status'] = BookingStatus::from((int)$raw_data['status']);
            } catch (\ValueError $e) {
                self::$errors['status'] = __('Invalid status value.','events');
            }
        }
		
        
        if (!empty(self::$errors)) {
            return null;
        }

        $final_data = array_merge($raw_data, $data);
        
        foreach($final_data as $key => $value){
            if( property_exists($booking, $key) ) {
				$booking->$key = $value;
			}
        }

        return $booking;
    }

	public static function get_errors(): array
	{
		return self::$errors;
	}



	
}
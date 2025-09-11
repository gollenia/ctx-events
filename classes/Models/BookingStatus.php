<?php
namespace Contexis\Events\Models;

enum BookingStatus: int { 
	case PENDING = 0;
	case APPROVED = 1;
	case REJECTED = 2;
	case CANCELED = 3;
	case AWAITING_ONLINE_PAYMENT = 4;
	case AWAITING_PAYMENT = 5;
	case PAYMENT_FAILED = 6;
	case DELETED = 9;

	public function label(): string {
		return match($this) {
			self::PENDING => __('Pending','events'),
			self::APPROVED => __('Approved','events'),
			self::REJECTED => __('Rejected','events'),
			self::CANCELED => __('Cancelled','events'),
			self::AWAITING_ONLINE_PAYMENT => __('Awaiting Online Payment','events'),
			self::AWAITING_PAYMENT => __('Awaiting Payment','events'),
			self::PAYMENT_FAILED => __('Payment Failed','events'),
			self::DELETED => __('Deleted','events'),
		};
	}
};
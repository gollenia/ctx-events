<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

class MalfunctionException extends \Exception 
{
    public function __construct(
        string $message, 
        private readonly string $errorCode = 'DOMAIN_ERROR',
		public readonly ErrorType $errorType = ErrorType::ERROR,
        int $statusCode = 400,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getErrorCode(): string 
    {
        return $this->errorCode;
    }
}
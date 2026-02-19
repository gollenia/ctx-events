<?php

namespace Contexis\Events\Shared\Domain\ValueObjects;

enum ErrorType: string
{
	case ERROR = 'ERROR';
	case WARNING = 'WARNING';
	case INFO = 'INFO';
}
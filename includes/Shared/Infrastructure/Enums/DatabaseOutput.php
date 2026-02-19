<?php

namespace Contexis\Events\Shared\Infrastructure\Enums;

enum DatabaseOutput: string
{
	case OBJECT = 'OBJECT';
	case ARRAY_ASSOC = 'ARRAY_A';
	case ARRAY_NUM = 'ARRAY_N';
}
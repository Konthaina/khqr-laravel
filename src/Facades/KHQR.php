<?php

declare(strict_types=1);

namespace Konthaina\Khqr\Facades;

use Illuminate\Support\Facades\Facade;

class KHQR extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'khqr';
    }
}

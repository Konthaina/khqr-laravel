<?php

declare(strict_types=1);

namespace Konthaina\Khqr;

use Illuminate\Support\ServiceProvider;

class KhqrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('khqr', function () {
            return new KHQRGenerator();
        });
    }
}

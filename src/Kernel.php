<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();
        
        // Set application timezone to Singapore (UTC+08:00)
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Singapore');
    }
}

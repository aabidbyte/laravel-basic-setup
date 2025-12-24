<?php

declare(strict_types=1);

namespace App\Enums\Toast;

enum ToastType: string
{
    case Success = 'success';
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
    case Classic = 'classic';
}

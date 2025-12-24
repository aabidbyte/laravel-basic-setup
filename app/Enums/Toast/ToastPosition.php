<?php

declare(strict_types=1);

namespace App\Enums\Toast;

enum ToastPosition: string
{
    case TopRight = 'top-right';
    case TopLeft = 'top-left';
    case TopCenter = 'top-center';
    case BottomRight = 'bottom-right';
    case BottomLeft = 'bottom-left';
    case BottomCenter = 'bottom-center';
    case Center = 'center';
}

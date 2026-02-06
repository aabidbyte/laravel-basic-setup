<?php

declare(strict_types=1);

namespace App\Enums\Toast;

use App\Enums\Ui\ThemeColorTypes;

enum ToastType: string
{
    case Success = ThemeColorTypes::SUCCESS->value;
    case Info = ThemeColorTypes::INFO->value;
    case Warning = ThemeColorTypes::WARNING->value;
    case Error = ThemeColorTypes::ERROR->value;
    case Classic = ThemeColorTypes::NEUTRAL->value;
}

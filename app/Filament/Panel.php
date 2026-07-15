<?php

namespace App\Filament;

use Filament\Panel as BasePanel;

class Panel extends BasePanel
{
    public static function make(): static
    {
        return new static();
    }
}

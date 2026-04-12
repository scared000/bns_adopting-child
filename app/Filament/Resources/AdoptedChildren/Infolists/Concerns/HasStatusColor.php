<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Concerns;

use App\Filament\Resources\AdoptedChildren\Tables\AdoptedChildrenTable;

trait HasStatusColor
{
    protected static function statusColor(string $state): string
    {
        return AdoptedChildrenTable::statusColor($state);
    }
}

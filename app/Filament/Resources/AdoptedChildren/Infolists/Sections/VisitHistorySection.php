<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Sections;

use App\Filament\Resources\AdoptedChildren\Infolists\Html\VisitHistoryRenderer;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

final class VisitHistorySection
{
    public static function make(): Section
    {
        return Section::make()
            ->schema([
                TextEntry::make('Visits')
                    ->label('')
                    ->columnSpanFull()
                    ->getStateUsing(
                        fn ($record) => (new VisitHistoryRenderer($record))->render()
                    ),
            ])
            ->extraAttributes(['style' => 'padding:0;border:none;box-shadow:none;background:transparent;']);
    }
}

<?php

namespace App\Filament\Resources\AdoptedChildren\Pages;

use App\Filament\Resources\AdoptedChildren\AdoptedChildResource;
use Filament\Resources\Pages\ListRecords;

class ListAdoptedChildren extends ListRecords
{
    protected static string $resource = AdoptedChildResource::class;
    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        return parent::getTableQuery()->with([
            'officeVisits' => fn ($q) => $q->orderBy('visit_date', 'desc')->limit(1),
        ]);
    }
}

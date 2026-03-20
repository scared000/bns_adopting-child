<?php

namespace App\Filament\Resources\AdoptedChildren\Pages;

use App\Filament\Resources\AdoptedChildren\AdoptedChildResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdoptedChildren extends ListRecords
{
    protected static string $resource = AdoptedChildResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }
}

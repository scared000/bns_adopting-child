<?php

namespace App\Filament\Resources\AdoptedChildren\Pages;

use App\Filament\Resources\AdoptedChildren\AdoptedChildResource;
use App\Filament\Resources\AdoptedChildren\Infolists\AdoptedChildInfolist;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewAdoptedChild extends ViewRecord
{
    protected static string $resource = AdoptedChildResource::class;

    public function infolist(Schema $schema): Schema
    {
        return AdoptedChildInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

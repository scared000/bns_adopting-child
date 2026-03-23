<?php

namespace App\Filament\Resources\AdoptedChildren\Pages;

use App\Filament\Resources\AdoptedChildren\AdoptedChildResource;
use App\Filament\Resources\AdoptedChildren\Infolists\AdoptedChildInfolist;
use App\Filament\Resources\AdoptedChildren\Schemas\AdoptedChildForm;
use Filament\Actions\Action;
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
            Action::make('back')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(AdoptedChildResource::getUrl('index')),

            EditAction::make()
                ->icon('heroicon-o-pencil')
                ->label('Edit Child Details')
                ->modalWidth('6xl')
                ->steps(AdoptedChildForm::wizardSteps())
                ->fillForm(fn($record): array => AdoptedChildForm::fillEditForm($record))
                ->after(fn ($record, array $data) => AdoptedChildForm::afterEdit($record, $data)),
        ];
    }
}

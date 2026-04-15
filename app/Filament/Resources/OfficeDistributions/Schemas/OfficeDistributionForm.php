<?php

namespace App\Filament\Resources\OfficeDistributions\Schemas;

use App\Models\AdoptedChild;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class OfficeDistributionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('adopted_id')
                ->label('Child')
                ->options(function () {
                    $user     = auth()->user();
                    $officeId = $user->office_id;

                    // If no office assigned (e.g. super_admin), show all children
                    if (! $officeId) {
                        return AdoptedChild::query()
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => $c->firstname . ' ' . $c->lastname
                            ]);
                    }

                    // officeDistributor — only their assigned children
                    return AdoptedChild::whereHas(
                        'officeAssignments',
                        fn ($q) => $q->where('office_id', $officeId)
                    )
                        ->get()
                        ->mapWithKeys(fn ($c) => [
                            $c->id => $c->firstname . ' ' . $c->lastname
                        ]);
                })
                ->searchable()
                ->required(),
            Hidden::make('office_id')
                ->default(fn () => auth()->user()->office_id),

            Hidden::make('visit_type')
                ->default('office_distribution'),

            DatePicker::make('visit_date')
                ->label('Distribution Date')
                ->required()
                ->default(now()),

            Repeater::make('visitItems')
                ->relationship()
                ->label('Items Distributed')
                ->schema([
                    TextInput::make('Item_description')
                        ->label('Item Description')
                        ->required(),

                    TextInput::make('item_quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->required(),

                    TextInput::make('item_amount')
                        ->label('Amount (₱)')
                        ->numeric()
                        ->prefix('₱'),
                ])
                ->columns(3)
                ->columnSpanFull()
                ->addActionLabel('Add Item')
                ->minItems(1),
        ]);
    }
}

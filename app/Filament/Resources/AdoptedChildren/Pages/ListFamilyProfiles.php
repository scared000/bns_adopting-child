<?php

namespace App\Filament\Resources\AdoptedChildren\Pages;

use App\Models\AdoptedChild;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ListFamilyProfiles extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Family Status';
    protected static string|null|\UnitEnum $navigationGroup = 'OVERVIEW';
    protected static ?string $title = 'Family Profiles';

    protected static ?int $navigationSort = 2;
    public function getView(): string
    {
        return 'filament.pages.CustomFamilyProfiles.list-family-profiles';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AdoptedChild::query()
                    ->with(['motherProfile', 'fatherProfile', 'familyStatus'])
            )
            ->columns([
                TextColumn::make('firstname')
                    ->label('CHILD NAME')
                    ->formatStateUsing(fn ($record) =>
                    trim($record->firstname . ' ' . $record->lastname)
                    )
                    ->searchable()
                    ->sortable(),

                TextColumn::make('motherProfile.firstname')
                    ->label('MOTHER')
                    ->getStateUsing(fn ($record) =>
                    trim(
                        ($record->motherProfile?->firstname ?? '') . ' ' .
                        ($record->motherProfile?->middlename ?? '') . ' ' .
                        ($record->motherProfile?->lastname ?? '')
                    ) ?: '—'
                    )
                    ->searchable(),

                TextColumn::make('fatherProfile.firstname')
                    ->label('FATHER')
                    ->getStateUsing(fn ($record) =>
                    trim(
                        ($record->fatherProfile?->firstname ?? '') . ' ' .
                        ($record->fatherProfile?->middlename ?? '') . ' ' .
                        ($record->fatherProfile?->lastname ?? '')
                    ) ?: '—'
                    )
                    ->searchable(),

                TextColumn::make('monthly_income')
                    ->label('MONTHLY INCOME')
                    ->getstateUsing(fn ($record) => match ($record->familyStatus->first()?->monthly_income){
                        'below_5000' => 'Below ₱5,000',
                        '5000-9999' => '₱5,000 - ₱9,999',
                        '10000-14999' => '₱10,000 - ₱14,999',
                        '15000-19999' => '₱15,000 - ₱19,999',
                        '20000-above' => '₱20,000 and above',
                        default => '—',
                    }
                ),

                TextColumn::make('phil_member')
                    ->label('PHIL-HEALTH')
                    ->badge()
                    ->getStateUsing(fn ($record) =>
                        $record->familyStatus->first()?->phil_member ?? null
                    )
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'yes' => 'Yes', 'no' => 'No', default => '—',
                    })
                    ->color(fn ($state) => match ($state) {
                        'yes' => 'success', 'no' => 'danger', default => 'gray',
                    }),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}

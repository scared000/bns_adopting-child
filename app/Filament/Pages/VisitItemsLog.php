<?php

namespace App\Filament\Pages;

use App\Models\VisitItems;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VisitItemsLog extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected static bool $shouldRegisterNavigation = false;
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|null|\UnitEnum $navigationGroup = 'LOG';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Visit Items Log';
    protected static ?string $title = 'Visit Items Log';

    protected string $view = 'filament.pages.item-logs.visit-items-log';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VisitItems::query()
                    ->with(['officeVisit.child', 'officeVisit.bns'])
                    ->latest('created_at')
            )
            ->heading('Visit Items Log')
            ->description('Items distributed per visit')
            ->columns([
                TextColumn::make('officeVisit.visit_date')
                    ->label('VISIT DATE')
                    ->date('M d, Y')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('officeVisit.child.firstname')
                    ->label('CHILD')
                    ->formatStateUsing(
                        fn ($record) =>
                        trim(($record->officeVisit?->child?->firstname ?? '') . ' ' .
                            ($record->officeVisit?->child?->lastname ?? '')) ?: '—'
                    )
                    ->searchable(query: function (Builder $query, string $search) {
                        $query->whereHas('officeVisit.child', function ($q) use ($search) {
                            $q->where('firstname', 'like', "%{$search}%")
                                ->orWhere('lastname',  'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('officeVisit.bns.firstname')
                    ->label('BARANGAY')
                    ->formatStateUsing(
                        fn ($record) =>
                            $record->officeVisit?->bns?->barangay?->brgyDesc ?? '—'
                    ),

                TextColumn::make('Item_description')
                    ->label('ITEM DESCRIPTION')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('item_quantity')
                    ->label('QUANTITY')
                    ->alignCenter(),

                TextColumn::make('item_amount')
                    ->label('AMOUNT (₱)')
                    ->formatStateUsing(
                        fn ($state) =>
                        $state === null || $state == 0
                            ? 'Free'
                            : '₱' . number_format((float) $state, 2)
                    )
                    ->alignRight(),
            ])
            ->filters([])
            ->paginated([10, 25, 50]);
    }
}

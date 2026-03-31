<?php

namespace App\Filament\Resources\Immunizations;

use App\Filament\Resources\Immunizations\Pages\CreateImmunizations;
use App\Filament\Resources\Immunizations\Pages\EditImmunizations;
use App\Filament\Resources\Immunizations\Pages\ListImmunizations;
use App\Models\AdoptedChild;
use App\Models\Immunizations;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ImmunizationsResource extends Resource
{
    protected static ?string $model = Immunizations::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static string|null|\UnitEnum $navigationGroup = 'MONITORING';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Immunization Records';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('child_id')
                ->label('Child')
                ->options(
                    AdoptedChild::all()->mapWithKeys(fn ($c) => [
                        $c->id => $c->firstname . ' ' . $c->lastname
                    ])
                )
                ->searchable()
                ->required(),

            Select::make('vaccine_description')
                ->label('Vaccine / Description')
                ->options([
                    'BCG' => 'BCG',
                    'Hepatitis B' => 'Hepatitis B',
                    'Pentavalent' => 'Pentavalent (DPT-HepB-Hib)',
                    'OPV' => 'OPV (Oral Polio)',
                    'IPV' => 'IPV (Inactivated Polio)',
                    'PCV' => 'PCV (Pneumococcal)',
                    'MMR' => 'MMR (Measles, Mumps, Rubella)',
                    'MCV' => 'MCV (Measles-Containing)',
                    'Vitamin A' => 'Vitamin A Supplementation',
                    'Rotavirus' => 'Rotavirus',
                    'Influenza' => 'Influenza',
                    'Other' => 'Other',
                ])
                ->searchable()
                ->required(),

            DatePicker::make('dose_1')
                ->label('1st Dose Date')
                ->native(false),

            DatePicker::make('dose_2')
                ->label('2nd Dose Date')
                ->native(false),

            DatePicker::make('dose_3')
                ->label('3rd Dose Date')
                ->native(false),

            Textarea::make('remarks')
                ->label('Remarks')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Immunizations::query()->with(['child.municipality.province'])
            )
            ->heading('Immunization Records')
            ->description('EPI vaccine tracking per child')
            ->columns([
                TextColumn::make('child.firstname')
                    ->label('CHILD')
                    ->weight('bold')
                    ->formatStateUsing(fn ($record) =>
                        ($record->child?->firstname ?? '') . ' ' . ($record->child?->lastname ?? '')
                    )
                    ->searchable(query: fn ($query, $search) =>
                    $query->whereHas('child', fn ($q) =>
                    $q->where('firstname', 'like', "%$search%")
                        ->orWhere('lastname', 'like', "%$search%")
                    )
                    ),

                TextColumn::make('child.birthdate')
                    ->label('AGE')
                    ->formatStateUsing(fn ($record) => self::formatAge($record->child?->birthdate))
                    ->sortable(query: fn ($query, $direction) =>
                    $query->join('adopted_children', 'immunizations.child_id', '=', 'adopted_children.id')
                        ->orderBy('adopted_children.birthdate', $direction)
                    ),

                TextColumn::make('vaccine_description')
                    ->label('VACCINE')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('dose_1')
                    ->label('1ST DOSE')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('M d, Y') : '—')
                    ->icon(fn ($state) => $state ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                    ->iconColor(fn ($state) => $state ? 'success' : 'gray')
                    ->alignCenter(),

                TextColumn::make('dose_2')
                    ->label('2ND DOSE')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('M d, Y') : '—')
                    ->icon(fn ($state) => $state ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                    ->iconColor(fn ($state) => $state ? 'success' : 'gray')
                    ->alignCenter(),

                TextColumn::make('dose_3')
                    ->label('3RD DOSE')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('M d, Y') : '—')
                    ->icon(fn ($state) => $state ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle')
                    ->iconColor(fn ($state) => $state ? 'success' : 'gray')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('STATUS')
                    ->badge()
                    ->color(fn (string $state) => $state === 'complete' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'complete'   => 'Complete',
                        'incomplete' => 'Incomplete',
                    ]),

                SelectFilter::make('vaccine_description')
                    ->label('Vaccine')
                    ->options([
                        'BCG' => 'BCG',
                        'Hepatitis B' => 'Hepatitis B',
                        'Pentavalent' => 'Pentavalent',
                        'OPV' => 'OPV',
                        'MCV' => 'MCV',
                        'Vitamin A' => 'Vitamin A',
                    ]),
            ])
            ->recordActionsColumnLabel('ACTION')
            ->recordActions([
                EditAction::make()->icon('heroicon-o-pencil')->badge(),
                DeleteAction::make()->icon('heroicon-o-trash')->badge(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function formatAge(?string $birthdate): string
    {
        if (!$birthdate) return '—';
        $diff = Carbon::parse($birthdate)->diff(now());
        $years  = $diff->y;
        $months = $diff->m;
        return $years > 0
            ? "{$years}y {$months}m"
            : "{$months}m";
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListImmunizations::route('/'),
            'create' => CreateImmunizations::route('/create'),
            'edit'   => EditImmunizations::route('/{record}/edit'),
        ];
    }
}

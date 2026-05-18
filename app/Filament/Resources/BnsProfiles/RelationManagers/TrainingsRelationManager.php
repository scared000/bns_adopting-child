<?php
namespace App\Filament\Resources\BnsProfiles\RelationManagers;

use App\Models\BnsTraining;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainingsRelationManager extends RelationManager
{
    protected static string $relationship = 'trainings';
    protected static ?string $title = 'Training & Capacity Building';
    protected static string|null|\BackedEnum $icon = 'heroicon-o-academic-cap';

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Training Details')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')
                        ->label('Training Title')
                        ->columnSpanFull()
                        ->weight('bold'),

                    TextEntry::make('date_attended')
                        ->label('Date Attended')
                        ->date('F d, Y'),

                    TextEntry::make('duration_hours')
                        ->label('Duration')
                        ->suffix(' hours'),

                    TextEntry::make('conducted_by')
                        ->label('Conducted By'),

                    TextEntry::make('venue')
                        ->label('Venue'),

                    TextEntry::make('remarks')
                        ->label('Remarks')
                        ->columnSpanFull()
                        ->placeholder('No remarks.'),
                ]),

            Section::make('Certificate of Completion')
                ->schema([
                    ImageEntry::make('certificate')
                        ->label('')
                        ->disk('public')
                        ->columnSpanFull()
                        ->extraImgAttributes([        // ← let image scale naturally
                            'style' => 'width: 100%; height: auto; max-height: none; border-radius: 0.5rem;',
                        ])
                        ->visible(function ($record): bool {
                            if (empty($record->certificate)) return false;
                            $files = is_array($record->certificate)
                                ? $record->certificate
                                : [$record->certificate];
                            return collect($files)->contains(
                                fn ($f) => preg_match('/\.(jpg|jpeg|png)$/i', $f)
                            );
                        }),

                    TextEntry::make('pdf_certificates')
                        ->label('PDF Files')
                        ->columnSpanFull()
                        ->html()
                        ->getStateUsing(function ($record): string {
                            if (empty($record->certificate)) return '';
                            $files = is_array($record->certificate)
                                ? $record->certificate
                                : [$record->certificate];

                            $pdfs = collect($files)->filter(
                                fn ($f) => preg_match('/\.pdf$/i', $f)
                            );

                            if ($pdfs->isEmpty()) return '';

                            return $pdfs->values()->map(function ($path, $i) {
                                $url = asset('storage/' . $path);
                                $name = 'Certificate ' . ($i + 1) . '.pdf';
                                return "<a href=\"{$url}\" target=\"_blank\"
                                class=\"inline-flex items-center gap-1 text-primary-600 underline hover:text-primary-800\">
                                📄 {$name}
                            </a>";
                            })->implode('<br>');
                        })
                        ->visible(function ($record): bool {
                            if (empty($record->certificate)) return false;
                            $files = is_array($record->certificate)
                                ? $record->certificate
                                : [$record->certificate];
                            return collect($files)->contains(
                                fn ($f) => preg_match('/\.pdf$/i', $f)
                            );
                        }),

                    TextEntry::make('no_certificate')
                        ->label('')
                        ->getStateUsing(fn () => 'No certificate uploaded.')
                        ->columnSpanFull()
                        ->visible(fn ($record) => empty($record->certificate)),
                ]),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columnSpanFull()
                ->columns(12)
                ->schema([

                    // Training title — full width
                    Select::make('title')
                        ->label('Training Title')
                        ->options(array_combine(
                            BnsTraining::commonTitles(),
                            BnsTraining::commonTitles()
                        ))
                        ->searchable()
                        ->createOptionForm([
                            TextInput::make('title')
                                ->label('Custom Training Title')
                                ->required(),
                        ])
                        ->createOptionUsing(fn (array $data) => $data['title'])
                        ->required()
                        ->columnSpan(12),

                    // Date + Duration side by side
                    DatePicker::make('date_attended')
                        ->label('Date Attended')
                        ->native(false)
                        ->displayFormat('M d, Y')
                        ->maxDate(now())
                        ->columnSpan(6),

                    TextInput::make('duration_hours')
                        ->label('Duration (hours)')
                        ->numeric()
                        ->minValue(1)
                        ->suffix('hrs')
                        ->columnSpan(6),

                    // Conducted by + Venue side by side
                    TextInput::make('conducted_by')
                        ->label('Conducted By')
                        ->maxLength(150)
                        ->columnSpan(6),

                    TextInput::make('venue')
                        ->label('Venue')
                        ->maxLength(200)
                        ->columnSpan(6),

                    // Certificate — full width
                    FileUpload::make('certificate')
                        ->label('Certificate of Completion')
                        ->disk('public')
                        ->directory('bns-training-certificates')
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(3072)
                        ->multiple()
                        ->downloadable()
                        ->openable()
                        ->columnSpan(12),

                    // Remarks — full width
                    Textarea::make('remarks')
                        ->label('Remarks')
                        ->rows(3)
                        ->placeholder('Optional notes about this training...')
                        ->columnSpan(12),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('date_attended')
                    ->date('M d, Y')
                    ->sortable(),
                TextColumn::make('conducted_by')
                    ->label('Conducted By')
                    ->toggleable(),
                TextColumn::make('venue')
                    ->toggleable(),
                TextColumn::make('duration_hours')
                    ->label('Hours')
                    ->suffix(' hrs')
                    ->toggleable(),
                IconColumn::make('certificate')
                    ->label('Cert.')
                    ->boolean()
                    ->getStateUsing(fn ($record) => ! empty($record->certificate)),
            ])
            ->defaultSort('date_attended', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Add Training')
                    ->modalWidth('5xl'),
            ])
            ->recordActions([
                ViewAction::make()->modalWidth('5xl'),
                EditAction::make()->modalWidth('5xl'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Immunizations\Tables;

use App\Filament\Resources\AdoptedChildren\AdoptedChildResource;
use App\Models\Immunizations;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ImmunizationsTable
{
    private static function formatAge(?string $birthdate): string
    {
        if (! $birthdate) return '—';
        $diff   = Carbon::parse($birthdate)->diff(now());
        $years  = $diff->y;
        $months = $diff->m;

        return $years > 0
            ? "{$years}y {$months}m old"
            : "{$months}m old";
    }
    public static function configure(Table $table): Table
    {
        return $table
            //            ->query(
//                Immunizations::query()->with(['child.municipality.province'])
//            )
            ->heading('Immunization Records')
            ->description('EPI vaccine tracking per child — click any row to view the child\'s full immunization history.')

            //Row click → child view page on the Immunization tab
            ->recordUrl(function (Immunizations $record): ?string {
                if (! $record->child_id) {
                    return null;
                }

                // Adjust the tab query-string key if your setup differs
                return AdoptedChildResource::getUrl('view', [
                        'record' => $record->child_id,
                    ]) . '?tab=-immunization-records-tab';
            })

            ->columns([
                TextColumn::make('child.firstname')
                    ->label('CHILD')
                    ->weight('bold')
                    ->formatStateUsing(fn ($record) =>
                        ($record->child?->firstname ?? '') . ' ' . ($record->child?->lastname ?? '')
                    )
                    ->description(fn ($record) => self::formatAge($record->child?->birthdate))
                    ->searchable(query: fn ($query, $search) =>
                    $query->whereHas('child', fn ($q) =>
                    $q->where('firstname', 'like', "%$search%")
                        ->orWhere('lastname', 'like', "%$search%")
                    )
                    ),

                TextColumn::make('vaccine_description')
                    ->label('VACCINE')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                // Dynamic dose schedule
                // Shows only as many dose pills as total_doses recorded per row.
                // This mirrors real-world EPI cards where each vaccine has its
                // own schedule (BCG=1, HepB=3, Pentavalent=3, OPV=3, etc.).
                TextColumn::make('dose_schedule_html')
                    ->label('DOSE SCHEDULE')
                    ->html()
                    ->getStateUsing(function (Immunizations $record): string {
                        $totalDoses = max((int) ($record->total_doses ?? 1), 1);
                        $pills      = '';

                        for ($i = 1; $i <= $totalDoses; $i++) {
                            $field = "dose_{$i}";
                            $date  = $record->$field ?? null;

                            if ($date) {
                                $formatted = Carbon::parse($date)->format('M d, Y');
                                $pills .= <<<HTML
                                    <div style="display:inline-flex;flex-direction:column;align-items:flex-start;gap:2px;margin-right:10px;">
                                        <span style="font-size:9px;font-weight:800;color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;">Dose {$i}</span>
                                        <span style="display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:2px 8px;font-size:11px;font-weight:600;color:#15803d;white-space:nowrap;">
                                            <svg style="width:12px;height:12px;flex-shrink:0;" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                                            {$formatted}
                                        </span>
                                    </div>
                                HTML;
                            } else {
                                $pills .= <<<HTML
                                    <div style="display:inline-flex;flex-direction:column;align-items:flex-start;gap:2px;margin-right:10px;">
                                        <span style="font-size:9px;font-weight:800;color:#9ca3af;letter-spacing:.08em;text-transform:uppercase;">Dose {$i}</span>
                                        <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:2px dashed #e5e7eb;background:#fafafa;">
                                            <svg style="width:12px;height:12px;color:#d1d5db;" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
                                        </span>
                                    </div>
                                HTML;
                            }
                        }
                        return '<div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:4px 0;">' . $pills . '</div>';
                    }),

                TextColumn::make('status')
                    ->label('STATUS')
                    ->badge()
                    ->color(fn (string $state) => $state === 'complete' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->alignCenter(),
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
                        'IPV' => 'IPV',
                        'PCV' => 'PCV',
                        'MMR' => 'MMR',
                        'MCV' => 'MCV',
                        'Vitamin A' => 'Vitamin A',
                        'Rotavirus' => 'Rotavirus',
                        'Influenza' => 'Influenza',
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}

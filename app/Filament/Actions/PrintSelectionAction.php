<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\CheckboxList;
use Livewire\Component;

class PrintSelectionAction
{
    public static function make(): Action
    {
        return Action::make('print_child')
            ->label('Print Report')
            ->icon('heroicon-o-printer')
            ->color('success')
            ->iconButton()
            ->tooltip("Print this child's latest report")
            ->modalHeading('Select Print Type')
            ->modalDescription('Choose which document(s) you want to print.')
            ->modalWidth('sm')
            ->form([
                Radio::make('print_mode')
                    ->label('Print Mode')
                    ->required()
                    ->options([
                        'monthly-monitoring' => 'Monthly Monitoring (single)',
                        'combined'           => 'Other Documents',
                    ])
                    ->default('monthly-monitoring')
                    ->live(),

                CheckboxList::make('print_types')
                    ->label('Select Documents')
                    ->options([
                        'profile'             => 'Child Profile',
                        'items-delivered'     => 'List of Items Delivered',
                        'immunization-record' => 'Immunization Record',
                    ])
                    ->visible(fn ($get) => $get('print_mode') === 'combined')
                    ->required(fn ($get) => $get('print_mode') === 'combined')
                    ->minItems(1),
            ])
            ->fillForm(fn () => [
                'print_mode'  => 'monthly-monitoring',
                'print_types' => [],
            ])
            ->action(function (array $data, $record, Component $livewire): void {
                $mode = $data['print_mode'] ?? 'monthly-monitoring';

                if ($mode === 'monthly-monitoring') {
                    $url = route('print.child.monthly-monitoring', ['id' => $record->id]);
                } else {
                    $types = implode(',', $data['print_types'] ?? []);
                    $url   = route('print.child.combined', [
                        'id'    => $record->id,
                        'types' => $types,
                    ]);
                }

                $livewire->dispatch('open-print-tab', url: $url);
            });
    }
}

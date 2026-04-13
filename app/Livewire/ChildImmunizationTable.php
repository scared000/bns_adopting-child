<?php

namespace App\Livewire;

use App\Models\Immunizations;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ChildImmunizationTable extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    #[Locked]
    public ?int $childId = null;
    public ?int $editingDoseRow = null;
    public ?string $editingDoseField = null;
    public string $doseDate = '';
    public int $extraDoses = 0;

    public ?int $confirmDeleteId = null;

    public const array VACCINES = [
        'BCG',
        'Hepatitis B',
        'Pentavalent',
        'OPV (Oral Polio)',
        'IPV (Inactivated Polio)',
        'PCV (Pneumococcal)',
        'MMR',
        'MCV',
        'Vitamin A',
        'Rotavirus',
        'Influenza',
    ];

    /**
     * Reference data: recommended dose count and schedule per vaccine.
     * 'doses'    → total recommended doses
     * 'schedule' → when each dose is given (array, one entry per dose)
     */
    public const array VACCINE_INFO = [
        'BCG' => [
            'doses'    => 1,
            'schedule' => ['At birth'],
        ],
        'Hepatitis B' => [
            'doses'    => 1,
            'schedule' => ['At birth'],
        ],
        'Pentavalent' => [
            'doses'    => 3,
            'schedule' => ['1½ months', '2½ months', '3½ months'],
        ],
        'OPV (Oral Polio)' => [
            'doses'    => 3,
            'schedule' => ['1½ months', '2½ months', '3½ months'],
        ],
        'IPV (Inactivated Polio)' => [
            'doses'    => 1,
            'schedule' => ['3½ months'],
        ],
        'PCV (Pneumococcal)' => [
            'doses'    => 3,
            'schedule' => ['1½ months', '2½ months', '3½ months'],
        ],
        'MMR' => [
            'doses'    => 2,
            'schedule' => ['9 months', '12 months (1 year)'],
        ],
    ];

    public function mount(?int $childId = null): void
    {
        $this->childId = $childId;
    }

    public function startEdit(int $rowId, string $field, ?string $currentDate): void
    {
        $this->editingDoseRow   = $rowId;
        $this->editingDoseField = $field;
        $this->doseDate         = $currentDate ?? now()->format('Y-m-d');
    }

    public function saveDose(): void
    {
        $record = Immunizations::findOrFail($this->editingDoseRow);
        $record->{$this->editingDoseField} = !empty($this->doseDate) ? $this->doseDate : null;
        $record->save();
        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->editingDoseRow   = null;
        $this->editingDoseField = null;
        $this->doseDate         = '';
    }

    public function confirmAddRecord(): void
    {
        if (! $this->childId) return;

        $vaccine = $this->newVaccine === 'Other'
            ? trim($this->newOtherVaccine)
            : $this->newVaccine;

        if (empty($vaccine) || $vaccine === 'Select Vaccine') {
            Notification::make()
                ->title('Please select a valid vaccine.')
                ->warning()
                ->send();
            return;
        }

        $exists = Immunizations::where('child_id', $this->childId)
            ->where('vaccine_description', $vaccine)
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Duplicate Vaccine')
                ->body("{$vaccine} is already recorded for this child.")
                ->warning()
                ->send();
            return;
        }

        Immunizations::create([
            'child_id'            => $this->childId,
            'vaccine_description' => $vaccine,
            'total_doses'         => 1,
            'status'              => 'incomplete',
        ]);

        $this->cancelAdd();
    }

    public function addDoseColumn(): void
    {
        $records = Immunizations::where('child_id', $this->childId)->get();

        foreach ($records as $record) {
            if ($record->total_doses < 5) {
                $record->total_doses += 1;
                $record->save();
            }
        }
        $this->extraDoses++;
    }

    public function incrementDose(int $recordId): void
    {
        $record = Immunizations::findOrFail($recordId);
        if (empty($record->vaccine_description) || $record->vaccine_description === 'Select vaccine') {
            Notification::make()
                ->title('Select a vaccine first before adding doses.')
                ->warning()
                ->send();
            return;
        }

        if ($record->total_doses < 5) {
            $record->increment('total_doses');
        }
    }

    public function decrementDose(int $recordId): void
    {
        $record = Immunizations::findOrFail($recordId);
        if ($record->total_doses > 1) {
            $fieldToClear = 'dose_' . $record->total_doses;
            $record->$fieldToClear = null;
            $record->decrement('total_doses');
        }
    }

    public function getMaxDoses(): int
    {
        $hasDose4 = Immunizations::where('child_id', $this->childId)->whereNotNull('dose_4')->exists();
        $hasDose5 = Immunizations::where('child_id', $this->childId)->whereNotNull('dose_5')->exists();
        $dbMax = 1;
        if ($hasDose5) $dbMax = 5;
        elseif ($hasDose4) $dbMax = 4;
        return min(max($dbMax, 1 + $this->extraDoses), 5);
    }

    public function clearDose(int $recordId, string $field): void
    {
        $record = Immunizations::find($recordId);

        if ($record) {
            $record->{$field} = null;
            $record->save();
            $this->dispatch('$refresh');
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function deleteRecord(): void
    {
        if ($this->confirmDeleteId === null) return;

        $record = Immunizations::find($this->confirmDeleteId);
        if ($record) {
            $record->delete();
        }
        $this->confirmDeleteId = null;
    }

    public function addVaccineAction(): Action
    {
        return Action::make('addVaccine')
            ->label('Add Vaccine')
            ->icon('heroicon-m-plus')
            ->color('warning')
            ->modalHeading('Add Immunization Record')
            ->modalDescription('Select the vaccine to add for this child.')
            ->modalSubmitActionLabel('Add Vaccine')
            ->modalWidth('md')
            ->form([
                Select::make('vaccine')
                    ->label('Vaccine')
                    ->options(
                        collect(self::VACCINES)
                            ->mapWithKeys(fn($v) => [$v => $v])
                            ->put('Other', 'Other (Specify)')
                            ->toArray()
                    )
                    ->live()
                    ->required()
                    ->placeholder('-- Select Vaccine --'),

                TextInput::make('other_vaccine')
                    ->label('Specify Vaccine Name')
                    ->placeholder('e.g. Varicella, Typhoid...')
                    ->visible(fn(Get $get) => $get('vaccine') === 'Other')
                    ->requiredIf('vaccine', 'Other'),
            ])
            ->action(function (array $data) {
                if (! $this->childId) return;

                $vaccine = $data['vaccine'] === 'Other'
                    ? trim($data['other_vaccine'] ?? '')
                    : $data['vaccine'];

                if (empty($vaccine)) {
                    Notification::make()
                        ->title('Please specify a vaccine name.')
                        ->warning()
                        ->send();
                    return;
                }

                $exists = Immunizations::where('child_id', $this->childId)
                    ->where('vaccine_description', $vaccine)
                    ->exists();

                if ($exists) {
                    Notification::make()
                        ->title('Duplicate Vaccine')
                        ->body("{$vaccine} is already recorded for this child.")
                        ->warning()
                        ->send();
                    return;
                }

                Immunizations::create([
                    'child_id'            => $this->childId,
                    'vaccine_description' => $vaccine,
                    'total_doses'         => 1,
                    'status'              => 'incomplete',
                ]);

                Notification::make()
                    ->title('Vaccine Added')
                    ->body("{$vaccine} has been added successfully.")
                    ->success()
                    ->send();
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->requiresConfirmation()
            ->modalHeading('Delete Vaccine Record')
            ->modalDescription('Are you sure you want to delete this immunization record? This action cannot be undone.')
            ->modalSubmitActionLabel('Yes, delete it')
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->action(function (array $arguments) {
                $record = Immunizations::find($arguments['record']);

                if ($record) {
                    $record->delete();

                    Notification::make()
                        ->title('Deleted')
                        ->success()
                        ->body('Vaccine record deleted successfully.')
                        ->send();
                }
            });
    }

    public function render()
    {
        return view('livewire.child-immunization-table', [
            'records'     => Immunizations::where('child_id', $this->childId)->get(),
            'maxDoses'    => $this->getMaxDoses(),
            'vaccineInfo' => self::VACCINE_INFO,
        ]);
    }
}

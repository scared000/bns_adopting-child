<?php

namespace App\Livewire;

use App\Models\Immunizations;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
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
    public string $otherVaccine = '';
    public int $extraDoses = 0;

    public ?int $confirmDeleteId = null;

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

        if ($this->editingDoseField === 'vaccine') {
            $finalValue = ($this->doseDate === 'Other') ? $this->otherVaccine : $this->doseDate;
            $record->vaccine_description = $finalValue;
        } else {
            $record->{$this->editingDoseField} = !empty($this->doseDate) ? $this->doseDate : null;
        }

        $record->save();
        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->editingDoseRow   = null;
        $this->editingDoseField = null;
        $this->doseDate         = '';
    }

    public function addRecord(): void
    {
        if (! $this->childId) return;

        Immunizations::create([
            'child_id'           => $this->childId,
            'vaccine_description' => 'Select vaccine',
            'status'             => 'incomplete',
        ]);
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
        if ($record->total_doses < 5) {
            $record->increment('total_doses');
        }
    }

    public function decrementDose(int $recordId): void
    {
        $record = Immunizations::findOrFail($recordId);
        if ($record->total_doses > 1) {
            // Optional: Clear the date of the dose being removed
            $fieldToClear = 'dose_' . $record->total_doses;
            $record->$fieldToClear = null;

            $record->decrement('total_doses');
        }
    }

    public function getMaxDoses(): int
    {
        $hasDose4 = Immunizations::where('child_id', $this->childId)->whereNotNull('dose_4')->exists();
        $hasDose5 = Immunizations::where('child_id', $this->childId)->whereNotNull('dose_5')->exists();
        $dbMax = 3;
        if ($hasDose5) $dbMax = 5;
        elseif ($hasDose4) $dbMax = 4;
        return min(max($dbMax, 3 + $this->extraDoses), 5);
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
        if($this->confirmDeleteId === null) return;

        $record = Immunizations::find($this->confirmDeleteId);
        if ($record) {
            $record->delete();
        }
        $this->confirmDeleteId = null;
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
            'records'  => Immunizations::where('child_id', $this->childId)->get(),
            'maxDoses' => $this->getMaxDoses(),
        ]);
    }
}

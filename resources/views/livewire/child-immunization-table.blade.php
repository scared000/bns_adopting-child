<div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Immunization Records</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $records->count() }} {{ str('vaccine')->plural($records->count()) }} recorded
                    </p>
                </div>
            </div>

            <button wire:click="addRecord"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white transition-colors shadow-sm">
                <x-heroicon-m-plus class="w-4 h-4" />
                Add Vaccine
            </button>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                <tr class="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Vaccine Details</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Dose Schedule</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($records as $record)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors border-b border-gray-100 dark:border-gray-800">
                        <td class="px-6 py-4">
                            @if($editingDoseRow === $record->id && $editingDoseField === 'vaccine')
                                <div class="flex flex-col gap-2 min-w-[180px]">
                                    <select wire:model.live="doseDate"
                                            class="text-xs p-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition">
                                        <option>Select Vaccine</option>
                                        <option value="BCG">BCG</option>
                                        <option value="Hepatitis B">Hepatitis B</option>
                                        <option value="Pentavalent">Pentavalent</option>
                                        <option value="OPV">OPV (Oral Polio)</option>
                                        <option value="IPV">IPV (Inactivated Polio)</option>
                                        <option value="PCV">PCV (Pneumococcal)</option>
                                        <option value="MMR">MMR</option>
                                        <option value="MCV">MCV</option>
                                        <option value="Vitamin A">Vitamin A</option>
                                        <option value="Rotavirus">Rotavirus</option>
                                        <option value="Influenza">Influenza</option>
                                        <option value="Other">Other (Specify)</option>
                                    </select>

                                    @if($doseDate === 'Other')
                                        <input type="text" wire:model="otherVaccine" placeholder="Type vaccine name..."
                                               class="text-xs p-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition" />
                                    @endif

                                    <div class="flex gap-2">
                                        <button wire:click="saveDose" class="flex-1 text-xs px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition-colors">Save</button>
                                        <button wire:click="cancelEdit" class="flex-1 text-xs px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg font-medium transition-colors">Cancel</button>
                                    </div>
                                </div>
                            @else
                                <button wire:click="startEdit({{ $record->id }}, 'vaccine', '{{ $record->vaccine_description }}')"
                                        class="flex items-center gap-2 group">
                                    <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                                        <x-heroicon-o-shield-check class="w-4 h-4 text-orange-500 dark:text-orange-400" />
                                    </div>
                                    <span class="font-semibold text-gray-800 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                        {{ $record->vaccine_description ?? 'Select Vaccine' }}
                                    </span>
                                </button>
                            @endif
                        </td>

                        {{-- Dynamic Doses Column (Spans across all dose headers) --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4 flex-wrap">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= ($record->total_doses ?? 3))
                                        @php $field = "dose_$i"; $val = $record->$field; @endphp

                                        <div class="flex flex-col items-start gap-1">
                                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase">Dose {{ $i }}</span>

                                            @if($editingDoseRow === $record->id && $editingDoseField === $field)
                                                <div class="flex items-center gap-1 bg-white dark:bg-gray-800 p-1 rounded-lg border border-orange-200 shadow-sm">
                                                    <input type="date" wire:model="doseDate" class="text-xs p-1 border-none focus:ring-0 bg-transparent dark:text-gray-200 w-28">
                                                    <button wire:click="saveDose" class="p-1 text-emerald-600"> <x-heroicon-s-check class="w-4 h-4" /> </button>
                                                    <button wire:click="cancelEdit" class="p-1 text-gray-400"> <x-heroicon-s-x-mark class="w-4 h-4" /> </button>
                                                </div>
                                            @else
                                                <button wire:click="startEdit({{ $record->id }}, '{{ $field }}', '{{ $val }}')"
                                                        class="group flex items-center justify-center transition-all">
                                                    @if(!empty($val))
                                                        <div class="px-2.5 py-1 rounded-lg bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800/50 text-orange-600 dark:text-orange-400 text-[11px] font-bold flex items-center gap-1">
                                                            <x-heroicon-s-calendar-days class="w-3 h-3" />
                                                            {{ \Carbon\Carbon::parse($val)->format('M d, Y') }}
                                                        </div>
                                                    @else
                                                        <div class="w-8 h-8 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700 group-hover:border-orange-400 group-hover:bg-orange-50/50 transition-all flex items-center justify-center">
                                                            <x-heroicon-s-plus class="w-3.5 h-3.5 text-gray-300 group-hover:text-orange-500" />
                                                        </div>
                                                    @endif
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                @endfor

                                {{-- Add/Remove Dose Buttons for THIS specific row --}}
                                <div class="flex items-center gap-1 ml-2 self-end pb-1">
                                    <button wire:click="incrementDose({{ $record->id }})" class="text-gray-300 hover:text-emerald-500 transition-colors">
                                        <x-heroicon-s-plus-circle class="w-5 h-5" />
                                    </button>
                                    <button wire:click="decrementDose({{ $record->id }})" class="text-gray-300 hover:text-red-400 transition-colors">
                                        <x-heroicon-s-minus-circle class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </td>

                        {{-- 3. Status --}}
                        <td class="px-6 py-4 text-center">
                            @if($record->status === 'complete')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-[10px] font-black uppercase tracking-tighter">
                                    <x-heroicon-s-check-circle class="w-3.5 h-3.5" /> Complete
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-[10px] font-black uppercase tracking-tighter">
                                    <x-heroicon-s-clock class="w-3.5 h-3.5" /> Incomplete
                                </span>
                            @endif
                        </td>

                        {{-- 4. Actions --}}
                        <td class="px-6 py-4 text-center">
                            {{ ($this->deleteAction)(['record' => $record->id]) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $maxDoses + ($maxDoses < 5 ? 4 : 3) }}" class="px-6 py-16 text-center">
                            No records found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <x-filament-actions::modals />
</div>

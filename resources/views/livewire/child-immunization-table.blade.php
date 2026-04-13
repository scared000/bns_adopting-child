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
            {{ ($this->addVaccineAction)([]) }}
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                <tr class="bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Vaccine Details</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Recommended Doses</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Dose Schedule</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($records as $record)
                    @php
                        $info = $vaccineInfo[$record->vaccine_description] ?? null;
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">

                        {{-- Vaccine Name --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                                    <x-heroicon-o-shield-check class="w-4 h-4 text-orange-500 dark:text-orange-400" />
                                </div>
                                <span class="font-semibold text-gray-800 dark:text-white text-sm">
                                    {{ $record->vaccine_description }}
                                </span>
                            </div>
                        </td>

                        {{-- Recommended Doses (reference column) --}}
                        <td class="px-6 py-4">
                            @if ($info)
                                <div class="flex flex-col gap-1.5">
                                    {{-- Dose count badge --}}
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold"
                                              style="background-color:#fff7ed;color:#c2410c;border:1px solid #fed7aa;">
                                            <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zM6 8a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0zM3.293 13.293A1 1 0 014 13h12a1 1 0 01.707 1.707C15.187 16.237 12.738 17 10 17s-5.187-.763-6.707-2.293a1 1 0 010-1.414z"/>
                                            </svg>
                                            {{ $info['doses'] }} {{ $info['doses'] === 1 ? 'Dose' : 'Doses' }}
                                        </span>
                                    </div>
                                    {{-- Schedule list --}}
                                    <div class="flex flex-col gap-0.5">
                                        @foreach ($info['schedule'] as $index => $when)
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-4 h-4 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0 text-[9px] font-black text-orange-500">
                                                    {{ $index + 1 }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $when }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                {{-- Vaccine not in reference list (custom/other) --}}
                                <span class="inline-flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500 italic">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                                    </svg>
                                    No reference data
                                </span>
                            @endif
                        </td>

                        {{-- Dose Schedule --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3 flex-wrap">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= ($record->total_doses ?? 1))
                                        @php $field = "dose_$i"; $val = $record->$field; @endphp

                                        <div class="flex flex-col items-start gap-1">
                                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Dose {{ $i }}</span>

                                            @if($editingDoseRow === $record->id && $editingDoseField === $field)
                                                <div class="flex items-center gap-1 bg-white dark:bg-gray-800 p-1 rounded-lg border border-orange-200 shadow-sm">
                                                    <input type="date" wire:model="doseDate"
                                                           class="text-xs p-1 border-none focus:ring-0 bg-transparent dark:text-gray-200 w-28">
                                                    <button wire:click="saveDose" class="p-1 text-emerald-600">
                                                        <x-heroicon-s-check class="w-4 h-4" />
                                                    </button>
                                                    <button wire:click="cancelEdit" class="p-1 text-gray-400">
                                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            @else
                                                <button wire:click="startEdit({{ $record->id }}, '{{ $field }}', '{{ $val }}')"
                                                        class="group flex items-center justify-center transition-all">
                                                    @if(!empty($val))
                                                        <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 hover:text-orange-500 transition-colors">
                                                            <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                                            <span class="text-xs font-medium whitespace-nowrap">
                                                                {{ \Carbon\Carbon::parse($val)->format('M d, Y') }}
                                                            </span>
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

                                {{-- Add/Remove Dose Buttons --}}
                                @php $vaccineSet = !empty($record->vaccine_description) && $record->vaccine_description !== 'Select vaccine'; @endphp
                                <div class="flex items-center gap-1 ml-1 self-end pb-0.5">
                                    <button wire:click="incrementDose({{ $record->id }})"
                                            @if(!$vaccineSet) disabled @endif
                                            class="transition-colors {{ $vaccineSet ? 'text-gray-300 hover:text-emerald-500' : 'text-gray-200 dark:text-gray-700 cursor-not-allowed opacity-40' }}"
                                            title="{{ $vaccineSet ? 'Add dose' : 'Select a vaccine first' }}">
                                        <x-heroicon-s-plus-circle class="w-5 h-5" />
                                    </button>
                                    <button wire:click="decrementDose({{ $record->id }})"
                                            class="text-gray-300 hover:text-red-400 transition-colors"
                                            title="Remove dose">
                                        <x-heroicon-s-minus-circle class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">
                            @if($record->status === 'complete')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                                      style="background-color:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                                    </svg>
                                    Complete
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                                      style="background-color:#fffbeb;color:#b45309;border:1px solid #fde68a;">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
                                    </svg>
                                    Incomplete
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-center">
                            {{ ($this->deleteAction)(['record' => $record->id]) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400 dark:text-gray-600">
                                <x-heroicon-o-shield-check class="w-8 h-8" />
                                <span class="text-sm">No immunization records yet.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <x-filament-actions::modals />
</div>

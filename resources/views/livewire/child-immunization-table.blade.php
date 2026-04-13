<div class="ddo-scope">
    <style>
        .ddo-scope {
            --gold-50:  #fffbeb;
            --gold-100: #fef3c7;
            --gold-200: #fde68a;
            --gold-300: #fbbf24;
            --gold-400: #f59e0b;
            --gold-500: #d97706;
            --gold-600: #b45309;
            --gold-700: #92400e;
            --gold-800: #78350f;
            --gold-900: #451a03;
            --surface:        #ffffff;
            --surface-2:      #fffbf2;
            --border:         #fde68a;
            --border-strong:  #f59e0b;
            --text-primary:   #1c0a00;
            --text-secondary: #78350f;
            --text-muted:     #a16207;
        }
        .dark .ddo-scope {
            --surface:        #1c1008;
            --surface-2:      #251508;
            --border:         #78350f;
            --border-strong:  #b45309;
            --text-primary:   #fef3c7;
            --text-secondary: #fbbf24;
            --text-muted:     #d97706;
        }

        .ddo-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(180,83,9,0.08), 0 0 0 1px rgba(253,230,138,0.3);
        }
        .ddo-card-header {
            background: var(--surface-2);
            border-bottom: 2px solid var(--border);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .ddo-icon-wrap {
            width: 36px; height: 36px; border-radius: 10px;
            background: var(--gold-100);
            border: 1px solid var(--gold-200);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ddo-title {
            font-size: 14px; font-weight: 700;
            color: var(--text-primary); margin: 0;
        }
        .ddo-subtitle {
            font-size: 12px; color: var(--text-muted); margin-top: 2px;
        }

        /* Table */
        .ddo-table { width: 100%; text-align: left; border-collapse: collapse; font-size: 13px; }
        .ddo-table thead tr {
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }
        .ddo-table thead th {
            padding: 12px 24px;
            font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--text-muted);
        }
        .ddo-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
        .ddo-table tbody tr:last-child { border-bottom: none; }
        .ddo-table tbody tr:hover { background: var(--surface-2); }
        .ddo-table td { padding: 16px 24px; vertical-align: middle; }

        /* Vaccine icon */
        .ddo-vaccine-icon {
            width: 32px; height: 32px; border-radius: 8px;
            background: var(--gold-100); border: 1px solid var(--gold-200);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ddo-vaccine-name { font-size: 13px; font-weight: 700; color: var(--text-primary); }

        /* Recommended doses */
        .ddo-dose-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 800;
            background: var(--gold-100); color: var(--gold-700); border: 1px solid var(--gold-200);
        }
        .ddo-schedule-num {
            width: 16px; height: 16px; border-radius: 50%;
            background: var(--gold-200); color: var(--gold-700);
            display: flex; align-items: center; justify-content: center;
            font-size: 9px; font-weight: 900; flex-shrink: 0;
        }
        .ddo-schedule-text { font-size: 11px; color: var(--text-muted); white-space: nowrap; }
        .ddo-no-ref { font-size: 11px; color: var(--text-muted); font-style: italic;
            display: inline-flex; align-items: center; gap: 4px; }

        /* Dose schedule inputs */
        .ddo-dose-label {
            font-size: 9px; font-weight: 900; text-transform: uppercase;
            letter-spacing: .1em; color: var(--text-muted);
        }
        .ddo-date-btn {
            display: inline-flex; align-items: center; gap: 5px;
            color: var(--text-secondary);
            transition: color .15s; background: none; border: none; cursor: pointer; padding: 0;
        }
        .ddo-date-btn:hover { color: var(--gold-500); }
        .ddo-date-check { color: #16a34a; width:15px; height:15px; flex-shrink:0; }
        .ddo-date-text { font-size: 12px; font-weight: 600; white-space: nowrap; color: var(--text-secondary); }
        .ddo-empty-slot {
            width: 32px; height: 32px; border-radius: 8px;
            border: 2px dashed var(--gold-300);
            display: flex; align-items: center; justify-content: center;
            transition: border-color .15s, background .15s;
        }
        .ddo-date-btn:hover .ddo-empty-slot { border-color: var(--gold-500); background: var(--gold-50); }

        .ddo-edit-box {
            display: flex; align-items: center; gap: 4px;
            background: var(--surface); border: 1px solid var(--gold-300);
            border-radius: 8px; padding: 4px 6px;
            box-shadow: 0 1px 3px rgba(180,83,9,0.1);
        }
        .ddo-edit-box input[type="date"] {
            font-size: 12px; padding: 2px 4px;
            border: none; background: transparent; outline: none;
            color: var(--text-primary); width: 112px;
        }
        .ddo-edit-save  { color: #16a34a; background:none; border:none; cursor:pointer; padding:2px; }
        .ddo-edit-cancel{ color: var(--text-muted); background:none; border:none; cursor:pointer; padding:2px; }

        .ddo-dose-controls { display:flex; align-items:center; gap:4px; margin-left:4px; align-self:flex-end; padding-bottom:2px; }
        .ddo-btn-add    { color: var(--gold-400); background:none; border:none; cursor:pointer; transition:color .15s; }
        .ddo-btn-add:hover { color: #16a34a; }
        .ddo-btn-add:disabled { opacity:.35; cursor:not-allowed; }
        .ddo-btn-remove { color: var(--gold-400); background:none; border:none; cursor:pointer; transition:color .15s; }
        .ddo-btn-remove:hover { color: #dc2626; }

        /* Status badges */
        .ddo-badge-complete {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 12px; border-radius: 999px;
            font-size: 11px; font-weight: 700;
            background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;
        }
        .ddo-badge-incomplete {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 12px; border-radius: 999px;
            font-size: 11px; font-weight: 700;
            background: var(--gold-50); color: var(--gold-600); border: 1px solid var(--gold-200);
        }

        /* Empty state */
        .ddo-empty { padding: 64px 24px; text-align: center; }
        .ddo-empty span { font-size: 13px; color: var(--text-muted); display:block; margin-top: 8px; }
    </style>

    <div class="ddo-card">
        {{-- Header --}}
        <div class="ddo-card-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="ddo-icon-wrap">
                    <x-heroicon-o-shield-check style="width:18px;height:18px;color:var(--gold-600);" />
                </div>
                <div>
                    <p class="ddo-title">Immunization Records</p>
                    <p class="ddo-subtitle">
                        {{ $records->count() }} {{ str('vaccine')->plural($records->count()) }} recorded
                    </p>
                </div>
            </div>
            {{ ($this->addVaccineAction)([]) }}
        </div>

        {{-- Table --}}
        <div style="overflow-x:auto;">
            <table class="ddo-table">
                <thead>
                <tr>
                    <th>Vaccine Details</th>
                    <th>Recommended Doses</th>
                    <th>Dose Schedule</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($records as $record)
                    @php $info = $vaccineInfo[$record->vaccine_description] ?? null; @endphp
                    <tr>

                        {{-- Vaccine Name --}}
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="ddo-vaccine-icon">
                                    <x-heroicon-o-shield-check style="width:15px;height:15px;color:var(--gold-500);" />
                                </div>
                                <span class="ddo-vaccine-name">{{ $record->vaccine_description }}</span>
                            </div>
                        </td>

                        {{-- Recommended Doses --}}
                        <td>
                            @if ($info)
                                <div style="display:flex;flex-direction:column;gap:6px;">
                                    <span class="ddo-dose-badge">
                                        <svg width="11" height="11" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $info['doses'] }} {{ $info['doses'] === 1 ? 'Dose' : 'Doses' }}
                                    </span>
                                    <div style="display:flex;flex-direction:column;gap:3px;">
                                        @foreach ($info['schedule'] as $index => $when)
                                            <div style="display:flex;align-items:center;gap:6px;">
                                                <span class="ddo-schedule-num">{{ $index + 1 }}</span>
                                                <span class="ddo-schedule-text">{{ $when }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <span class="ddo-no-ref">
                                    <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/>
                                    </svg>
                                    No reference data
                                </span>
                            @endif
                        </td>

                        {{-- Dose Schedule --}}
                        <td>
                            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= ($record->total_doses ?? 1))
                                        @php $field = "dose_$i"; $val = $record->$field; @endphp
                                        <div style="display:flex;flex-direction:column;align-items:flex-start;gap:4px;">
                                            <span class="ddo-dose-label">Dose {{ $i }}</span>

                                            @if($editingDoseRow === $record->id && $editingDoseField === $field)
                                                <div class="ddo-edit-box">
                                                    <input type="date" wire:model="doseDate">
                                                    <button wire:click="saveDose" class="ddo-edit-save">
                                                        <x-heroicon-s-check style="width:15px;height:15px;" />
                                                    </button>
                                                    <button wire:click="cancelEdit" class="ddo-edit-cancel">
                                                        <x-heroicon-s-x-mark style="width:15px;height:15px;" />
                                                    </button>
                                                </div>
                                            @else
                                                <button wire:click="startEdit({{ $record->id }}, '{{ $field }}', '{{ $val }}')"
                                                        class="ddo-date-btn">
                                                    @if(!empty($val))
                                                        <svg class="ddo-date-check" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span class="ddo-date-text">
                                                            {{ \Carbon\Carbon::parse($val)->format('M d, Y') }}
                                                        </span>
                                                    @else
                                                        <div class="ddo-empty-slot">
                                                            <x-heroicon-s-plus style="width:12px;height:12px;color:var(--gold-400);" />
                                                        </div>
                                                    @endif
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                @endfor

                                @php $vaccineSet = !empty($record->vaccine_description) && $record->vaccine_description !== 'Select vaccine'; @endphp
                                <div class="ddo-dose-controls">
                                    <button wire:click="incrementDose({{ $record->id }})"
                                            @if(!$vaccineSet) disabled @endif
                                            class="ddo-btn-add"
                                            title="{{ $vaccineSet ? 'Add dose' : 'Select a vaccine first' }}">
                                        <x-heroicon-s-plus-circle style="width:20px;height:20px;" />
                                    </button>
                                    <button wire:click="decrementDose({{ $record->id }})"
                                            class="ddo-btn-remove" title="Remove dose">
                                        <x-heroicon-s-minus-circle style="width:20px;height:20px;" />
                                    </button>
                                </div>
                            </div>
                        </td>

                        {{-- Status --}}
                        <td style="text-align:center;">
                            @if($record->status === 'complete')
                                <span class="ddo-badge-complete">
                                    <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                                    </svg>
                                    Complete
                                </span>
                            @else
                                <span class="ddo-badge-incomplete">
                                    <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd"/>
                                    </svg>
                                    Incomplete
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td style="text-align:center;">
                            {{ ($this->deleteAction)(['record' => $record->id]) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="ddo-empty">
                                <x-heroicon-o-shield-check style="width:32px;height:32px;color:var(--gold-300);margin:0 auto;" />
                                <span>No immunization records yet.</span>
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

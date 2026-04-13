<x-filament-panels::page>
    @php
        $perPage        = 5;

        //Children pagination data
        $totalChildren  = $record->childAssignments()->count();
        $totalChildPages = max(1, (int) ceil($totalChildren / $perPage));
        $assignments    = $record->childAssignments()->with('child')->latest()
                            ->forPage($this->childrenPage, $perPage)->get();

        //Visits pagination data
        $totalVisits    = $record->officeVisits()->count();
        $totalVisitPages = max(1, (int) ceil($totalVisits / $perPage));
        $visits         = $record->officeVisits()->with(['child', 'visitItems'])->latest('visit_date')
                            ->forPage($this->visitsPage, $perPage)->get();
    @endphp

    <style>
        /* Davao de Oro Gold Palette */
        .ddo {
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
        .dark .ddo {
            --surface:        #1c1008;
            --surface-2:      #251508;
            --border:         #78350f;
            --border-strong:  #b45309;
            --text-primary:   #fef3c7;
            --text-secondary: #fbbf24;
            --text-muted:     #d97706;
        }

        /* Cards & Panels */
        .ddo-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(180,83,9,0.08), 0 0 0 1px rgba(253,230,138,0.25);
        }
        .ddo-card-header {
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .ddo-card-header-left { display: flex; align-items: center; gap: 12px; }

        /* Hero */
        .ddo-hero {
            background: var(--surface);
            border-top: 4px solid var(--gold-500);
            border: 1px solid var(--border);
            border-top-width: 4px;
            border-radius: 16px;
            overflow: hidden;
            padding: 0 32px 28px;
            box-shadow: 0 1px 6px rgba(180,83,9,0.08);
        }
        .ddo-hero-name {
            font-size: 20px; font-weight: 800;
            color: var(--text-primary); letter-spacing: -.01em;
        }
        .ddo-hero-location {
            font-size: 13px; color: var(--text-muted);
            display: flex; align-items: center; gap: 5px; margin-top: 3px;
        }
        .ddo-hero-location svg { color: var(--gold-500); width:14px; height:14px; }

        .ddo-hero-stats {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 0; margin-top: 24px; padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        .ddo-hero-stat { text-align: center; padding: 4px 0; }
        .ddo-hero-stat + .ddo-hero-stat { border-left: 1px solid var(--border); }
        .ddo-hero-stat-val { font-size: 24px; font-weight: 900; color: var(--text-primary); }
        .ddo-hero-stat-lbl {
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--text-muted); margin-top: 2px;
        }

        /* Buttons */
        .ddo-btn-primary {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 7px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            background: var(--gold-500); color: #fff;
            border: none; cursor: pointer; text-decoration: none;
            transition: background .15s, box-shadow .15s;
            box-shadow: 0 1px 3px rgba(180,83,9,0.25);
        }
        .ddo-btn-primary:hover { background: var(--gold-600); }
        .ddo-btn-secondary {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 7px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            background: var(--surface); color: var(--text-secondary);
            border: 1px solid var(--border-strong);
            cursor: pointer; text-decoration: none;
            transition: background .15s;
        }
        .ddo-btn-secondary:hover { background: var(--surface-2); }

        /* Tabs */
        .ddo-tabs {
            display: flex; gap: 4px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px; padding: 4px;
            width: fit-content;
            box-shadow: 0 1px 4px rgba(180,83,9,0.07);
        }
        .ddo-tab {
            padding: 6px 18px; border-radius: 9px;
            font-size: 13px; font-weight: 600;
            border: none; cursor: pointer;
            transition: background .15s, color .15s, box-shadow .15s;
            color: var(--text-muted); background: transparent;
        }
        .ddo-tab:hover:not(.ddo-tab-active) { color: var(--text-secondary); background: var(--surface-2); }
        .ddo-tab-active {
            background: var(--gold-500); color: #fff;
            box-shadow: 0 1px 4px rgba(180,83,9,0.3);
        }

        /* Icon wrapper */
        .ddo-icon-wrap {
            width: 36px; height: 36px; border-radius: 10px;
            background: var(--gold-100); border: 1px solid var(--gold-200);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .ddo-icon-wrap svg { color: var(--gold-600); width: 18px; height: 18px; }

        /* Section headings */
        .ddo-section-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
        .ddo-section-sub   { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        /* Tables */
        .ddo-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .ddo-table thead tr {
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }
        .ddo-table thead th {
            padding: 11px 24px;
            text-align: left;
            font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .08em;
            color: var(--text-muted);
        }
        .ddo-table tbody tr { border-bottom: 1px solid var(--border); transition: background .12s; }
        .ddo-table tbody tr:last-child { border-bottom: none; }
        .ddo-table tbody tr:hover { background: var(--surface-2); }
        .ddo-table td { padding: 14px 24px; color: var(--text-secondary); vertical-align: middle; }
        .ddo-table td.ddo-td-primary { font-weight: 700; color: var(--text-primary); white-space: nowrap; }
        .ddo-table td.ddo-td-muted   { color: var(--text-muted); }

        /* Inline action links */
        .ddo-link-view {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 600;
            color: var(--gold-600); text-decoration: none;
            transition: color .12s;
        }
        .ddo-link-view:hover { color: var(--gold-700); }
        .ddo-link-danger {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 600;
            color: #dc2626; background: none; border: none; cursor: pointer;
            transition: color .12s; padding: 0;
        }
        .ddo-link-danger:hover { color: #991b1b; }
        .ddo-divider { color: var(--border-strong); }

        /* Empty states */
        .ddo-empty {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; padding: 64px 24px; text-align: center; gap: 8px;
        }
        .ddo-empty-icon {
            width: 60px; height: 60px; border-radius: 16px;
            background: var(--gold-100); border: 1px solid var(--gold-200);
            display: flex; align-items: center; justify-content: center; margin-bottom: 4px;
        }
        .ddo-empty-icon svg { color: var(--gold-400); width:28px; height:28px; }
        .ddo-empty-title { font-size: 14px; font-weight: 700; color: var(--text-primary); }
        .ddo-empty-sub   { font-size: 12px; color: var(--text-muted); }

        /* Pagination */
        .ddo-pagination {
            padding: 14px 24px;
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .ddo-pagination-info { font-size: 12px; color: var(--text-muted); }
        .ddo-page-btn {
            width: 28px; height: 28px; border-radius: 8px;
            font-size: 12px; font-weight: 600;
            border: none; cursor: pointer; transition: background .12s, color .12s;
            display: inline-flex; align-items: center; justify-content: center;
            background: transparent; color: var(--text-secondary);
        }
        .ddo-page-btn:hover:not(.ddo-page-active) { background: var(--surface-2); }
        .ddo-page-btn:disabled { opacity: .3; cursor: not-allowed; }
        .ddo-page-active { background: var(--gold-500) !important; color: #fff !important; }
        .ddo-page-nav {
            padding: 5px; border-radius: 8px;
            border: none; cursor: pointer; transition: background .12s;
            background: transparent; color: var(--text-muted);
            display: inline-flex; align-items: center;
        }
        .ddo-page-nav:hover { background: var(--surface-2); color: var(--text-secondary); }
        .ddo-page-nav:disabled { opacity: .3; cursor: not-allowed; }
        .ddo-page-group { display: flex; align-items: center; gap: 3px; }

        /* Modal */
        .ddo-modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(28,10,0,0.18);
            width: 100%; max-width: 420px;
            padding: 28px 24px 24px;
        }
        .ddo-modal-icon-danger {
            width: 56px; height: 56px; border-radius: 50%;
            background: #fee2e2; border: 1px solid #fecaca;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;
        }
        .ddo-modal-icon-danger svg { color: #dc2626; width:26px; height:26px; }
        .ddo-modal-title { font-size: 16px; font-weight: 800; color: var(--text-primary); text-align:center; }
        .ddo-modal-body  { font-size: 13px; color: var(--text-muted); text-align:center; margin-top:6px; line-height:1.6; }
        .ddo-modal-body strong { color: var(--text-primary); font-weight: 700; }
        .ddo-btn-danger {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 8px 22px; border-radius: 10px;
            font-size: 13px; font-weight: 700;
            background: #dc2626; color: #fff; border: none; cursor: pointer;
            transition: background .15s;
        }
        .ddo-btn-danger:hover { background: #b91c1c; }
    </style>

    <div class="ddo space-y-6">

        {{--HERO PROFILE--}}
        <div class="ddo-hero">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-5">

                {{-- Avatar + Name --}}
                <div class="flex items-end gap-5">
                    <div class="relative">
                        @if($record->profile_path)
                            <img src="{{ Storage::disk('public')->url($record->profile_path) }}"
                                 alt="{{ $record->firstname }}"
                                 class="w-24 h-24 rounded-2xl object-cover"
                                 style="box-shadow:0 0 0 4px var(--surface),0 2px 12px rgba(180,83,9,0.2);" />
                        @else
                            <img src="https://ui-avatars.com/api/?{{ http_build_query([
                                    'name'       => $record->firstname . ' ' . $record->lastname,
                                    'background' => 'd97706',
                                    'color'      => 'fff',
                                    'size'       => '128',
                                    'bold'       => 'true',
                                ]) }}"
                                 alt="{{ $record->firstname }}"
                                 class="w-24 h-24 rounded-2xl"
                                 style="box-shadow:0 0 0 4px var(--surface),0 2px 12px rgba(180,83,9,0.2);" />
                        @endif
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 rounded-full"
                              style="box-shadow:0 0 0 2px var(--surface);"></span>
                    </div>

                    <div class="pb-1">
                        <h1 class="ddo-hero-name">
                            {{ $record->firstname }}
                            {{ $record->middlename ? $record->middlename[0] . '.' : '' }}
                            {{ $record->lastname }}
                            {{ $record->suffix ?? '' }}
                        </h1>
                        <p class="ddo-hero-location">
                            <x-heroicon-m-map-pin />
                            {{ $record->barangay?->brgyDesc ?? 'Barangay Not assigned' }},
                            {{ $record->municipality?->citymunDesc ?? 'Municipality Not assigned' }},
                            {{ $record->municipality?->province?->provDesc ?? 'Province Not assigned' }}
                        </p>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 pb-1">
                    <a href="{{ \App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource::getUrl('edit', ['record' => $record]) }}"
                       class="ddo-btn-primary">
                        <x-heroicon-m-pencil style="width:15px;height:15px;" />
                        Edit Profile
                    </a>
                    <a href="{{ \App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource::getUrl('index') }}"
                       class="ddo-btn-secondary">
                        <x-heroicon-m-arrow-left style="width:15px;height:15px;" />
                        Back
                    </a>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="ddo-hero-stats">
                <div class="ddo-hero-stat">
                    <p class="ddo-hero-stat-val">{{ $totalChildren }}</p>
                    <p class="ddo-hero-stat-lbl">Children Assigned</p>
                </div>
                <div class="ddo-hero-stat">
                    <p class="ddo-hero-stat-val">{{ $totalVisits }}</p>
                    <p class="ddo-hero-stat-lbl">Total Visits</p>
                </div>
            </div>
        </div>

        {{-- TABS --}}
        <div class="ddo-tabs">
            <button wire:click="setTab('children')"
                    class="ddo-tab {{ $this->activeTab === 'children' ? 'ddo-tab-active' : '' }}">
                Assigned Children
            </button>
            <button wire:click="setTab('visits')"
                    class="ddo-tab {{ $this->activeTab === 'visits' ? 'ddo-tab-active' : '' }}">
                Visit History
            </button>
        </div>

        {{--TAB: ASSIGNED CHILDREN--}}
        @if ($this->activeTab === 'children')
            <div class="ddo-card">

                {{-- Header --}}
                <div class="ddo-card-header">
                    <div class="ddo-card-header-left">
                        <div class="ddo-icon-wrap">
                            <x-heroicon-o-users />
                        </div>
                        <div>
                            <h2 class="ddo-section-title">Assigned Children</h2>
                            <p class="ddo-section-sub">
                                {{ $totalChildren }} {{ str('child')->plural($totalChildren) }} under this BNS
                            </p>
                        </div>
                    </div>
                    <a href="{{ \App\Filament\Resources\OfficeChildAssigns\OfficeChildAssignResource::getUrl('create') }}"
                       class="ddo-btn-primary">
                        <x-heroicon-m-plus style="width:15px;height:15px;" />
                        Assign Child
                    </a>
                </div>

                @if($assignments->isEmpty() && $this->childrenPage === 1)
                    <div class="ddo-empty">
                        <div class="ddo-empty-icon">
                            <x-heroicon-o-face-smile />
                        </div>
                        <p class="ddo-empty-title">No children assigned yet</p>
                        <p class="ddo-empty-sub">Click "Assign Child" to get started</p>
                    </div>
                @else
                    <table class="ddo-table">
                        <thead>
                        <tr>
                            <th>Child</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Nutritional Status</th>
                            <th>Assigned Date</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($assignments as $assignment)
                            @php $child = $assignment->child; @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?{{ http_build_query([
                                                'name'       => $child->firstname . ' ' . $child->lastname,
                                                'background' => $child->sex === 'male' ? '3b82f6' : 'ec4899',
                                                'color'      => 'fff',
                                                'size'       => '64',
                                                'bold'       => 'true',
                                            ]) }}"
                                             class="w-9 h-9 rounded-xl" />
                                        <div>
                                            <p style="font-size:13px;font-weight:700;color:var(--text-primary);text-transform:uppercase;">
                                                {{ $child->firstname }} {{ $child->lastname }}
                                            </p>
                                            <p style="font-size:11px;color:var(--text-muted);">ID #{{ $child->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="ddo-td-muted">{{ $child->age ?? '—' }} yrs</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold
                                        {{ $child->sex === 'male'
                                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                            : 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400' }}">
                                        {{ ucfirst($child->sex ?? '—') }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $status = $child->nutritional_status ?? '';
                                        $sc = match(true) {
                                            str_contains(strtolower($status), 'normal')   => 'bg-green-100 text-green-700',
                                            str_contains(strtolower($status), 'severely') => 'bg-red-100 text-red-700',
                                            str_contains(strtolower($status), 'under')    => 'bg-yellow-100 text-yellow-700',
                                            str_contains(strtolower($status), 'over')     => 'bg-orange-100 text-orange-700',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $sc }}">
                                        {{ $status ?: '—' }}
                                    </span>
                                </td>
                                <td class="ddo-td-muted">
                                    {{ $assignment->assigned_date ? \Carbon\Carbon::parse($assignment->assigned_date)->format('M d, Y') : '—' }}
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ \App\Filament\Resources\AdoptedChildren\AdoptedChildResource::getUrl('view', ['record' => $child]) }}"
                                           class="ddo-link-view">
                                            <x-heroicon-m-eye style="width:14px;height:14px;" />
                                            View
                                        </a>
                                        <span class="ddo-divider">|</span>
                                        <button wire:click="confirmUnassign({{ $assignment->id }})"
                                                class="ddo-link-danger">
                                            <x-heroicon-m-user-minus style="width:14px;height:14px;" />
                                            Unassign
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{-- Children Pagination --}}
                    @if($totalChildPages > 1)
                        <div class="ddo-pagination">
                            <p class="ddo-pagination-info">
                                Page {{ $this->childrenPage }} of {{ $totalChildPages }}
                                &middot; {{ $totalChildren }} {{ str('record')->plural($totalChildren) }}
                            </p>
                            <div class="ddo-page-group">
                                <button wire:click="childrenPrevPage"
                                        @if($this->childrenPage <= 1) disabled @endif
                                        class="ddo-page-nav">
                                    <x-heroicon-m-chevron-left style="width:15px;height:15px;" />
                                </button>
                                @for($p = 1; $p <= $totalChildPages; $p++)
                                    <button wire:click="childrenGoToPage({{ $p }})"
                                            class="ddo-page-btn {{ $this->childrenPage === $p ? 'ddo-page-active' : '' }}">
                                        {{ $p }}
                                    </button>
                                @endfor
                                <button wire:click="childrenNextPage"
                                        @if($this->childrenPage >= $totalChildPages) disabled @endif
                                        class="ddo-page-nav">
                                    <x-heroicon-m-chevron-right style="width:15px;height:15px;" />
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        {{--TAB: VISIT HISTORY--}}
        @if ($this->activeTab === 'visits')
            <div class="ddo-card">

                {{-- Header --}}
                <div class="ddo-card-header">
                    <div class="ddo-card-header-left">
                        <div class="ddo-icon-wrap">
                            <x-heroicon-o-calendar-days />
                        </div>
                        <div>
                            <h2 class="ddo-section-title">Visit History</h2>
                            <p class="ddo-section-sub">All recorded visits by this BNS</p>
                        </div>
                    </div>
                </div>

                @if ($visits->isEmpty() && $this->visitsPage === 1)
                    <div class="ddo-empty">
                        <div class="ddo-empty-icon">
                            <x-heroicon-o-calendar-days />
                        </div>
                        <p class="ddo-empty-title">No visits recorded yet</p>
                        <p class="ddo-empty-sub">Visits made by this BNS will appear here</p>
                    </div>
                @else
                    <table class="ddo-table">
                        <thead>
                        <tr>
                            <th>Visit Date</th>
                            <th>Child</th>
                            <th>Address</th>
                            <th>Height</th>
                            <th>Weight</th>
                            <th>Status</th>
                            <th>Items Given</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($visits as $visit)
                            @php
                                $s = strtolower($visit->status ?? '');
                                $badge = match(true) {
                                    str_contains($s, 'severely') || str_contains($s, 'wasted')      => 'bg-red-100 text-red-700',
                                    str_contains($s, 'underweight') || str_contains($s, 'stunted')  => 'bg-yellow-100 text-yellow-700',
                                    str_contains($s, 'normal')                                       => 'bg-green-100 text-green-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr>
                                <td class="ddo-td-primary">
                                    {{ $visit->visit_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <img src="https://ui-avatars.com/api/?{{ http_build_query([
                                                'name'       => ($visit->child?->firstname ?? '?') . ' ' . ($visit->child?->lastname ?? ''),
                                                'background' => $visit->child?->sex === 'male' ? '3b82f6' : 'ec4899',
                                                'color'      => 'fff',
                                                'size'       => '48',
                                                'bold'       => 'true',
                                            ]) }}"
                                             class="w-7 h-7 rounded-lg" />
                                        <span style="font-weight:600;color:var(--text-primary);">
                                            {{ $visit->child?->firstname }} {{ $visit->child?->lastname }}
                                        </span>
                                    </div>
                                </td>
                                <td class="ddo-td-muted" style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $visit->visit_address ?? '—' }}
                                </td>
                                <td class="ddo-td-muted">{{ $visit->height ? $visit->height . ' cm' : '—' }}</td>
                                <td class="ddo-td-muted">{{ $visit->weight ? $visit->weight . ' kg' : '—' }}</td>
                                <td>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $badge }}">
                                        {{ $visit->status ?? '—' }}
                                    </span>
                                </td>
                                <td class="ddo-td-muted" style="font-size:12px;">
                                    @if ($visit->visitItems->count())
                                        {{ $visit->visitItems->pluck('Item_description')->implode(', ') }}
                                    @else
                                        <span style="color:var(--border-strong);">None</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {{-- Visits Pagination --}}
                    @if($totalVisitPages > 1)
                        <div class="ddo-pagination">
                            <p class="ddo-pagination-info">
                                Page {{ $this->visitsPage }} of {{ $totalVisitPages }}
                                &middot; {{ $totalVisits }} {{ str('record')->plural($totalVisits) }}
                            </p>
                            <div class="ddo-page-group">
                                <button wire:click="visitsPrevPage"
                                        @if($this->visitsPage <= 1) disabled @endif
                                        class="ddo-page-nav">
                                    <x-heroicon-m-chevron-left style="width:15px;height:15px;" />
                                </button>
                                @for($p = 1; $p <= $totalVisitPages; $p++)
                                    <button wire:click="visitsGoToPage({{ $p }})"
                                            class="ddo-page-btn {{ $this->visitsPage === $p ? 'ddo-page-active' : '' }}">
                                        {{ $p }}
                                    </button>
                                @endfor
                                <button wire:click="visitsNextPage"
                                        @if($this->visitsPage >= $totalVisitPages) disabled @endif
                                        class="ddo-page-nav">
                                    <x-heroicon-m-chevron-right style="width:15px;height:15px;" />
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @endif

    </div>

    {{-- UNASSIGN CONFIRMATION MODAL--}}
    @if ($this->confirmingUnassignId !== null)
        @php
            $pendingAssignment = $record->childAssignments()->with('child')->find($this->confirmingUnassignId);
            $pendingChild      = $pendingAssignment?->child;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(28,10,0,0.55);">
            <div class="ddo ddo-modal">
                <div class="ddo-modal-icon-danger">
                    <x-heroicon-o-user-minus />
                </div>
                <h3 class="ddo-modal-title">Unassign Child</h3>
                <p class="ddo-modal-body">
                    Are you sure you want to unassign
                    <strong>{{ $pendingChild?->firstname }} {{ $pendingChild?->lastname }}</strong>
                    from this BNS? This action cannot be undone.
                </p>
                <div class="flex gap-3 justify-center mt-6">
                    <button wire:click="cancelUnassign" class="ddo-btn-secondary">
                        Cancel
                    </button>
                    <button wire:click="unassignChild" class="ddo-btn-danger">
                        <span wire:loading.remove wire:target="unassignChild">Yes, Unassign</span>
                        <span wire:loading wire:target="unassignChild">Unassigning…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</x-filament-panels::page>

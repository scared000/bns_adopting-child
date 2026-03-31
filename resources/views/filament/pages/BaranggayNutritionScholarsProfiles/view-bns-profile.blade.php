<x-filament-panels::page>
    <div class="space-y-6">

        {{-- HERO PROFILE SECTION --}}
        <div class="relative rounded-2xl overflow-hidden shadow-sm">
            <div class="bg-white dark:bg-gray-900 px-8 pb-7 border-t-4 border-orange-500">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-4">

                    {{-- Avatar --}}
                    <div class="flex items-end gap-5">
                        <div class="relative">
                            @if($record->profile_path)
                                <img src="{{ Storage::disk('public')->url($record->profile_path) }}"
                                     alt="{{ $record->firstname }}"
                                     class="w-24 h-24 rounded-2xl object-cover ring-4 ring-white dark:ring-gray-900 shadow-lg" />
                            @else
                                <img src="https://ui-avatars.com/api/?{{ http_build_query([
                                        'name'       => $record->firstname . ' ' . $record->lastname,
                                        'background' => 'f97316',
                                        'color'      => 'fff',
                                        'size'       => '128',
                                        'bold'       => 'true',
                                    ]) }}"
                                     alt="{{ $record->firstname }}"
                                     class="w-24 h-24 rounded-2xl ring-4 ring-white dark:ring-gray-900 shadow-lg" />
                            @endif
                            <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 rounded-full ring-2 ring-white dark:ring-gray-900"></span>
                        </div>

                        <div class="pb-1">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ $record->firstname }}
                                {{ $record->middlename ? $record->middlename[0] . '.' : '' }}
                                {{ $record->lastname }}
                                {{ $record->suffix ?? '' }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-orange-500" />
                                {{ $record->barangay?->brgyDesc ?? 'Barangay Not assigned' }},
                                {{ $record->municipality?->citymunDesc ?? 'Municipality Not assigned' }},
                                {{ $record->municipality?->province?->provDesc ?? 'Province Not assigned' }}
                            </p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2 pb-1">
                        <a href="{{ \App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource::getUrl('edit', ['record' => $record]) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white transition-colors shadow-sm">
                            <x-heroicon-m-pencil class="w-4 h-4" />
                            Edit Profile
                        </a>
                        <a href="{{ \App\Filament\Resources\BaranggayNutritionScholars\BaranggayNutritionScholarsResource::getUrl('index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <x-heroicon-m-arrow-left class="w-4 h-4" />
                            Back
                        </a>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $record->childAssignments()->count() }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 uppercase tracking-wide font-medium">
                            Children Assigned
                        </p>
                    </div>
                    <div class="text-center border-l border-gray-100 dark:border-gray-800">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $record->officeVisits()->count() }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 uppercase tracking-wide font-medium">
                            Total Visits
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABS --}}
        <div class="flex gap-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-1 w-fit shadow-sm">
            <button wire:click="setTab('children')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                        {{ $this->activeTab === 'children'
                            ? 'bg-orange-500 text-white shadow'
                            : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                Assigned Children
            </button>
            <button wire:click="setTab('visits')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                        {{ $this->activeTab === 'visits'
                            ? 'bg-orange-500 text-white shadow'
                            : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                Visit History
            </button>
        </div>

        {{-- TAB: ASSIGNED CHILDREN --}}
        @if ($this->activeTab === 'children')
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                            <x-heroicon-o-users class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Assigned Children</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $record->childAssignments()->count() }} {{ str('child')->plural($record->childAssignments()->count()) }} under this BNS
                            </p>
                        </div>
                    </div>
                    <a href="{{ \App\Filament\Resources\OfficeChildAssigns\OfficeChildAssignResource::getUrl('create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-orange-500 hover:bg-orange-600 text-white transition-colors shadow-sm">
                        <x-heroicon-m-plus class="w-4 h-4" />
                        Assign Child
                    </a>
                </div>

                @php $assignments = $record->childAssignments()->with('child')->latest()->get(); @endphp

                @if($assignments->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <x-heroicon-o-face-smile class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">No children assigned yet</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click "Assign Child" to get started</p>
                    </div>
                @else
                    <table class="w-full">
                        <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Child</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Age</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sex</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nutritional Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($assignments as $assignment)
                            @php $child = $assignment->child; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-4">
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
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white uppercase">
                                                {{ $child->firstname }} {{ $child->lastname }}
                                            </p>
                                            <p class="text-xs text-gray-400">ID #{{ $child->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $child->age ?? '—' }} yrs</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium
                                        {{ $child->sex === 'male'
                                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                                            : 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400' }}">
                                        {{ ucfirst($child->sex ?? '—') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $sc }}">
                                        {{ $status ?: '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $assignment->assigned_date ? \Carbon\Carbon::parse($assignment->assigned_date)->format('M d, Y') : '—' }}
                                </td>

                                {{-- ACTION column: View + Unassign --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        {{-- View child --}}
                                        <a href="{{ \App\Filament\Resources\AdoptedChildren\AdoptedChildResource::getUrl('view', ['record' => $child]) }}"
                                           class="inline-flex items-center gap-1.5 text-xs font-medium text-orange-600 hover:text-orange-700 transition-colors">
                                            <x-heroicon-m-eye class="w-3.5 h-3.5" />
                                            View
                                        </a>

                                        <span class="text-gray-300 dark:text-gray-600">|</span>

                                        {{-- Unassign button — triggers confirmation --}}
                                        <button wire:click="confirmUnassign({{ $assignment->id }})"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium text-red-500 hover:text-red-700 transition-colors">
                                            <x-heroicon-m-user-minus class="w-3.5 h-3.5" />
                                            Unassign
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        {{-- TAB: VISIT HISTORY --}}
        @if ($this->activeTab === 'visits')
            @php
                $visits = $record->officeVisits()->with(['child', 'visitItems'])->latest('visit_date')->get();
            @endphp

            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <x-heroicon-o-calendar-days class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Visit History</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">All recorded visits by this BNS</p>
                    </div>
                </div>

                @if ($visits->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <x-heroicon-o-calendar-days class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">No visits recorded yet</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Visits made by this BNS will appear here</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="px-6 py-3 text-left">Visit Date</th>
                            <th class="px-6 py-3 text-left">Child</th>
                            <th class="px-6 py-3 text-left">Address</th>
                            <th class="px-6 py-3 text-left">Height</th>
                            <th class="px-6 py-3 text-left">Weight</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Items Given</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($visits as $visit)
                            @php
                                $s = strtolower($visit->status ?? '');
                                $badge = match(true) {
                                    str_contains($s, 'severely') || str_contains($s, 'wasted') => 'bg-red-100 text-red-700',
                                    str_contains($s, 'underweight') || str_contains($s, 'stunted') => 'bg-yellow-100 text-yellow-700',
                                    str_contains($s, 'normal') => 'bg-green-100 text-green-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-4 font-semibold text-gray-800 dark:text-white whitespace-nowrap">
                                    {{ $visit->visit_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <img src="https://ui-avatars.com/api/?{{ http_build_query([
                                                    'name'       => ($visit->child?->firstname ?? '?') . ' ' . ($visit->child?->lastname ?? ''),
                                                    'background' => $visit->child?->sex === 'male' ? '3b82f6' : 'ec4899',
                                                    'color'      => 'fff',
                                                    'size'       => '48',
                                                    'bold'       => 'true',
                                                ]) }}"
                                             class="w-7 h-7 rounded-lg" />
                                        <span class="font-medium text-gray-800 dark:text-white">
                                            {{ $visit->child?->firstname }} {{ $visit->child?->lastname }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ $visit->visit_address ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ $visit->height ? $visit->height . ' cm' : '—' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ $visit->weight ? $visit->weight . ' kg' : '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $badge }}">
                                        {{ $visit->status ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">
                                    @if ($visit->visitItems->count())
                                        {{ $visit->visitItems->pluck('Item_description')->implode(', ') }}
                                    @else
                                        <span class="text-gray-400">None</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

    </div>

    {{-- ── UNASSIGN CONFIRMATION MODAL ─────────────────────────────────────── --}}
    @if ($this->confirmingUnassignId !== null)
        @php
            $pendingAssignment = $record->childAssignments()->with('child')->find($this->confirmingUnassignId);
            $pendingChild      = $pendingAssignment?->child;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5);">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-5">

                {{-- Icon + Heading --}}
                <div class="flex flex-col items-center text-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <x-heroicon-o-user-minus class="w-7 h-7 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Unassign Child</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Are you sure you want to unassign
                            <span class="font-semibold text-gray-800 dark:text-white">
                                {{ $pendingChild?->firstname }} {{ $pendingChild?->lastname }}
                            </span>
                            from this BNS? This action cannot be undone.
                        </p>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 justify-center">
                    <button wire:click="cancelUnassign"
                            class="px-5 py-2 rounded-xl text-sm font-medium border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="unassignChild"
                            class="px-5 py-2 rounded-xl text-sm font-medium bg-red-500 hover:bg-red-600 text-white transition-colors shadow-sm">
                        <span wire:loading.remove wire:target="unassignChild">Yes, Unassign</span>
                        <span wire:loading wire:target="unassignChild">Unassigning...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</x-filament-panels::page>

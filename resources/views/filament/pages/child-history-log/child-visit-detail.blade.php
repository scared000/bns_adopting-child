<x-filament-panels::page>
    @php
        $child = $this->child;
        $visits = $child->officeVisits->sortByDesc('visit_date');
        $totalVisits= $visits->count();
        $bns = $child->officeAssignments->first()?->bns;
        $initials = strtoupper(substr($child->firstname, 0, 1) . substr($child->lastname, 0, 1));
    @endphp

    {{-- HERO PROFILE SECTION --}}
    <div class="space-y-6">
        <div class="relative rounded-2xl overflow-hidden shadow-sm">
            <div class="bg-white dark:bg-gray-900 px-8 pb-7 border-t-4 border-orange-500">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-4">

                    {{-- Avatar --}}
                    <div class="flex items-end gap-5">
                        <div class="relative">
                            @if($child->profile_path)
                                <img
                                    src="{{ Storage::url($child->profile_path) }}"
                                    alt="{{ $child->firstname }}"
                                    class="w-24 h-24 rounded-2xl object-cover ring-4 ring-white dark:ring-gray-900 shadow-lg"
                                />
                            @else
                                <img
                                    src="https://ui-avatars.com/api/?{{ http_build_query([
                                        'name' => $child->firstname . ' ' . $child->lastname,
                                        'background' => $child->sex === 'male' ? '3b82f6' : 'ec4899',
                                        'color' => 'fff',
                                        'size' => '128',
                                        'bold' => 'true',
                                    ]) }}"
                                    alt="{{ $child->firstname }}"
                                    class="w-24 h-24 rounded-2xl ring-4 ring-white dark:ring-gray-900 shadow-lg"
                                />
                            @endif
                        </div>

                        <div class="pb-1">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ $child->firstname }} {{ $child->lastname }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-orange-500"/>
                                {{ $child->purok }},
                                {{ $child->barangay?->brgyDesc ?? 'No barangay' }},
                                {{ $child->municipality?->citymunDesc ?? 'No municipality' }},
                                {{ $child->municipality?->province?->provDesc ?? 'No province' }}
                            </p>
                        </div>
                    </div>

                    {{-- Back Button --}}
                    <div class="flex items-center gap-2 pb-1">
                        <a href="{{ route('filament.admin.pages.child-visit-log') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium
                                  border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400
                                  hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <x-heroicon-m-arrow-left class="w-4 h-4"/>
                            Back
                        </a>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalVisits }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 uppercase tracking-wide font-medium">
                            Total Visits
                        </p>
                    </div>
                    <div class="text-center border-l border-gray-100 dark:border-gray-800">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $bns ? $bns->firstname . ' ' . $bns->lastname : '—' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 uppercase tracking-wide font-medium">
                            Child Assigned BNS
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div
            class="flex gap-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-1 w-fit shadow-sm">
            <button wire:click="setTab('history')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $this->activeTab === 'history'
                           ? 'bg-orange-500 text-white shadow'
                           : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    Visit History
            </button>
            <button wire:click="setTab('items')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $this->activeTab === 'items'
                           ? 'bg-orange-500 text-white shadow'
                           : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    Visit Items
            </button>
            <button wire:click="setTab('activity')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                        {{ $this->activeTab === 'activity'
                           ? 'bg-orange-500 text-white shadow'
                           : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                Activity Log
            </button>
        </div>

        {{-- Visit History Tab --}}
        @if ($this->activeTab === 'history')
            <div
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Table Header --}}
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <x-heroicon-o-calendar-days class="w-5 h-5 text-orange-600 dark:text-orange-400"/>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Visit History</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">All recorded visits for this child</p>
                    </div>
                </div>

                <table class="w-full">
                    <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Visit Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Address
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Height
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Weight
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Nutritional Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Office
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            BNS
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($visits as $visit)
                        @php
                            $s = strtolower($visit->status ?? '');
                            $badge = match(true) {
                                str_contains($s, 'severely') ||
                                str_contains($s, 'wasted')   ||
                                str_contains($s, 'obese')    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                str_contains($s, 'overweight') ||
                                str_contains($s, 'underweight') ||
                                str_contains($s, 'stunted')     ||
                                str_contains($s, 'at risk')     => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                str_contains($s, 'normal')      => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $visit->visit_date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                {{ $visit->visit_address ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $visit->height ? $visit->height . ' cm' : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $visit->weight ? $visit->weight . ' kg' : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $badge }}">
                                    {{ $visit->status ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400 text-xs">  {{-- ← add this --}}
                                @if ($visit->office)
                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ $visit->office->short_name ?? $visit->office->office ?? '—' }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $visit->bns ? $visit->bns->firstname . ' ' . $visit->bns->lastname : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                        <x-heroicon-o-calendar-days class="w-8 h-8 text-gray-400"/>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">No visits recorded
                                        yet</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Visits will appear here
                                        once recorded</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Visit Items Tab --}}
        @if ($this->activeTab === 'items')
            <div
                class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Table Header --}}
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <x-heroicon-o-archive-box class="w-5 h-5 text-orange-600 dark:text-orange-400"/>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Visit Items Log</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Items distributed to this child across all
                            visits</p>
                    </div>
                </div>

                <table class="w-full">
                    <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Visit Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Item Description
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Quantity
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Amount
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($visits as $visit)
                        @foreach ($visit->visitItems as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $visit->visit_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $item->Item_description ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center text-gray-600 dark:text-gray-400">
                                    {{ $item->item_quantity ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ $item->item_amount ? '₱' . number_format($item->item_amount, 2) : 'Free' }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                        <x-heroicon-o-archive-box class="w-8 h-8 text-gray-400"/>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">No items recorded
                                        yet</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Distributed items will
                                        appear here</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Activity Log Tab --}}
        @if ($this->activeTab === 'activity')
            @php $activities = $this->activities; @endphp

            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Section Header --}}
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <x-heroicon-o-clock class="w-5 h-5 text-purple-600 dark:text-purple-400"/>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Activity Log</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $activities->count() }} {{ Str::plural('event', $activities->count()) }} recorded
                        </p>
                    </div>
                </div>

                @if ($activities->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                            <x-heroicon-o-clock class="w-8 h-8 text-gray-400"/>
                        </div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">No activity recorded yet</p>
                        <p class="text-xs text-gray-500 mt-1">Changes to this child's records will appear here</p>
                    </div>
                @else
                    <div class="px-6 py-6">
                        <ol class="relative border-l-2 border-orange-200 dark:border-orange-900/50 space-y-0">
                            @foreach ($activities as $activity)
                                @php
                                    $old = collect($activity->properties->get('old', []));
                                    $new = collect($activity->properties->get('attributes', []));
                                    $trackedKeys = ['weight', 'height', 'status', 'visit_date', 'firstname', 'lastname'];
                                    $changedKeys = $new->keys()->intersect(
                                        $old->isNotEmpty() ? $old->keys() : $new->keys()
                                    )->intersect($trackedKeys);

                                    $eventColors = match($activity->event) {
                                        'created' => ['dot' => 'bg-green-500', 'badge' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'],
                                        'updated' => ['dot' => 'bg-orange-500', 'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400'],
                                        'deleted' => ['dot' => 'bg-red-500',   'badge' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400'],
                                        default   => ['dot' => 'bg-gray-400',  'badge' => 'bg-gray-100 text-gray-600'],
                                    };
                                @endphp

                                <li class="mb-8 ml-6 last:mb-0">
                                    {{-- Timeline dot --}}
                                    <span class="absolute -left-[9px] flex items-center justify-center
                                         w-4 h-4 rounded-full ring-4 ring-white dark:ring-gray-900
                                         {{ $eventColors['dot'] }}">
                            </span>

                                    {{-- Card --}}
                                    <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-700 p-4">

                                        {{-- Header row --}}
                                        <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                                            <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $eventColors['badge'] }}">
                                            {{ ucfirst($activity->event ?? 'action') }}
                                        </span>
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ $activity->description }}
                                        </span>
                                            </div>
                                            <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                                <x-heroicon-m-clock class="w-3.5 h-3.5"/>
                                                <time datetime="{{ $activity->created_at->toIso8601String() }}">
                                                    {{ $activity->created_at->format('M d, Y · h:i A') }}
                                                </time>
                                            </div>
                                        </div>

                                        {{-- Causer --}}
                                        @if ($activity->causer)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">
                                            By: {{ $activity->causer->name ?? 'System' }}
                                        </span>
                                            </p>
                                        @endif

                                        {{-- Changed attributes: old → new --}}
                                        @if ($changedKeys->isNotEmpty())
                                            <div class="space-y-2">
                                                @foreach ($changedKeys as $key)
                                                    @php
                                                        $oldVal = $old->get($key);
                                                        $newVal = $new->get($key);
                                                        $label  = match($key) {
                                                            'weight'     => 'Weight',
                                                            'height'     => 'Height',
                                                            'status'     => 'Status',
                                                            'visit_date' => 'Visit Date',
                                                            'firstname'  => 'First Name',
                                                            'lastname'   => 'Last Name',
                                                            default      => ucfirst(str_replace('_', ' ', $key)),
                                                        };
                                                        $unit = match($key) {
                                                            'weight' => ' kg',
                                                            'height' => ' cm',
                                                            default  => '',
                                                        };
                                                    @endphp

                                                    <div class="flex items-center gap-2 text-xs flex-wrap">
                                                <span class="w-20 shrink-0 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                                    {{ $label }}
                                                </span>

                                                        {{-- Old value --}}
                                                        @if ($old->isNotEmpty() && $oldVal !== null)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md
                                                                 bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400
                                                                 line-through font-mono">
                                                        {{ $oldVal }}{{ $unit }}
                                                    </span>
                                                            <x-heroicon-m-arrow-right class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                                                        @endif

                                                        {{-- New value --}}
                                                        @if ($newVal !== null)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md
                                                                 bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                                                 font-mono font-semibold">
                                                        {{ $newVal }}{{ $unit }}
                                                    </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>

                                            {{-- Created event: just list all new values --}}
                                        @elseif ($activity->event === 'created' && $new->isNotEmpty())
                                            <div class="space-y-1">
                                                @foreach ($new->only($trackedKeys) as $key => $val)
                                                    @if ($val !== null)
                                                        <div class="flex items-center gap-2 text-xs">
                                                    <span class="w-20 shrink-0 font-semibold text-gray-500 uppercase tracking-wide">
                                                        {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                    </span>
                                                            <span class="px-2 py-0.5 rounded-md bg-green-50 text-green-700
                                                                 dark:bg-green-900/30 dark:text-green-400 font-mono">
                                                        {{ $val }}
                                                    </span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-400 italic">No tracked field changes recorded.</p>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>
        @endif

    </div>
</x-filament-panels::page>
